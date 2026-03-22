<?php

class JWTService {
    private $secret;
    private $algorithm = 'HS256';

    public function __construct($secret = null) {
        $this->secret = $secret ?? getenv('JWT_SECRET') ?? 'your-secret-key';
    }

    public function generateToken($userId, $userType, $expiresIn = 86400) {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        $payload = [
            'user_id' => $userId,
            'user_type' => $userType,
            'iat' => time(),
            'exp' => time() + $expiresIn
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $this->secret,
            true
        );
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public function verifyToken($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

            $signature = hash_hmac(
                'sha256',
                $headerEncoded . '.' . $payloadEncoded,
                $this->secret,
                true
            );
            $expectedSignature = $this->base64UrlEncode($signature);

            if ($signatureEncoded !== $expectedSignature) {
                return null;
            }

            $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

            if ($payload['exp'] < time()) {
                return null;
            }

            return $payload;
        } catch (Exception $e) {
            return null;
        }
    }

    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 4 - strlen($data) % 4));
    }
}
?>
