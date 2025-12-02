<?php
header('Content-Type: application/json');
session_start();

// Verificar se está logado
if (!isset($_SESSION['usuario_logado'])) {
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit;
}

require_once 'conexao.php';
require_once 'chat-groq.php';

$chat_manager = new ChatManager($conexao);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'send_admin_message':
            $conversa_id = intval($input['conversa_id'] ?? 0);
            $mensagem = trim($input['mensagem'] ?? '');
            $admin_id = $_SESSION['usuario_logado'];
            
            if (!$conversa_id || !$mensagem) {
                echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
                exit;
            }
            
            try {
                $resultado = $chat_manager->enviarMensagemAdmin($conversa_id, $mensagem, $admin_id);
                echo json_encode(['success' => $resultado]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        case 'escalar_humano':
            $conversa_id = intval($input['conversa_id'] ?? 0);
            
            if (!$conversa_id) {
                echo json_encode(['success' => false, 'error' => 'ID da conversa é obrigatório']);
                exit;
            }
            
            try {
                $resultado = $chat_manager->escalarParaHumano($conversa_id);
                
                // Enviar mensagem automática informando sobre o escalonamento
                $mensagem_sistema = "Esta conversa foi escalada para atendimento humano. Um de nossos especialistas entrará em contato em breve.";
                $chat_manager->enviarMensagemAdmin($conversa_id, $mensagem_sistema, 'sistema');
                
                echo json_encode(['success' => $resultado]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        case 'resolver_conversa':
            $conversa_id = intval($input['conversa_id'] ?? 0);
            
            if (!$conversa_id) {
                echo json_encode(['success' => false, 'error' => 'ID da conversa é obrigatório']);
                exit;
            }
            
            try {
                $resultado = $chat_manager->resolverConversa($conversa_id);
                
                // Enviar mensagem de encerramento
                $mensagem_encerramento = "Esta conversa foi marcada como resolvida. Obrigado por entrar em contato conosco! Se precisar de mais alguma coisa, inicie uma nova conversa.";
                $chat_manager->enviarMensagemAdmin($conversa_id, $mensagem_encerramento, 'sistema');
                
                echo json_encode(['success' => $resultado]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Ação não encontrada']);
            break;
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_conversations':
            $status = $_GET['status'] ?? null;
            
            try {
                $conversas = $chat_manager->obterConversas($status);
                echo json_encode($conversas);
            } catch (Exception $e) {
                echo json_encode([]);
            }
            break;
            
        case 'get_messages':
            $conversa_id = intval($_GET['conversa_id'] ?? 0);
            
            if (!$conversa_id) {
                echo json_encode([]);
                exit;
            }
            
            try {
                $mensagens = $chat_manager->obterMensagens($conversa_id);
                
                // Marcar mensagens como lidas
                $sql = "UPDATE mensagens SET lida = TRUE WHERE conversa_id = ? AND remetente != 'admin'";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("i", $conversa_id);
                $stmt->execute();
                
                echo json_encode($mensagens);
            } catch (Exception $e) {
                echo json_encode([]);
            }
            break;
            
        case 'get_stats':
            try {
                $stats = $chat_manager->obterEstatisticas();
                echo json_encode($stats);
            } catch (Exception $e) {
                echo json_encode([
                    'total_conversas' => 0,
                    'mensagens_hoje' => 0,
                    'conversas_ativas' => 0,
                    'nao_lidas' => 0
                ]);
            }
            break;
            
        case 'test_groq':
            try {
                $groq = new GroqAPI();
                $resultado = $groq->enviarMensagem("Teste de funcionamento da API");
                
                echo json_encode([
                    'success' => $resultado['success'],
                    'message' => $resultado['success'] ? 
                        'API Groq funcionando: ' . substr($resultado['message'], 0, 100) : 
                        'Erro: ' . $resultado['error']
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Erro no teste: ' . $e->getMessage()
                ]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}
?>
