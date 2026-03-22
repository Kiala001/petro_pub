
<div class="dashboard" id="dash-student">
  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">PETRO<span>PUB</span></div>
      <div class="sidebar-role">Painel do Estudante</div>
    </div>
    <div class="sidebar-user">
      <div class="avatar gold">MK</div>
      <div class="user-info">
        <div class="name">Manuel Kiala</div>
        <div class="email">manuel.kiala@uan.ao</div>
      </div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Principal</div>
      <div class="nav-item active"><span class="nav-icon">🏠</span> Início</div>
      <div class="nav-item"><span class="nav-icon">📚</span> Biblioteca</div>
      <div class="nav-item"><span class="nav-icon">⬆️</span> Submeter Artigo</div>
      <div class="nav-item"><span class="nav-icon">⭐</span> Gamificação</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Conta</div>
      <div class="nav-item"><span class="nav-icon">👤</span> Perfil</div>
      <div class="nav-item"><span class="nav-icon">🔔</span> Notificações <span class="nav-badge">5</span></div>
      <div class="nav-item"><span class="nav-icon">⚙️</span> Configurações</div>
      <div class="nav-item"><span class="nav-icon">🚪</span> Sair</div>
    </div>
  </aside>
  <!-- MAIN -->
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">PetroPub <span>/ Estudante / Início</span></div>
        <h1>Olá, Manuel!</h1>
      </div>
      <div class="topbar-right">
        <div class="icon-btn">🔔<span class="notif-dot"></span></div>
        <div class="avatar gold" style="width:38px;height:38px;font-size:13px;cursor:pointer">MK</div>
      </div>
    </div>
      </div>
      <script src="assets/js/dashboard-data.js"></script>

    <div class="page-wrap">

      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <h2>Bem-vindo à sua Biblioteca</h2>
        <p>Explore mais de 1.200 documentos académicos. O seu plano actual permite 5 downloads por mês.</p>
        <div class="wchips">
          <span class="wchip">Pesquisar Agora</span>
          <span class="wchip">Submeter Trabalho</span>
          <span class="wchip">Upgrade Premium</span>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid stats-grid-4">
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon crimson">⬇️</div>
            <span class="stat-change up">-</span>
          </div>
          <div class="stat-num" id="stat-downloads">...</div>
          <div class="stat-label">Downloads realizados</div>
        </div>
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon gold">❤️</div>
            <span class="stat-change neutral">—</span>
          </div>
          <div class="stat-num" id="stat-saved">...</div>
          <div class="stat-label">Documentos guardados</div>
        </div>
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon green">🏆</div>
            <span class="stat-change up">-</span>
          </div>
          <div class="stat-num" id="stat-points">...</div>
          <div class="stat-label">Pontos de gamificação</div>
        </div>
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon blue">💳</div>
            <span class="stat-change neutral">—</span>
          </div>
          <div class="stat-num" id="stat-remaining">...</div>
          <div class="stat-label">Downloads restantes</div>
        </div>
      </div>

      <!-- Badges + Ranking + Notifications -->
      <div class="two-col">

        <!-- Ranking -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Ranking Geral</div><div class="section-sub">Utilizadores mais activos</div></div>
          </div>
          <div id="ranking-list"></div>
        </div>

        <!-- Notifications -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Notificações</div><div class="section-sub">5 não lidas</div></div>
            <a class="section-action">Marcar todas</a>
          </div>
          <div id="notification-list"></div>
        </div>
      </div>

    </div>
  </div>
</div>