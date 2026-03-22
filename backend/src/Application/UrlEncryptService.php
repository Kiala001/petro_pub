<?php

define('ENCRYPTION_KEY', 'CheLseaFCB@#%&');
define('ENCRYPTION_METHOD', 'AES-256-CBC');

function encrypt($value) {
    $value = (string) $value;
    
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    $encrypted = openssl_encrypt(
        $value,
        ENCRYPTION_METHOD,
        ENCRYPTION_KEY,
        0,
        $iv
    );
    
    $encrypted_data = base64_encode($iv . $encrypted);
    
    $url_safe = strtr($encrypted_data, '+/=', '-_~');
    
    return $url_safe;
}

function decrypt($encrypted_value) {
    try {
        $encrypted_data = strtr($encrypted_value, '-_~', '+/=');
        $encrypted_data = base64_decode($encrypted_data);
        
        if ($encrypted_data === false) {
            return false;
        }
        
        $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
        $iv = substr($encrypted_data, 0, $iv_length);
        $encrypted = substr($encrypted_data, $iv_length);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            ENCRYPTION_METHOD,
            ENCRYPTION_KEY,
            0,
            $iv
        );
        
        return $decrypted !== false ? $decrypted : false;
        
    } catch (Exception $e) {
        error_log("Erro ao descriptografar URL: " . $e->getMessage());
        return false;
    }
}

function encrypt_params($params) {
    $json = json_encode($params);
    return encrypt($json);
}

function decrypt_params($encrypted) {
    $json = decrypt($encrypted);
    if ($json === false) {
        return false;
    }
    
    $params = json_decode($json, true);
    return is_array($params) ? $params : false;
}

function validate_token($encrypted, $max_age = 3600) {
    $data = decrypt_params($encrypted);
    
    if (!$data || !isset($data['timestamp'])) {
        return false;
    }
    
    if (time() - $data['timestamp'] > $max_age) {
        return false;
    }
    
    return $data;
}

function create_token_temporary($value, $extra_data = []) {
    $data = array_merge([
        'value' => $value,
        'timestamp' => time()
    ], $extra_data);
    
    return encrypt_params($data);
}