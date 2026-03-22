<?php
class Notification {

    private $id;
    private $userId;
    private $title;
    private $message;
    private $type;
    private $icon;
    private $link;
    private $visibility;
    private $roleTarget;
    private $isRead;
    private $createdAt;

    public function __construct($id, $userId, $title, $message, $type, $icon, $link, $visibility)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->icon = $icon;
        $this->link = $link;
        $this->visibility = $visibility || 'private';
        $this->isRead = false;
        $this->createdAt = new DateTime();
    }

    public function getId(){ return $this->id; }
    public function getUserId(){ return $this->userId; }
    public function getTitle(){ return $this->title; }
    public function getMessage(){ return $this->message; }
    public function getType(){ return $this->type; }
    public function getIcon(){ return $this->icon; }
    public function getLink(){ return $this->link; }
    public function getVisibility(){ return $this->visibility; }
    public function getRoleTarget(){ return $this->roleTarget; }
    public function isRead(){ return $this->isRead; }
    public function getCreatedAt(){ return $this->createdAt; }

    public function markAsRead(){
        $this->isRead = true;
    }

    public function setRoleTarget($roleTarget){
        $this->roleTarget = $roleTarget;
    }
}