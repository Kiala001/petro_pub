
<div class="dashboard" id="dash-student">
  <!-- SIDEBAR -->
    <?php 
        // require_once 'header-common-user.php'; 
    ?>
  <!-- MAIN -->
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">PetroPub <span>/ Estudante / Início</span></div>
        <h1>Olá , como está? </h1>
      </div>
      <div class="topbar-right">
        <div style="display:flex;gap:6px;flex-wrap:wrap">
          <span class="chip active">Todos</span>
          <span class="chip">Dissertações</span>
          <span class="chip">Monografias</span>
          <span class="chip">PAP</span>
        </div>
        <div class="icon-btn">🔔<span class="notif-dot"></span></div>
        <div class="icon-btn">🔍</div>
        <div class="avatar" style="width:38px;height:38px;font-size:13px;cursor:pointer">MK</div>
      </div>
    </div>

    <div class="page-content">

      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <h2>Bem-vindo à sua Biblioteca</h2>
        <p>Explore mais de 1.200 documentos académicos. O seu plano actual permite 5 downloads por mês.</p>
        <div class="wchips">
          <span class="wchip">🔍 Pesquisar Agora</span>
          <span class="wchip">⬆️ Submeter Trabalho</span>
          <span class="wchip">👑 Upgrade Premium</span>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid stats-grid-4">
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon crimson">⬇️</div>
            <span class="stat-change up">+2</span>
          </div>
          <div class="stat-num">12</div>
          <div class="stat-label">Downloads realizados</div>
        </div>
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon gold">❤️</div>
            <span class="stat-change neutral">—</span>
          </div>
          <div class="stat-num">8</div>
          <div class="stat-label">Documentos guardados</div>
        </div>
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon green">🏆</div>
            <span class="stat-change up">+120</span>
          </div>
          <div class="stat-num">850</div>
          <div class="stat-label">Pontos de gamificação</div>
        </div>
        <div class="stat-card">
          <div class="stat-top">
            <div class="stat-icon blue">💳</div>
            <span class="stat-change neutral">—</span>
          </div>
          <div class="stat-num">3</div>
          <div class="stat-label">Downloads restantes</div>
        </div>
      </div>

      <!-- Plan + Progress -->
      <div class="col-5-7">
        <div class="section-card">
          <div class="section-header">
            <div>
              <div class="section-title">Meu Plano</div>
              <div class="section-sub">Ciclo actual: Março 2025</div>
            </div>
            <span class="plan-badge">⭐ Básico</span>
          </div>
          <div style="padding:20px 24px">
            <div class="prog-wrap">
              <div class="prog-label"><span>Downloads (2/5)</span><span>40%</span></div>
              <div class="prog-bar"><div class="prog-fill" style="width:40%"></div></div>
            </div>
            <div class="prog-wrap">
              <div class="prog-label"><span>Uploads (1/2)</span><span>50%</span></div>
              <div class="prog-bar"><div class="prog-fill gold" style="width:50%"></div></div>
            </div>
            <div class="prog-wrap">
              <div class="prog-label"><span>Armazenamento (12/50 MB)</span><span>24%</span></div>
              <div class="prog-bar"><div class="prog-fill green" style="width:24%"></div></div>
            </div>
          </div>
          <div style="padding:0 24px 20px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
            <div class="plan-card"><div class="plan-name">Básico</div><div class="plan-price">0 <span>Kz/mês</span></div><div class="plan-desc">5 downloads</div></div>
            <div class="plan-card active"><div class="plan-name">Avançado</div><div class="plan-price">2.500 <span>Kz/mês</span></div><div class="plan-desc">20 downloads</div></div>
            <div class="plan-card"><div class="plan-name">Premium</div><div class="plan-price">5.000 <span>Kz/mês</span></div><div class="plan-desc">Ilimitado</div></div>
          </div>
          <div style="padding:0 24px 20px">
            <button class="btn btn-primary" style="width:100%">👑 Fazer Upgrade</button>
          </div>
        </div>

        <!-- Recent Downloads -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Downloads Recentes</div><div class="section-sub">Últimos documentos acedidos</div></div>
            <a class="section-action">Ver todos</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Documento</th><th>Tipo</th><th>Data</th><th>Estado</th></tr></thead>
              <tbody>
                <tr><td><strong>Análise do Bloco 0 de Cabinda</strong><br><small>M. Cardoso</small></td><td><span class="badge crimson">Dissertação</span></td><td>12 Mar 2025</td><td><span class="badge green">✓ Concluído</span></td></tr>
                <tr><td><strong>Gestão de RH no Sector Energético</strong><br><small>F. Santos</small></td><td><span class="badge blue">Monografia</span></td><td>10 Mar 2025</td><td><span class="badge green">✓ Concluído</span></td></tr>
                <tr><td><strong>Direito Petrolífero Angolano</strong><br><small>C. Bento</small></td><td><span class="badge blue">Monografia</span></td><td>08 Mar 2025</td><td><span class="badge orange">⏳ Pendente</span></td></tr>
                <tr><td><strong>Sistemas TIC no Ensino</strong><br><small>A. Canga</small></td><td><span class="badge gray">PAP</span></td><td>05 Mar 2025</td><td><span class="badge green">✓ Concluído</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Badges + Ranking + Notifications -->
      <div class="three-col">
        <!-- Badges -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Conquistas</div><div class="section-sub">Badges desbloqueados</div></div>
          </div>
          <div class="badge-grid">
            <div class="badge-item earned"><div class="b-icon">🥇</div><div class="b-name">1º Download</div></div>
            <div class="badge-item earned"><div class="b-icon">📚</div><div class="b-name">Leitor Ávido</div></div>
            <div class="badge-item earned"><div class="b-icon">⭐</div><div class="b-name">5 Estrelas</div></div>
            <div class="badge-item"><div class="b-icon" style="opacity:.3">🏆</div><div class="b-name" style="opacity:.4">Top 10</div></div>
            <div class="badge-item"><div class="b-icon" style="opacity:.3">🔥</div><div class="b-name" style="opacity:.4">Streak 30</div></div>
            <div class="badge-item earned"><div class="b-icon">💎</div><div class="b-name">Premium</div></div>
            <div class="badge-item"><div class="b-icon" style="opacity:.3">📝</div><div class="b-name" style="opacity:.4">Revisor</div></div>
            <div class="badge-item"><div class="b-icon" style="opacity:.3">🌟</div><div class="b-name" style="opacity:.4">Top Autor</div></div>
          </div>
          <div style="padding:0 24px 16px;text-align:center">
            <div style="font-size:13px;color:var(--text-light)">4 de 8 badges conquistados</div>
            <div class="prog-bar" style="margin-top:8px"><div class="prog-fill gold" style="width:50%"></div></div>
          </div>
        </div>

        <!-- Ranking -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Ranking Geral</div><div class="section-sub">Utilizadores mais activos</div></div>
          </div>
          <div class="rank-item"><div class="rank-num gold">1</div><div class="avatar" style="width:32px;height:32px;font-size:12px">FS</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Filomena Santos</div><div style="font-size:11px;color:var(--text-light)">UAN</div></div><div class="rank-points">2.400 pts</div></div>
          <div class="rank-item"><div class="rank-num silver">2</div><div class="avatar" style="width:32px;height:32px;font-size:12px">RC</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Ricardo Dias</div><div style="font-size:11px;color:var(--text-light)">ISPTEC</div></div><div class="rank-points">1.980 pts</div></div>
          <div class="rank-item" style="background:var(--crimson-xlight)"><div class="rank-num" style="background:var(--crimson-xlight);color:var(--crimson)">7</div><div class="avatar gold" style="width:32px;height:32px;font-size:12px">MK</div><div style="flex:1"><div style="font-size:14px;font-weight:600;color:var(--crimson)">Manuel Kiala <small>(Você)</small></div><div style="font-size:11px;color:var(--text-light)">UAN</div></div><div class="rank-points" style="color:var(--crimson)">850 pts</div></div>
          <div class="rank-item"><div class="rank-num bronze">3</div><div class="avatar" style="width:32px;height:32px;font-size:12px">EK</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Eunice Kakunda</div><div style="font-size:11px;color:var(--text-light)">UCAN</div></div><div class="rank-points">1.720 pts</div></div>
          <div class="rank-item"><div class="rank-num">4</div><div class="avatar" style="width:32px;height:32px;font-size:12px">SL</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Sónia Pimentel</div><div style="font-size:11px;color:var(--text-light)">ISPTEC</div></div><div class="rank-points">1.550 pts</div></div>
        </div>

        <!-- Notifications -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Notificações</div><div class="section-sub">5 não lidas</div></div>
            <a class="section-action">Marcar todas</a>
          </div>
          <div class="notif-item unread"><span class="n-icon">📥</span><div class="n-body"><div class="n-title">Download aprovado</div><div class="n-sub">O seu pagamento de 2.500 Kz foi confirmado</div></div><div class="notif-unread-dot"></div></div>
          <div class="notif-item unread"><span class="n-icon">🏆</span><div class="n-body"><div class="n-title">Badge conquistado!</div><div class="n-sub">Você desbloqueou "Leitor Ávido"</div></div><div class="notif-unread-dot"></div></div>
          <div class="notif-item unread"><span class="n-icon">📢</span><div class="n-body"><div class="n-title">Novo documento disponível</div><div class="n-sub">Geologia Estrutural da Margem Continental</div></div><div class="notif-unread-dot"></div></div>
          <div class="notif-item"><span class="n-icon">💬</span><div class="n-body"><div class="n-title">Resposta no fórum</div><div class="n-sub">Ricardo respondeu à sua discussão</div></div></div>
          <div class="notif-item"><span class="n-icon">⚠️</span><div class="n-body"><div class="n-title">Comprovante pendente</div><div class="n-sub">Envie o comprovante para libertar o download</div></div></div>
        </div>
      </div>

      <!-- Recommended -->
      <div class="section-card" style="animation-delay:.3s">
        <div class="section-header">
          <div><div class="section-title">Recomendados para Você</div><div class="section-sub">Baseado no seu histórico de Engenharia do Petróleo</div></div>
          <a class="section-action">Ver mais</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Título</th><th>Autor</th><th>Área</th><th>Ano</th><th>Avaliação</th><th>Preço</th><th></th></tr></thead>
            <tbody>
              <tr><td><strong>Optimização de Processos de Refino</strong></td><td>S. Pimentel</td><td><span class="badge crimson">Eng. Petróleo</span></td><td>2024</td><td>⭐⭐⭐⭐⭐ <small>(42)</small></td><td><strong>2.800 Kz</strong></td><td><button class="btn btn-primary btn-sm">Aceder</button></td></tr>
              <tr><td><strong>Geologia da Margem Continental</strong></td><td>D. Vida</td><td><span class="badge crimson">Geologia</span></td><td>2022</td><td>⭐⭐⭐⭐ <small>(28)</small></td><td><strong>3.500 Kz</strong></td><td><button class="btn btn-primary btn-sm">Aceder</button></td></tr>
              <tr><td><strong>Contrato de Partilha de Produção</strong></td><td>R. Dias</td><td><span class="badge blue">Direito</span></td><td>2022</td><td>⭐⭐⭐⭐ <small>(18)</small></td><td><strong>3.200 Kz</strong></td><td><button class="btn btn-primary btn-sm">Aceder</button></td></tr>
              <tr><td><strong>Impacto Ambiental — Bacia do Congo</strong></td><td>H. Tchinganha</td><td><span class="badge green">Ambiente</span></td><td>2023</td><td>⭐⭐⭐⭐⭐ <small>(31)</small></td><td><strong>3.000 Kz</strong></td><td><button class="btn btn-primary btn-sm">Aceder</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>