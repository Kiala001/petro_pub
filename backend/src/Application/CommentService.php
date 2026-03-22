<?php
// Application/CommentService.php
// Serviço de aplicação para gerenciar comentários

require_once '../Domain/Comment/Comment.php';
require_once '../Infrastructure/Database/CommentRepositoryImpl.php';
require_once '../Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../Infrastructure/Database/UserRepositoryImpl.php';

class CommentService {
    
    private $commentRepository;
    private $documentRepository;
    private $userRepository;
    
    public function __construct() {
        $this->commentRepository = new CommentRepositoryImpl();
        $this->documentRepository = new DocumentRepositoryImpl();
        $this->userRepository = new UserRepositoryImpl();
    }
    
    /**
     * Criar um novo comentário
     */
    public function createComment($documentId, $userId, $content, $parentCommentId = null) {
        try {
            // Validar documento
            $document = $this->documentRepository->findById($documentId);
            if (!$document) {
                throw new Exception("Documento não encontrado");
            }
            
            // Validar usuário
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new Exception("Usuário não encontrado");
            }
            
            // Se for uma resposta, validar comentário pai
            if ($parentCommentId) {
                $parentComment = $this->commentRepository->findById($parentCommentId);
                if (!$parentComment) {
                    throw new Exception("Comentário pai não encontrado");
                }
            }
            
            // Criar comentário
            $commentId = uniqid('comment-');
            $comment = new Comment(
                $commentId,
                $documentId,
                $userId,
                $content,
                Comment::STATUS_APPROVED, // Por padrão, comentários são aprovados
                $parentCommentId
            );
            
            // Salvar
            $this->commentRepository->save($comment);
            
            // Retornar comentário com dados do usuário
            $comment->setUserName($user->getName());
            $comment->setUserEmail($user->getEmail());
            
            return $comment;
        } catch (Exception $e) {
            throw new Exception("Erro ao criar comentário: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar comentários de um documento
     */
    public function getDocumentComments($documentId, $limit = 50, $offset = 0) {
        try {
            // Buscar comentários principais (sem pai)
            $comments = $this->commentRepository->findByDocumentId(
                $documentId,
                null,
                $limit,
                $offset
            );
            
            // Para cada comentário, buscar respostas
            foreach ($comments as $comment) {
                $replies = $this->commentRepository->findByDocumentId(
                    $documentId,
                    $comment->getId(),
                    10 // Limite de respostas por comentário
                );
                $comment->setReplies($replies);
            }
            
            return $comments;
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar comentários: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar comentário específico
     */
    public function getCommentById($commentId) {
        try {
            $comment = $this->commentRepository->findById($commentId);
            if (!$comment) {
                throw new Exception("Comentário não encontrado");
            }
            return $comment;
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar comentário: " . $e->getMessage());
        }
    }
    
    /**
     * Atualizar conteúdo de um comentário
     */
    public function updateComment($commentId, $userId, $newContent) {
        try {
            $comment = $this->commentRepository->findById($commentId);
            if (!$comment) {
                throw new Exception("Comentário não encontrado");
            }
            
            // Verificar permissão (apenas autor ou admin)
            if ($comment->getUserId() !== $userId) {
                throw new Exception("Permissão negada para editar este comentário");
            }
            
            // Atualizar conteúdo
            $comment->setContent($newContent);
            $this->commentRepository->update($comment);
            
            return $comment;
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar comentário: " . $e->getMessage());
        }
    }
    
    /**
     * Deletar comentário
     */
    public function deleteComment($commentId, $userId) {
        try {
            $comment = $this->commentRepository->findById($commentId);
            if (!$comment) {
                throw new Exception("Comentário não encontrado");
            }
            
            // Verificar permissão
            if ($comment->getUserId() !== $userId) {
                throw new Exception("Permissão negada para deletar este comentário");
            }
            
            // Deletar comentário (cascata deleta respostas)
            $this->commentRepository->delete($commentId);
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Erro ao deletar comentário: " . $e->getMessage());
        }
    }
    
    /**
     * Marcar comentário como útil
     */
    public function markAsHelpful($commentId, $userId) {
        try {
            $comment = $this->commentRepository->findById($commentId);
            if (!$comment) {
                throw new Exception("Comentário não encontrado");
            }
            
            // Verificar reação anterior
            $previousReaction = $this->commentRepository->getUserReaction($commentId, $userId);
            
            if ($previousReaction === 'HELPFUL') {
                // Remover se já foi marcado
                $this->commentRepository->removeReaction($commentId, $userId);
                return 'removed';
            }
            
            // Adicionar nova reação
            $this->commentRepository->addReaction($commentId, $userId, 'HELPFUL');
            
            // Retornar comentário atualizado
            return $this->commentRepository->findById($commentId);
        } catch (Exception $e) {
            throw new Exception("Erro ao marcar como útil: " . $e->getMessage());
        }
    }
    
    /**
     * Marcar comentário como não útil
     */
    public function markAsNotHelpful($commentId, $userId) {
        try {
            $comment = $this->commentRepository->findById($commentId);
            if (!$comment) {
                throw new Exception("Comentário não encontrado");
            }
            
            // Verificar reação anterior
            $previousReaction = $this->commentRepository->getUserReaction($commentId, $userId);
            
            if ($previousReaction === 'NOT_HELPFUL') {
                // Remover se já foi marcado
                $this->commentRepository->removeReaction($commentId, $userId);
                return 'removed';
            }
            
            // Adicionar nova reação
            $this->commentRepository->addReaction($commentId, $userId, 'NOT_HELPFUL');
            
            // Retornar comentário atualizado
            return $this->commentRepository->findById($commentId);
        } catch (Exception $e) {
            throw new Exception("Erro ao marcar como não útil: " . $e->getMessage());
        }
    }
    
    /**
     * Contar comentários de um documento
     */
    public function countDocumentComments($documentId) {
        try {
            return $this->commentRepository->countByDocument($documentId);
        } catch (Exception $e) {
            throw new Exception("Erro ao contar comentários: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar comentários pendentes (admin)
     */
    public function getPendingComments($documentId = null) {
        try {
            return $this->commentRepository->findPendingComments($documentId);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar comentários pendentes: " . $e->getMessage());
        }
    }
    
    /**
     * Aprovar comentário (admin)
     */
    public function approveComment($commentId) {
        try {
            $comment = $this->commentRepository->findById($commentId);
            if (!$comment) {
                throw new Exception("Comentário não encontrado");
            }
            
            $comment->approve();
            $this->commentRepository->update($comment);
            
            return $comment;
        } catch (Exception $e) {
            throw new Exception("Erro ao aprovar comentário: " . $e->getMessage());
        }
    }
    
    /**
     * Rejeitar comentário (admin)
     */
    public function rejectComment($commentId) {
        try {
            $comment = $this->commentRepository->findById($commentId);
            if (!$comment) {
                throw new Exception("Comentário não encontrado");
            }
            
            $comment->reject();
            $this->commentRepository->update($comment);
            
            return $comment;
        } catch (Exception $e) {
            throw new Exception("Erro ao rejeitar comentário: " . $e->getMessage());
        }
    }
}
?>
