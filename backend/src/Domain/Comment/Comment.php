<?php
// Domain/Comment/Comment.php
// Entidade que representa um comentário em um documento

class Comment {
    
    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';
    
    const MIN_CONTENT_LENGTH = 3;
    const MAX_CONTENT_LENGTH = 5000;
    
    private $id;
    private $documentId;
    private $userId;
    private $parentCommentId;
    private $content;
    private $status;
    private $isHelpfulCount;
    private $isNotHelpfulCount;
    private $createdAt;
    private $updatedAt;
    
    // Para carregar dados do usuário
    private $userName;
    private $userEmail;
    
    // Para carregar respostas
    private $replies = [];
    
    public function __construct(
        $id,
        $documentId,
        $userId,
        $content,
        $status = self::STATUS_APPROVED,
        $parentCommentId = null,
        $isHelpfulCount = 0,
        $isNotHelpfulCount = 0,
        $createdAt = null,
        $updatedAt = null
    ) {
        $this->id = $id;
        $this->documentId = $documentId;
        $this->userId = $userId;
        $this->parentCommentId = $parentCommentId;
        $this->content = $content;
        $this->status = $status;
        $this->isHelpfulCount = $isHelpfulCount;
        $this->isNotHelpfulCount = $isNotHelpfulCount;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
        
        $this->validate();
    }
    
    private function validate() {
        if (strlen($this->content) < self::MIN_CONTENT_LENGTH) {
            throw new Exception("Comentário deve ter no mínimo " . self::MIN_CONTENT_LENGTH . " caracteres");
        }
        
        if (strlen($this->content) > self::MAX_CONTENT_LENGTH) {
            throw new Exception("Comentário não pode exceder " . self::MAX_CONTENT_LENGTH . " caracteres");
        }
        
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED])) {
            throw new Exception("Status de comentário inválido");
        }
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getDocumentId() {
        return $this->documentId;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getParentCommentId() {
        return $this->parentCommentId;
    }
    
    public function isReply() {
        return !is_null($this->parentCommentId);
    }
    
    public function getContent() {
        return $this->content;
    }
    
    public function setContent($content) {
        $this->content = $content;
        $this->updatedAt = date('Y-m-d H:i:s');
        $this->validate();
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function setStatus($status) {
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED])) {
            throw new Exception("Status inválido");
        }
        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
    }
    
    public function approve() {
        $this->setStatus(self::STATUS_APPROVED);
    }
    
    public function reject() {
        $this->setStatus(self::STATUS_REJECTED);
    }
    
    public function isPending() {
        return $this->status === self::STATUS_PENDING;
    }
    
    public function isApproved() {
        return $this->status === self::STATUS_APPROVED;
    }
    
    public function isRejected() {
        return $this->status === self::STATUS_REJECTED;
    }
    
    public function getIsHelpfulCount() {
        return $this->isHelpfulCount;
    }
    
    public function getIsNotHelpfulCount() {
        return $this->isNotHelpfulCount;
    }
    
    public function getHelpfulnessScore() {
        $total = $this->isHelpfulCount + $this->isNotHelpfulCount;
        if ($total === 0) {
            return 0;
        }
        return round(($this->isHelpfulCount / $total) * 100);
    }
    
    public function incrementHelpful() {
        $this->isHelpfulCount++;
    }
    
    public function decrementHelpful() {
        if ($this->isHelpfulCount > 0) {
            $this->isHelpfulCount--;
        }
    }
    
    public function incrementNotHelpful() {
        $this->isNotHelpfulCount++;
    }
    
    public function decrementNotHelpful() {
        if ($this->isNotHelpfulCount > 0) {
            $this->isNotHelpfulCount--;
        }
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    public function getUpdatedAt() {
        return $this->updatedAt;
    }
    
    public function setUserName($name) {
        $this->userName = $name;
    }
    
    public function getUserName() {
        return $this->userName;
    }
    
    public function setUserEmail($email) {
        $this->userEmail = $email;
    }
    
    public function getUserEmail() {
        return $this->userEmail;
    }
    
    public function addReply(Comment $reply) {
        $this->replies[] = $reply;
    }
    
    public function getReplies() {
        return $this->replies;
    }
    
    public function setReplies(array $replies) {
        $this->replies = $replies;
    }
    
    public function getRepliesCount() {
        return count($this->replies);
    }
    
    public function toArray() {
        $data = [
            'id' => $this->id,
            'document_id' => $this->documentId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
            'parent_comment_id' => $this->parentCommentId,
            'content' => $this->content,
            'status' => $this->status,
            'is_helpful_count' => $this->isHelpfulCount,
            'is_not_helpful_count' => $this->isNotHelpfulCount,
            'helpfulness_score' => $this->getHelpfulnessScore(),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'is_reply' => $this->isReply(),
            'replies_count' => $this->getRepliesCount()
        ];
        
        if (!empty($this->replies)) {
            $data['replies'] = array_map(fn($reply) => $reply->toArray(), $this->replies);
        }
        
        return $data;
    }
}
?>
