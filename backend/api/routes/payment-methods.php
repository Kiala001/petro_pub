<?php 

require_once '../src/Domain/Id.php';
require_once '../src/Application/PaymentMethodService.php'; 
require_once '../src/Domain/PaymentMethod/PaymentMethod.php'; 
require_once '../src/Infrastructure/Database/PaymentMethodRepositoryImpl.php'; 
require_once '../src/Infrastructure/Security/JWTService.php';


$input = json_decode(file_get_contents('php://input'), true) ?? [];

$authHeader = $_SESSION['jwt_auth'];
$token = str_replace('Bearer ', '', $authHeader);
$jwtService = new JWTService();
$payload = $jwtService->verifyToken($token);

if (!$payload && $method !== 'GET') {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$repository = new PaymentMethodRepositoryImpl($db); 
$service = new PaymentMethodService($repository); 

if ($method === "GET") { 

    $methods = $service->getUserMethods($payload["user_id"]); 
    echo json_encode([ "success" => true, "methods" => $methods ]); 

} elseif ($method === "POST" && ($path === 'payment-methods' || $path === 'payment-methods/')) { 

    $result = $service->createMethod( $payload["user_id"], $input ); 
    echo json_encode($result); 

} elseif ($method === "PUT" && ($path === 'payment-methods' || $path === 'payment-methods/')) { 

    $result = $service->updateMethod($payload['user_id'], $input['id'], $input);

    echo json_encode($result);
    
} elseif ($method === "DELETE") { 

    $pmId = str_replace('payment-methods/', '', $path);
    $userID = $_SESSION['user_uuid'];

    $result = $service->deleteMethod($pmId, $userID);
    $result['id'] = $pmId; 
    echo json_encode($result); 

} 
