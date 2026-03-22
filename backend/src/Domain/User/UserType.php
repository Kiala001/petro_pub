<?php

class UserType {
    const ADMIN = 'ADMIN';
    const PROFESSOR = 'TEACHER';
    const COORDINATOR = 'COORDINATOR';
    const COMMON_USER = 'COMMON_USER';

    private $value;

    public function __construct($value) {
        $valid = [self::ADMIN, self::PROFESSOR, self::COORDINATOR, self::COMMON_USER];
        if (!in_array($value, $valid)) {
            throw new InvalidArgumentException('Tipo de usuário inválido: ' . $value);
        }
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

    public function isAdmin() {
        return $this->value === self::ADMIN;
    }

    public function isProfessor() {
        return $this->value === self::PROFESSOR;
    }

    public function isCoordinator() {
        return $this->value === self::COORDINATOR;
    }

    public function isCommonUser() {
        return $this->value === self::COMMON_USER;
    }

    public function canUploadFree() {
        return $this->isAdmin() || $this->isProfessor() || $this->isCoordinator();
    }

    public function __toString() {
        return $this->value;
    }
}
?>
