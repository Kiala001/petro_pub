
<?php
require_once 'includes.php';

$uploadDir = '../../uploads/documents';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$documentRepository = new DocumentRepositoryImpl($db);
$categoryRepository = new CategoryRepositoryImpl($db);
$documentService = new DocumentService($documentRepository, $categoryRepository, $uploadDir);
$userId = $_SESSION['user_uuid'];

$result = $documentService->getDocumentsPending();

?>
<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetroPub – Artigos Para Avaliação</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/dashboard-style.css" rel="stylesheet">
    <link href="assets/css/elements.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>
    <header class="header">
        <div class="role-switcher">
            <span class="role-label">Olá, <?=$_SESSION['user_name']?></span>
            <button class="role-btn" onclick="">Home</button>
            <a class="role-btn" 
                href="upload-document.php?sdjbjvnjnsdjvjncvnjdn47y894rhuhihwu849u9i32jnjdsn=huhu93439u593u ufiohuw9r4 hudy3gh8jjrbfjhu34hr" 
            >Submeter Artigo</a>
    
            <button class="role-btn" onclick="">Meus Artigos</button>
            <button class="role-btn active" onclick="">Revisão por Pares</button>
        </div>
    </header>

    <section class="main" style="margin-top: 40px;">
        <section class="topbar">
            <div class="logo-wrap" onclick="goHome()">
                <svg width="44" height="44" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Book shape -->
                <rect x="30" y="20" width="140" height="160" rx="8" fill="#6B1020"/>
                <rect x="30" y="20" width="12" height="160" rx="4" fill="#E5C97E"/>
                <!-- <rect x="30" y="20" width="12" height="160" rx="4" fill="#E5C97E"/> -->
                <!-- Oil drop -->
                <ellipse cx="105" cy="75" rx="14" ry="18" fill="#E5C97E"/>
                <polygon points="105,48 94,72 116,72" fill="#E5C97E"/>
                <!-- Derrick tower simplified -->
                <rect x="96" y="88" width="18" height="55" rx="2" fill="#E5C97E" opacity="0.9"/>
                <polygon points="105,85 88,143 122,143" fill="none" stroke="#E5C97E" stroke-width="5"/>
                </svg>
                <span class="logo-text">PETRO<span>PUB</span></span>
            </div>

            <!-- <div class="topbar-left">
                <div class="breadcrumb">PetroPub <span>/ Painel</span></div>
                <h1>
                    Painel do
                    <?php
                    $userType = $_SESSION['type_auth'];
                    $profile = ($userType == "ADMIN") ? "Administrativo" : $userType ;
                    $profile = ($userType == "TEACHER") ? "Docente" : "Estudante" ;
                    
                    echo $profile;
                    ?> 
                </h1>
            </div> -->
            <div class="topbar-right">
                <a 
                    href="upload-document.php?sdjbjvnjnsdjvjncvnjdn47y894rhuhihwu849u9i32jnjdsn=huhu93439u593u ufiohuw9r4 hudy3gh8jjrbfjhu34hr" 
                    class="btn btn-primary">
                    <span>📤</span> Submeter Artigo
                </a>
                <div class="icon-btn">🔔<span class="notif-dot"></span></div>
                <div class="avatar gold" style="width:38px;height:38px;font-size:13px;cursor:pointer">Sair</div>
            </div>
        </section>
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
    </section>

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