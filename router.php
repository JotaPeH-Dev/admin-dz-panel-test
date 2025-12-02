<?php
// Arquivo de entrada principal
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Roteamento simples
switch ($path) {
    case '/':
        require 'index.php';
        break;
    case '/login':
        require 'PHP/login.php';
        break;
    case '/logout':
        require 'PHP/logout.php';
        break;
    default:
        // Verificar se o arquivo existe
        $file = ltrim($path, '/');
        if (file_exists($file) && is_file($file)) {
            // Servir arquivo estático ou PHP
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                require $file;
            } else {
                return false; // Deixa o servidor builtin servir arquivos estáticos
            }
        } else {
            http_response_code(404);
            echo '404 - Página não encontrada';
        }
        break;
}
?>