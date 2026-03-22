<?php

class Payment {
    private $id;
    private $userId;
    private $documentId;
    private $amountKz;
    private $method;
    private $status;
    private $referenceNumber;
    private $proofFilePath;
    private $proofApprovedAt;
    private $approvedBy;
    private $notes;
    private $createdAt;
    private $updatedAt;

    const METHOD_TRANSFER = 'TRANSFER';
    const METHOD_CARD = 'CARD';
    const METHOD_DIGITAL_APP = 'DIGITAL_APP';

    const STATUS_PENDING = 'PENDING';
    const STATUS_VERIFIED = 'VERIFIED';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    public function __construct(
        $id,
        $userId,
        $documentId,
        $amountKz,
        $method,
        $referenceNumber,
        $proofFilePath
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->documentId = $documentId;
        $this->amountKz = $this->validateAmount($amountKz);
        $this->method = $this->validateMethod($method);
        $this->referenceNumber = $referenceNumber;
        $this->validateProofFile($proofFilePath);
        $this->proofFilePath = $proofFilePath;
        $this->status = self::STATUS_PENDING;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    private function validateAmount($amount) {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Valor do pagamento deve ser maior que 0');
        }
        return (float)$amount;
    }

    private function validateMethod($method) {
        $valid = [self::METHOD_TRANSFER, self::METHOD_CARD, self::METHOD_DIGITAL_APP];
        if (!in_array($method, $valid)) {
            throw new InvalidArgumentException('Método de pagamento inválido');
        }
        return $method;
    }

    private function validateProofFile($filePath) {
        // Comprovantes devem ser imagens ou PDF
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new InvalidArgumentException("Comprovante deve ser JPG, PNG ou PDF. Arquivo: {$filePath}");
        }
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->userId; }
    public function getDocumentId() { return $this->documentId; }
    public function getAmountKz() { return $this->amountKz; }
    public function getMethod() { return $this->method; }
    public function getStatus() { return $this->status; }
    public function getReferenceNumber() { return $this->referenceNumber; }
    public function getProofFilePath() { return $this->proofFilePath; }
    public function getProofApprovedAt() { return $this->proofApprovedAt; }
    public function getApprovedBy() { return $this->approvedBy; }
    public function getNotes() { return $this->notes; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    public function verify($adminId) {
        if ($this->status !== self::STATUS_PENDING) {
            throw new DomainException('Apenas pagamentos pendentes podem ser verificados');
        }
        $this->status = self::STATUS_VERIFIED;
        $this->approvedBy = $adminId;
        $this->updatedAt = new DateTime();
    }

    public function approve($adminId, $notes = null) {
        if ($this->status !== self::STATUS_VERIFIED && $this->status !== self::STATUS_PENDING) {
            throw new DomainException('Apenas pagamentos verificados podem ser aprovados');
        }
        $this->status = self::STATUS_APPROVED;
        $this->approvedBy = $adminId;
        $this->proofApprovedAt = new DateTime();
        if ($notes) {
            $this->notes = $notes;
        }
        $this->updatedAt = new DateTime();
    }

    public function reject($adminId, $notes) {
        if ($this->status === self::STATUS_APPROVED) {
            throw new DomainException('Não é possível rejeitar um pagamento já aprovado');
        }
        $this->status = self::STATUS_REJECTED;
        $this->approvedBy = $adminId;
        $this->notes = $notes;
        $this->updatedAt = new DateTime();
    }

    public function isApproved() {
        return $this->status === self::STATUS_APPROVED;
    }
}
?>
