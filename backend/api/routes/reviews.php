<?php

require_once '../src/Domain/Id.php';
require_once '../src/Domain/Review/Review.php';
require_once '../src/Domain/Document/Document.php';
require_once '../src/Domain/Category/Category.php';
require_once '../src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../src/Infrastructure/Database/ReviewRepositoryImpl.php';
require_once '../src/Infrastructure/Database/CategoryRepositoryImpl.php';
require_once '../src/Application/DocumentService.php';
require_once '../src/Application/ReviewService.php';
require_once '../src/Infrastructure/Security/JWTService.php';

// Verificar autenticação
$authHeader = $_SESSION['jwt_auth'];
$token = str_replace('Bearer ', '', $authHeader);
$jwtService = new JWTService();
$payload = $jwtService->verifyToken($token);

if (!$payload && $method !== 'GET') {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$reviewService = new ReviewService($db);

if ($method === 'POST' && ($path === 'reviews' || $path === 'reviews/')) {
    $userId = $payload['user_id'];
    $reviewData = $_POST;

    $result = $reviewService->createReview($reviewData, $userId);

    echo json_encode($result);
}