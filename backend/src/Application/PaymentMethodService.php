<?php
class PaymentMethodService { 
    private $repository; 
    
    public function __construct(PaymentMethodRepository $repository) { 
        $this->repository = $repository; 
    } 
    
    public function createMethod($userId, $input) { 
        $type = $input["type"]; 
        
        $data = json_encode($input); 
        $id = new Id("MP");
        $method = new PaymentMethod($id->getValue(), $userId, $type, $data, $input["active"] ); 
        
        $this->repository->save($method); 
        
        return [ "success" => true, "message" => "Meio de pagamento registado" ]; 
    } 
    
    public function getUserMethods($userId) { 
        return $this->repository->findByUser($userId); 
    } 
    
    public function deleteMethod($id, $userId) { 
        $result = $this->repository->delete($id, $userId);
        
        return [
            'success' => true,
            'message' => $result
        ];
    }
    
    public function updateMethod($userId, $pmId, $input) {
        $type = $input["type"]; 

        $data = json_encode($input); 

        $method = new PaymentMethod($pmId, $userId, $type, $data, $input["active"] ); 
        
        $this->repository->edit($method); 
        
        return [ "success" => true, "message" => "Meio de pagamento actualizado com sucesso" ]; 
    }
} 
