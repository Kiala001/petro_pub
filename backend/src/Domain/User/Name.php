<?php
// Domain/User/Name.php
// Value Object for user's full name. Requires at least first and last name, no numbers or special characters.

class Name {
    private $fullName;
    private $firstName;
    private $lastName;

    public function __construct($fullName) {
        $fullName = trim($fullName);
        $this->validate($fullName);
        $this->fullName = preg_replace('/\s+/', ' ', $fullName);

        $parts = explode(' ', $this->fullName);
        $this->firstName = $parts[0];
        $this->lastName = end($parts);
    }

    private function validate($name) {
        if (!is_string($name) || strlen($name) < 3) {
            throw new InvalidArgumentException('Nome inválido');
        }

        // Must contain at least two words
        $parts = preg_split('/\s+/', $name);
        if (count($parts) < 2) {
            throw new InvalidArgumentException('Informe pelo menos primeiro e último nome');
        }

        // Only letters, spaces, hyphen and apostrophe allowed
        if (!preg_match('/^[A-Za-záàâãéèêíïóôõöúçñ\s\'-]+$/iu', $name)) {
            throw new InvalidArgumentException('Nome não pode conter números ou caracteres especiais');
        }

        // No numbers
        if (preg_match('/\d/', $name)) {
            throw new InvalidArgumentException('Nome não pode conter números');
        }
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function __toString() {
        return $this->fullName;
    }
}
?>