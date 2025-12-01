<?php
// Configurações para produção (Vercel)
if (isset($_ENV['DB_HOST'])) {
    // Ambiente de produção (Vercel)
    define('HOST', $_ENV['DB_HOST']);
    define('USUARIO', $_ENV['DB_USER']);
    define('SENHA', $_ENV['DB_PASS']);
    define('DB', $_ENV['DB_NAME']);
} else {
    // Ambiente de desenvolvimento (XAMPP)
    define('HOST', '127.0.0.1');
    define('USUARIO', 'root');
    define('SENHA', '');
    define('DB', 'teste_dz');
}

try {
    $conexao = mysqli_connect(HOST, USUARIO, SENHA, DB);
    if (!$conexao) {
        throw new Exception('Erro na conexão: ' . mysqli_connect_error());
    }
    mysqli_set_charset($conexao, 'utf8');
} catch (Exception $e) {
    die('Erro de conexão com o banco de dados: ' . $e->getMessage());
}
?>