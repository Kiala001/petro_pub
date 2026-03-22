<?php
class PointService {

    private $pointRepo;
    private $userRepo;

    const POINT_DOCUMENT_SUBMETED = 50;
    const POINT_DOCUMENT_APPROVED = 150;
    const POINT_DOCUMENT_DOWNLOADED = 10;
    const POINT_EVALUATION = 20;
    const POINT_REVIEW = 25;
    const POINT_QUIZZES = 20;
    const POINT_DOCUMENT_REJECTED = -30;

    public function __construct(PointRepository $pointRepo, UserRepository $userRepo = null){
        $this->pointRepo = $pointRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Adiciona pontos quando um documento é submetido
     */
    public function pointDocumentSubmited($user_id) {
        $id = new Id('PT');
        $event = 'DOCUMENT_SUBMISSION';
        $point = self::POINT_DOCUMENT_SUBMETED;

        $this->pointRepo->add($id->getValue(), $user_id, $point, $event);

        return [
            'success' => true,
            'points' => $point
        ];
    }

    /**
     * Adiciona pontos quando um documento é aprovado
     */
    public function pointDocumentApproved($userId, $documentId) {
        $id = new Id('PT');
        $event = 'DOCUMENT_APPROVAL';
        $points = self::POINT_DOCUMENT_APPROVED;

        $this->pointRepo->add($id->getValue(), $userId, $points, $event, $documentId);

        return [
            'success' => true,
            'points' => $points
        ];
    }

    /**
     * Remove pontos quando um documento é rejeitado
     */
    public function pointDocumentRejected($userId) {
        $event = 'DOCUMENT_REJECTION';
        $points = abs(self::POINT_DOCUMENT_REJECTED);

        $this->pointRepo->remove($userId, $points, $event);

        return [
            'success' => true,
            'points' => self::POINT_DOCUMENT_REJECTED
        ];
    }

    /**
     * Adiciona pontos para review de documento
     */
    public function pointDocumentReview($userId, $documentId) {
        $id = new Id('PT');
        $event = 'DOCUMENT_REVIEW';
        $points = self::POINT_REVIEW;

        $this->pointRepo->add($id->getValue(), $userId, $points, $event, $documentId);

        return [
            'success' => true,
            'points' => $points
        ];
    }

    /**
     * Adiciona pontos para download de documento
     */
    public function pointDocumentDownload($userId, $documentId) {
        $id = new Id('PT');
        $event = 'DOCUMENT_DOWNLOAD';
        $points = self::POINT_DOCUMENT_DOWNLOADED;

        $this->pointRepo->add($id->getValue(), $userId, $points, $event, $documentId);

        return [
            'success' => true,
            'points' => $points
        ];
    }

    /**
     * Adiciona pontos genéricos para eventos customizados
     */
    public function awardCustomPoints($userId, $points, $eventType, $referenceId = null) {
        $id = new Id('PT');
        
        $this->pointRepo->add(
            $id->getValue(),
            $userId,
            $points,
            $eventType,
            $referenceId
        );
        
        return [
            'success' => true,
            'points' => $points
        ];
    }

    /**
     * Obter total de pontos do usuário
     */
    public function getTotalPoints($userId){
        if ($this->userRepo) {
            $user = $this->userRepo->findById(new UserId('US', $userId));
            return $user['total_points'] ?? 0;
        }
        return 0;
    }

    /**
     * Obter histórico de pontos do usuário
     */
    public function getPointsHistory($userId, $limit = 50){
        return $this->pointRepo->findByUser($userId, $limit);
    }

}


