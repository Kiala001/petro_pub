<?php
class DocumentReviewService {

    private $documentRepo;
    private $userRepo;
    private $reviewRepo;

    public function __construct(
        DocumentRepository $documentRepo,
        ReviewRepositoryInterface $reviewRepo,
        UserRepository $userRepo
    ){
        $this->documentRepo = $documentRepo;
        $this->reviewRepo = $reviewRepo;
        $this->userRepo = $userRepo;
    }

    public function getDocumentsWithReviews(){

                
        $documents = $this->documentRepo->getAll();
        $result = [];
        
        foreach($documents as $doc){
            
            $reviewData = $this->reviewRepo->findByDocument($doc['id']);
            $reviews = $reviewData['reviews'];
            
            $mappedReviews = [];
            $sum = 0;
            $i = 1;

            foreach($reviews as $rv){
                
                $sum += (int)$rv['rating'];
                
                $decision = $this->mapDecision($rv['decision']);
                $id = new UserId("US");
                $id->__fromString($rv['user_id']);
                
                $role = "";
                $initials = "";
                $name = "";
                $user = $this->userRepo->findById($id);
                
                if (!empty($user)) {
                    $initials = $this->getInitials($user['name']);
                    $role = ($user['type'] === "TEACHER") ? "Docente" : "Estudante/Usuário Comum";

                    $role = $role." | ".$user['email']." | ".$user['points']." pontos";

                    $name = $user['name'];
                }

                $mappedReviews[] = [
                    "n" => $i++,
                    "score" => (int)$rv['rating'],
                    "dcls" => $decision['cls'],
                    "dLbl" => $decision['label'],
                    "dbdg" => $decision['badge'],
                    "comment" => $rv['comment'],
                    "suggestion" => $rv['suggest'],
                    "rev" => [
                        "ini" => $initials,
                        "name" => $name,
                        "role" => $role
                    ],
                    "date" => date("d M Y", strtotime($rv['created_at']))
                ];
            }
            
            $avg = count($reviews) ? $sum / count($reviews) : 0;
            
            $author = json_decode($doc['authors']);
            $author = explode(", ", $author);
            
            $result[] = [
                "id" => $doc['id'],
                "status" => strtolower($doc['status']),
                "cat" => $doc['category_id'],
                "file_size" => $doc['file_size'],
                "type" => $doc['title'],
                "course" => $doc['course'],
                "ico" => "📄",
                "bg" => "ic-b",
                "title" => $doc['title'],
                "author" =>  $this->arrayForString($author),
                "inst" => $doc['advisor'],
                "year" => date("Y", strtotime($doc['created_at'])),
                "pages" => $doc['file_size'],
                "avg" => round($avg,1),
                "reviews" => $mappedReviews,
                "documentId" => encrypt($doc['id'])
            ];

        }
        
        return ['documents' => $result, 'success' => true, 'total' => count($documents)];
    }

    private function mapDecision($decision){

        switch($decision){

            case "APROVADO":
                return [
                    "cls" => "rv-ok",
                    "label" => "Aprovado",
                    "badge" => "bg"
                ];

            case "REJEITADO":
                return [
                    "cls" => "rv-er",
                    "label" => "Rejeitado",
                    "badge" => "br"
                ];

            default:
                return [
                    "cls" => "rv-wn",
                    "label" => "Pedido de Revisão",
                    "badge" => "bv"
                ];
        }
    }

    private function getInitials(string $name): string {
        $name = trim($name);

        if ($name === '') {
            return '';
        }

        $parts = explode(' ', $name);

        $initials = '';

        foreach ($parts as $part) {
            if ($part !== '') {
                $initials .= strtoupper($part[0]);
            }
        }

        return $initials;
    }

    private function arrayForString($array) {
        $list = array_map(function($item) {
            return trim($item, '[]"');
        }, $array);

        $result = implode(", ", $list);
        return $result;
    }
}
