<?php

// Carregar classes necessárias
require_once '../src/Domain/Payment/Payment.php';
require_once '../src/Domain/Document/Document.php';
require_once '../src/Domain/User/UserId.php';
require_once '../src/Domain/User/User.php';
require_once '../src/Infrastructure/Database/PaymentRepositoryImpl.php';
require_once '../src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../src/Application/PaymentService.php';
require_once '../src/Infrastructure/Security/JWTService.php';

// Verificar autenticação
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);
$jwtService = new JWTService();
$payload = $jwtService->verifyToken($token);

if (!$payload) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$userId = $payload['user_id'];

$paymentRepository = new PaymentRepositoryImpl($db);
$documentRepository = new DocumentRepositoryImpl($db);
$userRepository = new UserRepositoryImpl($db);
$paymentService = new PaymentService($paymentRepository, $documentRepository, $userRepository);

$proofDir = '../../uploads/proofs';
if (!is_dir($proofDir)) {
    mkdir($proofDir, 0755, true);
}

if ($method === 'POST' && ($path === 'payments' || $path === 'payments/')) {
    $proofPath = '';

    if (isset($_FILES['proof'])) {
        $fileName = uniqid('proof_') . '_' . time() . '.' . pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        $proofPath = $proofDir . '/' . $fileName;
        move_uploaded_file($_FILES['proof']['tmp_name'], $proofPath);
    }

    $result = $paymentService->initiatePayment(
        $userId,
        $input['document_id'] ?? '',
        $input['method'] ?? '',
        $input['reference_number'] ?? '',
        $proofPath
    );

    http_response_code($result['success'] ? 201 : 400);
    echo json_encode($result);

} elseif ($method === 'GET' && ($path === 'payments/history' || $path === 'payments/history/')) {
    $result = $paymentService->getUserPaymentHistory($userId);

    $result['payments'] = array_map(function($payment) {
        return [
            'id' => $payment->getId(),
            'document_id' => $payment->getDocumentId(),
            'amount_kz' => $payment->getAmountKz(),
            'method' => $payment->getMethod(),
            'status' => $payment->getStatus(),
            'created_at' => $payment->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }, $result['payments']);

    echo json_encode($result);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
