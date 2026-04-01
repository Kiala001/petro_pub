<?php
require_once 'includes.php';


$sections = [];
$counts   = [];

// ─── API AJAX ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_section') {
        $id  = (int)($_POST['id'] ?? 0);
        $col = in_array($_POST['col'] ?? '', ['is_active','show_home']) ? $_POST['col'] : 'is_active';
        $val = (int)(bool)($_POST['value'] ?? 0);
        $stmt = db()->prepare("UPDATE sections SET {$col}=? WHERE id=?");
        $stmt->execute([$val, $id]);
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($action === 'reorder') {
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        $stmt = db()->prepare("UPDATE sections SET sort_order=? WHERE id=?");
        foreach ($ids as $i => $id) {
            $stmt->execute([$i, (int)$id]);
        }
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($action === 'save_section') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $desc = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon'] ?? '📂');
        $color = sanitize($_POST['color'] ?? '#6B1020');
        if ($id > 0) {
            db()->prepare("UPDATE sections SET name=?,description=?,icon=?,color=? WHERE id=?")
                 ->execute([$name, $desc, $icon, $color, $id]);
            echo json_encode(['ok'=>true,'msg'=>'Secção actualizada.']);
        }
        exit;
    }
    echo json_encode(['ok'=>false,'msg'=>'Acção desconhecida']);
    exit;
}

// ─── LOAD DATA ────────────────────────────────────────────────
try {
    $sections = $db->query("SELECT * FROM sections ORDER BY sort_order ASC, id ASC")->fetchAll();

    // counts per section
    $counts = [
        'oportunidades' => $db->query("SELECT COUNT(*) FROM opportunities WHERE is_active=1")->fetchColumn(),
        'avisos'        => $db->query("SELECT COUNT(*) FROM notices      WHERE is_active=1")->fetchColumn(),
        'destaques'     => $db->query("SELECT COUNT(*) FROM featured_docs WHERE is_active=1")->fetchColumn(),
    ];
} catch (PDOException $e) {
    $sections = [];
    $counts   = [];
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub Admin — Secções do Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<style>
:root{
  --cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);
  --gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;--gd-bg:rgba(201,168,76,.11);
  --cream:#FAF7F2;--warm:#FEF9F3;--bdr:rgba(107,16,32,.10);--bdr2:rgba(107,16,32,.06);
  --tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;
  --ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);--wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);
  --er:#C53030;--er-bg:rgba(197,48,48,.10);--inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);
  --sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);--sh2:0 8px 32px rgba(107,16,32,.13);
  --r1:7px;--r2:11px;--r3:15px;--r4:20px;
  --sb:260px;--sb-ico:66px;--sb-mob:248px;--hdr:62px;
  --t:.2s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button,textarea{font-family:inherit}
.app{display:flex;min-height:100vh}

/* SIDEBAR */
.sidebar{width:var(--sb);flex-shrink:0;background:var(--cr-dk);height:100vh;position:sticky;top:0;overflow-y:auto;overflow-x:hidden;display:flex;flex-direction:column;transition:width var(--t),transform var(--t);z-index:200}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12)}
.sb-head{padding:22px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:flex-start;justify-content:space-between;min-height:70px;flex-shrink:0}
.sb-logo{font-family:'Arial',serif;font-size:21px;font-weight:900;color:#fff}
.sb-logo span{color:var(--gd-lt)}
.sb-role{font-size:10px;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.6px;margin-top:4px}
.sb-tog{display:none;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);color:#fff;width:30px;height:30px;border-radius:var(--r1);font-size:14px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0;transition:background var(--t);margin-top:2px}
.sb-tog:hover{background:rgba(255,255,255,.22)}
.sb-user{padding:13px 20px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0}
.ava{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;border:2px solid rgba(255,255,255,.20);flex-shrink:0;background:#1A3A4A}
.sb-un{font-size:13px;font-weight:600;color:#fff}
.sb-ue{font-size:11px;color:rgba(255,255,255,.38)}
.nav-s{padding:12px 10px 2px}
.nav-l{font-size:10px;font-weight:700;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:1.5px;padding:0 9px;margin-bottom:4px}
.nav-i{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:10px;cursor:pointer;color:rgba(255,255,255,.58);font-size:13px;font-weight:500;margin-bottom:2px;white-space:nowrap;overflow:hidden;position:relative;transition:all .16s}
.nav-i:hover{background:rgba(255,255,255,.08);color:#fff}
.nav-i.act{background:rgba(255,255,255,.14);color:#fff}
.nav-i.act::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gd);border-radius:0 2px 2px 0}
.ni{font-size:15px;width:18px;text-align:center;flex-shrink:0}
.nb{margin-left:auto;background:#E53E3E;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px;flex-shrink:0}
.sb-foot{margin-top:auto;padding:10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0}
.sidebar.collapsed{width:var(--sb-ico)}
.sidebar.collapsed .sb-logo,.sidebar.collapsed .sb-role,.sidebar.collapsed .sb-un,.sidebar.collapsed .sb-ue,.sidebar.collapsed .nav-l,.sidebar.collapsed .nav-i span:not(.ni),.sidebar.collapsed .nb{opacity:0;max-width:0;pointer-events:none;overflow:hidden}
.sidebar.collapsed .nav-i{justify-content:center;padding:9px}
.sidebar.collapsed .sb-user{justify-content:center;padding:12px}
.sidebar.collapsed .sb-head{justify-content:center;padding:16px 10px}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:190;backdrop-filter:blur(3px);opacity:0;transition:opacity .28s}
.sb-ov.open{opacity:1}

/* MAIN */
.main{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{background:#fff;border-bottom:1px solid var(--bdr);padding:0 clamp(14px,3vw,30px);height:var(--hdr);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:var(--sh0);gap:10px;flex-shrink:0}
.tb-l{display:flex;align-items:center;gap:10px;min-width:0;flex:1}
.tb-ham{display:none;width:36px;height:36px;border-radius:var(--r1);background:var(--cr-xl);border:1px solid var(--bdr);color:var(--cr);font-size:18px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0}
.tb-bc{font-size:11px;color:var(--tx-l);margin-bottom:2px}
.tb-bc a{color:var(--cr);font-weight:600;cursor:pointer}
.tb-title{font-family:'Arial',serif;font-size:clamp(15px,2vw,18px);font-weight:700;color:var(--cr-dk)}
.tb-r{display:flex;align-items:center;gap:8px;flex-shrink:0}
.admin-badge{display:flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff;padding:5px 14px;border-radius:100px;font-size:12px;font-weight:700}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--r2);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-gh{background:var(--cream);color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#246640}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}
.btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}

/* PAGE */
.page-wrap{flex:1;overflow-y:auto;padding:clamp(16px,3vw,28px) clamp(14px,3vw,32px)}

/* FLASH */
.flash{padding:12px 18px;border-radius:var(--r2);margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;animation:fadeUp .35s ease}
.flash-ok{background:var(--ok-bg);color:var(--ok);border:1px solid rgba(45,122,79,.2)}
.flash-er{background:var(--er-bg);color:var(--er);border:1px solid rgba(197,48,48,.2)}

/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:clamp(10px,1.5vw,14px);margin-bottom:clamp(20px,3vw,28px)}
.sc{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(14px,2vw,18px);box-shadow:var(--sh0);animation:fadeUp .4s ease both}
.sc:nth-child(n){animation-delay:calc(.04s * var(--i,1))}
.sc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
.sc-ico{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px}
.si-cr{background:var(--cr-xl)}.si-ok{background:var(--ok-bg)}.si-wn{background:var(--wn-bg)}.si-inf{background:var(--inf-bg)}
.sc-pill{font-size:11px;font-weight:600;padding:2px 8px;border-radius:100px;background:var(--ok-bg);color:var(--ok)}
.sc-num{font-family:'Arial',serif;font-size:clamp(22px,3vw,30px);font-weight:700;color:var(--tx);line-height:1}
.sc-lbl{font-size:12px;color:var(--tx-l);margin-top:4px}

/* SECTIONS GRID */
.sections-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(280px,30vw,360px),1fr));gap:clamp(14px,2vw,18px);margin-bottom:clamp(20px,3vw,28px)}
.section-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);overflow:hidden;box-shadow:var(--sh0);transition:box-shadow var(--t);animation:fadeUp .4s ease both}
.section-card:hover{box-shadow:var(--sh1)}
.sc-banner{height:6px}
.sc-content{padding:clamp(16px,2vw,22px)}
.sc-header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px}
.sc-ico-wrap{width:46px;height:46px;border-radius:var(--r2);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.sc-info{flex:1;min-width:0}
.sc-name{font-family:'Arial',serif;font-size:clamp(15px,1.8vw,17px);font-weight:700;color:var(--tx);margin-bottom:3px}
.sc-slug{font-size:11px;color:var(--tx-l);font-family:monospace}
.sc-desc{font-size:13px;color:var(--tx-m);line-height:1.55;margin-bottom:14px;min-height:36px}
.sc-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.sc-count{font-size:12px;font-weight:600;color:var(--tx-m);background:var(--cream);border:1px solid var(--bdr);padding:3px 10px;border-radius:100px}
.sc-toggles{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.toggle-row{display:flex;align-items:center;gap:7px}
.toggle-lbl{font-size:12px;color:var(--tx-l)}
.toggle{position:relative;width:36px;height:20px;cursor:pointer}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;background:var(--bdr);border-radius:100px;transition:background var(--t)}
.toggle input:checked + .toggle-track{background:var(--ok)}
.toggle-thumb{position:absolute;top:3px;left:3px;width:14px;height:14px;background:#fff;border-radius:50%;transition:transform var(--t);box-shadow:0 1px 4px rgba(0,0,0,.2)}
.toggle input:checked ~ .toggle-thumb{transform:translateX(16px)}
.sc-actions{display:flex;gap:7px;flex-wrap:wrap}

/* REORDER CARD */
.reorder-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);padding:clamp(14px,2vw,20px);box-shadow:var(--sh0);margin-bottom:clamp(20px,3vw,28px)}
.reorder-list{display:flex;flex-direction:column;gap:8px;margin-top:14px}
.reorder-item{display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--bdr);border-radius:var(--r2);background:var(--cream);cursor:grab;user-select:none;transition:box-shadow var(--t),background var(--t)}
.reorder-item:hover{background:#fff;box-shadow:var(--sh1)}
.reorder-item.dragging{opacity:.5;box-shadow:var(--sh2);cursor:grabbing}
.ri-drag{font-size:16px;color:var(--tx-l);flex-shrink:0}
.ri-ico{font-size:18px;flex-shrink:0}
.ri-name{font-size:13px;font-weight:600;color:var(--tx);flex:1}
.ri-order{font-size:11px;color:var(--tx-l);font-family:monospace}

/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;overflow-y:auto}
.overlay.open{display:flex}
.modal{background:#fff;border-radius:var(--r4);width:100%;max-width:520px;box-shadow:var(--sh3);animation:modalIn .28s cubic-bezier(.22,1,.36,1);max-height:calc(100vh - 40px);overflow-y:auto}
@keyframes modalIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
.m-hd{padding:clamp(18px,2vw,24px);background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));border-radius:var(--r4) var(--r4) 0 0;position:relative}
.m-hd h3{font-family:'Arial',serif;font-size:18px;font-weight:700;color:#fff}
.m-hd p{font-size:12px;color:rgba(255,255,255,.58);margin-top:4px}
.m-close{position:absolute;top:14px;right:14px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.m-close:hover{background:rgba(255,255,255,.28)}
.m-body{padding:clamp(18px,2vw,24px)}
.m-foot{padding:clamp(12px,1.5vw,16px) clamp(18px,2vw,24px);border-top:1px solid var(--bdr);background:var(--warm);border-radius:0 0 var(--r4) var(--r4);display:flex;justify-content:flex-end;gap:8px}
.f-group{margin-bottom:16px}
.f-lbl{display:block;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:7px}
.f-input{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}
.f-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
.f-ta{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;resize:vertical;min-height:80px;line-height:1.6;transition:all var(--t)}
.f-ta:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
.f-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* TOAST */
.toast{position:fixed;bottom:clamp(14px,3vw,22px);right:clamp(14px,3vw,22px);z-index:9999;transform:translateY(30px);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;line-height:1.4;border:1px solid rgba(201,168,76,.2)}
.toast.show{opacity:1;transform:translateY(0)}
.t-ok{background:var(--ok)}.t-er{background:var(--er)}.t-def{background:var(--cr-dk)}

@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}

@media(max-width:1199px){
  :root{--sb:var(--sb-ico)}
  .sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nav-i span:not(.ni),.sidebar .nb{opacity:0;max-width:0;pointer-events:none;overflow:hidden}
  .sidebar .nav-i{justify-content:center;padding:9px}
  .sidebar .sb-user{justify-content:center;padding:12px}
  .sidebar .sb-head{justify-content:center;padding:16px 10px}
  .sb-tog{display:none!important}
  .stats-row{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:767px){
  .sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sb-mob)!important;height:100vh;z-index:300;transform:translateX(-100%);box-shadow:var(--sh3)}
  .sidebar.open{transform:translateX(0)}
  .sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nav-i span:not(.ni),.sidebar .nb{opacity:1!important;max-width:unset!important;pointer-events:auto!important}
  .sidebar .nav-i{justify-content:flex-start;padding:8px 10px;gap:9px}
  .sidebar .sb-user{justify-content:flex-start;padding:13px 20px}
  .sidebar .sb-head{justify-content:space-between;padding:18px 20px}
  .sb-ov{display:block}.sb-tog{display:flex!important}.tb-ham{display:flex}
  .stats-row{grid-template-columns:repeat(2,1fr)}
  .topbar{padding:0 14px;height:56px}
  .f-row{grid-template-columns:1fr}
}
@media(max-width:480px){.stats-row{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="toast t-def" id="toast"></div>
<div class="sb-ov" id="sb-ov" onclick="closeSB()"></div>

<div class="app">
<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sb-head">
    <div><div class="sb-logo">PETRO<span>PUB</span></div><div class="sb-role">Administração</div></div>
    <button class="sb-tog" id="sb-close" onclick="closeSB()">✕</button>
    <button class="sb-tog" id="sb-col" onclick="toggleCol()">◀</button>
  </div>
  <div class="sb-user">
    <div class="ava">AD</div>
    <div><div class="sb-un">Ana Domingos</div><div class="sb-ue">admin@petropub.ao</div></div>
  </div>
  <div class="nav-s">
    <div class="nav-l">Visão Geral</div>
    <div class="nav-i" onclick="location.href='admin-dashboard.php'"><span class="ni">📊</span><span>Dashboard</span></div>
    <div class="nav-i" onclick="location.href='petropub-admin-utilizadores.html'"><span class="ni">👥</span><span>Utilizadores</span></div>
    <div class="nav-i" onclick="location.href='admin-avaliacoes-lista.html'"><span class="ni">🔍</span><span>Avaliações</span><span class="nb">2</span></div>
  </div>
  <div class="nav-s">
    <div class="nav-l">Portal</div>
    <div class="nav-i act"><span class="ni">🗂️</span><span>Secções</span></div>
    <div class="nav-i" onclick="location.href='admin-oportunidades.php'"><span class="ni">⛽</span><span>Oportunidades</span></div>
    <div class="nav-i" onclick="location.href='admin-avisos.php'"><span class="ni">📢</span><span>Avisos</span></div>
    <div class="nav-i" onclick="location.href='admin-destaques.php'"><span class="ni">🔥</span><span>Destaques</span></div>
  </div>
  <div class="nav-s">
    <div class="nav-l">Sistema</div>
    <div class="nav-i" onclick="location.href='admin-configuracoes.php'"><span class="ni">⚙️</span><span>Configurações</span></div>
  </div>
  <div class="sb-foot">
    <div class="nav-i"><span class="ni">🚪</span><span>Sair</span></div>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="tb-l">
      <button class="tb-ham" onclick="openSB()">☰</button>
      <div>
        <div class="tb-bc"><a href="admin-dashboard.php">Dashboard</a> / Secções</div>
        <div class="tb-title">🗂️ Secções do Portal</div>
      </div>
    </div>
    <div class="tb-r">
      <div class="admin-badge">⚙️ Admin</div>
      <div class="ava" style="width:36px;height:36px;font-size:12px;cursor:pointer">AD</div>
    </div>
  </div>

  <div class="page-wrap">

    <?php

     if ($flash): ?>
    <div class="flash flash-<?= $flash['type'] === 'ok' ? 'ok' : 'er' ?>">
      <?= $flash['type'] === 'ok' ? '✅' : '❌' ?> <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; 
    ?>

    <!-- STATS -->
    <div class="stats-row">
      <div class="sc" style="--i:1">
        <div class="sc-top"><div class="sc-ico si-cr">🗂️</div><span class="sc-pill"><?= count($sections) ?> total</span></div>
        <div class="sc-num"><?= count($sections) ?></div>
        <div class="sc-lbl">Secções configuradas</div>
      </div>
      <div class="sc" style="--i:2">
        <div class="sc-top"><div class="sc-ico si-ok">✅</div><span class="sc-pill">Activas</span></div>
        <div class="sc-num"><?= count(array_filter($sections, fn($s) => $s['is_active'])) ?></div>
        <div class="sc-lbl">Secções visíveis</div>
      </div>
      <div class="sc" style="--i:3">
        <div class="sc-top"><div class="sc-ico si-wn">⛽</div><span class="sc-pill">Oport.</span></div>
        <div class="sc-num"><?= $counts['oportunidades'] ?? 0 ?></div>
        <div class="sc-lbl">Oportunidades activas</div>
      </div>
      <div class="sc" style="--i:4">
        <div class="sc-top"><div class="sc-ico si-inf">📢</div><span class="sc-pill">Avisos</span></div>
        <div class="sc-num"><?= $counts['avisos'] ?? 0 ?></div>
        <div class="sc-lbl">Avisos publicados</div>
      </div>
    </div>

    <!-- QUICK NAV CARDS -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(200px,25vw,240px),1fr));gap:clamp(12px,2vw,16px);margin-bottom:clamp(20px,3vw,28px)">
      <?php
      $navCards = [
        ['href'=>'admin-oportunidades.php','ico'=>'⛽','name'=>'Oportunidades Oil & Gas','desc'=>'Cursos, vagas, eventos, equipamentos','count'=>$counts['oportunidades']??0,'color'=>'#6B1020'],
        ['href'=>'admin-avisos.php',       'ico'=>'📢','name'=>'Avisos & Novidades',     'desc'=>'Notificações e actualizações',       'count'=>$counts['avisos']??0,       'color'=>'#C47A1A'],
        ['href'=>'admin-destaques.php',    'ico'=>'🔥','name'=>'Conteúdo em Destaque',   'desc'=>'Popular, recente, recomendado',      'count'=>$counts['destaques']??0,    'color'=>'#1A5C8A'],
      ];
      foreach ($navCards as $nc): ?>
      <a href="<?= $nc['href'] ?>" style="text-decoration:none">
        <div style="background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(16px,2vw,20px);box-shadow:var(--sh0);cursor:pointer;transition:all var(--t);display:flex;flex-direction:column;gap:10px;position:relative;overflow:hidden"
             onmouseover="this.style.boxShadow='var(--sh2)';this.style.transform='translateY(-2px)'"
             onmouseout="this.style.boxShadow='var(--sh0)';this.style.transform=''">
          <div style="width:3px;position:absolute;left:0;top:0;bottom:0;background:<?= $nc['color'] ?>"></div>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:26px"><?= $nc['ico'] ?></span>
            <span style="font-family:'Arial',serif;font-size:22px;font-weight:700;color:var(--cr)"><?= $nc['count'] ?></span>
          </div>
          <div>
            <div style="font-size:14px;font-weight:700;color:var(--tx);margin-bottom:3px"><?= $nc['name'] ?></div>
            <div style="font-size:12px;color:var(--tx-l)"><?= $nc['desc'] ?></div>
          </div>
          <div style="font-size:12px;font-weight:600;color:<?= $nc['color'] ?>">Gerir →</div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- SECTIONS CARDS -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
      <div>
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;color:var(--cr);margin-bottom:4px">Configuração</div>
        <div style="font-family:'Arial',serif;font-size:clamp(18px,2.5vw,22px);font-weight:700;color:var(--tx)">Secções do Portal</div>
      </div>
    </div>

    <div class="sections-grid">
      <?php foreach ($sections as $i => $sec):
        $cnt = $counts[$sec['slug']] ?? '—';
        $editHref = match($sec['slug']) {
          'oportunidades' => 'admin-oportunidades.php',
          'avisos'        => 'admin-avisos.php',
          'destaques'     => 'admin-destaques.php',
          default         => '#',
        };
      ?>
      <div class="section-card" style="animation-delay:<?= $i * 0.05 ?>s">
        <div class="sc-banner" style="background:<?= htmlspecialchars($sec['color']) ?>"></div>
        <div class="sc-content">
          <div class="sc-header">
            <div class="sc-ico-wrap" style="background:<?= htmlspecialchars($sec['color']) ?>22">
              <span style="font-size:22px"><?= htmlspecialchars($sec['icon']) ?></span>
            </div>
            <div class="sc-info">
              <div class="sc-name"><?= htmlspecialchars($sec['name']) ?></div>
              <div class="sc-slug">/<?= htmlspecialchars($sec['slug']) ?></div>
            </div>
          </div>
          <div class="sc-desc"><?= htmlspecialchars($sec['description'] ?: 'Sem descrição') ?></div>
          <div class="sc-meta">
            <?php if ($cnt !== '—'): ?>
            <span class="sc-count"><?= $cnt ?> itens</span>
            <?php endif; ?>
            <span style="font-size:11px;color:var(--tx-l)">Ordem: #<?= $sec['sort_order'] ?></span>
          </div>
          <div class="sc-toggles">
            <label class="toggle-row" title="Visível no portal">
              <label class="toggle">
                <input type="checkbox" <?= $sec['is_active'] ? 'checked' : '' ?>
                  onchange="toggleSection(<?= $sec['id'] ?>,'is_active',this.checked)">
                <div class="toggle-track"></div>
                <div class="toggle-thumb"></div>
              </label>
              <span class="toggle-lbl">Portal activo</span>
            </label>
            <label class="toggle-row" title="Aparece na Home">
              <label class="toggle">
                <input type="checkbox" <?= $sec['show_home'] ? 'checked' : '' ?>
                  onchange="toggleSection(<?= $sec['id'] ?>,'show_home',this.checked)">
                <div class="toggle-track"></div>
                <div class="toggle-thumb"></div>
              </label>
              <span class="toggle-lbl">Mostrar na Home</span>
            </label>
          </div>
          <div class="sc-actions">
            <?php if ($editHref !== '#'): ?>
            <a href="<?= $editHref ?>"><button class="btn btn-cr btn-sm">📝 Gerir conteúdo</button></a>
            <?php endif; ?>
            <button class="btn btn-gh btn-sm" onclick="openEdit(<?= $sec['id'] ?>,<?= htmlspecialchars(json_encode($sec), ENT_QUOTES) ?>)">⚙️ Editar</button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- REORDER -->
    <div class="reorder-card">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div>
          <div style="font-size:14px;font-weight:700;color:var(--tx)">↕️ Reordenar Secções</div>
          <div style="font-size:12px;color:var(--tx-l);margin-top:3px">Arraste para definir a ordem de exibição no portal</div>
        </div>
        <button class="btn btn-ok btn-sm" onclick="saveOrder()">💾 Guardar ordem</button>
      </div>
      <div class="reorder-list" id="reorder-list">
        <?php foreach ($sections as $sec): ?>
        <div class="reorder-item" draggable="true" data-id="<?= $sec['id'] ?>">
          <span class="ri-drag">⠿</span>
          <span class="ri-ico"><?= htmlspecialchars($sec['icon']) ?></span>
          <span class="ri-name"><?= htmlspecialchars($sec['name']) ?></span>
          <span class="ri-order">#<?= $sec['sort_order'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div><!-- page-wrap -->
</div><!-- main -->
</div><!-- app -->

<!-- MODAL EDIT SECTION -->
<div class="overlay" id="modal-edit" onclick="if(event.target.id==='modal-edit')closeModal()">
  <div class="modal">
    <div class="m-hd">
      <h3>⚙️ Editar Secção</h3>
      <p id="edit-slug-lbl">—</p>
      <button class="m-close" onclick="closeModal()">✕</button>
    </div>
    <form id="edit-form">
      <div class="m-body">
        <input type="hidden" id="edit-id" name="id">
        <input type="hidden" name="action" value="save_section">
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Ícone (emoji)</label>
            <input class="f-input" id="edit-icon" name="icon" maxlength="4" placeholder="📂">
          </div>
          <div class="f-group">
            <label class="f-lbl">Cor de destaque</label>
            <input class="f-input" type="color" id="edit-color" name="color" style="height:44px;cursor:pointer">
          </div>
        </div>
        <div class="f-group">
          <label class="f-lbl">Nome da secção <span style="color:var(--cr)">*</span></label>
          <input class="f-input" id="edit-name" name="name" placeholder="Nome visível no portal" required>
        </div>
        <div class="f-group">
          <label class="f-lbl">Descrição</label>
          <textarea class="f-ta" id="edit-desc" name="description" placeholder="Descrição breve da secção…"></textarea>
        </div>
      </div>
      <div class="m-foot">
        <button type="button" class="btn btn-gh" onclick="closeModal()">Cancelar</button>
        <button type="submit" class="btn btn-cr">💾 Guardar alterações</button>
      </div>
    </form>
  </div>
</div>

<script>
/* ═══ SIDEBAR ═══ */
const sidebar=document.getElementById('sidebar'),sbOv=document.getElementById('sb-ov');
const sbClose=document.getElementById('sb-close'),sbCol=document.getElementById('sb-col');
let collapsed=false;
function checkBP(){
  const w=window.innerWidth;
  if(w<768){sbClose.style.display=sidebar.classList.contains('open')?'flex':'none';sbCol.style.display='none';sidebar.classList.remove('collapsed');}
  else if(w<1200){sbClose.style.display='none';sbCol.style.display='none';}
  else{sbClose.style.display='none';sbCol.style.display='flex';sbCol.textContent=collapsed?'▶':'◀';}
}
function openSB(){sidebar.classList.add('open');sbOv.style.display='block';setTimeout(()=>sbOv.classList.add('open'),10);sbClose.style.display='flex';document.body.style.overflow='hidden';}
function closeSB(){sidebar.classList.remove('open');sbOv.classList.remove('open');setTimeout(()=>sbOv.style.display='none',300);sbClose.style.display='none';document.body.style.overflow='';}
function toggleCol(){collapsed=!collapsed;sidebar.classList.toggle('collapsed',collapsed);sbCol.textContent=collapsed?'▶':'◀';}
window.addEventListener('resize',checkBP);checkBP();

/* ═══ TOGGLE SECTION ═══ */
function toggleSection(id, col, val) {
  fetch('?api=1', {
    method:'POST',
    body: new URLSearchParams({action:'toggle_section', id, col, value: val ? 1 : 0})
  }).then(r=>r.json()).then(d=>{
    showToast(d.ok ? '✅ Actualizado!' : '❌ Erro ao actualizar', d.ok ? 't-ok' : 't-er');
  });
}

/* ═══ EDIT MODAL ═══ */
function openEdit(id, sec) {
  document.getElementById('edit-id').value   = sec.id;
  document.getElementById('edit-icon').value  = sec.icon;
  document.getElementById('edit-color').value = sec.color || '#6B1020';
  document.getElementById('edit-name').value  = sec.name;
  document.getElementById('edit-desc').value  = sec.description || '';
  document.getElementById('edit-slug-lbl').textContent = '/' + sec.slug;
  document.getElementById('modal-edit').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('modal-edit').classList.remove('open');
  document.body.style.overflow = '';
}
document.getElementById('edit-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  fetch('?api=1', { method:'POST', body: new URLSearchParams(fd) })
    .then(r => r.json()).then(d => {
      showToast(d.msg || '✅ Guardado!', d.ok ? 't-ok' : 't-er');
      if (d.ok) { closeModal(); setTimeout(()=>location.reload(), 700); }
    });
});

/* ═══ DRAG & DROP REORDER ═══ */
let dragSrc = null;
document.querySelectorAll('.reorder-item').forEach(el => {
  el.addEventListener('dragstart', function(e) {
    dragSrc = this; this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
  });
  el.addEventListener('dragend', function() { this.classList.remove('dragging'); });
  el.addEventListener('dragover', function(e) {
    e.preventDefault(); e.dataTransfer.dropEffect = 'move';
    if (this !== dragSrc) {
      const list = this.parentNode;
      const items = [...list.children];
      const srcIdx = items.indexOf(dragSrc);
      const dstIdx = items.indexOf(this);
      if (srcIdx < dstIdx) list.insertBefore(dragSrc, this.nextSibling);
      else list.insertBefore(dragSrc, this);
    }
  });
});

function saveOrder() {
  const ids = [...document.querySelectorAll('.reorder-item')].map(el => el.dataset.id);
  fetch('?api=1', {
    method:'POST',
    body: new URLSearchParams({action:'reorder', ids: JSON.stringify(ids)})
  }).then(r=>r.json()).then(d=>{
    showToast(d.ok ? '✅ Ordem guardada!' : '❌ Erro', d.ok ? 't-ok' : 't-er');
    if(d.ok) setTimeout(()=>location.reload(),700);
  });
}

/* ═══ TOAST ═══ */
function showToast(msg, cls='t-def') {
  const t=document.getElementById('toast');
  t.textContent=msg;t.className='toast '+cls;t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3200);
}
</script>
</body>
</html>
