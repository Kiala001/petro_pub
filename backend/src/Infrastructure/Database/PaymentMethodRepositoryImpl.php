<?php
interface PaymentMethodRepository { 
    public function save(PaymentMethod $method); 
    public function findByUser($userId); 
    public function delete($id, $userId); 
} 

class PaymentMethodRepositoryImpl implements PaymentMethodRepository { 
    private $db; 
    
    public function __construct(PDO $db) { 
        $this->db = $db; 
    } 
    
    public function save(PaymentMethod $method) { 
        $stmt = $this->db->prepare(" INSERT INTO payment_methods (id, user_id, type, data, active) VALUES (?, ?, ?, ?, ?) "); 
        
        return $stmt->execute([ 
            $method->getId(), 
            $method->getUserId(), 
            $method->getType(), 
            json_encode($method->getData()), 
            $method->isActive() 
        ]); 
    
    }

    public function edit(PaymentMethod $method) { 
        $stmt = $this->db->prepare("UPDATE payment_methods SET type=?, data=?, active=? WHERE id=? AND user_id=?"); 
        
        return $stmt->execute([ 
            $method->getType(), 
            json_encode($method->getData()), 
            $method->isActive(),
            $method->getId(), 
            $method->getUserId()
        ]); 
    
    } 
    
    public function findByUser($userId) { 
        $stmt = $this->db->prepare(" SELECT * FROM payment_methods WHERE user_id = ? "); 
        
        $stmt->execute([$userId]); 
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    } 
    
    public function delete($id, $userId) { 
        $stmt = $this->db->prepare(" DELETE FROM payment_methods WHERE id = ? AND user_id = ? "); 
        $result = $stmt->execute([$id, $userId]); 
        if ($result) {
            return 'Meio de pagamento excluido com sucesso';
        }

        return 'Erro ao excluir o meio de pagamento';
    } 
} 
