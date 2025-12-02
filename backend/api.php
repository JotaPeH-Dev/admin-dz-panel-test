<?php
/**
 * Sistema de Chat D&Z - Backend Consolidado
 * Todos os endpoints e funcionalidades em um arquivo
 */

// =================== CONFIGURAÇÕES ===================
define('GROQ_API_KEY', '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');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL', 'llama-3.3-70b-versatile');
define('GROQ_TEMPERATURE', 0.7);
define('GROQ_MAX_TOKENS', 1000);
define('SISTEMA_PROMPT', 'Você é um assistente de atendimento ao cliente da empresa D&Z. 
Seja prestativo, educado e objetivo. Tente resolver as dúvidas dos clientes da melhor forma possível.
Se não conseguir resolver completamente, sugira que o cliente fale com um atendente humano.
Sempre responda em português brasileiro de forma amigável e profissional. Mantenha as respostas concisas e úteis.
Não mencione que você é uma IA, apenas ajude como um atendente da empresa.');

// =================== CONEXÃO BANCO ===================
require_once 'conexao.php';

// =================== CLASSE GROQ API ===================
class GroqAPI {
    private $api_key;
    private $base_url;
    
    public function __construct() {
        $this->api_key = GROQ_API_KEY;
        $this->base_url = GROQ_API_URL;
    }
    
    public function enviarMensagem($mensagem, $contexto = '') {
        if (empty($this->api_key)) {
            return ['success' => false, 'error' => 'API Key não configurada'];
        }
        
        $messages = [
            ['role' => 'system', 'content' => SISTEMA_PROMPT]
        ];
        
        if (!empty($contexto)) {
            $messages[] = ['role' => 'assistant', 'content' => 'Contexto: ' . $contexto];
        }
        
        $messages[] = ['role' => 'user', 'content' => $mensagem];
        
        $data = [
            'model' => GROQ_MODEL,
            'messages' => $messages,
            'temperature' => GROQ_TEMPERATURE,
            'max_tokens' => GROQ_MAX_TOKENS,
            'stream' => false
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->base_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'error' => 'Erro de conexão: ' . $error];
        }
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return ['success' => true, 'message' => trim($result['choices'][0]['message']['content'])];
            }
        }
        
        $error_data = json_decode($response, true);
        return ['success' => false, 'error' => "HTTP $http_code: " . ($error_data['error']['message'] ?? 'Erro desconhecido')];
    }
}

// =================== CLASSE CHAT MANAGER ===================
class ChatManager {
    private $conexao;
    private $groq;
    
    public function __construct($conexao) {
        $this->conexao = $conexao;
        $this->groq = new GroqAPI();
    }
    
    public function obterConversas($status = null) {
        $sql = "SELECT c.*, 
                       (SELECT conteudo FROM mensagens WHERE conversa_id = c.id ORDER BY timestamp DESC LIMIT 1) as ultima_mensagem,
                       (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id AND lida = FALSE AND remetente != 'admin') as nao_lidas,
                       (SELECT COUNT(*) FROM mensagens WHERE conversa_id = c.id) as total_mensagens
                FROM conversas c
                ORDER BY c.created_at DESC";
        
        if ($status) {
            $sql = str_replace("ORDER BY", "WHERE c.status = ? ORDER BY", $sql);
            $stmt = $this->conexao->prepare($sql);
            $stmt->bind_param("s", $status);
        } else {
            $stmt = $this->conexao->prepare($sql);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function obterMensagens($conversa_id) {
        $sql = "SELECT * FROM mensagens WHERE conversa_id = ? ORDER BY timestamp ASC";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("i", $conversa_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function processarMensagemUsuario($conversa_id, $mensagem) {
        // Salvar mensagem do usuário
        $sql = "INSERT INTO mensagens (conversa_id, remetente, conteudo) VALUES (?, 'usuario', ?)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("is", $conversa_id, $mensagem);
        $stmt->execute();
        
        // Obter contexto
        $contexto = $this->obterContextoConversa($conversa_id);
        
        // Usar IA
        $resposta_ia = $this->groq->enviarMensagem($mensagem, $contexto);
        
        if ($resposta_ia['success']) {
            $resposta_texto = $resposta_ia['message'];
        } else {
            $resposta_texto = "Obrigado por sua mensagem! Nosso sistema está com alta demanda. Um atendente entrará em contato em breve para ajudá-lo. 👨‍💼";
        }
        
        // Salvar resposta
        $sql = "INSERT INTO mensagens (conversa_id, remetente, conteudo) VALUES (?, 'ia', ?)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("is", $conversa_id, $resposta_texto);
        $stmt->execute();
        
        return $resposta_texto;
    }
    
    public function obterContextoConversa($conversa_id, $limite = 6) {
        $sql = "SELECT remetente, conteudo FROM mensagens WHERE conversa_id = ? ORDER BY timestamp DESC LIMIT ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("ii", $conversa_id, $limite);
        $stmt->execute();
        $mensagens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $contexto = "";
        foreach (array_reverse($mensagens) as $msg) {
            $remetente = $msg['remetente'] === 'usuario' ? 'Cliente' : 'Atendente';
            $contexto .= "$remetente: " . $msg['conteudo'] . "\n";
        }
        
        return $contexto;
    }
    
    public function enviarMensagemAdmin($conversa_id, $mensagem, $admin_id) {
        $sql = "INSERT INTO mensagens (conversa_id, remetente, conteudo) VALUES (?, 'admin', ?)";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("is", $conversa_id, $mensagem);
        return $stmt->execute();
    }
    
    public function escalarParaHumano($conversa_id) {
        $sql = "UPDATE conversas SET status = 'aguardando_humano' WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("i", $conversa_id);
        return $stmt->execute();
    }
    
    public function resolverConversa($conversa_id) {
        $sql = "UPDATE conversas SET status = 'resolvida' WHERE id = ?";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bind_param("i", $conversa_id);
        return $stmt->execute();
    }
    
    public function obterEstatisticas() {
        $stats = [];
        
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM conversas");
        $stats['total_conversas'] = $result->fetch_assoc()['total'];
        
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM mensagens WHERE DATE(timestamp) = CURDATE()");
        $stats['mensagens_hoje'] = $result->fetch_assoc()['total'];
        
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM conversas WHERE status = 'ativa'");
        $stats['conversas_ativas'] = $result->fetch_assoc()['total'];
        
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM mensagens WHERE lida = FALSE AND remetente != 'admin'");
        $stats['nao_lidas'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
}

// =================== INICIALIZAÇÃO ===================
$chat_manager = new ChatManager($conexao);

// Headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// =================== ROTEAMENTO ===================
$endpoint = $_GET['endpoint'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($endpoint) {
    case 'client':
        handleClientEndpoint($chat_manager, $action);
        break;
    case 'admin':
        handleAdminEndpoint($chat_manager, $action);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Endpoint não encontrado']);
        break;
}

// =================== HANDLERS ===================
function handleClientEndpoint($chat_manager, $action) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($action) {
            case 'start_conversation':
                $nome = trim($input['nome'] ?? '');
                $email = trim($input['email'] ?? '');
                $mensagem = trim($input['mensagem'] ?? '');
                
                if (!$nome || !$email || !$mensagem) {
                    echo json_encode(['success' => false, 'error' => 'Por favor, preencha todos os campos']);
                    exit;
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'error' => 'Email inválido']);
                    exit;
                }
                
                try {
                    global $conexao;
                    $sql = "INSERT INTO conversas (usuario_nome, usuario_email, status) VALUES (?, ?, 'ativa')";
                    $stmt = $conexao->prepare($sql);
                    $stmt->bind_param("ss", $nome, $email);
                    $stmt->execute();
                    
                    $conversa_id = $conexao->insert_id;
                    
                    if ($conversa_id) {
                        $resposta_ia = $chat_manager->processarMensagemUsuario($conversa_id, $mensagem);
                        echo json_encode([
                            'success' => true,
                            'conversa_id' => $conversa_id,
                            'resposta_ia' => $resposta_ia
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Erro ao criar conversa']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;
                
            case 'send_message':
                $conversa_id = intval($input['conversa_id'] ?? 0);
                $mensagem = trim($input['mensagem'] ?? '');
                
                if (!$conversa_id || !$mensagem) {
                    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
                    exit;
                }
                
                try {
                    global $conexao;
                    $sql = "SELECT id, status FROM conversas WHERE id = ?";
                    $stmt = $conexao->prepare($sql);
                    $stmt->bind_param("i", $conversa_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $conversa = $result->fetch_assoc();
                    
                    if (!$conversa) {
                        echo json_encode(['success' => false, 'error' => 'Conversa não encontrada']);
                        exit;
                    }
                    
                    if ($conversa['status'] === 'resolvida') {
                        echo json_encode([
                            'success' => true,
                            'resposta' => 'Esta conversa foi finalizada. Se precisar de mais ajuda, inicie uma nova conversa.'
                        ]);
                        exit;
                    }
                    
                    if ($conversa['status'] === 'aguardando_humano') {
                        $sql = "INSERT INTO mensagens (conversa_id, remetente, conteudo) VALUES (?, 'usuario', ?)";
                        $stmt = $conexao->prepare($sql);
                        $stmt->bind_param("is", $conversa_id, $mensagem);
                        $stmt->execute();
                        
                        echo json_encode([
                            'success' => true,
                            'resposta' => 'Sua mensagem foi recebida. Um atendente humano responderá em breve. 👨‍💼'
                        ]);
                        exit;
                    }
                    
                    $resposta_ia = $chat_manager->processarMensagemUsuario($conversa_id, $mensagem);
                    echo json_encode(['success' => true, 'resposta' => $resposta_ia]);
                    
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Ação não encontrada']);
                break;
        }
    }
}

function handleAdminEndpoint($chat_manager, $action) {
    session_start();
    
    if (!isset($_SESSION['usuario_logado'])) {
        echo json_encode(['success' => false, 'error' => 'Não autorizado']);
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        switch ($action) {
            case 'send_admin_message':
                $conversa_id = intval($input['conversa_id'] ?? 0);
                $mensagem = trim($input['mensagem'] ?? '');
                $admin_id = $_SESSION['usuario_logado'];
                
                if (!$conversa_id || !$mensagem) {
                    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
                    exit;
                }
                
                $resultado = $chat_manager->enviarMensagemAdmin($conversa_id, $mensagem, $admin_id);
                echo json_encode(['success' => $resultado]);
                break;
                
            case 'escalar_humano':
                $conversa_id = intval($input['conversa_id'] ?? 0);
                if (!$conversa_id) {
                    echo json_encode(['success' => false, 'error' => 'ID da conversa é obrigatório']);
                    exit;
                }
                
                $resultado = $chat_manager->escalarParaHumano($conversa_id);
                $mensagem_sistema = "Esta conversa foi escalada para atendimento humano. Um especialista entrará em contato em breve.";
                $chat_manager->enviarMensagemAdmin($conversa_id, $mensagem_sistema, 'sistema');
                echo json_encode(['success' => $resultado]);
                break;
                
            case 'resolver_conversa':
                $conversa_id = intval($input['conversa_id'] ?? 0);
                if (!$conversa_id) {
                    echo json_encode(['success' => false, 'error' => 'ID da conversa é obrigatório']);
                    exit;
                }
                
                $resultado = $chat_manager->resolverConversa($conversa_id);
                $mensagem_encerramento = "Esta conversa foi marcada como resolvida. Obrigado por entrar em contato conosco!";
                $chat_manager->enviarMensagemAdmin($conversa_id, $mensagem_encerramento, 'sistema');
                echo json_encode(['success' => $resultado]);
                break;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        switch ($action) {
            case 'get_conversations':
                $status = $_GET['status'] ?? null;
                $conversas = $chat_manager->obterConversas($status);
                echo json_encode($conversas);
                break;
                
            case 'get_messages':
                $conversa_id = intval($_GET['conversa_id'] ?? 0);
                if (!$conversa_id) {
                    echo json_encode([]);
                    exit;
                }
                
                $mensagens = $chat_manager->obterMensagens($conversa_id);
                
                // Marcar como lidas
                global $conexao;
                $sql = "UPDATE mensagens SET lida = TRUE WHERE conversa_id = ? AND remetente != 'admin'";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("i", $conversa_id);
                $stmt->execute();
                
                echo json_encode($mensagens);
                break;
                
            case 'get_stats':
                $stats = $chat_manager->obterEstatisticas();
                echo json_encode($stats);
                break;
        }
    }
}
?>
