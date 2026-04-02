<?php
require_once 'includes.php';

$uploadDir = '../../uploads/documents';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$userId = $_SESSION['user_uuid'];
$result = $documentService->getUserDocuments($userId);

session_start();
if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php');
    exit;
}
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
    <title>PetroPub — Meus Artigos </title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <link href="assets/css/dashboard-style.css" rel="stylesheet">
    <link href="assets/css/my-document.css" rel="stylesheet">
    <link href="assets/css/modals.css" rel="stylesheet">
    <link href="assets/css/elements.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>
    <div class="app">
    <?php
    require_once 'header-role.php';
    ?>
    <div class="main">
        <div class="topbar">
            <div class="tb-l">
                <button class="tb-ham" onclick="openSB()">☰</button>
                <!-- <div class="tb-title">Meus Artigos</div> -->
            </div>
            <div class="tb-r">
                <a 
                    href="upload-document.php?sdjbjvnjnsdjvjncvnjdn47y894rhuhihwu849u9i32jnjdsn=huhu93439u593u ufiohuw9r4 hudy3gh8jjrbfjhu34hr" 
                    class="btn btn-primary">
                    <span>📤</span> Submeter Documento
                </a>
                <div class="icon-btn">🔔<span class="notif-dot"></span></div>
                <a href="logout.php" class="avatar gold" style="width:38px;height:38px;font-size:13px;cursor:pointer">Sair</a>
            </div>
        </div>
        <div class="page-wrap">
        <div class="page-content">
            <div class="section-card">
                <div class="section-header">
                    <div><div class="section-title">Meus Artigos</div><div class="section-sub"><?=$result['count']?> carregado(s) até agora</div></div>
                    <!-- <a class="section-action">Gerir</a> -->
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Visualizações</th>
                                <th>Avaliações</th>
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
                                <td> <?=$article['review_count']?> </td>
                                <td> <?=$article['read_count']?> </td>
                                <td>
                                    <?=renderColorStatus($article['status'])?>
                                </td>
                                <td>   
                                    <div>
                                        <!-- <button class="btn btn-success btn-sm" style="margin-bottom: 5px;">✓ Ver Detalhes</button> <br> -->
                                        <button class="btn btn-ghost btn-sm" style="margin-bottom: 5px;" onclick="location.href='edit-document.php?id=<?=encrypt($article['id'])?>'">✎ Editar</button> <br>
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
                return;
            }

            userDocuments = response.data.documents;
        }

        function openSB() {
            document.querySelector('.sidebar').classList.add('open');
            document.querySelector('.sb-ov').classList.add('open');
        }

        function closeSB() {
            document.querySelector('.sidebar').classList.remove('open');
            document.querySelector('.sb-ov').classList.remove('open');
        }
        function closeSidebar() {
            document.getElementById("sidebar").classList.remove("open");
            document.getElementById("overlay").classList.remove("open");
        }
</script>
</body>
</html>
