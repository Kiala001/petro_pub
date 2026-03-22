<?php
// backend/api/routes/notification.php
// Endpoint para notificações do usuário

require_once '../src/Application/NotificationService.php';
require_once '../src/Infrastructure/Database/NotificationRepositoryImpl.php';
require_once '../src/Domain/Notificacao/Notificacao.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['path'] ?? '';

session_start();
$authHeader = $_SESSION['jwt_auth'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

require_once '../src/Infrastructure/Security/JWTService.php';
$jwtService = new JWTService();
$payload = $jwtService->verifyToken($token);

if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$userId = $payload['user_id'];

// Instanciar repositório e serviço
$notificationRepo = new NotificationRepositoryImpl($db);
$notificationService = new NotificationService($notificationRepo);

if ($method === 'GET' && ($path === 'notifications' || $path === 'notifications/')) {
    // Retorna notificações do usuário
    $notifications = $notificationRepo->findByUser($userId);
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
    exit;
}

if ($method === 'POST' && ($path === 'notifications/read' || $path === 'notifications/read/')) {
    // Marca notificação como lida
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $notificationId = $input['id'] ?? null;
    if ($notificationId) {
        $notificationRepo->markAsRead($notificationId);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'ID não informado']);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint não encontrado']);
