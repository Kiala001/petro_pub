<?php

class DocumentService {
    private $documentRepository;
    private $categoryRepository;
    private $uploadDir;

    public function __construct(DocumentRepository $documentRepository, CategoryRepository $categoryRepository, $uploadDir) {
        $this->documentRepository = $documentRepository;
        $this->categoryRepository = $categoryRepository;
        $this->uploadDir = $uploadDir;
    }

    public function submitDocument($userId, $categoryId, $data, $file, $role) {
        try {
            // $category = $this->categoryRepository->findById($categoryId);
            // if (!$category) {
            //     throw new DomainException('Categoria não encontrada');
            // }
            
            // Validar tipo de arquivo
            $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $validExtension = ['pdf', 'application/pdf'];
            if (!in_array($fileType, $validExtension)) {
                throw new DomainException('Extensão de arquivo não permitido, apenas PDF.');
            }
            
            // Gerar caminho de arquivo
            $fileName = $this->generateFileName($file['name']);
            $filePath = $this->uploadDir . '/' . $fileName;
            
            
            // Criar documento
            $documentId = new ID("DOC");
            
            $document = new Document(
                $documentId->getValue(),
                $userId,
                $categoryId,
                $data['title'],
                $data['authors'],
                $data['advisor'] ?? NULL,
                $data['course'],
                $data['summary'],
                $data['keywords'],
                $filePath,
                $data['file_size'],
                $fileType,
                $data['price'],
                $data['accessMode'],
                $data['pubMode'],
                $data['sched_date'],
                $data['sched_time'],
                $data['payment_method'],
                // isset($data['is_paid']) ? $data['is_paid'] : true
            );

            if ($data['pubMode'] == 'immediate') {
                $document->addScheduleDate(NULL);
                $document->addScheduleTime(NULL);
            }
            
            if ($role == 'ADMIN') {
                $document->publishAdmin();
            }
            
            // Mover arquivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new DomainException('Erro ao fazer upload do arquivo');
            }
            
            $this->documentRepository->save($document);
            
            return [
                'success' => true,
                'document_id' => $documentId,
                'message' => 'Documento enviado e aguardando revisão',
            ];
        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function approveDocument($documentId, $data) {
        $documentId = $data['id'];

        try {
            $document = $this->documentRepository->findById($documentId);
            if (!$document) {
                throw new DomainException('Documento não encontrado');
            }
            
            $newDocument = $this->instanceDocument($document);
            
            $newDocument->approve();
            $message = "O artigo foi aprovado e será publicado em breve";

            $this->documentRepository->updateStatus($newDocument);

            return [
                'success' => true, 
                'message' => $message, 
                'user_id' => $newDocument->getUserId(), 
                'document' => $newDocument,
                'title' => $newDocument->getTitle(),
                'status' => $newDocument->getStatus()
            ];

        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function requirePayment($documentId, $document) {
   
        $doc = $this->documentRepository->findById($documentId);
        if (!$doc) {
            throw new DomainException('Documento não encontrado');
        }
        
        $document->waitting_payment();

        $this->documentRepository->updateStatus($document);
        
        return [
            'success' => true, 
            'message' => 'Documento aprovado e aguardando pagamento.',
            'title' => $document->getTitle(),
            'status' => $document->getStatus()
        ];
    }

    public function rejectDocument($documentId, $data) {
        try {
            $document = $this->documentRepository->findById($documentId);
            if (!$document) {
                throw new DomainException('Documento não encontrado');
            }
            
            $newDocument = $this->instanceDocument($document);
            
            $newDocument->reject();
            $message = "o artigo foi rejeitado, o autor será notificado";

            $this->documentRepository->updateStatus($newDocument);

            return [
                'success' => true, 
                'message' => $message, 
                'user_id' => $newDocument->getUserId(), 
                'document' => $newDocument,
                'title' => $newDocument->getTitle(),
                'status' => $newDocument->getStatus()
            ];

        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function searchDocuments($criteria = []) {
        try {
            $approved = $this->documentRepository->findApproved();

            // Filtrar por critérios
            if (!empty($criteria['title'])) {
                $approved = array_filter($approved, function($doc) use ($criteria) {
                    return stripos($doc->getTitle(), $criteria['title']) !== false;
                });
            }

            if (!empty($criteria['category_id'])) {
                $approved = array_filter($approved, function($doc) use ($criteria) {
                    return $doc->getCategoryId() === $criteria['category_id'];
                });
            }

            if (!empty($criteria['author'])) {
                $approved = array_filter($approved, function($doc) use ($criteria) {
                    $authors = $doc->getAuthors();
                    return in_array($criteria['author'], $authors);
                });
            }

            return [
                'success' => true,
                'documents' => $approved,
                'count' => count($approved)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDocumentDetails($documentId) {
        try {
            $document = $this->documentRepository->findById($documentId);
            if (!$document) {
                throw new DomainException('Documento que procura não foi encontrado');
            }

            return [
                'success' => true,
                'document' => $document
            ];
        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getUserDocuments($userId) {
        try {
            $documents = $this->documentRepository->findByUserId($userId);

            return [
                'success' => true,
                'documents' => $documents,
                'count' => count($documents)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDocumentsPending() {
        try {
            $documents = $this->documentRepository->findPending();

            return [
                'success' => true,
                'documents' => $documents,
                'count' => count($documents)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAllDocuments() {
        try {
            $documents = $this->documentRepository->getAll();

            $countPending = 0;
            $countApproved = 0;
            $countPaymentReq = 0;
            if (!empty($documents)) {
                foreach ($documents as $article) {
                    if ($article['status'] === "PENDENTE") {
                        $countPending++;
                    }

                    if ($article['status'] === "APROVADO") {
                        $countApproved++;
                    }

                    if ($article['status'] === 'AGUARDANDO PAGAMENTO') {
                        $countPaymentReq++;
                    }
                }
            }

            return [
                'success' => true,
                'documents' => $documents,
                'total' => count($documents),
                'pending' => $countPending,
                'approved' => $countApproved,
                'payment' => $countPaymentReq
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function generateFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid('doc_') . '_' . time() . '.' . $extension;
    }

    private function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function deleteDocument($documentId, $role) {
        try {
            $document = $this->documentRepository->findById($documentId);
            if (!$document) {
                throw new DomainException('Artigo não encontrado');
            }

            if ($document['status'] == "PENDENTE" && $document['status'] == "REJEITADO") {
                throw new DomainException('Só pode ser excluido artigo com o estado PENDENTE ou REJEITADO, estado actual: '.$document['status']);
            }
            
            $this->documentRepository->delete($documentId);

            return ['success' => true, 'message' => 'Artigo excluido com sucesso'];
        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function listAuthors($authors) { 
        $authors_json = json_decode($authors, true);
        $authors = "";
        return $authors_json;

        for ($i=0; $i < count($authors_json); $i++) { 
            if ($i <= $i-1) {
                $authors .=  $authors[$i];
                $authors .= ", ";
            }
            $authors .=  $authors[$i];
        }

        return $authors;
    }

    public function publishDocument($documentId, $document) {

        try {
            $doc = $this->documentRepository->findById($documentId);
            if (!$doc) {
                throw new DomainException('Documento não encontrado');
            }
            
            $document->published();
            $message = "O artigo foi publicado com sucesso na plataforma";

            $this->documentRepository->updateStatus($document);

            return [
                'success' => true, 
                'message' => $message, 
                'user_id' => $document->getUserId(), 
                'document' => $document,
                'title' => $document->getTitle(),
                'status' => $document->getStatus()
            ];

        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

    }

    public function instanceDocument($document_request) {
        return  $document = new Document(
                $document_request['id'],
                $document_request['user_id'],
                $document_request['category_id'],
                $document_request['title'],
                $document_request['authors'],
                $document_request['advisor'] ?? NULL,
                $document_request['course'],
                $document_request['summary'],
                $document_request['keywords'],
                $document_request['file_path'],
                $document_request['file_size'],
                $document_request['file_type'],
                $document_request['price'],
                $document_request['accessMode'],
                $document_request['pubMode'],
                $document_request['sched_date'],
                $document_request['sched_time'],
                $document_request['payment_method'],
                // isset($data['is_paid']) ? $data['is_paid'] : true
            );
    }
}
?>
