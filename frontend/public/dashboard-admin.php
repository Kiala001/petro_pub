
<div class="dashboard" id="dash-admin">
  <!-- <aside class="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">PETRO<span>PUB</span></div>
      <div class="sidebar-role">Painel Administrativo</div>
    </div>
    <div class="sidebar-user">
      <div class="avatar" style="background:linear-gradient(135deg,#4A0B16,#8C1A2E)">AD</div>
      <div class="user-info">
        <div class="name">Ana Domingos</div>
        <div class="email">admin@petropub.ao</div>
      </div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Visão Geral</div>
      <div class="nav-item active"><span class="nav-icon">📊</span> Dashboard</div>
      <div class="nav-item"><span class="nav-icon">📈</span> Analytics</div>
      <div class="nav-item"><span class="nav-icon">📋</span> Relatórios</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Gestão de Conteúdo</div>
      <div class="nav-item"><span class="nav-icon">📂</span> Arquivos <span class="nav-badge red">8</span></div>
      <div class="nav-item"><span class="nav-icon">✅</span> Aprovar Uploads <span class="nav-badge red">6</span></div>
      <div class="nav-item"><span class="nav-icon">⏰</span> Uploads Programados</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Utilizadores</div>
      <div class="nav-item"><span class="nav-icon">👥</span> Gerir Utilizadores</div>
      <div class="nav-item"><span class="nav-icon">🎓</span> Estudantes</div>
      <div class="nav-item"><span class="nav-icon">👨‍🏫</span> Docentes</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Financeiro</div>
      <div class="nav-item"><span class="nav-icon">💳</span> Comprovantes <span class="nav-badge red">12</span></div>
      <div class="nav-item"><span class="nav-icon">💰</span> Pagamentos</div>
      <div class="nav-item"><span class="nav-icon">📜</span> Assinaturas</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Sistema</div>
      <div class="nav-item"><span class="nav-icon">🔔</span> Notificações <span class="nav-badge">9</span></div>
      <div class="nav-item"><span class="nav-icon">🛡️</span> Anti-fraude</div>
      <div class="nav-item"><span class="nav-icon">⚙️</span> Configurações</div>
    </div>
    <div class="sidebar-footer">
      <div class="nav-item"><span class="nav-icon">🚪</span> Sair</div>
    </div>
  </aside> -->

  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">PetroPub <span>/ Admin / Dashboard</span></div>
        <h1>Painel Administrativo</h1>
      </div>
      <div class="topbar-right">
        <button class="btn btn-outline btn-sm">📥 Exportar Relatório</button>
        <button class="btn btn-primary btn-sm">📤 Upload Manual</button>
        <div class="icon-btn">🔔<span class="notif-dot"></span></div>
        <div class="avatar" style="width:38px;height:38px;font-size:13px;cursor:pointer;background:linear-gradient(135deg,#4A0B16,#8C1A2E)">AD</div>
      </div>
    </div>

    <div class="page-content">

      <!-- KPI Stats -->
      <div class="stats-grid stats-grid-4">
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon crimson">👥</div><span class="stat-change up">+48 hoje</span></div>
          <div class="stat-num">5.842</div>
          <div class="stat-label">Utilizadores registados</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon gold">📂</div><span class="stat-change up">+12</span></div>
          <div class="stat-num">1.247</div>
          <div class="stat-label">Documentos no acervo</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon green">💰</div><span class="stat-change up">+23%</span></div>
          <div class="stat-num">4.820.000</div>
          <div class="stat-label">Receita total (Kz)</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon blue">⬇️</div><span class="stat-change up">+340</span></div>
          <div class="stat-num">28.450</div>
          <div class="stat-label">Downloads totais</div>
        </div>
      </div>

      <!-- Second row stats -->
      <div class="stats-grid stats-grid-4" style="margin-top:-10px">
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon crimson">⏳</div><span class="stat-change neutral">urgente</span></div>
          <div class="stat-num">12</div>
          <div class="stat-label">Comprovantes pendentes</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon gold">✅</div><span class="stat-change neutral">6 novos</span></div>
          <div class="stat-num">6</div>
          <div class="stat-label">Uploads para aprovar</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon green">👑</div><span class="stat-change up">+8</span></div>
          <div class="stat-num">312</div>
          <div class="stat-label">Assinantes premium</div>
        </div>
        <div class="stat-card">
          <div class="stat-top"><div class="stat-icon blue">🚨</div><span class="stat-change down">3 alertas</span></div>
          <div class="stat-num">3</div>
          <div class="stat-label">Alertas anti-fraude</div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="section-card" style="margin-bottom:20px">
        <div class="section-header">
          <div><div class="section-title">Acções Rápidas</div></div>
        </div>
        <div style="padding:16px 24px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
          <div class="quick-action"><div class="qa-icon crimson" style="background:var(--crimson-xlight)">✅</div><div><div class="qa-title">Aprovar Uploads</div><div class="qa-sub">6 pendentes</div></div></div>
          <div class="quick-action"><div class="qa-icon" style="background:var(--warn-bg)">💳</div><div><div class="qa-title">Verificar Comprovantes</div><div class="qa-sub">12 pendentes</div></div></div>
          <div class="quick-action"><div class="qa-icon" style="background:var(--success-bg)">👥</div><div><div class="qa-title">Gerir Utilizadores</div><div class="qa-sub">5.842 registados</div></div></div>
          <div class="quick-action"><div class="qa-icon" style="background:var(--info-bg)">📊</div><div><div class="qa-title">Gerar Relatório</div><div class="qa-sub">Analytics completo</div></div></div>
        </div>
      </div>

      <!-- Revenue Chart + Distribution -->
      <div class="col-7-5">
        <!-- Revenue Chart -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Receita Mensal</div><div class="section-sub">2025 — em Kz (milhares)</div></div>
            <div style="display:flex;gap:8px">
              <span class="chip active">Downloads</span>
              <span class="chip">Assinaturas</span>
              <span class="chip">Total</span>
            </div>
          </div>
          <div style="height:20px"></div>
          <div class="bar-chart" style="height:100px">
            <div class="bar" style="height:50%"><span class="bar-val">380k</span></div>
            <div class="bar" style="height:65%"><span class="bar-val">495k</span></div>
            <div class="bar" style="height:45%"><span class="bar-val">342k</span></div>
            <div class="bar" style="height:80%"><span class="bar-val">608k</span></div>
            <div class="bar" style="height:70%"><span class="bar-val">532k</span></div>
            <div class="bar" style="height:72%"><span class="bar-val">547k</span></div>
            <div class="bar" style="height:60%"><span class="bar-val">456k</span></div>
            <div class="bar" style="height:85%"><span class="bar-val">647k</span></div>
            <div class="bar" style="height:75%"><span class="bar-val">570k</span></div>
            <div class="bar" style="height:90%"><span class="bar-val">685k</span></div>
            <div class="bar" style="height:78%"><span class="bar-val">594k</span></div>
            <div class="bar" style="height:100%;background:var(--crimson)"><span class="bar-val" style="color:var(--crimson)"><strong>760k</strong></span></div>
          </div>
          <div class="bar-labels">
            <span class="bar-label">Jan</span><span class="bar-label">Fev</span><span class="bar-label">Mar</span><span class="bar-label">Abr</span><span class="bar-label">Mai</span><span class="bar-label">Jun</span><span class="bar-label">Jul</span><span class="bar-label">Ago</span><span class="bar-label">Set</span><span class="bar-label">Out</span><span class="bar-label">Nov</span><span class="bar-label" style="color:var(--crimson);font-weight:700">Dez</span>
          </div>
          <div class="kpi-row">
            <div class="kpi-mini"><div class="kv">4,82M Kz</div><div class="kl">Receita Total</div></div>
            <div class="kpi-mini"><div class="kv">760k Kz</div><div class="kl">Este Mês</div></div>
            <div class="kpi-mini"><div class="kv">+23%</div><div class="kl">vs mês anterior</div></div>
            <div class="kpi-mini"><div class="kv">28.450</div><div class="kl">Downloads</div></div>
          </div>
        </div>

        <!-- Distribution -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Distribuição de Acervo</div><div class="section-sub">Por tipo de documento</div></div>
          </div>
          <div class="donut-wrap">
            <div class="donut">
              <svg width="100" height="100" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="38" fill="none" stroke="var(--cream)" stroke-width="14"/>
                <circle cx="50" cy="50" r="38" fill="none" stroke="var(--crimson)" stroke-width="14" stroke-dasharray="90 149" stroke-dashoffset="0"/>
                <circle cx="50" cy="50" r="38" fill="none" stroke="var(--gold)" stroke-width="14" stroke-dasharray="70 149" stroke-dashoffset="-90"/>
                <circle cx="50" cy="50" r="38" fill="none" stroke="var(--info)" stroke-width="14" stroke-dasharray="50 149" stroke-dashoffset="-160"/>
                <circle cx="50" cy="50" r="38" fill="none" stroke="var(--success)" stroke-width="14" stroke-dasharray="29 149" stroke-dashoffset="-210"/>
              </svg>
              <div class="donut-center"><div class="donut-num">1.247</div><div class="donut-sub">Total</div></div>
            </div>
            <div class="donut-legend">
              <div class="legend-item"><div class="legend-dot" style="background:var(--crimson)"></div><div class="legend-label">Dissertações</div><div class="legend-val">342</div></div>
              <div class="legend-item"><div class="legend-dot" style="background:var(--gold)"></div><div class="legend-label">Monografias</div><div class="legend-val">510</div></div>
              <div class="legend-item"><div class="legend-dot" style="background:var(--info)"></div><div class="legend-label">PAP</div><div class="legend-val">218</div></div>
              <div class="legend-item"><div class="legend-dot" style="background:var(--success)"></div><div class="legend-label">Artigos</div><div class="legend-val">177</div></div>
            </div>
          </div>
          <div class="sep"></div>
          <div style="padding:16px 24px">
            <div class="prog-wrap"><div class="prog-label"><span>Taxa de aprovação</span><span>87%</span></div><div class="prog-bar"><div class="prog-fill green" style="width:87%"></div></div></div>
            <div class="prog-wrap"><div class="prog-label"><span>Taxa de conversão (downloads)</span><span>64%</span></div><div class="prog-bar"><div class="prog-fill" style="width:64%"></div></div></div>
            <div class="prog-wrap"><div class="prog-label"><span>Utilizadores premium</span><span>5,3%</span></div><div class="prog-bar"><div class="prog-fill gold" style="width:5.3%"></div></div></div>
          </div>
        </div>
      </div>

      <!-- Pending Payments + Pending Uploads -->
      <div class="two-col">
        <!-- Pending Payments -->
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Comprovantes Pendentes</div><div class="section-sub">12 aguardando verificação</div></div>
            <a class="section-action">Ver todos</a>
          </div>
          <div style="padding:16px 24px">
            <div class="payment-card">
              <div class="pay-header">
                <div><div class="pay-title">Manuel Kiala</div><div class="pay-meta">m.kiala@uan.ao · Análise do Bloco 0</div></div>
                <div><div class="pay-amount">2.500 Kz</div><span class="badge orange">Pendente</span></div>
              </div>
              <div class="pay-meta">IBAN: AO06 0044 0000 6729 5034 1 01 · enviado há 2h</div>
              <div class="pay-actions">
                <button class="btn btn-success btn-sm">✓ Aprovar</button>
                <button class="btn btn-ghost btn-sm">👁 Ver Comprovante</button>
                <button class="btn btn-ghost btn-sm" style="color:#E53E3E">✕ Rejeitar</button>
              </div>
            </div>
            <div class="payment-card">
              <div class="pay-header">
                <div><div class="pay-title">Eunice Kakunda</div><div class="pay-meta">e.kakunda@ucan.ao · Plano Premium</div></div>
                <div><div class="pay-amount">5.000 Kz</div><span class="badge blue">Novo</span></div>
              </div>
              <div class="pay-meta">App Digital (Multicaixa Express) · enviado há 45min</div>
              <div class="pay-actions">
                <button class="btn btn-success btn-sm">✓ Aprovar</button>
                <button class="btn btn-ghost btn-sm">👁 Ver Comprovante</button>
                <button class="btn btn-ghost btn-sm" style="color:#E53E3E">✕ Rejeitar</button>
              </div>
            </div>
            <div class="payment-card" style="border-color:rgba(229,62,62,0.3)">
              <div class="pay-header">
                <div><div class="pay-title">Ricardo Dias</div><div class="pay-meta">r.dias@lusíada.ao · Geologia Estrutural</div></div>
                <div><div class="pay-amount">3.500 Kz</div><span class="badge red">⚠️ Suspeito</span></div>
              </div>
              <div class="pay-meta">Cartão · Comprovante com inconsistência detectada</div>
              <div class="pay-actions">
                <button class="btn btn-ghost btn-sm" style="color:#E53E3E">🛡️ Investigar Fraude</button>
                <button class="btn btn-ghost btn-sm">👁 Ver Comprovante</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Uploads + Recent Users -->
        <div style="display:flex;flex-direction:column;gap:20px">
          <div class="section-card">
            <div class="section-header">
              <div><div class="section-title">Uploads para Aprovar</div><div class="section-sub">6 aguardando publicação</div></div>
              <a class="section-action">Ver todos</a>
            </div>
            <div class="table-wrap">
              <table>
                <thead><tr><th>Documento</th><th>Docente</th><th>Tipo</th><th>Ação</th></tr></thead>
                <tbody>
                  <tr><td><strong>Análise Sísmica Kwanza</strong><br><small>2025 · Geofísica</small></td><td>Prof. A. Lopes</td><td><span class="badge crimson">Dissertação</span></td><td><div style="display:flex;gap:6px"><button class="btn btn-success btn-sm">✓</button><button class="btn btn-ghost btn-sm">✎</button><button class="btn btn-ghost btn-sm" style="color:#E53E3E">✕</button></div></td></tr>
                  <tr><td><strong>Impacto do LNG em Angola</strong><br><small>2025 · Energia</small></td><td>Prof. B. Muanda</td><td><span class="badge blue">Monografia</span></td><td><div style="display:flex;gap:6px"><button class="btn btn-success btn-sm">✓</button><button class="btn btn-ghost btn-sm">✎</button><button class="btn btn-ghost btn-sm" style="color:#E53E3E">✕</button></div></td></tr>
                  <tr><td><strong>Segurança Industrial Offshore</strong><br><small>2025 · Eng. Petróleo</small></td><td>Prof. C. Mendes</td><td><span class="badge crimson">Dissertação</span></td><td><div style="display:flex;gap:6px"><button class="btn btn-success btn-sm">✓</button><button class="btn btn-ghost btn-sm">✎</button><button class="btn btn-ghost btn-sm" style="color:#E53E3E">✕</button></div></td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="section-card">
            <div class="section-header">
              <div><div class="section-title">Novos Utilizadores</div><div class="section-sub">Últimos registos hoje</div></div>
            </div>
            <div class="rank-item"><div class="avatar" style="width:34px;height:34px;font-size:12px">SM</div><div style="flex:1"><div style="font-size:14px;font-weight:500">Sónia Muanda</div><div style="font-size:11px;color:var(--text-light)">ISPTEC · Estudante</div></div><span class="badge green">Activo</span></div>
            <div class="rank-item"><div class="avatar" style="width:34px;height:34px;font-size:12px;background:#1A3A4A">JF</div><div style="flex:1"><div style="font-size:14px;font-weight:500">Prof. José Ferreira</div><div style="font-size:11px;color:var(--text-light)">UAN · Docente</div></div><span class="badge blue">Docente</span></div>
            <div class="rank-item"><div class="avatar" style="width:34px;height:34px;font-size:12px">CL</div><div style="flex:1"><div style="font-size:14px;font-weight:500">Catarina Lima</div><div style="font-size:11px;color:var(--text-light)">UCAN · Estudante</div></div><span class="badge orange">Verificar</span></div>
          </div>
        </div>
      </div>

      <!-- Most Downloaded + System Activity -->
      <div class="two-col">
        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Documentos Mais Baixados</div><div class="section-sub">Ranking este mês</div></div>
          </div>
          <div class="rank-item"><div class="rank-num gold">1</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Sistemas TIC no Ensino Secundário</div><div style="font-size:11px;color:var(--text-light)">A. Canga · PAP · 2024</div></div><div style="text-align:right"><div class="rank-points">1.204 ⬇️</div><div style="font-size:11px;color:var(--success)">Gratuito</div></div></div>
          <div class="rank-item"><div class="rank-num silver">2</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Prevalência de Malária no Moxico</div><div style="font-size:11px;color:var(--text-light)">E. Kakunda · Monografia · 2024</div></div><div style="text-align:right"><div class="rank-points">956 ⬇️</div><div style="font-size:11px;color:var(--success)">Gratuito</div></div></div>
          <div class="rank-item"><div class="rank-num bronze">3</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Análise do Bloco 0 de Cabinda</div><div style="font-size:11px;color:var(--text-light)">M. Cardoso · Dissertação · 2024</div></div><div style="text-align:right"><div class="rank-points">842 ⬇️</div><div style="font-size:11px;color:var(--crimson)">2.500 Kz</div></div></div>
          <div class="rank-item"><div class="rank-num">4</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Economia Digital em Angola</div><div style="font-size:11px;color:var(--text-light)">J. Kiala · Monografia · 2023</div></div><div style="text-align:right"><div class="rank-points">845 ⬇️</div><div style="font-size:11px;color:var(--crimson)">1.500 Kz</div></div></div>
          <div class="rank-item"><div class="rank-num">5</div><div style="flex:1"><div style="font-size:14px;font-weight:600">Geologia Estrutural da Margem</div><div style="font-size:11px;color:var(--text-light)">D. Vida · Dissertação · 2022</div></div><div style="text-align:right"><div class="rank-points">712 ⬇️</div><div style="font-size:11px;color:var(--crimson)">3.500 Kz</div></div></div>
        </div>

        <div class="section-card">
          <div class="section-header">
            <div><div class="section-title">Actividade do Sistema</div><div class="section-sub">Log em tempo real</div></div>
          </div>
          <div class="activity-item"><div class="activity-dot green">✅</div><div class="activity-body"><div class="title">Comprovante aprovado — <strong>2.500 Kz</strong></div><div class="sub">Manuel Kiala · Download liberado automaticamente</div></div><div class="activity-time">há 2min</div></div>
          <div class="activity-item"><div class="activity-dot blue">👤</div><div class="activity-body"><div class="title">Novo utilizador registado</div><div class="sub">sonia.muanda@isptec.ao — Estudante</div></div><div class="activity-time">há 8min</div></div>
          <div class="activity-item"><div class="activity-dot gold">📤</div><div class="activity-body"><div class="title">Upload submetido para aprovação</div><div class="sub">Análise Sísmica Kwanza — Prof. A. Lopes</div></div><div class="activity-time">há 15min</div></div>
          <div class="activity-item"><div class="activity-dot crimson">🚨</div><div class="activity-body"><div class="title">Alerta anti-fraude detectado</div><div class="sub">Comprovante suspeito — R. Dias · Em análise</div></div><div class="activity-time">há 32min</div></div>
          <div class="activity-item"><div class="activity-dot green">💰</div><div class="activity-body"><div class="title">Assinatura Premium activada</div><div class="sub">Eunice Kakunda · Plano Premium · 5.000 Kz</div></div><div class="activity-time">há 45min</div></div>
          <div class="activity-item"><div class="activity-dot blue">📊</div><div class="activity-body"><div class="title">Backup automático concluído</div><div class="sub">1.247 documentos · 2,3 GB · Sem erros</div></div><div class="activity-time">há 1h</div></div>
        </div>
      </div>

    </div>
  </div>
</div>