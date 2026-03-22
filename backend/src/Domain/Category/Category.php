<?php

class Category {
    private $id;
    private $name;
    private $description;
    private $icon;
    private $allowedFileTypes;
    private $basePriceKz;
    private $requiresReview;
    private $uploadCount;
    private $downloadCount;
    private $revenueKz;
    private $createdAt;

    public function __construct(
        $id,
        $name,
        $description,
        $icon,
        array $allowedFileTypes,
        $basePriceKz,
        $requiresReview = false
    ) {
        $this->id = $id;
        $this->name = $this->validateName($name);
        $this->description = $description;
        $this->icon = $icon;
        $this->allowedFileTypes = $allowedFileTypes;
        $this->basePriceKz = $this->validatePrice($basePriceKz);
        $this->requiresReview = $requiresReview;
        $this->uploadCount = 0;
        $this->downloadCount = 0;
        $this->revenueKz = 0.0;
        $this->createdAt = new DateTime();
    }

    private function validateName($name) {
        if (strlen($name) < 3 || strlen($name) > 50) {
            throw new InvalidArgumentException('Nome da categoria inválido');
        }
        return $name;
    }

    private function validatePrice($price) {
        if ($price < 0) {
            throw new InvalidArgumentException('Preço não pode ser negativo');
        }
        return (float)$price;
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getIcon() { return $this->icon; }
    public function getAllowedFileTypes() { return $this->allowedFileTypes; }
    public function getBasePriceKz() { return $this->basePriceKz; }
    public function requiresReview() { return $this->requiresReview; }
    public function getUploadCount() { return $this->uploadCount; }
    public function getDownloadCount() { return $this->downloadCount; }
    public function getRevenueKz() { return $this->revenueKz; }
    public function getCreatedAt() { return $this->createdAt; }

    public function incrementUpload() {
        $this->uploadCount++;
    }

    public function incrementDownload() {
        $this->downloadCount++;
    }

    public function addRevenue($amountKz) {
        if ($amountKz < 0) {
            throw new InvalidArgumentException('Valor de receita não pode ser negativo');
        }
        $this->revenueKz += $amountKz;
    }

    public function isFileTypeAllowed($fileType) {
        return in_array($fileType, $this->allowedFileTypes);
    }
}
?>
