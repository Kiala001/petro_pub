<?php

interface UserRepository {
    public function save(User $user);
    public function findById(UserId $id);
    public function findByEmail(Email $email);
    public function update(User $user);
    public function delete(UserId $id);
    public function all();
    public function getAllOrderedByPoints();
}

class UserRepositoryImpl implements UserRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save(User $user) {
        $stmt = $this->connection->prepare('
        INSERT INTO users (id, email, password_hash, name, type, balance, two_factor_enabled, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $user->getId()->getValue(),
            $user->getEmail()->getValue(),
            $user->getPassword()->hash(),
            $user->getName(),
            $user->getType()->getValue(),
            $user->getBalance(),
            $user->isTwoFactorEnabled() ? 1 : 0,
            $user->getCreatedAt()->format('Y-m-d H:i:s'),
            $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function findById(UserId $id) {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id->getValue()]);
        $row = $stmt->fetch();
        
        return $row;
    }

    public function findByEmail(Email $email) {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email->getValue()]);
        $row = $stmt->fetch();
        

        return $row ? $this->mapToUser($row) : null;
    }

    public function update(User $user) {
        $stmt = $this->connection->prepare('
            UPDATE users 
            SET email = ?, name = ?, type = ?, balance = ?, two_factor_enabled = ?, updated_at = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            $user->getEmail()->getValue(),
            $user->getName(),
            $user->getType()->getValue(),
            $user->getBalance(),
            $user->isTwoFactorEnabled() ? 1 : 0,
            $user->getUpdatedAt()->format('Y-m-d H:i:s'),
            $user->getId()->getValue()
        ]);
    }

    public function delete(UserId $id) {
        $stmt = $this->connection->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id->getValue()]);
    }

    public function all() {
        $stmt = $this->connection->query('SELECT * FROM users');
        $rows = $stmt->fetchAll();
        
        return array_map(function($row) {
            return $this->mapToUser($row);
        }, $rows);
    }

    private function mapToUser($row) {
        $user = new User(
            new UserId("Us"),
            new Email($row['email']),
            Password::createFromHash($row['password_hash']),
            new Name($row['name']),
            new UserType($row['type'])
        );
       
        $user->addId($row['id']);
        $user->addBalance($row['balance']);
        
        if ($row['two_factor_enabled']) {
            $user->enableTwoFactor($row['two_factor_secret']);
        }
        
        return $user;
    }

    public function getAllOrderedByPoints() {
        $stmt = $this->connection->query('SELECT * FROM users ORDER BY points DESC');
        $rows = $stmt->fetchAll();
        return array_map(function($row) {
            return [
                'id' => $row['id'],
                'name' => $row['name'],
                'email' => $row['email'],
                'type' => $row['type'],
                'balance' => $row['balance'],
                'points' => $row['points'],
            ];
        }, $rows);
    }
}
?>
