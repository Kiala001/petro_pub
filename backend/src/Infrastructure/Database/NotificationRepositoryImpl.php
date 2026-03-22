<?php
interface NotificationRepository {

    public function save(Notification $notification);
    public function findByUser($userId);
    public function markAsRead($id);
}

class NotificationRepositoryImpl implements NotificationRepository {

    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    public function save(Notification $n){
        $stmt = $this->db->prepare("
            INSERT INTO notifications 
            (id, user_id, title, message, type, icon, link, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $n->getId(),
            $n->getUserId(),
            $n->getTitle(),
            $n->getMessage(),
            $n->getType(),
            $n->getIcon(),
            $n->getLink(),
            0,
            (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    public function findByUser($userId){
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markAsRead($id){
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
}