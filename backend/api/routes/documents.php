<?php

// Carregar classes necessárias
require_once '../src/Domain/Id.php';
require_once '../src/Domain/User/UserId.php';
require_once '../src/Domain/Document/Document.php';
require_once '../src/Domain/Category/Category.php';
require_once '../src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../src/Infrastructure/Database/CategoryRepositoryImpl.php';
require_once '../src/Application/DocumentService.php';
require_once '../src/Infrastructure/Security/JWTService.php';
require_once '../src/Infrastructure/Database/UserRepositoryImpl.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

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

$uploadDir = '../../uploads/documents';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$userRepository = new UserRepositoryImpl($db);
$documentRepository = new DocumentRepositoryImpl($db);
$categoryRepository = new CategoryRepositoryImpl($db);
$documentService = new DocumentService($documentRepository, $categoryRepository, $uploadDir);

if ($method === 'GET' && ($path === 'documents' || $path === 'documents/')) {

    $result = $documentService->getAllDocuments();
    
    echo json_encode($result);

} elseif ($method === 'GET' && strpos($path, 'documents/') === 0) {
    $documentId = str_replace('documents/', '', $path);
    $result = $documentService->getDocumentDetails($documentId);

    if ($result['success']) {
        $doc = $result['document'];
        $result['document'] = [
            'id' => $doc->getId(),
            'title' => $doc->getTitle(),
            'authors' => $doc->getAuthors(),
            'course' => $doc->getCourse(),
            'summary' => $doc->getSummary(),
            'keywords' => $doc->getKeywords(),
            'price_kz' => $doc->getPriceKz(),
            'pub_mode' => $doc->getPubMode(),
            'access_mode' => $doc->getAccessMode(),
            'status' => $doc->getStatus(),
            'created_at' => $doc->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }

    http_response_code($result['success'] ? 200 : 404);
    echo json_encode($result);

} elseif ($method === 'POST' && ($path === 'documents' || $path === 'documents/')) {
    $userId = $payload['user_id'];
    $categoryId = $_POST['docType'] ?? '';

    $userType = $_SESSION['type_auth'];
    $data = $_POST;

    if (!isset($_FILES['document'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Arquivo não fornecido']);
        exit;
    }

    $result = $documentService->submitDocument(
        $userId,
        $categoryId,
        $data,
        $_FILES['document'],
        $userType
    );

    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);

} elseif ($method === 'GET' && strpos($path, 'documents/user/') === 0) {
    $userId = str_replace('documents/user/', '', $path);
    $userId = $_SESSION['user_uuid'];
    $result = $documentService->getUserDocuments($userId);

    $result['documents'] = array_map(function($doc) {
        return [
            'id' => $doc->getId(),
            'title' => $doc->getTitle(),
            'status' => $doc->getStatus(),
            'price_kz' => $doc->getPriceKz(),
            'created_at' => $doc->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }, $result['documents']);

    echo json_encode($result);

} elseif ($method === 'DELETE' && strpos($path, 'documents/') === 0) {
    $documentId = str_replace('documents/', '', $path);
    $userType = $_SESSION['type_auth'];

    $result = $documentService->deleteDocument($documentId, $userType);

    echo json_encode($result);
} elseif ($method === 'PUT' && ($path === 'documents-decision' || $path === 'documents-decision/')) {
    if ($input['dec'] == 'reject') {
        $result = $documentService->rejectDocument($input['id'], $input);

        echo json_encode($result);
        exit();
    }
    
    $result = $documentService->approveDocument($input['id'], $input);

    $user_id = new UserId("");
    $user_id->__fromString($result['user_id']);
    $user = $userRepository->findById($user_id);
    
    $result['user'] = $user;

    // if ($user['type'] == 'COMMON_USER') {
    //     $result = $documentService->requirePayment($input['id'], $result['document']);
    //     echo json_encode($result);
    //     exit();
    // }
    
    $result = $documentService->publishDocument($input['id'], $result['document']);
    echo json_encode($result);

} elseif ($method === 'GET' && ($path === 'documents-detail' || $path === 'documents-detail/')) {

echo json_encode(['error' => $input]);

}else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
