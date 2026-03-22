<?php
class PaymentMethod { 
    private $id; 
    private $userId; 
    private $type; 
    private $data; 
    private $active; 
    private $createdAt; 
    public function __construct($id, $userId, $type, $data, $active ) { 
        $this->id = $id; 
        $this->userId = $userId; 
        $this->type = $type; 
        $this->data = $data; 
        $this->active = $active; 
        $this->createdAt = new DateTime(); 
    } 
    
    public function getId() { 
        return $this->id; 
    } 
    
    public function getUserId() { 
        return $this->userId; 
    } 
    
    public function getType() { 
        return $this->type; 
    } 
    
    public function getData() { 
        return $this->data; 
    } 
    
    public function isActive() { 
        return $this->active; 
    } 
} 
