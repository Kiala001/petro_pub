<?php

class ReviewService {

    private ReviewRepository $repository;
    private DocumentRepository $documentRepository;

    public function __construct($db){
        $this->repository = new ReviewRepository($db);
        $this->documentRepository = new DocumentRepositoryImpl($db);
    }

    public function createReview($data, $user_id){

        $document = $this->documentRepository->findById($data['document_id']);
        if (!$document) {
            throw new DomainException('Documento não encontrado, ID: '.$data['document_id']);
        }

        $id = new ID("AV");

        $review = new Review(
            $id->getValue(),
            $data['document_id'],
            $user_id,
            $data['score'],
            $data['comment'],
            $data['suggest'],
            $data['decision']
        );
        
        $this->repository->save($review);

        return [ "success"=>true];
    }

    public function getReviewsByDocument($documentId){
        return $this->repository->findByDocument($documentId);
    }

}
