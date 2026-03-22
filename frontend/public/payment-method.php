<?php
require_once 'includes.php';


$userId = $_SESSION['user_uuid'];
$methods = $servicePM->getUserMethods($userId);

foreach ($methods as $method) {
  $data = json_decode($method['data']);

  $data = json_decode($data);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub – Meios de Pagamento</title>
  <link href="assets/css/payment.method.css" rel="stylesheet">
  <link href="assets/css/elements.css" rel="stylesheet">
  <link href="assets/css/sidebar.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>

  <div class="toast" id="toast"></div>
  <div class="sb-overlay" id="sb-overlay" onclick="closeSidebar()"></div>

  <div class="app">

    <!-- ══════════════════════════════════
        SIDEBAR
    ══════════════════════════════════ -->
    <!-- <aside class="sidebar" id="sidebar">
      <div class="sb-head">
        <div>
          <div class="sb-logo">PETRO<span>PUB</span></div>
          <div class="sb-sub">Portal do Docente</div>
        </div>
        <button class="sb-toggle-btn" id="sb-close" onclick="closeSidebar()">✕</button>
        <button class="sb-toggle-btn" id="sb-collapse" onclick="toggleCollapse()">◀</button>
      </div>
      <div class="sb-user">
        <div class="ava ava-gd">JP</div>
        <div class="sb-uinfo">
          <div class="sb-uname">Prof. João Pinto</div>
          <div class="sb-uemail">j.pinto@isptec.ao</div>
        </div>
      </div>
      <div class="nav-sec">
        <div class="nav-lbl">Principal</div>
        <div class="nav-it" data-tip="Início"><span class="ni">🏠</span><span class="nt">Início</span></div>
        <div class="nav-it" data-tip="Submeter"><span class="ni">📤</span><span class="nt">Submeter Documento</span></div>
        <div class="nav-it" data-tip="Documentos"><span class="ni">📂</span><span class="nt">Meus Documentos</span></div>
        <div class="nav-it" data-tip="Avaliações"><span class="ni">✅</span><span class="nt">Avaliações</span><span class="nb">4</span></div>
      </div>
      <div class="nav-sec">
        <div class="nav-lbl">Financeiro</div>
        <div class="nav-it act" data-tip="Pagamentos"><span class="ni">💳</span><span class="nt">Meios de Pagamento</span></div>
        <div class="nav-it" data-tip="Receitas"><span class="ni">💰</span><span class="nt">Receitas</span></div>
        <div class="nav-it" data-tip="Comprovantes"><span class="ni">📜</span><span class="nt">Comprovantes</span></div>
      </div>
      <div class="nav-sec">
        <div class="nav-lbl">Comunidade</div>
        <div class="nav-it" data-tip="Notificações"><span class="ni">🔔</span><span class="nt">Notificações</span><span class="nb">3</span></div>
        <div class="nav-it" data-tip="Comentários"><span class="ni">💬</span><span class="nt">Comentários</span><span class="ng">7</span></div>
      </div>
      <div class="sb-foot">
        <div class="nav-it" data-tip="Configurações"><span class="ni">⚙️</span><span class="nt">Configurações</span></div>
        <div class="nav-it" data-tip="Sair"><span class="ni">🚪</span><span class="nt">Sair</span></div>
      </div>
    </aside> -->

    <div class="main">

      <!-- TOPBAR -->
      <div class="topbar">
        <div class="tb-left">
          <button class="tb-ham" onclick="openSidebar()">☰</button>
          <div class="tb-info">
            <div class="tb-bc">PetroPub <span>/ Meios de Pagamento</span></div>
            <div class="tb-title">Meios de Pagamento</div>
          </div>
        </div>
        <div class="tb-right">
          <button class="btn btn-cr" onclick="openModal('modal-add')">
            <span>＋</span><span>Registar Meio</span>
          </button>
          <div class="tb-notif">🔔</div>
          <div class="ava ava-gd" style="width:36px;height:36px;font-size:12px;cursor:pointer;flex-shrink:0">JP</div>
        </div>
      </div>

      <!-- CONTENT -->
      <div class="page-wrap">

        <!-- STATS -->
        <div class="stats-grid">
          <div class="stat-c">
            <div class="stat-top"><div class="stat-ico s-cr"><i class="fa fa-bank"></i></div><span class="stat-pill sp-ok">Registados</span></div>
            <div class="stat-num" id="stat-total">-</div>
            <div class="stat-lbl">Meios de pagamento</div>
          </div>
          <div class="stat-c">
            <div class="stat-top"><div class="stat-ico s-ok"><i class="fa fa-credit-card"></i></div><span class="stat-pill sp-ok">Activos</span></div>
            <div class="stat-num" id="stat-active">-</div>
            <div class="stat-lbl">Meios activos</div>
          </div>
          <div class="stat-c">
            <div class="stat-top"><div class="stat-ico s-gd"><i class="fa fa-money"></i></div><span class="stat-pill sp-ok">+12%</span></div>
            <div class="stat-num">- Kz</div>
            <div class="stat-lbl">Receita recebida</div>
          </div>
          <div class="stat-c">
            <div class="stat-top"><div class="stat-ico s-inf"><i class="fa fa-movimentation"></i></div><span class="stat-pill sp-wn">Este mês</span></div>
            <div class="stat-num">-</div>
            <div class="stat-lbl">Transacções totais</div>
          </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
          <div class="tb-search">
            <span class="tb-search-ico"><i class="fa fa-search"></i></span>
            <input type="text" placeholder="Pesquisar por nome, IBAN, número…" style="padding-left: 40px;" oninput="filterList(this.value)" id="search-input">
          </div>
          <div class="filter-tabs" id="filter-tabs">
            <button class="ft-btn on" onclick="filterTab('all',this)">Todos</button>
            <button class="ft-btn" onclick="filterTab('iban',this)">Transferência</button>
            <button class="ft-btn" onclick="filterTab('express',this)">Express</button>
            <button class="ft-btn" onclick="filterTab('kwik',this)">Kwik</button>
          </div>
        </div>

        <!-- LIST -->
        <div class="pm-list" id="pm-list">
        </div>

        <!-- EMPTY STATE (hidden by default) -->
        <div class="empty-state" id="empty-state" style="display:none">
          <div class="empty-ico"><i class="fa fa-bank"></i></div>
          <h3>Nenhum meio de pagamento</h3>
          <p>Registe o seu primeiro meio de pagamento para começar a receber pelos seus documentos.</p>
          <button class="btn btn-cr" onclick="openModal('modal-add')">＋ Registar Agora</button>
        </div>

      </div>
    </div>
  </div>

  <?php
    require_once 'assets/modals/payment.php';
  ?>

  <script src="assets/js/api.js"></script>
  <script src="assets/js/util.js"></script>
  <script src="assets/js/payment-method.js"></script>
  <script src="assets/js/payment-aux.js"></script>
  <script>
    let pmData = [];

    let nextId = 4;
    let editingId = null;
    let deleteId = null;
    let currentFilter = 'all';
    let currentMType = 'iban';
    let currentKwik  = 'alcunha';
    let modalActive  = true; // for modal toggle

    function openModal(id) {
      document.getElementById(id).classList.add('open');
      document.body.style.overflow='hidden';
    }
    function closeModal(id) {
      document.getElementById(id).classList.remove('open');
      document.body.style.overflow='';
    }
    function ovClose(e,id) {
      if (e.target.id===id) closeModal(id);
    }

  </script>
</body>
</html>