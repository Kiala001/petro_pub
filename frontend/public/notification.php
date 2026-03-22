<?php
// Página de notificações do usuário
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
<title>PetroPub — Notificações</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/sidebar.css">
<link rel="stylesheet" href="assets/css/element.css">
</head>
<body>
<div class="app">
  <?php require_once 'header-role.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="tb-l">
        <button class="tb-ham" onclick="openSB()">☰</button>
        <div class="tb-title">Minhas Notificações</div>
      </div>
    </div>
    <div class="page-wrap">
      <div class="card">
        <div class="card-title">Minhas Notificações</div>
        <div id="notif-list" class="notif-list">Carregando...</div>
      </div>
    </div>
  </div>
</div>
<script src="assets/js/sidebar.js"></script>
<script src="assets/js/dashboard-data.js"></script>
<script>
async function fetchNotifications() {
  const res = await fetch('/backend/api/routes/notification.php?path=notifications', {credentials:'same-origin'});
  const data = await res.json();
  if(data.success) {
    renderNotifications(data.notifications);
  }
}
function renderNotifications(list) {
  const el = document.getElementById('notif-list');
  if(!list || !list.length) { el.textContent = 'Nenhuma notificação.'; return; }
  el.innerHTML = list.map(n => `
    <div class="notif-item${n.is_read ? '' : ' notif-unread'}">
      <div class="notif-ico"><i class="${n.icon}"></i></div>
      <div class="notif-body">
        <div class="notif-title">${n.title}</div>
        <div class="notif-msg">${n.message}</div>
        <div class="notif-meta">${n.created_at ? n.created_at.substring(0,16) : ''}</div>
      </div>
      ${!n.is_read ? `<button class="mark-read-btn" onclick="markAsRead('${n.id}')">Marcar como lida</button>` : ''}
    </div>
  `).join('');
}
async function markAsRead(id) {
  await fetch('/backend/api/routes/notification.php?path=notifications/read', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id})
  });
  fetchNotifications();
}
fetchNotifications();
</script>
</body>
</html>
