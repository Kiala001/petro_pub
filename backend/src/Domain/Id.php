
<?php

class ID {
    
    private $value;
    
    private static $counter = 1000; 
    
    public function __construct($prefix) {
        if ($prefix === null) {
            $prefix = "Us";
        }
        
        $this->value = $this->generateId($prefix);
    }
    
    private function generateId($prefix) {
        $timestamp = time();
        $random = random_int(1000, 9999);
        $id = sprintf($prefix.'-%04d', ($timestamp % 10000));
        
        return $id;
    }
    
    private function validate($id) {
        // Validar formato Us-XXXX
        if (!preg_match('/^Us-\d{4}$/', $id)) {
            throw new Exception("ID de usuário deve estar no formato Us-XXXX");
        }
    }
    
    public static function generate() {
        return new self();
    }
    
    public function getValue() {
        return $this->value;
    }
    
    public function __toString() {
        return $this->value;
    }

    public function __fromString($value) {
        $this->value = $value;
    }
    
    public function equals(ID $other) {
        return $this->value === $other->getValue();
    }
}
?>