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
<link rel="stylesheet" href="/frontend/assets/css/style.css">
<style>
body{background:#FAF7F2;}
.page-wrap{max-width:700px;margin:0 auto;padding:40px 16px;}
.card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(107,16,32,.08);padding:28px 24px;margin-bottom:24px;}
.card-title{font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:#6B1020;margin-bottom:18px;}
.notif-list{display:flex;flex-direction:column;gap:18px;}
.notif-item{display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid #eee;}
.notif-item:last-child{border-bottom:none;}
.notif-ico{font-size:22px;width:38px;height:38px;border-radius:12px;background:#F5E9E9;display:flex;align-items:center;justify-content:center;color:#6B1020;}
.notif-body{flex:1;}
.notif-title{font-size:15px;font-weight:700;color:#6B1020;}
.notif-msg{font-size:13px;color:#4A3728;margin-top:2px;}
.notif-meta{font-size:11px;color:#8A7060;margin-top:2px;}
.notif-unread{background:#FDF6F6;}
.mark-read-btn{background:#6B1020;color:#fff;border:none;padding:5px 12px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;margin-left:10px;}
.mark-read-btn:hover{background:#4A0B16;}
</style>
</head>
<body>
<div class="page-wrap">
  <div class="card">
    <div class="card-title">Minhas Notificações</div>
    <div id="notif-list" class="notif-list">Carregando...</div>
  </div>
</div>
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
