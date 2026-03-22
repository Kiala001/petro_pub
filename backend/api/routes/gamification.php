<?php
// backend/api/routes/gamification.php
// Endpoint para gamificação: pontos, ranking, conquistas


require_once '../src/Application/PointService.php';
require_once '../src/Infrastructure/Database/PointRepositoryImpl.php';
require_once '../src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../src/Domain/User/UserId.php';
require_once '../src/Domain/Id.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

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

// Instanciar repositórios e serviços
$pointRepo = new PointRepositoryImpl($db);
$userRepo = new UserRepositoryImpl($db);
$pointService = new PointService($pointRepo, $userRepo);

if ($method === 'GET' && ($path === 'gamification' || $path === 'gamification/')) {
    // Retorna pontos totais do usuário

    $total = $pointService->getTotalPoints($userId);
    $history = $pointService->getPointsHistory($userId, 30);
    echo json_encode([
        'success' => true,
        'total_points' => $total,
        'history' => $history
    ]);
    exit;
}

if ($method === 'GET' && ($path === 'ranking' || $path === 'ranking/')) {
    echo json_encode(['success' => true, 'history' => ["Kiala", "Emanuel"]]);
    // Ranking dos usuários por pontos
    $users = $userRepo->getAllOrderedByPoints();
    echo json_encode([
        'success' => true,
        'ranking' => $users
    ]);
    exit;
}

// Outros endpoints de gamificação podem ser adicionados aqui

http_response_code(404);
echo json_encode(['error' => 'Endpoint não encontrado']);
