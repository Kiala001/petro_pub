<?php

class Review {

    private string $id;
    private string $documentId;
    private string $userId;
    private string $rating;
    private string $comment;
    private string $suggest;
    private string $decision;

    public function __construct(
        $id,
        $documentId,
        $userId,
        $rating,
        $comment,
        $suggest,
        $decision
    ){

        if($rating < 1 || $rating > 5){
            throw new Exception("Rating inválido");
        }

        $this->id = $id;
        $this->documentId = $documentId;
        $this->userId = $userId;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->suggest = $suggest;
        $this->decision = $decision;
    }

    public function getId(){
        return $this->id;
    }
    
    public function getDocumentId(){
        return $this->documentId;
    }

    public function getUserId(){
        return $this->userId;
    }

    public function getRating(){
        return $this->rating;
    }

    public function getComment(){
        return $this->comment;
    }

    public function getSuggestion(){
        return $this->suggest;
    }

    public function getDecision(){
        if ($this->decision == "approve") {
            return "APROVADO";
        } elseif ($this->decision == "reject") {
            return "REJEITADO"; 
        } elseif($this->decision == "other") {  
            return  "other"; 
        }else {
            return "REVISÃO";
        }
    }

}