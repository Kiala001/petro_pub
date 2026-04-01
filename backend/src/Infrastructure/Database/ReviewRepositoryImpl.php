<?php

interface ReviewRepositoryInterface {

    public function save(Review $review);

    public function findByDocument($documentId);

}

class ReviewRepository implements ReviewRepositoryInterface {

    private $conn;

    public function __construct($connection){
        $this->conn = $connection;
    }

    public function save(Review $review){

        $sql = "INSERT INTO document_review
        (id, document_id, user_id, rating, comment, suggest, decision)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            $review->getId(),
            $review->getDocumentId(),
            $review->getUserId(),
            $review->getRating(),
            $review->getComment(),
            $review->getSuggestion(),
            $review->getDecision()
        ]);

        $stmt1 = $this->conn->prepare("UPDATE documents SET read_count = ? + read_count WHERE id=?");
        $stmt1->execute([
            1, 
            $review->getDocumentId()
        ]);
 
    }

    public function findByDocument($documentId){

        $sql = "SELECT *
                FROM document_review
                WHERE document_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$documentId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'reviews' => $reviews,
            'count' => count($reviews),
        ];
    }

}
