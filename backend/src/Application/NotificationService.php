<?php
class NotificationService {

    private $repo;

    public function __construct(NotificationRepository $repo){
        $this->repo = $repo;
    }

    public function notify($userId, $title, $message, $type, $icon, $link, $visibility){

        $id = new Id("NF");

        $notification = new Notification(
            $id->getValue(),
            $userId,
            $title,
            $message,
            $type,
            $icon,
            $link,
            $visibility
        );

        $this->repo->save($notification);
    }

}
