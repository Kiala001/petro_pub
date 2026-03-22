<?php

require_once '../src/Infrastructure/Database/PDOConnection.php';

// Configuração do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'base_academica_petrochamp');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Inicializar sessão
session_start();

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obter método e caminho
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/petro_pub/backend/api/', '', $path);

// Roteador simples
try {
    $db = PDOConnection::getInstance()->getConnection();

    // Carregar o arquivo de rota apropriado
    if (strpos($path, 'auth') === 0) {
        require_once 'routes/auth.php';
    } elseif (strpos($path, 'auth/login') === 0) {
        require_once 'routes/auth.php';
    } elseif (strpos($path, 'documents') === 0) {
        require_once 'routes/documents.php';
    } elseif (strpos($path, 'payments') === 0) {
        require_once 'routes/payments.php';
    } elseif (strpos($path, 'categories') === 0) {
        require_once 'routes/categories.php';
    } elseif (strpos($path, 'comments') === 0) {
        require_once 'routes/comments.php';
    } elseif (strpos($path, 'admin') === 0) {
        require_once 'routes/admin.php';
    }elseif (strpos($path, 'reviews') === 0) { 
        require_once 'routes/reviews.php';
    }elseif (strpos($path, 'payment-methods') === 0) { 
        require_once 'routes/payment-methods.php';
    }elseif (strpos($path, 'article') === 0) { 
        require_once 'routes/documents_review.php';
    }elseif (strpos($path, 'read') === 0) { 
        require_once 'routes/read_article.php';
    }elseif (strpos($path, 'gamification') === 0) { 
        require_once 'routes/gamification.php';
    }elseif (strpos($path, 'ranking') === 0) { 
        require_once 'routes/gamification.php';
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Rota não encontrada']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}
?>
