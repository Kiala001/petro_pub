<?php
include_once 'includes.php';

// ─── AUTH CHECK ───────────────────────────────────────────────
if (!isset($_SESSION['jwt_auth'])) {
    header('Location: index.php'); exit;
}

// ─── API ─────────────────────────────────────────────────────
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id  = (int)($_POST['id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($act === 'status') {
        $status = in_array($_POST['status']??'', ['new','read','replied']) ? $_POST['status'] : 'read';
        $db->prepare("UPDATE contacts SET status=? WHERE id=?")->execute([$status, $id]);
        echo json_encode(['ok'=>true,'msg'=>'Estado actualizado.']);
    } elseif ($act === 'delete') {
        $db->prepare("DELETE FROM contacts WHERE id=?")->execute([$id]);
        echo json_encode(['ok'=>true,'msg'=>'Mensagem eliminada.']);
    } elseif ($act === 'bulk_read') {
        $db->query("UPDATE contacts SET status='read' WHERE status='new'");
        echo json_encode(['ok'=>true,'msg'=>'Todas marcadas como lidas.']);
    } else {
        echo json_encode(['ok'=>false,'msg'=>'Acção inválida.'],400);
    }
    exit;
}

// ─── FILTERS ─────────────────────────────────────────────────
$status = in_array($_GET['status']??'', ['new','read','replied','all']) ? ($_GET['status']??'all') : 'all';
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage= 15;

$where  = ['1=1']; $params = [];
if ($status !== 'all') { $where[] = 'status = :st'; $params[':st'] = $status; }
if ($search) { $where[] = '(name LIKE :q OR email LIKE :q OR subject LIKE :q OR message LIKE :q)'; $params[':q'] = "%$search%"; }
$wSql = implode(' AND ', $where);

$cSt = $db->prepare("SELECT COUNT(*) as total FROM contacts WHERE $wSql");
$cSt->execute($params); $total = (int)$cSt->fetch()['total'];
$pages  = max(1, (int)ceil($total / $perPage));
$page   = min($page, $pages);
$offset = ($page - 1) * $perPage;

$dSt = $db->prepare("SELECT * FROM contacts WHERE $wSql ORDER BY created_at DESC LIMIT :lim OFFSET :off");
$dSt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$dSt->bindValue(':off', $offset,  PDO::PARAM_INT);
foreach ($params as $k => $v) $dSt->bindValue($k, $v);
$dSt->execute(); $contacts = $dSt->fetchAll(PDO::FETCH_ASSOC);

// counts for tabs
$cnts = $db->query("SELECT status, COUNT(*) as c FROM contacts GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$newCount = (int)($cnts['new'] ?? 0);

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function buildUrl(array $ov=[]): string {
    global $status,$search,$page;
    $p=['status'=>$status,'q'=>$search,'page'=>$page];
    foreach($ov as $k=>$v) $p[$k]=$v;
    return '?'.http_build_query(array_filter($p, fn($v)=>$v!==null&&$v!==''&&$v!==0&&$v!=='all'));
}
$userName     = $_SESSION['user_name']  ?? 'Usuário';
$userEmail    = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));
?>
<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub Admin — Contactos</title>
<link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<style>
:root{
  --cr:#6b1020;--cr-dk:#4a0b16;--cr-lt:#8c1a2e;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);
  --gd:#c9a84c;--gd-lt:#e5c97e;--gd-dk:#9a7828;--gd-bg:rgba(201,168,76,.11);
  --cream:#faf7f2;--warm:#fef9f3;--bdr:rgba(107,16,32,.10);--bdr2:rgba(107,16,32,.06);
  --tx:#1a1208;--tx-m:#4a3728;--tx-l:#8a7060;
  --ok:#2d7a4f;--ok-bg:rgba(45,122,79,.10);--ok-bdr:rgba(45,122,79,.25);
  --wn:#c47a1a;--wn-bg:rgba(196,122,26,.10);--wn-bdr:rgba(196,122,26,.25);
  --er:#c53030;--er-bg:rgba(197,48,48,.10);--er-bdr:rgba(197,48,48,.22);
  --inf:#1a5c8a;--inf-bg:rgba(26,92,138,.10);
  --sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);
  --sh2:0 8px 32px rgba(107,16,32,.13);--sh3:0 24px 64px rgba(107,16,32,.18);
  --r1:7px;--r2:11px;--r3:15px;--r4:20px;
  --sb:258px;--sb-ico:64px;--hdr:62px;--t:.22s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button{font-family:inherit}a{color:inherit;text-decoration:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--r2);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk)}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#246640}
.btn-er{background:var(--er);color:#fff}.btn-er:hover{background:#a62424}
.btn-gh{background:var(--cream);color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-wn{background:var(--wn);color:#fff}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}
.btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:100px;font-size:11px;font-weight:700}
.badge-new{background:var(--er-bg);color:var(--er);border:1px solid var(--er-bdr)}
.badge-read{background:var(--ok-bg);color:var(--ok);border:1px solid var(--ok-bdr)}
.badge-replied{background:var(--inf-bg);color:var(--inf)}
.badge-wn{background:var(--wn-bg);color:var(--wn);border:1px solid var(--wn-bdr)}
/* APP */
.app{display:flex;min-height:100vh}
/* SIDEBAR */
.sidebar{width:var(--sb);flex-shrink:0;background:var(--cr-dk);height:100vh;position:sticky;top:0;overflow-y:auto;display:flex;flex-direction:column;z-index:200}
.sb-head{padding:22px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:flex-start;gap:8px}
.sb-logo{font-family:'Arial',serif;font-size:21px;font-weight:900;color:#fff}.sb-logo span{color:var(--gd-lt)}
.sb-role{font-size:10px;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.6px;margin-top:4px}
.sb-user{padding:13px 20px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.08)}
.ava{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;border:2px solid rgba(255,255,255,.20);flex-shrink:0;background:#1A3A4A}
.sb-un{font-size:13px;font-weight:600;color:#fff}.sb-ue{font-size:11px;color:rgba(255,255,255,.38)}
.nav-s{padding:12px 10px 2px}.nav-l{font-size:10px;font-weight:700;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:1.5px;padding:0 9px;margin-bottom:4px}
.nav-i{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:10px;cursor:pointer;color:rgba(255,255,255,.58);font-size:13px;font-weight:500;margin-bottom:2px;position:relative;transition:all .16s;text-decoration:none}
.nav-i:hover{background:rgba(255,255,255,.08);color:#fff}.nav-i.act{background:rgba(255,255,255,.14);color:#fff}
.nav-i.act::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gd);border-radius:0 2px 2px 0}
.ni{font-size:15px;width:18px;text-align:center;flex-shrink:0}.nb{margin-left:auto;background:#E53E3E;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px}
.sb-foot{margin-top:auto;padding:10px;border-top:1px solid rgba(255,255,255,.08)}
/* MAIN */
.main{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{background:#fff;border-bottom:1px solid var(--bdr);padding:0 clamp(14px,3vw,30px);height:var(--hdr);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:var(--sh0);gap:10px;flex-shrink:0}
.tb-l{display:flex;align-items:center;gap:10px;min-width:0;flex:1}
.tb-ham{display:none;width:36px;height:36px;border-radius:var(--r1);background:var(--cr-xl);border:1px solid var(--bdr);color:var(--cr);font-size:18px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0}
.tb-bc{font-size:11px;color:var(--tx-l);margin-bottom:2px}.tb-bc a{color:var(--cr);font-weight:600}
.tb-title{font-family:'Arial',serif;font-size:clamp(15px,2vw,18px);font-weight:700;color:var(--cr-dk)}
.tb-r{display:flex;align-items:center;gap:8px;flex-shrink:0}
.admin-badge{display:flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff;padding:5px 14px;border-radius:100px;font-size:12px;font-weight:700}
.page-wrap{flex:1;overflow-y:auto;padding:clamp(16px,3vw,28px) clamp(14px,3vw,32px)}
/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:clamp(10px,1.5vw,14px);margin-bottom:clamp(18px,2.5vw,24px)}
.sc{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(13px,2vw,17px);box-shadow:var(--sh0);animation:fadeUp .4s ease both;cursor:pointer;transition:box-shadow var(--t)}
.sc:hover{box-shadow:var(--sh1)}.sc.on{border-color:var(--cr);box-shadow:0 0 0 2px rgba(107,16,32,.12)}
.sc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
.sc-ico{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px}
.si-cr{background:var(--cr-xl)}.si-ok{background:var(--ok-bg)}.si-er{background:var(--er-bg)}.si-inf{background:var(--inf-bg)}
.sc-num{font-family:'Arial',serif;font-size:clamp(22px,3vw,30px);font-weight:700;color:var(--tx);line-height:1}
.sc-lbl{font-size:12px;color:var(--tx-l);margin-top:4px}
/* TOOLBAR */
.toolbar{background:#fff;border:1px solid var(--bdr);border-radius:var(--r3);padding:clamp(12px,2vw,16px) clamp(14px,2vw,22px);margin-bottom:clamp(14px,2vw,18px);display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--sh0)}
.search-wrap{flex:1;min-width:180px;position:relative}.search-wrap input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}.search-wrap input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.search-wrap input::placeholder{color:var(--tx-l)}
.s-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
.f-sel{padding:9px 28px 9px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer}
.f-sel:focus{border-color:var(--cr)}
/* TABS */
.tab-nav{display:flex;gap:0;border-bottom:2px solid var(--bdr);margin-bottom:20px;overflow-x:auto;scrollbar-width:none}.tab-nav::-webkit-scrollbar{display:none}
.tab-link{padding:10px 18px;font-size:13px;font-weight:600;color:var(--tx-l);text-decoration:none;border-bottom:2.5px solid transparent;margin-bottom:-2px;transition:all var(--t);white-space:nowrap;display:flex;align-items:center;gap:6px}
.tab-link:hover{color:var(--cr)}.tab-link.on{color:var(--cr);border-bottom-color:var(--cr)}
.tab-cnt{background:var(--er);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:100px}
.tab-cnt.ok{background:var(--ok)}.tab-cnt.inf{background:var(--inf)}.tab-cnt.gray{background:var(--tx-l)}
/* CONTACT CARDS */
.contact-list{display:flex;flex-direction:column;gap:10px;margin-bottom:28px}
.contact-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);overflow:hidden;box-shadow:var(--sh0);transition:box-shadow var(--t);animation:fadeUp .35s ease both}
.contact-card:hover{box-shadow:var(--sh1)}.contact-card.is-new{border-left:3px solid var(--er)}
.cc-head{padding:clamp(14px,2vw,18px) clamp(16px,2.5vw,22px);display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;cursor:pointer;user-select:none}
.cc-head:hover{background:var(--warm)}
.cc-left{display:flex;align-items:flex-start;gap:12px;flex:1;min-width:0}
.cc-ava{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.cc-name{font-size:14px;font-weight:700;color:var(--tx);margin-bottom:2px}
.cc-email{font-size:12px;color:var(--tx-l)}
.cc-subject{font-size:13px;color:var(--tx-m);margin-top:5px;font-weight:600}
.cc-meta{display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap}
.cc-date{font-size:11px;color:var(--tx-l)}
.cc-right{display:flex;align-items:center;gap:8px;flex-shrink:0;flex-wrap:wrap}
.cc-actions{display:flex;gap:6px;align-items:center}
/* EXPAND */
.cc-expand{max-height:0;overflow:hidden;transition:max-height .35s cubic-bezier(.4,0,.2,1)}
.cc-expand.open{max-height:600px}
.cc-body{padding:clamp(14px,2vw,18px) clamp(16px,2.5vw,22px);border-top:1px solid var(--bdr);background:var(--warm)}
.cc-msg{font-size:14px;color:var(--tx-m);line-height:1.75;white-space:pre-wrap}
.cc-reply-row{display:flex;gap:8px;margin-top:14px;flex-wrap:wrap}
/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t)}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}.pg-btn.disabled{opacity:.3;pointer-events:none}
/* EMPTY */
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr)}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:12px}.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m)}
/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;overflow-y:auto}.overlay.open{display:flex}
.modal{background:#fff;border-radius:var(--r4);width:100%;max-width:520px;box-shadow:var(--sh3);animation:modalIn .28s cubic-bezier(.22,1,.36,1);max-height:calc(100vh - 40px);overflow-y:auto}
@keyframes modalIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
.m-hd{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px);background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));border-radius:var(--r4) var(--r4) 0 0;position:relative}
.m-hd h3{font-family:'Arial',serif;font-size:18px;font-weight:700;color:#fff}.m-hd p{font-size:12px;color:rgba(255,255,255,.58);margin-top:4px}
.m-close{position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center}.m-close:hover{background:rgba(255,255,255,.28)}
.m-body{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px)}.m-foot{padding:clamp(12px,1.5vw,14px) clamp(18px,2vw,24px);border-top:1px solid var(--bdr);background:var(--warm);border-radius:0 0 var(--r4) var(--r4);display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
.f-group{margin-bottom:14px}.f-lbl{display:block;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}
.f-ta{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;resize:vertical;min-height:120px;line-height:1.6;transition:all var(--t);font-family:inherit}.f-ta:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
/* TOAST */
.toast{position:fixed;bottom:20px;right:20px;z-index:9999;transform:translateY(30px);background:var(--cr-dk);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;border:1px solid rgba(201,168,76,.2)}.toast.show{opacity:1;transform:translateY(0)}
.t-ok{background:var(--ok)}.t-er{background:var(--er)}.t-wn{background:var(--wn)}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media(max-width:1199px){:root{--sb:var(--sb-ico)}.sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nav-i span:not(.ni),.sidebar .nb{opacity:0;max-width:0;pointer-events:none;overflow:hidden}.sidebar .nav-i{justify-content:center;padding:9px}.sidebar .sb-user{justify-content:center;padding:12px}.sidebar .sb-head{justify-content:center;padding:16px 10px}}
@media(max-width:767px){.stats-row{grid-template-columns:repeat(2,1fr)}.tb-ham{display:flex}.topbar{padding:0 14px;height:56px}}
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- MODAL: REPLY -->
<div class="overlay" id="modal-reply" onclick="if(event.target.id==='modal-reply')closeModal('modal-reply')">
  <div class="modal">
    <div class="m-hd"><h3>Responder ao contacto</h3><p id="reply-to-lbl">—</p><button class="m-close" onclick="closeModal('modal-reply')">✕</button></div>
    <div class="m-body">
      <div style="background:var(--cream);border:1px solid var(--bdr);border-radius:var(--r2);padding:12px 14px;margin-bottom:14px;font-size:13px;color:var(--tx-m)" id="reply-original"></div>
      <div class="f-group">
        <label class="f-lbl">A sua resposta</label>
        <textarea class="f-ta" id="reply-text" placeholder="Escreva a sua resposta aqui…"></textarea>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-gh" onclick="closeModal('modal-reply')">Cancelar</button>
      <button class="btn btn-cr" id="reply-send-btn" onclick="sendReply()">Enviar resposta</button>
    </div>
  </div>
</div>

<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="sb-head">
      <div>
        <div class="sb-logo">PETRO<span>PUB</span></div>
        <!-- <div class="sb-role-tag">Administração</div> -->
      </div>
      <button class="sb-tog" id="sb-close" onclick="closeSB()">✕</button>
      <!-- <button class="sb-tog" id="sb-col" onclick="toggleCol()">◀</button> -->
    </div>
    <div class="sb-user">
      <div class="ava ava-dk"><?=$userInitials?></div>
      <div class="sb-ui">
        <div class="sb-un"><?=$userName?></div>
        <div class="sb-ue"><?=$userEmail?></div>
      </div>
    </div>
    <div class="nav-s">
      <div class="nav-l">Visão Geral</div>
      <a href="users.php" class="nav-i" data-tip="Utilizadores">
        <span class="ni"><i class="fa fa-users"></i></span><span class="nt">Utilizadores</span>
      </a>
      <a href="articles.php" class="nav-i act" data-tip="Documentos">
        <span class="ni"><i class="fa fa-book"></i></span><span class="nt">Documentos</span>
      </a>
      <a href="opportunities.php" class="nav-i" data-tip="Oportunidades">
        <span class="ni"><i class="fa fa-list"></i></span><span class="nt">Oportunidades</span>
      </a>
      <a href="library.php" class="nav-i">
        <span class="ni"><i class="fa fa-book"></i></span><span class="nt">Biblioteca</span>
      </a>
    </div>
    <div class="nav-s">
      <a href="opportunity-approve.php" class="nav-i" data-tip="Avaliações">
        <span class="ni"><i class="fa fa-comment-o"></i></span
        ><span class="nt">Revisão das Oportunidades</span
        >
      </a>
      <a href="admin-contacts.php" class="nav-i" data-tip="Avaliações">
        <span class="ni"><i class="fa fa-phone"></i></span
        ><span class="nt">Mensagens de Contacto</span
        >
      </a>
      <div class="nav-i" data-tip="Publicação">
        <span class="ni"><i class="fa fa-file"></i></span><span class="nt">Meus Documentos</span>
      </div>
    </div>
    <div class="sb-foot">
      <div class="nav-i" data-tip="Sair">
        <span class="ni">🚪</span><span class="nt">Terminar Sessão</span>
      </div>
    </div>
  </aside>

<div class="main">
  <div class="topbar">
    <div class="tb-l">
      <div>
        <div class="tb-bc"><a href="admin-dashboard.php">Dashboard</a> / Contactos</div>
        <div class="tb-title">Registos de Contacto</div>
      </div>
    </div>
    <div class="tb-r">
      <?php if($newCount > 0): ?>
      <button class="btn btn-ok btn-sm" onclick="bulkRead()"><i class="fa fa-check"></i> Marcar todas lidas</button>
      <?php endif; ?>
      <div class="admin-badge"><i class="fa fa-cog"></i> <?=$userInitials?></div>
    </div>
  </div>
  <div class="page-wrap">

    <!-- STATS -->
    <div class="stats-row">
      <?php
      $statCards = [
        ['all',  '<i class="fa fa-envelope"></i>', 'si-cr', ($cnts['new']??0)+($cnts['read']??0)+($cnts['replied']??0), 'Total de mensagens'],
        ['new',  '<i class="fa fa-info"></i>', 'si-er', $cnts['new']??0, 'Novas mensagens'],
        ['read', '<i class="fa fa-check"></i>', 'si-ok', $cnts['read']??0, 'Lidas / Vistas'],
        ['replied','<i class="fa fa-comment"></i>','si-inf',$cnts['replied']??0, 'Respondidas'],
      ];
      foreach ($statCards as $i=>[$sv,$ico,$cls,$cnt,$lbl]):
      ?>
      <a href="<?= buildUrl(['status'=>$sv,'page'=>1]) ?>" style="text-decoration:none">
        <div class="sc <?= $status===$sv?'on':'' ?>" style="animation-delay:<?= $i*.06 ?>s">
          <div class="sc-top"><div class="sc-ico <?= $cls ?>"><?= $ico ?></div></div>
          <div class="sc-num"><?= $cnt ?></div>
          <div class="sc-lbl"><?= $lbl ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TABS -->
    <div class="tab-nav">
      <a href="<?= buildUrl(['status'=>'all','page'=>1]) ?>"     class="tab-link <?= $status==='all'?'on':'' ?>">Todas <span class="tab-cnt gray"><?= ($cnts['new']??0)+($cnts['read']??0)+($cnts['replied']??0) ?></span></a>
      <a href="<?= buildUrl(['status'=>'new','page'=>1]) ?>"     class="tab-link <?= $status==='new'?'on':'' ?>">Novas <span class="tab-cnt"><?= $cnts['new']??0 ?></span></a>
      <a href="<?= buildUrl(['status'=>'read','page'=>1]) ?>"    class="tab-link <?= $status==='read'?'on':'' ?>">Lidas <span class="tab-cnt ok"><?= $cnts['read']??0 ?></span></a>
      <a href="<?= buildUrl(['status'=>'replied','page'=>1]) ?>" class="tab-link <?= $status==='replied'?'on':'' ?>">Respondidas <span class="tab-cnt inf"><?= $cnts['replied']??0 ?></span></a>
    </div>

    <!-- SEARCH -->
    <form method="GET" action="">
      <input type="hidden" name="status" value="<?= h($status) ?>">
      <div class="toolbar">
        <div class="search-wrap">
          <span class="s-ico">🔍</span>
          <input type="text" name="q" value="<?= h($search) ?>" placeholder="Pesquisar por nome, e-mail, assunto…">
        </div>
        <button type="submit" class="btn btn-cr btn-sm"><i class="fa fa-search"></i> Pesquisar</button>
        <?php if ($search): ?><a href="<?= buildUrl(['q'=>'','page'=>1]) ?>" class="btn btn-gh btn-sm">✕ Limpar</a><?php endif; ?>
        <span style="font-size:13px;color:var(--tx-l);margin-left:auto"><?= $total ?> mensagem<?= $total!=1?'s':'' ?></span>
      </div>
    </form>

    <!-- CONTACT LIST -->
    <?php if (empty($contacts)): ?>
    <div class="empty">
      <div class="empty-ico"><i class="fa fa-envelope"></i></div>
      <div class="empty-title">Nenhuma mensagem <?= $status!=='all' ? '"'.h($status).'"' : '' ?></div>
      <p style="font-size:14px;color:var(--tx-l);margin-top:8px">
        <?= $search ? 'Tente outros termos de pesquisa.' : 'Nenhum contacto registado ainda.' ?>
      </p>
    </div>
    <?php else: ?>
    <div class="contact-list">
      <?php foreach ($contacts as $i => $c):
        $initials = strtoupper(mb_substr($c['name'], 0, 1));
        $statusBadge = match($c['status']) {
            'new'     => '<span class="badge badge-new">Nova</span>',
            'read'    => '<span class="badge badge-read">Lida</span>',
            'replied' => '<span class="badge badge-replied">Respondida</span>',
            default   => '<span class="badge">—</span>',
        };
        $cj = h(json_encode(['id'=>$c['id'],'name'=>$c['name'],'email'=>$c['email'],'subject'=>$c['subject'],'message'=>$c['message']],JSON_UNESCAPED_UNICODE));
      ?>
      <div class="contact-card <?= $c['status']==='new'?'is-new':'' ?>" id="cc-<?= $c['id'] ?>" style="animation-delay:<?= $i*.04 ?>s">
        <div class="cc-head" onclick="toggleCard(<?= $c['id'] ?>)">
          <div class="cc-left">
            <div class="cc-ava"><?= h($initials) ?></div>
            <div>
              <div class="cc-name"><?= h($c['name']) ?></div>
              <div class="cc-email"><?= h($c['email']) ?></div>
              <div class="cc-subject"><?= h($c['subject']) ?></div>
              <div class="cc-meta">
                <?= $statusBadge ?>
                <span class="cc-date"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
              </div>
            </div>
          </div>
          <div class="cc-right">
            <div class="cc-actions">
              <?php if ($c['status'] === 'new'): ?>
              <button class="btn btn-ok btn-xs" onclick="event.stopPropagation();changeStatus(<?= $c['id'] ?>,'read',this)" title="Marcar como lida"></button>
              <?php endif; ?>
              <button class="btn btn-cr btn-xs"  onclick="event.stopPropagation();openReply(<?= $cj ?>)"  title="Responder"><i class="fa fa-envelope"></i></button>
              <button class="btn btn-er btn-xs"  onclick="event.stopPropagation();deleteContact(<?= $c['id'] ?>)" title="Eliminar"><i class="fa fa-trash"></i></button>
            </div>
          </div>
        </div>
        <div class="cc-expand" id="expand-<?= $c['id'] ?>">
          <div class="cc-body">
            <div class="cc-msg"><?= h($c['message']) ?></div>
            <div class="cc-reply-row">
              <button class="btn btn-cr btn-sm" onclick="openReply(<?= $cj ?>)">Responder por e-mail</button>
              <?php if ($c['status'] !== 'replied'): ?>
              <button class="btn btn-wn btn-sm" onclick="changeStatus(<?= $c['id'] ?>,'replied',null,true)">Marcar como respondida</button>
              <?php endif; ?>
              <a href="mailto:<?= h($c['email']) ?>?subject=Re: <?= urlencode($c['subject']) ?>" class="btn btn-gh btn-sm">Abrir no cliente de e-mail</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($pages > 1): ?>
    <div class="pagination">
      <a href="<?= buildUrl(['page'=>$page-1]) ?>" class="pg-btn <?= $page<=1?'disabled':'' ?>">‹</a>
      <?php for ($p=1; $p<=$pages; $p++):
        $show = ($p===1||$p===$pages||abs($p-$page)<=1);
        if ($show): ?><a href="<?= buildUrl(['page'=>$p]) ?>" class="pg-btn <?= $p===$page?'on':'' ?>"><?= $p ?></a>
        <?php elseif (abs($p-$page)===2): ?><span style="color:var(--tx-l);padding:0 3px">…</span><?php endif;
      endfor; ?>
      <a href="<?= buildUrl(['page'=>$page+1]) ?>" class="pg-btn <?= $page>=$pages?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</div>
</div>

<script>
/* ═══ CARD TOGGLE ═══ */
const openCards = new Set();
function toggleCard(id) {
    const el = document.getElementById('expand-'+id);
    if (!el) return;
    const isOpen = openCards.has(id);
    if (isOpen) { el.classList.remove('open'); openCards.delete(id); }
    else {
        el.classList.add('open'); openCards.add(id);
        // auto-mark as read if new
        const card = document.getElementById('cc-'+id);
        if (card && card.classList.contains('is-new')) {
            changeStatus(id, 'read', null, false);
        }
    }
}

/* ═══ STATUS ═══ */
async function changeStatus(id, status, btn, reload=false) {
    const res  = await fetch('admin-contacts.php?api=1', {method:'POST', body:new URLSearchParams({action:'status',id,status})});
    const data = await res.json();
    showToast(data.msg || '✅ Actualizado', data.ok ? 't-ok' : 't-er');
    if (data.ok) {
        const card = document.getElementById('cc-'+id);
        if (card) card.classList.remove('is-new');
        if (btn) btn.remove();
        if (reload) setTimeout(() => location.reload(), 600);
    }
}

/* ═══ DELETE ═══ */
async function deleteContact(id) {
    if (!confirm('Eliminar esta mensagem definitivamente?')) return;
    const res  = await fetch('admin-contacts.php?api=1', {method:'POST', body:new URLSearchParams({action:'delete',id})});
    const data = await res.json();
    showToast(data.msg || '🗑️ Eliminada', 't-er');
    if (data.ok) {
        const card = document.getElementById('cc-'+id);
        if (card) { card.style.opacity='0'; card.style.transform='translateX(20px)'; card.style.transition='all .4s'; setTimeout(() => card.remove(), 400); }
    }
}

/* ═══ BULK READ ═══ */
async function bulkRead() {
    const res  = await fetch('admin-contacts.php?api=1', {method:'POST', body:new URLSearchParams({action:'bulk_read'})});
    const data = await res.json();
    showToast(data.msg || '✅ Feito', 't-ok');
    if (data.ok) setTimeout(() => location.reload(), 600);
}

/* ═══ REPLY MODAL ═══ */
let replyContact = null;
function openReply(c) {
    replyContact = c;
    document.getElementById('reply-to-lbl').textContent = c.name + ' <' + c.email + '>';
    document.getElementById('reply-original').innerHTML =
        '<strong style="font-size:12px;color:var(--tx-l)">Mensagem original:</strong><br><span style="font-size:13px;color:var(--tx-m)">' + c.message.substring(0, 200) + (c.message.length > 200 ? '…' : '') + '</span>';
    document.getElementById('reply-text').value = '';
    document.getElementById('modal-reply').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
async function sendReply() {
    const text = document.getElementById('reply-text').value.trim();
    if (!text) { showToast('⚠️ Escreva uma resposta', 't-wn'); return; }
    const btn = document.getElementById('reply-send-btn');
    btn.disabled = true; btn.textContent = '⌛ A enviar…';
    // In production, call a real send-email endpoint
    // For now, mark as replied and simulate send
    await changeStatus(replyContact.id, 'replied', null, false);
    showToast('📩 Resposta enviada para ' + replyContact.email, 't-ok');
    closeModal('modal-reply');
    btn.disabled = false; btn.textContent = '📩 Enviar resposta';
}

/* ═══ TOAST ═══ */
function showToast(msg, cls='') {
    const t = document.getElementById('toast');
    t.textContent = msg; t.className = 'toast ' + (cls||''); t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3000);
}
</script>
</body>
</html>
