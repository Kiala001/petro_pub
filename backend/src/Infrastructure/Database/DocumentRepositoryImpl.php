<?php

interface DocumentRepository {
    public function save(Document $document);
    public function saveInfo(InfoContact $infoContact);
    public function updateStatus(Document $document);
    public function findById($id);
    public function findByUserId($userId);
    public function findByCategory($categoryId);
    public function findApproved();
    public function findPending();
    public function update(Document $document);
    public function delete($id);
    public function getAll();
}

class DocumentRepositoryImpl implements DocumentRepository {
    private $connection;

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function save(Document $document) {
        $stmt = $this->connection->prepare('
            INSERT INTO documents (
                id, user_id, category_id, title, authors, advisor, course, 
                summary, price, location, keywords, file_cover, file_path, file_size, file_type,
                pub_mode, status, version, download_link, expires_at, plagiarism_score, 
                schedule_date, schedule_time, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $document->getId(),
            $document->getUserId(),
            $document->getCategoryId(),
            $document->getTitle(),
            json_encode($document->getAuthors()),
            $document->getAdvisor(),
            $document->getCourse(),
            $document->getSummary(),
            $document->getPriceKz(),
            $document->getLocation(),
            json_encode($document->getKeywords()),
            $document->getFilePathCover(),
            $document->getFilePath(),
            $document->getFileSize(),
            $document->getFileType(),
            $document->getPubMode(),
            $document->getStatus(),
            $document->getVersion(),
            $document->getBookMode(),
            NULL,
            NULL,
            $document->getScheduleDate(),
            $document->getScheduleTime(),
            $document->getCreatedAt()->format('Y-m-d H:i:s'),
            $document->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function saveInfo(InfoContact $infoContact) {
        $stmt = $this->connection->prepare('
        INSERT INTO info_contact (
            id, document_id, tel, whatsapp, email
            ) VALUES (?, ?, ?, ?, ?)
            ');
            
        $stmt->execute([
            $infoContact->getId(),
            $infoContact->getDocumentId(),
            $infoContact->getTel(),
            $infoContact->getWhatsapp(),
            $infoContact->getEmail()
        ]);
    }

    public function findById($id) {
        $stmt = $this->connection->prepare('SELECT * FROM documents WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row;
    }

    public function getAll() {
        $stmt = $this->connection->prepare('SELECT * FROM documents ORDER BY created_at DESC');
        $stmt->execute();
        $row = $stmt->fetchAll();

        return $row;
    }

    public function findByUserId($userId) {
        $stmt = $this->connection->prepare('SELECT * FROM documents WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        return $rows;
        
        // return array_map(fn($row) => $this->mapToDocument($row), $rows);
    }

    public function findByCategory($categoryId) {
        $stmt = $this->connection->prepare('
            SELECT * FROM documents 
            WHERE category_id = ? AND status = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([$categoryId, Document::STATUS_APPROVED]);
        $rows = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->mapToDocument($row), $rows);
    }

    public function findApproved() {
        $stmt = $this->connection->prepare('
            SELECT * FROM documents 
            WHERE status = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([Document::STATUS_APPROVED]);
        $rows = $stmt->fetchAll();
        
        return array_map(fn($row) => $this->mapToDocument($row), $rows);
    }

    public function findPending() {
        $stmt = $this->connection->prepare('
            SELECT * FROM documents 
            WHERE status = ?
            ORDER BY created_at DESC
        ');
        $stmt->execute([Document::STATUS_PENDING]);
        $rows = $stmt->fetchAll();
    
        return $rows;
    }

    public function update(Document $document) {
        $stmt = $this->connection->prepare('
            UPDATE documents 
            SET title = ?, price = ?, is_paid = ?, status = ?, 
                version = ?, plagiarism_score = ?, updated_at = ?
            WHERE id = ?
        ');
        
        $stmt->execute([
            $document->getTitle(),
            $document->getPriceKz(),
            $document->isPaid() ? 1 : 0,
            $document->getStatus(),
            $document->getVersion(),
            $document->getPlagiarismScore(),
            $document->getUpdatedAt()->format('Y-m-d H:i:s'),
            $document->getId()
        ]);
    }

    public function updateStatus(Document $document) {
        $stmt = $this->connection->prepare('
            UPDATE documents 
            SET status = ?, updated_at = ?
            WHERE id = ?
        ');
        
        $re = $stmt->execute([
            $document->getStatus(),
            $document->getUpdatedAt()->format('Y-m-d H:i:s'),
            $document->getId()
        ]);

        if ($re) {
            return null;
        } else {
            throw new DomainException('Erro ao actualizar o artigo');
        }
    }

    public function delete($id) {
        $stmt = $this->connection->prepare('DELETE FROM documents WHERE id = ?');
        $stmt->execute([$id]);
    }

    private function mapToDocument($row) {
        $doc = new Document(
            $row['id'],
            $row['user_id'],
            $row['category_id'],
            $row['title'],
            json_decode($row['authors'], true),
            $row['advisor'],
            $row['course'],
            $row['summary'],
            json_decode($row['keywords'], true),
            json_decode($row['payment_method'], true),
            $row['file_path'],
            $row['file_size'],
            $row['file_type'],
            $row['price_kz'],
        );
        
        $reflection = new ReflectionClass($doc);
        
        $statusProp = $reflection->getProperty('status');
        $statusProp->setAccessible(true);
        $statusProp->setValue($doc, $row['status']);
        
        $versionProp = $reflection->getProperty('version');
        $versionProp->setAccessible(true);
        $versionProp->setValue($doc, $row['version']);
        
        if ($row['plagiarism_score']) {
            $doc->setPlagiarismScore($row['plagiarism_score']);
        }
        
        return $doc;
    }
}
?>
