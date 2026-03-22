<?php
// routes/comments.php
// Rotas para o sistema de comentários

require_once '../src/Application/CommentService.php';
require_once '../src/Infrastructure/Security/JWTService.php';

$commentService = new CommentService();
$jwtService = new JWTService();

// GET /comments?document_id=xxx&limit=50&offset=0
// Listar comentários de um documento
if ($method === 'GET' && isset($_GET['document_id'])) {
    try {
        $documentId = $_GET['document_id'] ?? null;
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);
        
        if (!$documentId) {
            http_response_code(400);
            echo json_encode(['error' => 'document_id é obrigatório']);
            exit;
        }
        
        $comments = $commentService->getDocumentComments($documentId, $limit, $offset);
        $totalCount = $commentService->countDocumentComments($documentId);
        
        http_response_code(200);
        echo json_encode([
            'data' => array_map(fn($c) => $c->toArray(), $comments),
            'pagination' => [
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// POST /comments
// Criar novo comentário
if ($method === 'POST') {
    try {
        // Verificar autenticação
        $token = getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            exit;
        }
        
        $userId = $jwtService->verifyToken($token);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        // Validar campos obrigatórios
        $documentId = $data['document_id'] ?? null;
        $content = $data['content'] ?? null;
        $parentCommentId = $data['parent_comment_id'] ?? null;
        
        if (!$documentId || !$content) {
            http_response_code(400);
            echo json_encode(['error' => 'document_id e content são obrigatórios']);
            exit;
        }
        
        // Criar comentário
        $comment = $commentService->createComment(
            $documentId,
            $userId,
            $content,
            $parentCommentId
        );
        
        http_response_code(201);
        echo json_encode([
            'message' => 'Comentário criado com sucesso',
            'data' => $comment->toArray()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// PUT /comments/{comment_id}
// Atualizar comentário
if ($method === 'PUT' && preg_match('/^\/comments\/([a-z0-9\-]+)$/', $path, $matches)) {
    try {
        // Verificar autenticação
        $token = getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            exit;
        }
        
        $userId = $jwtService->verifyToken($token);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        $commentId = $matches[1];
        $content = $data['content'] ?? null;
        
        if (!$content) {
            http_response_code(400);
            echo json_encode(['error' => 'content é obrigatório']);
            exit;
        }
        
        // Atualizar comentário
        $comment = $commentService->updateComment($commentId, $userId, $content);
        
        http_response_code(200);
        echo json_encode([
            'message' => 'Comentário atualizado com sucesso',
            'data' => $comment->toArray()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// DELETE /comments/{comment_id}
// Deletar comentário
if ($method === 'DELETE' && preg_match('/^\/comments\/([a-z0-9\-]+)$/', $path, $matches)) {
    try {
        // Verificar autenticação
        $token = getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            exit;
        }
        
        $userId = $jwtService->verifyToken($token);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        $commentId = $matches[1];
        
        // Deletar comentário
        $commentService->deleteComment($commentId, $userId);
        
        http_response_code(200);
        echo json_encode(['message' => 'Comentário deletado com sucesso']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// POST /comments/{comment_id}/helpful
// Marcar como útil
if ($method === 'POST' && preg_match('/^\/comments\/([a-z0-9\-]+)\/helpful$/', $path, $matches)) {
    try {
        // Verificar autenticação
        $token = getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            exit;
        }
        
        $userId = $jwtService->verifyToken($token);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        $commentId = $matches[1];
        $result = $commentService->markAsHelpful($commentId, $userId);
        
        if ($result === 'removed') {
            http_response_code(200);
            echo json_encode(['message' => 'Voto removido']);
        } else {
            http_response_code(200);
            echo json_encode([
                'message' => 'Marcado como útil',
                'data' => $result->toArray()
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// POST /comments/{comment_id}/not-helpful
// Marcar como não útil
if ($method === 'POST' && preg_match('/^\/comments\/([a-z0-9\-]+)\/not-helpful$/', $path, $matches)) {
    try {
        // Verificar autenticação
        $token = getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token não fornecido']);
            exit;
        }
        
        $userId = $jwtService->verifyToken($token);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }
        
        $commentId = $matches[1];
        $result = $commentService->markAsNotHelpful($commentId, $userId);
        
        if ($result === 'removed') {
            http_response_code(200);
            echo json_encode(['message' => 'Voto removido']);
        } else {
            http_response_code(200);
            echo json_encode([
                'message' => 'Marcado como não útil',
                'data' => $result->toArray()
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Função auxiliar para extrair token Bearer
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/^Bearer\s+(.+)$/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}
?>
