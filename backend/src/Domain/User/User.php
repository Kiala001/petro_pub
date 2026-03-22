<?php

class User {
    private $id;
    private $email;
    private $password;
    private $name;
    private $type;
    private $balance;
    private $twoFactorSecret;
    private $twoFactorEnabled;
    private $createdAt;
    private $updatedAt;

    public function __construct(
        UserId $id,
        Email $email,
        Password $password,
        Name $name,
        UserType $type
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->type = $type;
        $this->balance = 0.0;
        $this->twoFactorEnabled = false;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId() {
        return $this->id;
    }

    public function addId($id) {
        $this->id = $id;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getName() {
        return $this->name instanceof Name ? $this->name->getFullName() : (string)$this->name;
    }

    public function getType() {
        return $this->type;
    }

    public function getBalance() {
        return $this->balance;
    }

    public function addBalance($amount) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Saldo não pode ser negativo');
        }
        $this->balance += $amount;
        $this->updatedAt = new DateTime();
    }

    public function deductBalance($amount) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Saldo não pode ser negativo');
        }
        if ($this->balance < $amount) {
            throw new DomainException('Saldo insuficiente');
        }
        $this->balance -= $amount;
        $this->updatedAt = new DateTime();
    }

    public function enableTwoFactor($secret) {
        $this->twoFactorSecret = $secret;
        $this->twoFactorEnabled = true;
        $this->updatedAt = new DateTime();
    }

    public function disableTwoFactor() {
        $this->twoFactorSecret = null;
        $this->twoFactorEnabled = false;
        $this->updatedAt = new DateTime();
    }

    public function isTwoFactorEnabled() {
        return $this->twoFactorEnabled;
    }

    public function getTwoFactorSecret() {
        return $this->twoFactorSecret;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }
}
?>
