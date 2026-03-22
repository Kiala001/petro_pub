<?php

interface PaymentRepository {
    public function save(Payment $payment);
    public function findById($id);
    public function findByUserId($userId);
    public function findByDocumentId($documentId);
    public function findByStatus($status);
    public function update(Payment $payment);
}

class PaymentRepositoryImpl implements PaymentRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save(Payment $payment) {
        $stmt = $this->connection->prepare('
            INSERT INTO payments (
                id, user_id, document_id, amount_kz, method, status, 
                reference_number, proof_file_path, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $payment->getId(),
            $payment->getUserId(),
            $payment->getDocumentId(),
            $payment->getAmountKz(),
            $payment->getMethod(),
            $payment->getStatus(),
            $payment->getReferenceNumber(),
            $payment->getProofFilePath(),
            $payment->getCreatedAt()->format('Y-m-d H:i:s'),
            $payment->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function findById($id) {
        $stmt = $this->connection->prepare('SELECT * FROM payments WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        return $row ? $this->mapToPayment($row) : null;
    }

    public function findByUserId($userId) {
        $stmt = $this->connection->prepare('SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->mapToPayment($row), $rows);
    }

    public function findByDocumentId($documentId) {
        $stmt = $this->connection->prepare('SELECT * FROM payments WHERE document_id = ? ORDER BY created_at DESC');
        $stmt->execute([$documentId]);
        $rows = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->mapToPayment($row), $rows);
    }

    public function findByStatus($status) {
        $stmt = $this->connection->prepare('SELECT * FROM payments WHERE status = ? ORDER BY created_at DESC');
        $stmt->execute([$status]);
        $rows = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->mapToPayment($row), $rows);
    }

    public function update(Payment $payment) {
        $stmt = $this->connection->prepare('
            UPDATE payments 
            SET status = ?, approved_by = ?, proof_approved_at = ?, notes = ?, updated_at = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            $payment->getStatus(),
            $payment->getApprovedBy(),
            $payment->getProofApprovedAt() ? $payment->getProofApprovedAt()->format('Y-m-d H:i:s') : null,
            $payment->getNotes(),
            $payment->getUpdatedAt()->format('Y-m-d H:i:s'),
            $payment->getId()
        ]);
    }

    private function mapToPayment($row) {
        $payment = new Payment(
            $row['id'],
            $row['user_id'],
            $row['document_id'],
            $row['amount_kz'],
            $row['method'],
            $row['reference_number'],
            $row['proof_file_path']
        );
        
        $reflection = new ReflectionClass($payment);
        
        $statusProp = $reflection->getProperty('status');
        $statusProp->setAccessible(true);
        $statusProp->setValue($payment, $row['status']);
        
        $approvedByProp = $reflection->getProperty('approvedBy');
        $approvedByProp->setAccessible(true);
        $approvedByProp->setValue($payment, $row['approved_by']);
        
        $notesProp = $reflection->getProperty('notes');
        $notesProp->setAccessible(true);
        $notesProp->setValue($payment, $row['notes']);
        
        return $payment;
    }
}
?>
