<?php

interface PointRepository {
    public function add($id, $userId, $points, $event, $refId = null);
    public function remove($userId, $points, $event);
    public function findByUser($userId, $limit = 50);
}

class PointRepositoryImpl implements PointRepository {

    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    /**
     * Adiciona pontos ao usuário
     */
    public function add($id, $userId, $points, $event, $refId = null){

        // Insere no histórico
        $stmt = $this->db->prepare("
            INSERT INTO user_points_history
            (id, user_id, event_type, points, operation, reference_id)
            VALUES (?, ?, ?, ?, 'gain', ?)
        ");

        $stmt->execute([$id, $userId, $event, $points, $refId]);

        // Atualiza total de pontos do usuário
        $stmt = $this->db->prepare("
            UPDATE users 
            SET total_points = total_points + ?
            WHERE id = ?
        ");

        $stmt->execute([$points, $userId]);
    }

    /**
     * Remove pontos do usuário
     */
    public function remove($userId, $points, $event){
        $id = uniqid('PT_');

        // Insere no histórico
        $stmt = $this->db->prepare("
            INSERT INTO user_points_history
            (id, user_id, event_type, points, operation)
            VALUES (?, ?, ?, ?, 'loss')
        ");

        $stmt->execute([$id, $userId, $event, $points]);

        // Atualiza total de pontos do usuário
        $stmt = $this->db->prepare("
            UPDATE users 
            SET total_points = total_points - ?
            WHERE id = ?
        ");

        $stmt->execute([$points, $userId]);
    }

    /**
     * Obter histórico de pontos de um usuário
     */
    public function findByUser($userId, $limit = 50){
        $stmt = $this->db->prepare("
            SELECT * FROM user_points_history
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
