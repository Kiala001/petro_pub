<?php
require_once 'includes.php';

$uploadDir = '../../uploads/documents';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$userId = $_SESSION['user_uuid'];
$result = $documentService->getUserDocuments($userId);

?>
<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetroPub – Meus Artigos</title>
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
    
            <button class="role-btn active" onclick="">Meus Artigos</button>
            <button class="role-btn" onclick="">Revisão por Pares</button>
            <button class="role-btn" onclick="">Meios de pagamento</button> 
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

            <div class="topbar-left">
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
            </div>
            <div class="topbar-right">
                <a 
                    href="upload-document.php?sdjbjvnjnsdjvjncvnjdn47y894rhuhihwu849u9i32jnjdsn=huhu93439u593u ufiohuw9r4 hudy3gh8jjrbfjhu34hr" 
                    class="btn btn-primary">
                    <span>📤</span> Submeter Artigo
                </a>
                <div class="icon-btn">🔔<span class="notif-dot"></span></div>
                <a href="sair.php" class="avatar gold" style="width:38px;height:38px;font-size:13px;cursor:pointer">Sair</a>
            </div>
        </section>

        <div class="page-content">
            <div class="section-card">
                <div class="section-header">
                    <div><div class="section-title">Meus Artigos</div><div class="section-sub"><?=$result['count']?> carregados até agora</div></div>
                    <!-- <a class="section-action">Gerir</a> -->
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Downloads</th>
                                <th>Receita</th>
                                <th>Estado</th>
                                <th>Acções</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            if (!empty($result['documents'])) {
                                foreach ($result['documents'] as $article) {      
                        ?>
                            <!-- <div style="background:var(--cream);border-radius:12px;padding:16px;border:1px solid var(--border)"> -->
                            <tr>
                                <td>
                                    <strong><?=$article['title']?></strong>
                                    <br>
                                    <small><?=$article['course']?> · <?=$article['created_at']?></small>
                                </td>
                                <td>-</td>
                                <td> - </td>
                                <td>
                                    <?=renderColorStatus($article['status'])?>
                                </td>
                                <td>   
                                    <div>
                                        <button class="btn btn-success btn-sm" style="margin-bottom: 5px;">✓ Ver Detalhes</button> <br>
                                        <button class="btn btn-ghost btn-sm" style="margin-bottom: 5px;">✎ Editar</button> <br>
                                        <button class="btn btn-ghost btn-sm" style="color:#E53E3E;" onclick="deleteArticle('<?=$article['id']?>')">✕ Excluir</button>
                                    </div>
                                </td>
                            </tr>
                        <?php
                                }        
                            }else {
                                echo '
                                <tr> 
                                    <td colspan="5">   
                                        <div class="empty-state">
                                            <div class="e-icon">
                                                <i class="fa fa-book"></i>
                                            </div>
                                            <h3>Nenhum artigo carregado</h3>
                                            <p>Registe um artigo para começar a ganhar pontos e receber receitas.</p>
                                        </div>
                                    </td>
                                </tr>
                                ';
                            }
                        ?>
                        </tbody>
                    </table>
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