<?php
// Infrastructure/Database/CommentRepositoryImpl.php
// Implementação do repositório de comentários

require_once 'PDOConnection.php';
require_once '../../Domain/Comment/Comment.php';

class CommentRepositoryImpl {
    
    private $db;
    
    public function __construct() {
        $this->db = PDOConnection::getInstance()->getConnection();
    }
    
    public function save(Comment $comment) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO comments (
                    id, document_id, user_id, parent_comment_id, content, status, 
                    is_helpful_count, is_not_helpful_count, created_at, updated_at
                ) VALUES (
                    :id, :document_id, :user_id, :parent_comment_id, :content, :status,
                    :is_helpful_count, :is_not_helpful_count, :created_at, :updated_at
                )
            ");
            
            $stmt->execute([
                ':id' => $comment->getId(),
                ':document_id' => $comment->getDocumentId(),
                ':user_id' => $comment->getUserId(),
                ':parent_comment_id' => $comment->getParentCommentId(),
                ':content' => $comment->getContent(),
                ':status' => $comment->getStatus(),
                ':is_helpful_count' => $comment->getIsHelpfulCount(),
                ':is_not_helpful_count' => $comment->getIsNotHelpfulCount(),
                ':created_at' => $comment->getCreatedAt(),
                ':updated_at' => $comment->getUpdatedAt()
            ]);
            
            return $comment->getId();
        } catch (PDOException $e) {
            throw new Exception("Erro ao salvar comentário: " . $e->getMessage());
        }
    }
    
    public function findById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.name, u.email
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.id = :id
            ");
            
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            return $this->mapToComment($result);
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar comentário: " . $e->getMessage());
        }
    }
    
    public function findByDocumentId($documentId, $parentCommentId = null, $limit = 50, $offset = 0) {
        try {
            $query = "
                SELECT c.*, u.name, u.email
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.document_id = :document_id
            ";
            
            if ($parentCommentId === null) {
                $query .= " AND c.parent_comment_id IS NULL";
            } else {
                $query .= " AND c.parent_comment_id = :parent_comment_id";
            }
            
            $query .= " AND c.status = 'APPROVED'
                       ORDER BY c.created_at DESC
                       LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':document_id' => $documentId,
                ':parent_comment_id' => $parentCommentId,
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            
            $comments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comments[] = $this->mapToComment($row);
            }
            
            return $comments;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar comentários: " . $e->getMessage());
        }
    }
    
    public function findPendingComments($documentId = null) {
        try {
            $query = "
                SELECT c.*, u.name, u.email
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.status = 'PENDING'
            ";
            
            if ($documentId) {
                $query .= " AND c.document_id = :document_id";
            }
            
            $query .= " ORDER BY c.created_at ASC";
            
            $stmt = $this->db->prepare($query);
            
            if ($documentId) {
                $stmt->execute([':document_id' => $documentId]);
            } else {
                $stmt->execute();
            }
            
            $comments = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $comments[] = $this->mapToComment($row);
            }
            
            return $comments;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar comentários pendentes: " . $e->getMessage());
        }
    }
    
    public function update(Comment $comment) {
        try {
            $stmt = $this->db->prepare("
                UPDATE comments 
                SET content = :content, 
                    status = :status,
                    is_helpful_count = :is_helpful_count,
                    is_not_helpful_count = :is_not_helpful_count,
                    updated_at = :updated_at
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':id' => $comment->getId(),
                ':content' => $comment->getContent(),
                ':status' => $comment->getStatus(),
                ':is_helpful_count' => $comment->getIsHelpfulCount(),
                ':is_not_helpful_count' => $comment->getIsNotHelpfulCount(),
                ':updated_at' => $comment->getUpdatedAt()
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar comentário: " . $e->getMessage());
        }
    }
    
    public function delete($commentId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM comments WHERE id = :id");
            $stmt->execute([':id' => $commentId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar comentário: " . $e->getMessage());
        }
    }
    
    public function countByDocument($documentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM comments
                WHERE document_id = :document_id AND parent_comment_id IS NULL AND status = 'APPROVED'
            ");
            
            $stmt->execute([':document_id' => $documentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (PDOException $e) {
            throw new Exception("Erro ao contar comentários: " . $e->getMessage());
        }
    }
    
    public function addReaction($commentId, $userId, $reactionType) {
        try {
            // Remover reação anterior do mesmo usuário se existir
            $stmt = $this->db->prepare("
                DELETE FROM comment_reactions
                WHERE comment_id = :comment_id AND user_id = :user_id
            ");
            $stmt->execute([
                ':comment_id' => $commentId,
                ':user_id' => $userId
            ]);
            
            // Adicionar nova reação
            $reactionId = uniqid('reaction-');
            $stmt = $this->db->prepare("
                INSERT INTO comment_reactions (id, comment_id, user_id, reaction_type, created_at)
                VALUES (:id, :comment_id, :user_id, :reaction_type, :created_at)
            ");
            
            $stmt->execute([
                ':id' => $reactionId,
                ':comment_id' => $commentId,
                ':user_id' => $userId,
                ':reaction_type' => $reactionType,
                ':created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Atualizar contadores
            $this->updateReactionCounts($commentId);
            
            return $reactionId;
        } catch (PDOException $e) {
            throw new Exception("Erro ao adicionar reação: " . $e->getMessage());
        }
    }
    
    public function removeReaction($commentId, $userId) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM comment_reactions
                WHERE comment_id = :comment_id AND user_id = :user_id
            ");
            
            $stmt->execute([
                ':comment_id' => $commentId,
                ':user_id' => $userId
            ]);
            
            $this->updateReactionCounts($commentId);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erro ao remover reação: " . $e->getMessage());
        }
    }
    
    public function getUserReaction($commentId, $userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT reaction_type FROM comment_reactions
                WHERE comment_id = :comment_id AND user_id = :user_id
            ");
            
            $stmt->execute([
                ':comment_id' => $commentId,
                ':user_id' => $userId
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['reaction_type'] : null;
        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar reação do usuário: " . $e->getMessage());
        }
    }
    
    private function updateReactionCounts($commentId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE comments c
                SET 
                    is_helpful_count = (
                        SELECT COUNT(*) FROM comment_reactions 
                        WHERE comment_id = :comment_id AND reaction_type = 'HELPFUL'
                    ),
                    is_not_helpful_count = (
                        SELECT COUNT(*) FROM comment_reactions 
                        WHERE comment_id = :comment_id AND reaction_type = 'NOT_HELPFUL'
                    )
                WHERE id = :comment_id
            ");
            
            $stmt->execute([':comment_id' => $commentId]);
        } catch (PDOException $e) {
            throw new Exception("Erro ao atualizar contadores: " . $e->getMessage());
        }
    }
    
    private function mapToComment(array $data) {
        $comment = new Comment(
            $data['id'],
            $data['document_id'],
            $data['user_id'],
            $data['content'],
            $data['status'],
            $data['parent_comment_id'],
            (int)$data['is_helpful_count'],
            (int)$data['is_not_helpful_count'],
            $data['created_at'],
            $data['updated_at']
        );
        
        $comment->setUserName($data['name'] ?? '');
        $comment->setUserEmail($data['email'] ?? '');
        
        return $comment;
    }
}
?>
