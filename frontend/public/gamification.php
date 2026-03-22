<?php
// Página de gamificação: pontos, ranking, conquistas
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
<title>PetroPub — Pontos & Ranking</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/gamificacao.css">
</head>
<body>
<div class="app">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sb-head">
      <div><div class="sb-logo">PETRO<span>PUB</span></div><div class="sb-role">Portal do Estudante</div></div>
      <button class="sb-tog" id="sb-close" onclick="closeSB()">✕</button>
      <button class="sb-tog" id="sb-col"   onclick="toggleCol()">◀</button>
    </div>
    <div class="sb-user">
      <div class="ava ava-cr"><?php echo $userInitials; ?></div>
      <div class="sb-ui"><div class="sb-un"><?php echo $userName; ?></div><div class="sb-ue"><?php echo $userEmail; ?></div></div>
    </div>
    <!-- ... navegação ... -->
  </aside>
  <!-- Main -->
  <div class="main">
    <div class="topbar">
      <div class="tb-l">
        <div class="tb-title">Gamificação & Pontos</div>
      </div>
      <div class="tb-r">
        <div class="pts-pill" id="user-points"><span class="pts-pill-ico">🏅</span> <span id="points-value">...</span> pts</div>
      </div>
    </div>
    <div class="page-wrap">
      <div class="hero">
        <div class="hero-inner">
          <div class="hero-ava-wrap">
            <div class="hero-ava">🏅</div>
            <div class="hero-tier-badge" id="user-tier">Tier</div>
          </div>
          <div class="hero-body">
            <div class="hero-name"><?php echo $userName; ?></div>
            <div class="hero-sub">Veja seu progresso, conquistas e ranking!</div>
            <div class="hero-pts"><span class="hero-pts-num" id="hero-points">...</span> <span class="hero-pts-lbl">pontos</span></div>
            <div class="tier-prog"><div class="tier-prog-f" id="tier-progress" style="width:0%"></div></div>
            <div class="tier-prog-lbl"><span id="tier-label-left">Bronze</span><span id="tier-label-right">Próximo Tier</span></div>
            <div class="hero-stats">
              <div class="hs-item"><div class="hs-n" id="stat-docs">0</div><div class="hs-l">Docs Submetidos</div></div>
              <div class="hs-item"><div class="hs-n" id="stat-aprov">0</div><div class="hs-l">Aprovados</div></div>
              <div class="hs-item"><div class="hs-n" id="stat-rev">0</div><div class="hs-l">Reviews</div></div>
              <div class="hs-item"><div class="hs-n" id="stat-badges">0</div><div class="hs-l">Conquistas</div></div>
            </div>
          </div>
          <div class="hero-pos">
            <div class="pos-ring"><div class="pos-n" id="user-rank">#--</div><div class="pos-l">Ranking</div></div>
            <div class="pos-lbl">Sua posição</div>
          </div>
        </div>
      </div>
      <div class="stats-grid">
        <div class="stat-c"><div class="st-top"><div class="st-ico si-gd">🏆</div><div class="st-pill sp-gd">Ranking</div></div><div class="stat-num" id="stat-ranking">...</div><div class="stat-lbl">Sua posição no ranking</div></div>
        <div class="stat-c"><div class="st-top"><div class="st-ico si-ok">📄</div><div class="st-pill sp-ok">Docs</div></div><div class="stat-num" id="stat-docs2">...</div><div class="stat-lbl">Documentos enviados</div></div>
        <div class="stat-c"><div class="st-top"><div class="st-ico si-er">✅</div><div class="st-pill sp-ok">Aprovados</div></div><div class="stat-num" id="stat-aprov2">...</div><div class="stat-lbl">Documentos aprovados</div></div>
        <div class="stat-c"><div class="st-top"><div class="st-ico si-pu">⭐</div><div class="st-pill sp-gd">Conquistas</div></div><div class="stat-num" id="stat-badges2">...</div><div class="stat-lbl">Conquistas desbloqueadas</div></div>
      </div>
      <div class="card">
        <div class="card-head"><div class="card-title">Histórico de Pontos</div></div>
        <div class="card-body">
          <div id="points-history">Carregando...</div>
        </div>
      </div>
      <div class="card">
        <div class="card-head"><div class="card-title">Ranking Geral</div></div>
        <div class="card-body">
          <div id="ranking-list">Carregando...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/util.js"></script>
<script src="assets/js/api.js"></script>
<script>
// Funções para buscar dados do backend
async function fetchPoints() {
    const res = await apiRequest("gamification");
    const data = await res.data;
    if(data.success) {
        document.getElementById('points-value').textContent = data.total_points;
        document.getElementById('hero-points').textContent = data.total_points;
        // Atualizar histórico
        renderHistory(data.history);
    }
}
async function fetchRanking() {
    const res = await apiRequest("ranking");
    const data = await res.data;
    if(data.success) {
        renderRanking(data.ranking);
    }
}
function renderHistory(history) {
  const el = document.getElementById('points-history');
  if(!history || !history.length) { el.textContent = 'Nenhum registro.'; return; }
  el.innerHTML = history.map(item => `
    <div class="hist-item">
      <div class="hist-ico hi-gd">🏅</div>
      <div class="hist-body">
        <div class="hist-title">${item.event_type}</div>
        <div class="hist-meta">${item.operation === 'gain' ? 'Ganho' : 'Perda'} | Ref: ${item.reference_id || '-'}</div>
      </div>
      <div class="hist-pts ${item.operation === 'gain' ? 'pts-plus' : 'pts-minus'}">${item.operation === 'gain' ? '+' : '-'}${item.points}</div>
      <div class="hist-date">${item.created_at ? item.created_at.substring(0,10) : ''}</div>
    </div>
  `).join('');
}
function renderRanking(ranking) {
  const el = document.getElementById('ranking-list');
  if(!ranking || !ranking.length) { el.textContent = 'Nenhum usuário.'; return; }
  el.innerHTML = ranking.map((user, i) => `
    <div class="rank-item${user.is_me ? ' me' : ''}">
      <div class="rank-pos"><span class="rp-n">${i+1}</span></div>
      <div class="rank-ava" style="background:var(--gd-bg)">${user.initials || 'U'}</div>
      <div class="rank-body">
        <div class="rank-name">${user.name}</div>
        <div class="rank-inst">${user.email}</div>
      </div>
      <div class="rank-right">
        <div class="rank-pts">${user.total_points} <span class="rank-pts-lbl">pts</span></div>
      </div>
    </div>
  `).join('');
}
fetchPoints();
fetchRanking();
</script>
</body>
</html>
