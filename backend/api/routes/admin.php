<?php

// Carregar classes necessárias
require_once '../src/Domain/User/UserId.php';
require_once '../src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../src/Infrastructure/Database/PaymentRepositoryImpl.php';
require_once '../src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../src/Domain/Payment/Payment.php';
require_once '../src/Domain/Document/Document.php';
require_once '../src/Application/PaymentService.php';
require_once '../src/Infrastructure/Security/JWTService.php';

// Verificar autenticação e permissão de admin
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
$jwtService = new JWTService();
$payload = $jwtService->verifyToken($token);

if (!$payload || $payload['user_type'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$adminId = $payload['user_id'];

$paymentRepository = new PaymentRepositoryImpl($db);
$documentRepository = new DocumentRepositoryImpl($db);
$userRepository = new UserRepositoryImpl($db);
$paymentService = new PaymentService($paymentRepository, $documentRepository, $userRepository);

if ($method === 'GET' && ($path === 'admin/payments/pending' || $path === 'admin/payments/pending/')) {
    $result = $paymentService->getPendingPayments();

    $result['payments'] = array_map(function($payment) {
        return [
            'id' => $payment->getId(),
            'user_id' => $payment->getUserId(),
            'document_id' => $payment->getDocumentId(),
            'amount_kz' => $payment->getAmountKz(),
            'method' => $payment->getMethod(),
            'reference_number' => $payment->getReferenceNumber(),
            'status' => $payment->getStatus(),
            'created_at' => $payment->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }, $result['payments']);

    echo json_encode($result);

} elseif ($method === 'POST' && strpos($path, 'admin/payments/') === 0 && strpos($path, '/approve') !== false) {
    $paymentId = str_replace(['admin/payments/', '/approve'], '', $path);
    $result = $paymentService->approvePayment($paymentId, $adminId);

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);

} elseif ($method === 'POST' && strpos($path, 'admin/payments/') === 0 && strpos($path, '/reject') !== false) {
    $paymentId = str_replace(['admin/payments/', '/reject'], '', $path);
    $result = $paymentService->rejectPayment($paymentId, $adminId, $input['notes'] ?? '');

    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
