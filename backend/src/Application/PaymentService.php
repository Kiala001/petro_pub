<?php

class PaymentService {
    private $paymentRepository;
    private $documentRepository;
    private $userRepository;

    public function __construct(PaymentRepository $paymentRepository, DocumentRepository $documentRepository, UserRepository $userRepository) {
        $this->paymentRepository = $paymentRepository;
        $this->documentRepository = $documentRepository;
        $this->userRepository = $userRepository;
    }

    public function initiatePayment($userId, $documentId, $method, $referenceNumber, $proofFilePath) {
        try {
            $document = $this->documentRepository->findById($documentId);
            if (!$document) {
                throw new DomainException('Documento não encontrado');
            }

            if (!$document->isPaid()) {
                return ['success' => false, 'error' => 'Documento não requer pagamento'];
            }

            $paymentId = $this->generateUUID();
            $payment = new Payment(
                $paymentId,
                $userId,
                $documentId,
                $document->getPriceKz(),
                $method,
                $referenceNumber,
                $proofFilePath
            );

            $this->paymentRepository->save($payment);

            return [
                'success' => true,
                'payment_id' => $paymentId,
                'amount_kz' => $payment->getAmountKz(),
                'message' => 'Pagamento iniciado. Aguardando verificação.'
            ];
        } catch (DomainException | InvalidArgumentException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function approvePayment($paymentId, $adminId) {
        try {
            $payment = $this->paymentRepository->findById($paymentId);
            if (!$payment) {
                throw new DomainException('Pagamento não encontrado');
            }

            $payment->approve($adminId);
            $this->paymentRepository->update($payment);

            // Adicionar créditos ao usuário
            $user = $this->userRepository->findById(new UserId($payment->getUserId()));
            if ($user) {
                $user->addBalance($payment->getAmountKz());
                $this->userRepository->update($user);
            }

            // Gerar token de download
            $downloadToken = $this->generateDownloadToken();
            $document = $this->documentRepository->findById($payment->getDocumentId());
            $document->generateTemporaryDownloadLink($downloadToken, 24);
            $this->documentRepository->update($document);

            return [
                'success' => true,
                'download_token' => $downloadToken,
                'message' => 'Pagamento aprovado'
            ];
        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function rejectPayment($paymentId, $adminId, $notes) {
        try {
            $payment = $this->paymentRepository->findById($paymentId);
            if (!$payment) {
                throw new DomainException('Pagamento não encontrado');
            }

            $payment->reject($adminId, $notes);
            $this->paymentRepository->update($payment);

            return ['success' => true, 'message' => 'Pagamento rejeitado'];
        } catch (DomainException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPendingPayments() {
        try {
            $payments = $this->paymentRepository->findByStatus(Payment::STATUS_PENDING);
            return [
                'success' => true,
                'payments' => $payments,
                'count' => count($payments)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getUserPaymentHistory($userId) {
        try {
            $payments = $this->paymentRepository->findByUserId($userId);
            return [
                'success' => true,
                'payments' => $payments,
                'count' => count($payments)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function generateDownloadToken() {
        return bin2hex(random_bytes(32));
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
}
?>
