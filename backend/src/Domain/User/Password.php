<?php

class Password {
    private $value;

    public function __construct($value) {
        if (strlen($value) < 6) {
            throw new InvalidArgumentException('Senha deve ter no mínimo 6 caracteres');
        }

        // Must contain at least one letter, one number and one special character
        if (!preg_match('/[A-Za-z]/', $value)) {
            throw new InvalidArgumentException('A senha deve conter pelo menos uma letra');
        }

        if (!preg_match('/\d/', $value)) {
            throw new InvalidArgumentException('A senha deve conter pelo menos um número');
        }

        if (!preg_match('/[\W_]/', $value)) {
            throw new InvalidArgumentException('A senha deve conter pelo menos um caracter especial');
        }
        $this->value = $value;
    }

    public function hash() {
        return password_hash($this->value, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verify($hash) {
        return password_verify($this->value, $hash);
    }

    public static function createFromHash($hash) {
        // Use a temporary valid value to satisfy constructor validations, then overwrite with the hash
        $password = new self('Temp1!');
        $password->value = $hash;
        return $password;
    }

    public function getValue() {
        return $this->value;
    }
}
?>
