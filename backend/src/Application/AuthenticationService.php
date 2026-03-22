<?php

class AuthenticationService {
    private $userRepository;
    private $jwtService;

    public function __construct(UserRepository $userRepository, JWTService $jwtService) {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }

    public function register($email, $password, $name, $userType) {
        try {
            $emailVO = new Email($email);
            if ($this->userRepository->findByEmail($emailVO)) {
                throw new DomainException('Email já está registrado');
            }
 
            
            $passwordVO = new Password($password);
            $typeVO = new UserType($userType);
            $userId = new UserId("Us");
            $nameVO = new Name($name);
            
            $user = new User($userId, $emailVO, $passwordVO, $nameVO, $typeVO);
            
            $this->userRepository->save($user);
            
            return [
                'success' => true,
                'user_id' => $user->getId()->getValue(),
                'email' => $user->getEmail()->getValue(),
                'message' => 'Usuário registrado com sucesso'
            ];
        } catch (InvalidArgumentException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function login($email, $password, $type) {
        try {
            $emailVO = new Email($email);
            $user = $this->userRepository->findByEmail($emailVO);
            
            if (!$user) {
                throw new DomainException('Credenciais inválidas, verifique o seu email');
            }
            
            $passwordVO = new Password($password);
            if (!$passwordVO->verify($user->getPassword()->getValue())) {
                throw new DomainException('Credenciais inválidas, verifique a sua senha');
            }
            
            $typeVO = new UserType($type);
            if ($typeVO->getValue() != $user->getType()->getValue()) {
                throw new DomainException('Credenciais inválidas, verifique o tipo de usuário selecionado');
            }
            
            if ($user->isTwoFactorEnabled()) {
                // Gerar código 2FA
                $code = $this->generate2FACode();
                // Armazenar code temporariamente (sessão/cache)
                $_SESSION['2fa_pending'] = [
                    'user_id' => $user->getId()->getValue(),
                    'code' => $code,
                    'expires_at' => time() + 300 // 5 minutos
                ];
                
                return [
                    'success' => true,
                    'requires_2fa' => true,
                    'message' => 'Código 2FA enviado'
                ];
            }
            
            // Gerar JWT
            $token = $this->jwtService->generateToken($user->getId(), $user->getType()->getValue());

            $this->valueAuth($user->getType()->getValue(), $token, $user->getId(), $user->getName(), $user);

            return [
                'success' => true,
                'token' => $token,
                'user_id' => $user->getId(),
                'name' => $user->getName(),
                'type' => $user->getType()->getValue()
            ];
        } catch (InvalidArgumentException | DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function valueAuth($type, $token, $id, $name, $user) {
        $_SESSION['type_auth'] = $type;
        $_SESSION['jwt_auth'] = $token;
        $_SESSION['user_uuid'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_data'] = $user;
    }

    public function verify2FA($code) {
        if (!isset($_SESSION['2fa_pending'])) {
            return ['success' => false, 'error' => 'Sessão 2FA expirada'];
        }

        $session = $_SESSION['2fa_pending'];
        
        if (time() > $session['expires_at']) {
            unset($_SESSION['2fa_pending']);
            return ['success' => false, 'error' => 'Código expirado'];
        }

        if ($session['code'] !== $code) {
            return ['success' => false, 'error' => 'Código inválido'];
        }

        $user = $this->userRepository->findById(new UserId($session['user_id']));
        unset($_SESSION['2fa_pending']);
        
        $token = $this->jwtService->generateToken($user->getId()->getValue(), $user->getType()->getValue());
        
        return [
            'success' => true,
            'token' => $token,
            'user_id' => $user->getId()->getValue(),
            'name' => $user->getName(),
            'type' => $user->getType()->getValue()
        ];
    }

    private function generate2FACode() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
