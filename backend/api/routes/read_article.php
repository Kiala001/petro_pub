<?php

// Carregar classes necessárias
require_once '../src/Domain/User/UserId.php';
require_once '../src/Domain/Document/Document.php';
require_once '../src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../src/Infrastructure/Database/CategoryRepositoryImpl.php';
require_once '../src/Infrastructure/Database/ReviewRepositoryImpl.php';
require_once '../src/Application/DocumentService.php';
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
$categoryRepository = new CategoryRepositoryImpl($db);
$documentService = new DocumentService($documentRepository, $categoryRepository, $uploadDir);

$documentId = str_replace('read/', '', $path);

$result = $documentService->getDocumentDetails($documentId);

echo json_encode($result);
