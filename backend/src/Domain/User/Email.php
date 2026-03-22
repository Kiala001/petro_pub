<?php
// Domain/User/Email.php
// Value Object que representa um email simples

class Email {
    private $value;

    // Validate email format: local part (before @) may contain letters, numbers and dots;
    // domain part only letters and dots. No consecutive dots and basic checks.
    public function __construct($email) {
        $email = trim($email);
        $this->validate($email);
        $this->value = strtolower($email);
    }

    private function validate($email) {
        if (!is_string($email) || strlen($email) === 0) {
            throw new InvalidArgumentException('Email inválido');
        }

        // Basic split
        if (substr_count($email, '@') !== 1) {
            throw new InvalidArgumentException('Email deve conter um único @');
        }

        list($local, $domain) = explode('@', $email, 2);

        // Local: letters, numbers and dot only (no spaces, no other chars), cannot start/end with dot, no consecutive dots
        if (!preg_match('/^[A-Za-z0-9]+(\.[A-Za-z0-9]+)*$/', $local)) {
            throw new InvalidArgumentException('Parte local do email inválida. Só são permitidos letras, números e pontos.');
        }

        // Domain: letters and dots only, at least one dot, no consecutive dots, cannot start/end with dot
        if (!preg_match('/^[A-Za-z]+(\.[A-Za-z]+)+$/', $domain)) {
            throw new InvalidArgumentException('Domínio do email inválido. Só são permitidos letras e pontos, e deve conter ao menos um ponto.');
        }
    }

    public function getValue() {
        return $this->value;
    }

    public function __toString() {
        return $this->value;
    }
}
?>
