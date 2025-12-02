<?php
require_once 'config-groq.php';

// Classe para integração com Groq API
class GroqAPI {
    private $api_key;
    private $base_url;
    
    public function __construct() {
        $this->api_key = GROQ_API_KEY;
        $this->base_url = GROQ_API_URL;
    }
    
    public function enviarMensagem($mensagem, $contexto = '') {
        // Verificar se API key está configurada
        if (empty($this->api_key)) {
            return [
                'success' => false,
                'error' => 'API Key do Groq não configurada'
            ];
        }
        
        // Preparar mensagens para o formato do Groq (compatível com OpenAI)
        $messages = [
            [
                'role' => 'system',
                'content' => SISTEMA_PROMPT
            ]
        ];
        
        // Adicionar contexto se houver
        if (!empty($contexto)) {
            $messages[] = [
                'role' => 'assistant',
                'content' => 'Contexto da conversa: ' . $contexto
            ];
        }
        
        // Adicionar mensagem atual
        $messages[] = [
            'role' => 'user',
            'content' => $mensagem
        ];
        
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
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $error
            ];
        }
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            
            if (isset($result['choices'][0]['message']['content'])) {
                return [
                    'success' => true,
                    'message' => trim($result['choices'][0]['message']['content'])
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Resposta inválida: ' . substr($response, 0, 200)
                ];
            }
        } else {
            $error_data = json_decode($response, true);
            $error_message = $error_data['error']['message'] ?? 'Erro desconhecido';
            
            return [
                'success' => false,
                'error' => "HTTP $http_code: $error_message"
            ];
        }
    }
}

// Classe para gerenciar conversas
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
        
        // Obter contexto da conversa
        $contexto = $this->obterContextoConversa($conversa_id);
        
        // Usar Groq API
        $resposta_ia = $this->groq->enviarMensagem($mensagem, $contexto);
        
        if ($resposta_ia['success']) {
            $resposta_texto = $resposta_ia['message'];
        } else {
            // Log do erro para debug
            error_log("Erro Groq API: " . $resposta_ia['error']);
            
            // Fallback profissional
            $resposta_texto = "Obrigado por sua mensagem! Nosso sistema está processando muitas solicitações no momento. Um de nossos atendentes entrará em contato em breve para ajudá-lo da melhor forma. 👨‍💼";
        }
        
        // Salvar resposta da IA
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
    
    // Estatísticas para o dashboard
    public function obterEstatisticas() {
        $stats = [];
        
        // Total de conversas
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM conversas");
        $stats['total_conversas'] = $result->fetch_assoc()['total'];
        
        // Mensagens hoje
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM mensagens WHERE DATE(timestamp) = CURDATE()");
        $stats['mensagens_hoje'] = $result->fetch_assoc()['total'];
        
        // Conversas ativas
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM conversas WHERE status = 'ativa'");
        $stats['conversas_ativas'] = $result->fetch_assoc()['total'];
        
        // Não lidas
        $result = $this->conexao->query("SELECT COUNT(*) as total FROM mensagens WHERE lida = FALSE AND remetente != 'admin'");
        $stats['nao_lidas'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
}
?>
