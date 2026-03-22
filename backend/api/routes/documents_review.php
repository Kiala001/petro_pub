<?php

// Carregar classes necessárias
require_once '../src/Domain/User/UserId.php';
require_once '../src/Domain/Document/Document.php';
require_once '../src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../src/Infrastructure/Database/ReviewRepositoryImpl.php';
require_once '../src/Application/DocumentReviewService.php';
require_once '../src/Infrastructure/Security/JWTService.php';
require_once '../src/Application/UrlEncryptService.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$authHeader = $_SESSION['jwt_auth'];
$token = str_replace('Bearer ', '', $authHeader);
$jwtService = new JWTService();
$payload = $jwtService->verifyToken($token);

if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$userRepository = new UserRepositoryImpl($db);
$documentRepository = new DocumentRepositoryImpl($db);
$reviewRepository = new ReviewRepository($db);
$drService = new DocumentReviewService($documentRepository, $reviewRepository, $userRepository);

if ($method === 'GET' && ($path === 'article' || $path === 'article/')) {
    
    $result = $drService->getDocumentsWithReviews();
    
    echo json_encode($result);
}