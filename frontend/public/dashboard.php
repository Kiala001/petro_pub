<?php
require_once 'header.php';

$userData = $_SESSION['user_data'];
$type = $_SESSION['type_auth'];
?>
<!-- ROLE SWITCHER -->
<div class="role-switcher">
  <span class="role-label">Ver Dashboard: 
  </span>
  <button class="role-btn active" onclick="switchRole('student')">🎓 Estudante</button>
  <button class="role-btn" onclick="switchRole('teacher')">👨‍🏫 Docente</button>
  <button class="role-btn" onclick="switchRole('admin')">⚙️ Administrador</button>
</div>

<?php
  if ($type == "COMMON_USER") {
    require_once 'dashboard-common-user.php';
  }elseif ($type == "TEACHER") {
    require_once 'dashboard-teacher.php';
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
