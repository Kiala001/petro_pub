<?php

interface CategoryRepository {
    public function save(Category $category);
    public function findById($id);
    public function findByName($name);
    public function update(Category $category);
    public function delete($id);
    public function all();
}

class CategoryRepositoryImpl implements CategoryRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save(Category $category) {
        $stmt = $this->connection->prepare('
            INSERT INTO categories (id, name, description, icon, allowed_file_types, base_price_kz, requires_review, upload_count, download_count, revenue_kz, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $category->getId(),
            $category->getName(),
            $category->getDescription(),
            $category->getIcon(),
            json_encode($category->getAllowedFileTypes()),
            $category->getBasePriceKz(),
            $category->requiresReview() ? 1 : 0,
            $category->getUploadCount(),
            $category->getDownloadCount(),
            $category->getRevenueKz(),
            $category->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function findById($id) {
        $stmt = $this->connection->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        
        return $row ? $this->mapToCategory($row) : null;
    }

    public function findByName($name) {
        $stmt = $this->connection->prepare('SELECT * FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        
        return $row ? $this->mapToCategory($row) : null;
    }

    public function update(Category $category) {
        $stmt = $this->connection->prepare('
            UPDATE categories 
            SET description = ?, upload_count = ?, download_count = ?, revenue_kz = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            $category->getDescription(),
            $category->getUploadCount(),
            $category->getDownloadCount(),
            $category->getRevenueKz(),
            $category->getId()
        ]);
    }

    public function delete($id) {
        $stmt = $this->connection->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function all() {
        $stmt = $this->connection->query('SELECT * FROM categories');
        $rows = $stmt->fetchAll();
        
        return array_map(function($row) {
            return $this->mapToCategory($row);
        }, $rows);
    }

    private function mapToCategory($row) {
        $category = new Category(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['icon'],
            json_decode($row['allowed_file_types'], true),
            $row['base_price_kz'],
            (bool)$row['requires_review']
        );
        
        // Restaurar estado do objeto
        $reflection = new ReflectionClass($category);
        
        $uploadProp = $reflection->getProperty('uploadCount');
        $uploadProp->setAccessible(true);
        $uploadProp->setValue($category, $row['upload_count']);
        
        $downloadProp = $reflection->getProperty('downloadCount');
        $downloadProp->setAccessible(true);
        $downloadProp->setValue($category, $row['download_count']);
        
        $revenueProp = $reflection->getProperty('revenueKz');
        $revenueProp->setAccessible(true);
        $revenueProp->setValue($category, $row['revenue_kz']);
        
        return $category;
    }
}
?>
