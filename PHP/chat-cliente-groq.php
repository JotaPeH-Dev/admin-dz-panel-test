<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'conexao.php';
require_once 'chat-groq.php';

$chat_manager = new ChatManager($conexao);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
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
                // Criar nova conversa
                $sql = "INSERT INTO conversas (usuario_nome, usuario_email, status) VALUES (?, ?, 'ativa')";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("ss", $nome, $email);
                $stmt->execute();
                
                $conversa_id = $conexao->insert_id;
                
                if ($conversa_id) {
                    // Processar primeira mensagem
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
                // Verificar se conversa existe e está ativa
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
                    // Salvar apenas a mensagem do usuário, não responder automaticamente
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
                
                // Processar mensagem com IA
                $resposta_ia = $chat_manager->processarMensagemUsuario($conversa_id, $mensagem);
                
                echo json_encode([
                    'success' => true,
                    'resposta' => $resposta_ia
                ]);
                
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
        case 'get_messages':
            $conversa_id = intval($_GET['conversa_id'] ?? 0);
            
            if (!$conversa_id) {
                echo json_encode([]);
                exit;
            }
            
            try {
                $mensagens = $chat_manager->obterMensagens($conversa_id);
                echo json_encode($mensagens);
            } catch (Exception $e) {
                echo json_encode([]);
            }
            break;
            
        case 'test_api':
            // Teste rápido da API Groq
            try {
                $groq = new GroqAPI();
                $resultado = $groq->enviarMensagem("Teste rápido");
                
                echo json_encode([
                    'success' => $resultado['success'],
                    'message' => $resultado['success'] ? 'API Groq funcionando!' : $resultado['error']
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Ação não encontrada']);
            break;
    }
}
?>
