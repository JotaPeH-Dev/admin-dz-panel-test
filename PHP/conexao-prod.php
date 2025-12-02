<?php
// Configurações adaptativas para diferentes ambientes
if (isset($_ENV['MYSQL_URL'])) {
    // Railway - MySQL URL completa
    $url = parse_url($_ENV['MYSQL_URL']);
    define('HOST', $url['host']);
    define('USUARIO', $url['user']);
    define('SENHA', $url['pass']);
    define('DB', ltrim($url['path'], '/'));
    define('PORT', $url['port'] ?? 3306);
} elseif (isset($_ENV['DB_HOST'])) {
    // Outras plataformas (Vercel, etc)
    define('HOST', $_ENV['DB_HOST']);
    define('USUARIO', $_ENV['DB_USER']);
    define('SENHA', $_ENV['DB_PASS']);
    define('DB', $_ENV['DB_NAME']);
    define('PORT', $_ENV['DB_PORT'] ?? 3306);
} else {
    // Ambiente de desenvolvimento (XAMPP)
    define('HOST', '127.0.0.1');
    define('USUARIO', 'root');
    define('SENHA', '');
    define('DB', 'teste_dz');
    define('PORT', 3306);
}

try {
    $conexao = mysqli_connect(HOST, USUARIO, SENHA, DB, PORT);
    if (!$conexao) {
        throw new Exception('Erro na conexão: ' . mysqli_connect_error());
    }
    mysqli_set_charset($conexao, 'utf8');
} catch (Exception $e) {
    die('Erro de conexão com o banco de dados: ' . $e->getMessage());
}
?>