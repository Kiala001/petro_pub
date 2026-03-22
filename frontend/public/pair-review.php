<?php
require_once 'includes.php';
if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php');
    exit;
}

$uploadDir = '../../uploads/documents';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$documentRepository = new DocumentRepositoryImpl($db);
$categoryRepository = new CategoryRepositoryImpl($db);
$documentService = new DocumentService($documentRepository, $categoryRepository, $uploadDir);
$userId = $_SESSION['user_uuid'];

$result = $documentService->getDocumentsPending();

$jwt = $_SESSION['jwt_auth'];
$userName = $_SESSION['user_name'] ?? 'Usuário';
$userEmail = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetroPub — Documentos para Avaliação</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/gamificacao.css">
    <link href="assets/css/dashboard-style.css" rel="stylesheet">
    <link href="assets/css/modals.css" rel="stylesheet">
    <link href="assets/css/elements.css" rel="stylesheet">
    <link href="assets/css/sidebar.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>
    
<div class="app">
    
  <?php
  require_once 'header-role.php';
  ?>
  <!-- Main -->
  <div class="main">
    <div class="topbar">
      <div class="tb-l">
        <button class="tb-ham" onclick="openSB()">☰</button>
        <div class="tb-title">Documentos para Avaliação</div>
      </div>
      <div class="tb-r">
    <div class="topbar-right">
        <a 
            href="upload-document.php?sdjbjvnjnsdjvjncvnjdn47y894rhuhihwu849u9i32jnjdsn=huhu93439u593u ufiohuw9r4 hudy3gh8jjrbfjhu34hr" 
            class="btn btn-primary">
            <span>📤</span> Submeter Artigo
        </a>
        <div class="icon-btn">🔔<span class="notif-dot"></span></div>
        <div class="avatar gold" style="width:38px;height:38px;font-size:13px;cursor:pointer">Sair</div>
    </div>    
    </div>
    </div>
    <div class="page-wrap">
        <section class="main">
        <div class="page-wrap">
            <!-- ROLE BANNER -->
            <div class="role-banner rb-teacher" id="role-banner" style="padding-left: 20px;">
                <span class="rb-icon">
                    <!-- <i class="fa fa-book"></i> -->
                </span>
                <div>
                <div class="rb-title" id="rb-title">Documentos para Avaliação</div>
                <div class="rb-desc" id="rb-desc">Pode ver todas as avaliações, comentários, média e status. Opcionalmente, identidade dos avaliadores.</div>
                <div class="rb-chips" id="rb-chips">
                    <span class="rb-chip can">✓ Todas as avaliações</span>
                    <span class="rb-chip can">✓ Comentários</span>
                    <span class="rb-chip can">✓ Média de avaliação</span>
                    <span class="rb-chip can">✓ Status atual</span>
                    <span class="rb-chip can">✓ Identidade do avaliador (opcional)</span>
                </div>
                </div>
            </div>
        </div>
    </section>
         <div class="page-content">
            <div class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Artigos de Estudantes & Docentes</div><div class="section-sub"><?=$result['count']?> artigo(s) que precisa(m) ser avaliado(s). Provavelmente o seu também está a ser contabilizado mas não poderás avaliar o seu próprio artigo.</div>
                    </div>
                </div>
                <div style="padding:16px 24px;display:flex;flex-direction:column;gap:12px">
                <?php
                    if (!empty($result['documents'])) {
                        foreach ($result['documents'] as $article) {  
                            if ($article['user_id'] != $userId) {
                                $authors = json_decode($article['authors']);
                                $authors_list = explode(",", $authors);
                  
                                // Get info for user
                                $id = new UserId("US");
                                $id->__fromString($article['user_id']);
                                $user = $userRepository->findById($id);

                                // Get review stat
                                $result = $reviewService->getReviewsByDocument($article['id']);
                                $reviews = $result['reviews'];
                                $review_count = $result['count'];

                                $review_stat = calcularMediaAvaliacoes($reviews);
                                ?>
                                <div class="doc-card" data-status="avaliacao" data-title="Sistema de Gestão Hospitalar Distribuído">
                                    <div class="dc-header">
                                        <div class="dc-type-icon ic-blue" style="font-size:26px; color: var(--crimson);"><i class="fa fa-book"></i></div>
                                        <div class="dc-info">
                                        <div class="dc-meta-row">
                                            <span class="badge b-orange">⏳ Em Avaliação</span>
                                            <span class="badge b-blue"><?=$article['category_id']?></span>
                                        </div>
                                        <div class="dc-title"><?=$article['title']?></div>
                                        <div class="dc-author-row">
                                            <span><div class="fa fa-user"></div> <strong><?=$user['name']?></strong></span>
                                            <span><i class="fa fa-calendar"></i> <strong><?=$article['created_at']?></strong></span>
                                            <span><i class="fa fa-graduate"></i> <strong><?=$article['course']?></strong></span>
                                            <span><i class="fa fa-file"></i> <strong>-- págs.</strong></span>
                                        </div>
                                        </div>
                                        <div class="dc-actions-col">
                                        <div class="dc-avg">
                                            <div class="dc-avg-num"><?=$review_stat['media']?></div>
                                            <div class="dc-avg-stars"><?=$review_stat['stars']?></div>
                                            <div class="dc-avg-lbl"><?=$review_count?> avaliações</div>
                                        </div>
                                        <a class="btn btn-primary btn-sm" href="review.php?flex-direction=<?=encrypt($article['id'])?>">✎ Avaliar</a>
                                        </div>
                                    </div>
                                    <div class="dc-footer-actions">
                                        <!-- <button class="btn btn-ghost btn-sm">👁 Ver Documento</button> -->
                                        <!-- <button class="btn btn-primary btn-sm" onclick="showToast('📋 A abrir formulário de avaliação…')">✎ Nova Avaliação</button> -->
                                    </div>
                                </div>
                                <?php
                            }        
                        }        
                    }else {
                        echo '
                            <div style="text-align:center;padding:8px 0">
                                <span class="section-action">Nenhum artigo precisando ser avaliado.</span>
                            </div>
                        ';
                    }
                ?>
                </div>
            </div>

        </div>
    </div>
  </div>
</div>

    <div class="toast" id="toast"></div>
    <?php
    require_once 'assets/modals/document.php';
    ?>

    <script src="assets/js/api.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/my-documents.js"></script>
    <script>        
        loadUserDocuments();

        async function loadUserDocuments() {
            const userData = getUserData();
            const response = await apiRequest(`documents/user/${userData.user_id}`);

            if (!response.data.success) {
                // showToast(response.data.error || 'Erro ao carregar documentos');
                return;
            }

            userDocuments = response.data.documents;
        }
</script>
</body>
</html>
