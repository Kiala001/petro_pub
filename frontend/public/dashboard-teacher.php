
<div class="dashboard" id="dash-teacher">
  <!-- <aside class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">PETRO<span>PUB</span></div>
      <div class="sidebar-role">Portal do Docente</div>
    </div>
    <div class="sidebar-user">
      <div class="avatar gold">JP</div>
      <div class="user-info">
        <div class="name">Prof. João Pinto</div>
        <div class="email">j.pinto@isptec.ao</div>
      </div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Principal</div>
      <div class="nav-item active"><span class="nav-icon">🏠</span> Início</div>
      <a href="submit-docment.php" class="nav-item"><span class="nav-icon">📤</span> Submeter Trabalho</a>
      <div class="nav-item"><span class="nav-icon">📂</span> Meus Documentos</div>
      <div class="nav-item"><span class="nav-icon">✅</span> Revisão por Pares <span class="nav-badge red">4</span></div>
      <div class="nav-item"><span class="nav-icon">⏰</span> Uploads Programados</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Financeiro</div>
      <div class="nav-item"><span class="nav-icon">💰</span> Receitas</div>
      <div class="nav-item"><span class="nav-icon">📊</span> Relatórios</div>
      <div class="nav-item"><span class="nav-icon">💳</span> Saques</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Gestão</div>
      <div class="nav-item"><span class="nav-icon">👥</span> Meus Alunos</div>
      <div class="nav-item"><span class="nav-icon">💬</span> Comentários <span class="nav-badge">7</span></div>
      <div class="nav-item"><span class="nav-icon">🔔</span> Notificações <span class="nav-badge red">3</span></div>
    </div>
    <div class="sidebar-footer">
      <div class="nav-item"><span class="nav-icon">⚙️</span> Configurações</div>
      <a href="logout.php" class="nav-item"><span class="nav-icon"><i class="fa fa-logout"></i></span> Sair</a>
    </div>
  </aside> -->

  <div class="main">
    <!-- <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">PetroPub <span>/ Docente / Painel</span></div>
        <h1>Painel do Docente</h1>
      </div>
      <div class="topbar-right">
        <button class="btn btn-primary"><span>📤</span> Novo Upload</button>
        <div class="icon-btn">🔔<span class="notif-dot"></span></div>
        <div class="avatar gold" style="width:38px;height:38px;font-size:13px;cursor:pointer">JP</div>
      </div>
    </div> -->

    <div class="page-content">

      <!-- Welcome -->
      <div class="welcome-banner" style="background:linear-gradient(135deg,#1A3A4A,#2A5C6A)">
        <h2>Bem-vindo, Prof. João 👨‍🏫</h2>
        <p>Você tem 4 trabalhos aguardando revisão por pares e 2 uploads programados para esta semana.</p>
        <div class="wchips">
          <span class="wchip">📤 Novo Documento</span>
          <span class="wchip">✅ Revisar Pendentes</span>
          <span class="wchip">📊 Ver Relatório</span>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid stats-grid-4">
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon crimson">📂</div><span class="stat-change up">+3</span></div>
          <div class="stat-num">24</div>
          <div class="stat-label">Documentos publicados</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon gold">⬇️</div><span class="stat-change up">+142</span></div>
          <div class="stat-num">1.847</div>
          <div class="stat-label">Total de downloads</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon green">💰</div><span class="stat-change up">+18%</span></div>
          <div class="stat-num">124.500</div>
          <div class="stat-label">Receita total (Kz)</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon blue">✅</div><span class="stat-change neutral">4 pend.</span></div>
          <div class="stat-num">4</div>
          <div class="stat-label">Aguardando revisão</div>
        </div>
      </div>

      <!-- Upload + Analytics -->
      <div class="col-5-7">
        <!-- Upload -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Submeter Documento</div><div class="section-sub">Upload com revisão por pares</div></div>
          </div>
          <div class="upload-zone">
            <div class="icon">📄</div>
            <p><strong>Arraste o ficheiro aqui</strong><br>PDF ou DOCX até 50MB<br><small style="color:var(--text-light)">Passará por revisão antes da publicação</small></p>
          </div>
          <div style="padding:16px 24px;display:flex;flex-direction:column;gap:10px">
            <input style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;font-family:'Arial',sans-serif;outline:none;color:var(--text-dark)" placeholder="Título do documento">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <select style="padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;font-family:'Arial',sans-serif;outline:none;color:var(--text-mid);background:white">
                <option>Tipo de documento</option>
                <option>Dissertação</option>
                <option>Monografia</option>
                <option>PAP</option>
                <option>Artigo Científico</option>
              </select>
              <input type="number" style="padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;font-family:'Arial',sans-serif;outline:none;color:var(--text-dark)" placeholder="Preço (Kz)">
            </div>
            <input type="datetime-local" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;font-family:'Arial',sans-serif;outline:none;color:var(--text-mid)" title="Upload programado">
            <button class="btn btn-primary" style="width:100%">📤 Submeter para Revisão</button>
          </div>
        </div>

        <!-- Revenue Chart -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Receita por Documento</div><div class="section-sub">Últimos 6 meses — em Kz</div></div>
          </div>
          <div style="height:16px"></div>
          <div class="bar-chart">
            <div class="bar" style="height:45%"><span class="bar-val">18k</span></div>
            <div class="bar" style="height:60%"><span class="bar-val">24k</span></div>
            <div class="bar" style="height:40%"><span class="bar-val">16k</span></div>
            <div class="bar" style="height:75%"><span class="bar-val">30k</span></div>
            <div class="bar" style="height:55%"><span class="bar-val">22k</span></div>
            <div class="bar" style="height:90%;background:var(--crimson)"><span class="bar-val" style="color:var(--crimson)"><strong>36k</strong></span></div>
          </div>
          <div class="bar-labels">
            <span class="bar-label">Out</span><span class="bar-label">Nov</span><span class="bar-label">Dez</span>
            <span class="bar-label">Jan</span><span class="bar-label">Fev</span><span class="bar-label" style="color:var(--crimson);font-weight:700">Mar</span>
          </div>
          <div class="kpi-row">
            <div class="kpi-mini"><div class="kv">36.000</div><div class="kl">Este mês (Kz)</div></div>
            <div class="kpi-mini"><div class="kv">1.847</div><div class="kl">Downloads</div></div>
            <div class="kpi-mini"><div class="kv">4,8 ⭐</div><div class="kl">Avaliação Média</div></div>
          </div>
        </div>
      </div>

      <!-- My Documents + Pending Review -->
      <div class="two-col">
        <!-- My Docs -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Meus Documentos</div><div class="section-sub">24 publicados</div></div>
            <a class="section-action">Gerir</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Título</th><th>Downloads</th><th>Receita</th><th>Estado</th></tr></thead>
              <tbody>
                <tr><td><strong>Optimização de Refino</strong><br><small>2024 · Eng. Petróleo</small></td><td>623</td><td>17.444 Kz</td><td><span class="badge green">Publicado</span></td></tr>
                <tr><td><strong>Geologia Estrutural</strong><br><small>2023 · Geologia</small></td><td>528</td><td>15.840 Kz</td><td><span class="badge green">Publicado</span></td></tr>
                <tr><td><strong>Blocos Pré-Sal Angola</strong><br><small>2024 · Eng. Petróleo</small></td><td>—</td><td>—</td><td><span class="badge orange">Em Revisão</span></td></tr>
                <tr><td><strong>Reservatórios Offshore</strong><br><small>2025 · Geofísica</small></td><td>—</td><td>—</td><td><span class="badge blue">Programado</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending Review -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Revisão por Pares</div><div class="section-sub">4 trabalhos aguardando sua avaliação</div></div>
          </div>
          <div style="padding:16px 24px;display:flex;flex-direction:column;gap:12px">
            <div style="background:var(--cream);border-radius:12px;padding:16px;border:1px solid var(--border)">
              <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                <div><div style="font-size:14px;font-weight:600;color:var(--text-dark)">Análise Sísmica na Bacia do Kwanza</div><div style="font-size:12px;color:var(--text-light)">Por A. Lopes · Geofísica · 2025</div></div>
                <span class="badge orange">Urgente</span>
              </div>
              <div style="display:flex;gap:8px">
                <button class="btn btn-success btn-sm">✓ Aprovar</button>
                <button class="btn btn-ghost btn-sm">✎ Pedir Revisões</button>
                <button class="btn btn-ghost btn-sm" style="color:#E53E3E">✕ Reprovar</button>
              </div>
            </div>
            <div style="background:var(--cream);border-radius:12px;padding:16px;border:1px solid var(--border)">
              <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                <div><div style="font-size:14px;font-weight:600;color:var(--text-dark)">Impacto do LNG em Angola</div><div style="font-size:12px;color:var(--text-light)">Por B. Muanda · Energia · 2025</div></div>
                <span class="badge blue">Normal</span>
              </div>
              <div style="display:flex;gap:8px">
                <button class="btn btn-success btn-sm">✓ Aprovar</button>
                <button class="btn btn-ghost btn-sm">✎ Pedir Revisões</button>
                <button class="btn btn-ghost btn-sm" style="color:#E53E3E">✕ Reprovar</button>
              </div>
            </div>
            <div style="text-align:center;padding:8px 0">
              <a class="section-action">Ver mais 2 pendentes →</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Comments + Activity -->
      <div class="two-col">
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Comentários Recentes</div><div class="section-sub">7 novos comentários</div></div>
            <a class="section-action">Ver todos</a>
          </div>
          <div class="activity-item"><div class="activity-dot blue">💬</div><div class="activity-body"><div class="title">Manuel Kiala comentou em <strong>Optimização de Refino</strong></div><div class="sub">"Excelente trabalho, muito útil para a minha dissertação..."</div></div><div class="activity-time">há 2h</div></div>
          <div class="activity-item"><div class="activity-dot gold">⭐</div><div class="activity-body"><div class="title">Filomena Santos avaliou <strong>Geologia Estrutural</strong></div><div class="sub">Avaliação: 5 estrelas ⭐⭐⭐⭐⭐</div></div><div class="activity-time">há 5h</div></div>
          <div class="activity-item"><div class="activity-dot crimson">❓</div><div class="activity-body"><div class="title">Ricardo Dias perguntou sobre <strong>Blocos Pré-Sal</strong></div><div class="sub">"Qual a metodologia utilizada na secção 3?"</div></div><div class="activity-time">há 1d</div></div>
        </div>
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Actividade Recente</div></div>
          </div>
          <div class="activity-item"><div class="activity-dot green">💰</div><div class="activity-body"><div class="title">Receita recebida: <strong>2.800 Kz</strong></div><div class="sub">Download por S. Pimentel — Optimização de Refino</div></div><div class="activity-time">hoje</div></div>
          <div class="activity-item"><div class="activity-dot crimson">📤</div><div class="activity-body"><div class="title">Upload submetido para revisão</div><div class="sub">Blocos Pré-Sal Angola · Aguarda aprovação</div></div><div class="activity-time">ontem</div></div>
          <div class="activity-item"><div class="activity-dot blue">⏰</div><div class="activity-body"><div class="title">Upload programado criado</div><div class="sub">Reservatórios Offshore — publicação em 20 Mar</div></div><div class="activity-time">há 2d</div></div>
          <div class="activity-item"><div class="activity-dot gold">🏆</div><div class="activity-body"><div class="title">Documento em destaque!</div><div class="sub">Optimização de Refino entrou no Top 10 da semana</div></div><div class="activity-time">há 3d</div></div>
        </div>
      </div>

    </div>
  </div>
</div>