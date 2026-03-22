<?php

require_once '../src/Domain/User/UserId.php';
require_once '../src/Domain/User/Name.php';
require_once '../src/Domain/User/Email.php';
require_once '../src/Domain/User/Password.php';
require_once '../src/Domain/User/UserType.php';
require_once '../src/Domain/User/Name.php';
require_once '../src/Domain/User/User.php';
require_once '../src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../src/Infrastructure/Security/JWTService.php';
require_once '../src/Application/AuthenticationService.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'POST' && $path === 'auth/register') {
    
    $userRepository = new UserRepositoryImpl($db);
    $jwtService = new JWTService();
    $authService = new AuthenticationService($userRepository, $jwtService);
    
    $response = $authService->register(
        $input['email'] ?? '',
        $input['password'] ?? '',
        $input['name'] ?? '',
        $input['type'] ?? 'COMMON_USER'
    );
    
    http_response_code($response['success'] ? 201 : 400);
    echo json_encode($response);
    

} elseif ($method === 'POST' && $path === 'auth/login') {
    $userRepository = new UserRepositoryImpl($db);
    $jwtService = new JWTService();
    $authService = new AuthenticationService($userRepository, $jwtService);
    
    $response = $authService->login(
        $input['email'] ?? '',
        $input['password'] ?? '', 
        $input['type'] ?? 'COMMON_USER'
    );
    
    echo json_encode($response);

} elseif ($method === 'POST' && $path === 'auth/verify-2fa') {
    $userRepository = new UserRepositoryImpl($db);
    $jwtService = new JWTService();
    $authService = new AuthenticationService($userRepository, $jwtService);

    $response = $authService->verify2FA($input['code'] ?? '');

    http_response_code($response['success'] ? 200 : 401);
    echo json_encode($response);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
