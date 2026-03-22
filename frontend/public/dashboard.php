<?php

session_start();
$userData = $_SESSION['user_data'];
$type = $_SESSION['type_auth'];
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
    <link href="assets/css/dashboard-style.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>

<?php
  if ($type == "COMMON_USER" || $type == "TEACHER") {
    require_once 'dashboard-common-user.php';
  } elseif ($type == "ADMIN") {
    require_once 'dashboard-admin.php';
  }else {
    echo "Serás redirecionado para login";
  }

?>


<script>
function switchRole(role) {
  document.querySelectorAll('.dashboard').forEach(d => d.classList.remove('active'));
  document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('dash-' + role).classList.add('active');
  event.target.classList.add('active');
}
</script>
</body>
</html>
