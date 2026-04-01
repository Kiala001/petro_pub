<?php
// Página de gamificação: pontos, ranking, conquistas
session_start();
if (isset($_SESSION['jwt_auth'])) {
    header('Location: my-documents.php');
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
    <title>PetroPub – Acesso à Plataforma</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/auth-style.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>

<div class="bg-layer"></div>
<div class="bg-overlay"></div>

<!-- Orbs -->
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="page-wrap">

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <div class="brand">
      <img src="logo.jpg" width="100" height="100" style="border-radius: 10px;">
    </div>

    <div class="left-middle">
      <h1>Biblioteca<br>Académica<br><em>Digital</em></h1>
      <p>Aceda a milhares de dissertações, monografias e trabalhos académicos de instituições angolanas. A plataforma de referência para pesquisa científica.</p>
      <div class="left-btns">
        <button class="pill-btn">📖 O que esperar?</button>
        <button class="pill-btn">🔭 Explorar Acervo</button>
      </div>
    </div>

    <div class="left-footer">
      <div class="stat-mini">
        <div class="n">1.200+</div>
        <div class="l">Documentos</div>
      </div>
      <div class="stat-mini">
        <div class="n">48</div>
        <div class="l">Instituições</div>
      </div>
      <div class="stat-mini">
        <div class="n">5.800+</div>
        <div class="l">Utilizadores</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <div class="form-card">

      <!-- TABS -->
      <div class="tab-row">
        <button class="tab-btn active" id="tab-login" onclick="switchTab('login')">Iniciar Sessão</button>
        <button class="tab-btn" id="tab-register" onclick="switchTab('register')">Criar Conta</button>
      </div>

      <!-- ===== LOGIN FORM ===== -->
      <div class="form-section visible" id="section-login">
        <h2>Bem-vindo de Volta</h2>
        <p class="sub">Entre na sua conta para continuar</p>

        <div class="field">
          <label for="l-email">E-mail</label>
          <div class="input-wrap">
            <input type="email" id="l-email" placeholder="utilizador@gmail.ao" autocomplete="email">
            <span class="input-icon">
              <i class="fa fa-envelope"></i>
            </span>
          </div>
        </div>

        <div class="field">
          <label for="l-password">Palavra-passe</label>
          <div class="input-wrap">
            <input type="password" id="l-password" placeholder="••••••••••" autocomplete="current-password">
            <span class="input-icon toggle-pw" onclick="togglePw('l-password', this)">
              <i class="fa fa-eye"></i>
            </span>
          </div>
        </div>
        
        <div class="field">
          <label for="l-type">Tipo de Usuário</label>
          <div class="input-wrap">
            <select id="l-type">
              <option value="" disabled selected>Seleccione o seu tipo de usuário</option>
              <option value="COMMON_USER">Usuário Comum</option>
              <option value="TEACHER">Professor / Coordenador</option>
              <option value="ADMIN">Administrador</option>
            </select>
            <i class="input-icon fa fa-user"></i>
          </div>
        </div>

        <div class="extras-row">
          <label class="check-wrap">
            <input type="checkbox" checked>
            <span>Lembrar-me</span>
          </label>
          <a class="forgot-link">Esqueci a senha</a>
        </div>

        <button class="btn-submit" onclick="doLogin()">Entrar na Plataforma →</button>

        <div class="divider"><span> | </span></div>

        <!-- <button class="btn-social" onclick="showToast('🔗 Autenticação institucional em breve!')">
          🏛️ Entrar como Admin ou Docente
        </button> -->

        <div class="switch-txt">
          Não tem conta? <a onclick="switchTab('register')">Registe-se aqui</a>
        </div>
      </div>

      <!-- ===== REGISTER FORM ===== -->
      <div class="form-section" id="section-register">
        <h2>Criar Conta</h2>
        <p class="sub">Junte-se à comunidade académica angolana</p>

        <div class="field-row">
          <div class="field">
            <label>Nome *</label>
            <div class="input-wrap">
              <input type="text" id="r-fname" placeholder="Nome">
            </div>
          </div>
          <div class="field">
            <label>Apelido *</label>
            <div class="input-wrap">
              <input type="text" id="r-lname" placeholder="Apelido">
            </div>
          </div>
        </div>

        <div class="field">
          <label>E-mail *</label>
          <div class="input-wrap">
            <input type="email" id="r-email" placeholder="email@exemplo.ao">
            <span class="input-icon">
              <i class="fa fa-envelope"></i>
            </span>
          </div>
        </div>

        <div class="field">
          <label>Tipo de Usuário *</label>
          <div class="input-wrap">
            <select id="r-inst">
              <option value="" disabled selected>Seleccione o seu tipo de usuário</option>
              <option value="COMMON_USER">Usuário Comum</option>
              <option value="TEACHER">Professor / Coordenador</option>
            </select>
            <span class="input-icon">
              <i class="fa fa-user"></i>
            </span>
          </div>
        </div>

        <div class="field">
          <label>Palavra-passe *</label>
          <div class="input-wrap">
            <input type="password" id="r-password" placeholder="Mínimo 8 caracteres" oninput="checkStrength(this.value)">
            <span class="input-icon toggle-pw" onclick="togglePw('r-password', this)">
              <i class="fa fa-eye"></i>
            </span>
          </div>
          <div class="strength-bar"><div class="strength-fill" id="s-fill"></div></div>
        </div>

        <div class="field" style="margin-top:4px">
          <label class="check-wrap" style="display:flex;align-items:flex-start;gap:8px;cursor:pointer">
            <input type="checkbox" id="r-terms" style="margin-top:2px;accent-color:var(--gold)">
            <span style="font-size:12px;color:rgba(255,255,255,0.45);line-height:1.5">Aceito os <a style="color:var(--gold-light);cursor:pointer">Termos de Uso</a> e a <a style="color:var(--gold-light);cursor:pointer">Política de Privacidade</a> da PetroPub</span>
          </label>
        </div>

        <button class="btn-submit" onclick="doRegister()" style="margin-top:8px">Criar Conta Gratuita →</button>

        <div class="switch-txt">
          Já tem conta? <a onclick="switchTab('login')">Entrar aqui</a>
        </div>
      </div>

    </div>
  </div>

</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script src="assets/js/util.js"></script>
<script src="assets/js/login.js"></script>
<script src="assets/js/auth.js"></script>
<script src="assets/js/api.js"></script>
<script src="assets/js/register.js"></script>
<script>
</script>
</body>
</html>
