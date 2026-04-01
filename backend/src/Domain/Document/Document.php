<?php

class Document {
    private $id;
    private $userId;
    private $categoryId;
    private $title;
    private $authors;
    private $advisor;
    private $course;
    private $summary;
    private $keywords;
    private $paymentMethod;
    private $filePath;
    private $filePathCover;
    private $fileSize;
    private $fileType;
    private $priceKz;
    private $isPaid;
    private $location;
    private $status;
    private $version;
    private $downloadLink;
    private $expiresAt;
    private $accessMode;
    private $pubMode;
    private $sched_date;
    private $sched_time;
    private $plagiarismScore;
    private $createdAt;
    private $updatedAt;

    const STATUS_PENDING = 'PENDENTE';
    const STATUS_APPROVED = 'APROVADO';
    const STATUS_REJECTED = 'REJEITADO';
    const STATUS_ARCHIVED = 'ARQUIVADO';
    const STATUS_PAYMENT_REQUIRED = 'AGUARDANDO PAGAMENTO';
    const STATUS_PROGRAMMING = 'PROGRAMADO';
    const STATUS_PUBLISHED = 'PUBLICADO';
    const STATUS_PAID = 'PAGO';

    public function __construct(
        $id,
        $userId,
        $categoryId,
        $title,
        $authors,
        $advisor,
        $course,
        $summary,
        $keywords,
        $filePath,
        $fileSize,
        $fileType,
        $fileCover,
        $pubMode,
        $sched_date,
        $sched_time,
        $price,
        $location
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->categoryId = $categoryId;
        $this->title = $this->validateTitle($title);
        $this->authors = $authors;
        $this->advisor = $advisor;
        $this->course = $course;
        $this->summary = $summary;
        $this->keywords = $keywords;
        $this->validateFileType($fileType);
        $this->filePath = $filePath;
        $this->fileSize = $fileSize;
        $this->fileType = $fileType;
        $this->filePathCover = $fileCover;
        $this->status = self::STATUS_PENDING;
        $this->version = 1;
        $this->pubMode = $pubMode;
        $this->sched_date = $sched_date;
        $this->sched_time = $sched_time;
        $this->priceKz = $price;
        $this->location = $location;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    private function validateTitle($title) {
        if (strlen($title) < 5 || strlen($title) > 200) {
            throw new InvalidArgumentException('Título deve ter entre 5 e 200 caracteres');
        }
        return $title;
    }

    private function validateFileType($fileType) {
        // Apenas PDF permitido
        $allowedTypes = ['pdf', 'application/pdf'];
        $cleanType = strtolower(trim($fileType));
        
        if (!in_array($cleanType, $allowedTypes)) {
            throw new InvalidArgumentException("Apenas arquivos PDF são permitidos. Tipo fornecido: {$fileType}");
        }
    }

    private function validatePrice($price) {
        if ($price < 0) {
            throw new InvalidArgumentException('Preço não pode ser negativo');
        }
        return (float)$price;
    }

    public function getId() { return $this->id; }
    public function getScheduleDate() { return $this->sched_date; }
    public function getScheduleTime() { return $this->sched_time; }
    public function getAccessMode() { return $this->accessMode; }
    public function getPubMode() { return $this->pubMode; }
    public function getUserId() { return $this->userId; }
    public function getCategoryId() { return $this->categoryId; }
    public function getTitle() { return $this->title; }
    public function getAuthors() { return $this->authors; }
    public function getAdvisor() { return $this->advisor; }
    public function getCourse() { return $this->course; }
    public function getSummary() { return $this->summary; }
    public function getKeywords() { return $this->keywords; }
    public function getPaymentMethods() { return $this->paymentMethod; }
    public function getFilePath() { return $this->filePath; }
    public function getFilePathCover() { return $this->filePathCover; }
    public function getFileSize() { return $this->fileSize; }
    public function getFileType() { return $this->fileType; }
    public function getPriceKz() { return $this->priceKz; }
    public function getLocation() { return $this->location; }
    public function getStatus() { return $this->status; }
    public function getVersion() { return $this->version; }
    public function getDownloadLink() { return $this->downloadLink; }
    public function getExpiresAt() { return $this->expiresAt; }
    public function getPlagiarismScore() { return $this->plagiarismScore; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    public function addPaymentMethod($paymentMethods) {
        $this->paymentMethod = $paymentMethod;
    }
    
    public function addScheduleDate($sched_date) {
        $this->sched_date = $sched_date;
    }

    public function addScheduleTime($sched_time) {
        $this->sched_time = $sched_time;
    }
    
    public function approve() {
        if ($this->status !== self::STATUS_PENDING) {
            throw new DomainException('Apenas documentos pendentes podem ser aprovados');
        }

        $this->status = self::STATUS_APPROVED;
        $this->updatedAt = new DateTime();
    }

    public function reject() {
        if ($this->status !== self::STATUS_PENDING) {
            throw new DomainException('Apenas documentos pendentes podem ser rejeitados');
        }
        $this->status = self::STATUS_REJECTED;
        $this->updatedAt = new DateTime();
    }

    public function archive() {
        if ($this->status !== self::STATUS_PENDING || $this->status !== self::STATUS_APPROVED) {
            throw new DomainException('Apenas documentos pendentes ou aprovados podem ser arquivados');
        }
        $this->status = self::STATUS_ARCHIVED;
        $this->updatedAt = new DateTime();
    }

    public function requirePayment() {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new DomainException('Documento precisa ser aprovado primeiro');
        }

        $this->status = self::STATUS_PAYMENT_REQUIRED;
    }
    
    public function waitting_payment() {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new DomainException('Apenas documentos aprovados podem ser pagos');
        }
        $this->status = self::STATUS_PAYMENT_REQUIRED;
        $this->updatedAt = new DateTime();
    }

    public function programming() {
        if ($this->status !== self::STATUS_APPROVED && $this->status !== self::STATUS_PAID) {
            throw new DomainException('Apenas documentos aprovados ou pagos podem ser estar na programação');
        }
        $this->status = self::STATUS_PROGRAMMING;
        $this->updatedAt = new DateTime();
    }

    public function published() {
        if ($this->status !== self::STATUS_APPROVED && $this->status !== self::STATUS_PROGRAMMING) {
            throw new DomainException('Apenas documentos pagos, programados ou aprovados podem ser publicados');
        }
        $this->status = self::STATUS_PUBLISHED;
        $this->updatedAt = new DateTime();
    }
    
    public function publishAdmin() {
        $this->status = self::STATUS_PUBLISHED;
    }

    public function programmingAdmin() {
        $this->status = self::STATUS_PROGRAMMING;
    }
    
    public function isPaid() {
        return $this->status == self::STATUS_PAID;
    }

    public function isApproved() {
        return $this->status === self::STATUS_APPROVED;
    }

    public function generateTemporaryDownloadLink($token, $expiresInHours = 24*30) {
        $this->downloadLink = $token;
        $this->expiresAt = (new DateTime())->modify("+{$expiresInHours} horas");
        $this->updatedAt = new DateTime();
    }

    public function setPlagiarismScore($score) {
        if ($score < 0 || $score > 100) {
            throw new InvalidArgumentException('Pontuação de plágio deve estar entre 0 e 100');
        }
        $this->plagiarismScore = $score;
    }

    public function incrementVersion() {
        $this->version++;
        $this->updatedAt = new DateTime();
    }

    public function makeFree() {
        $this->isPaid = false;
        $this->priceKz = 0;
        $this->updatedAt = new DateTime();
    }

    public function thumbnail($path, $thumbPath) {
        try {
        $imagick = new Imagick();
        $imagick->setResolution(150, 150);

        $imagick->readImage($pdfPath . '[0]');
        $imagick->setImageFormat('jpg');

        $imagick->setImageCompressionQuality(80);

        $imagick->thumbnailImage(300, 400, true);

        $imagick->writeImage($thumbnailPath);

        $imagick->clear();
        $imagick->destroy();

        return true;
    } catch (Exception $e) {
        return false;
    }
    }
}
?>
