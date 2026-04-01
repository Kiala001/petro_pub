<?php
include_once 'includes.php';

if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php'); exit;
}

/* ══ API ══ */
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id  = sanitize($_POST['id'] ?? '');
    $act = $_POST['action'] ?? '';

    if ($act === 'block') {
        $val = (int)(bool)($_POST['blocked'] ?? 1);
        // Add `blocked` column if not present (graceful)
        try { $db->exec("ALTER TABLE users ADD COLUMN blocked TINYINT(1) DEFAULT 0 AFTER type"); } catch(Exception $e) {}
        $db->prepare("UPDATE users SET blocked=? WHERE id=?")->execute([$val, $id]);
        echo json_encode(['ok'=>true,'msg'=>$val ? 'Utilizador bloqueado.' : 'Utilizador desbloqueado.','blocked'=>$val]);
        exit;
    }
    if ($act === 'change_type') {
        $type = in_array($_POST['type']??'', ['ADMIN','TEACHER','COORDINATOR','COMMON_USER']) ? $_POST['type'] : 'COMMON_USER';
        $db->prepare("UPDATE users SET type=? WHERE id=?")->execute([$type, $id]);
        echo json_encode(['ok'=>true,'msg'=>'Tipo actualizado para '.$type]);
        exit;
    }
    if ($act === 'history') {
        // Documents by user
        $docs = $db->prepare("SELECT d.id, d.title, d.status, d.category_id, d.created_at, c.name as cat_name FROM documents d LEFT JOIN categories c ON c.id=d.category_id WHERE d.user_id=? ORDER BY d.created_at DESC LIMIT 30");
        $docs->execute([$id]); $docRows = $docs->fetchAll(PDO::FETCH_ASSOC);
        // Downloads by user
        $dls = $db->prepare("SELECT dh.downloaded_at, d.title, d.id as doc_id FROM download_history dh JOIN documents d ON d.id=dh.document_id WHERE dh.user_id=? ORDER BY dh.downloaded_at DESC LIMIT 20");
        $dls->execute([$id]); $dlRows = $dls->fetchAll(PDO::FETCH_ASSOC);
        // Reviews by user
        $rvs = $db->prepare("SELECT dr.rating, dr.comment, dr.created_at, d.title FROM document_review dr JOIN documents d ON d.id=dr.document_id WHERE dr.user_id=? ORDER BY dr.created_at DESC LIMIT 20");
        $rvs->execute([$id]); $rvRows = $rvs->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['ok'=>true,'documents'=>$docRows,'downloads'=>$dlRows,'reviews'=>$rvRows], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(['ok'=>false,'msg'=>'Acção inválida'], 400);
    exit;
}

/* ══ ENSURE BLOCKED COLUMN ══ */
try { $db->exec("ALTER TABLE users ADD COLUMN blocked TINYINT(1) DEFAULT 0 AFTER type"); } catch(Exception $e) {}

/* ══ PARAMS ══ */
$type   = in_array($_GET['type']??'', ['ADMIN','TEACHER','COORDINATOR','COMMON_USER','blocked','all']) ? ($_GET['type']??'all') : 'all';
$search = trim($_GET['q'] ?? '');
$sort   = in_array($_GET['sort']??'', ['name','email','recent','points','balance']) ? $_GET['sort'] : 'recent';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage= 15;

$where  = ['1=1']; $params = [];
if ($type === 'blocked')  { $where[] = 'blocked = 1'; }
elseif ($type !== 'all')  { $where[] = 'type = :type'; $params[':type'] = $type; }
if ($search) { $where[] = '(name LIKE :q OR email LIKE :q)'; $params[':q'] = "%$search%"; }

$oMap = ['name'=>'name ASC','email'=>'email ASC','recent'=>'created_at DESC','points'=>'points DESC','balance'=>'balance DESC'];
$oSql = $oMap[$sort] ?? $oMap['recent'];
$wSql = implode(' AND ', $where);

$cSt = $db->prepare("SELECT COUNT(*) as total FROM users WHERE $wSql");
$cSt->execute($params); $total = (int)$cSt->fetch()['total'];
$pages  = max(1, (int)ceil($total / $perPage));
$page   = min($page, $pages);
$offset = ($page - 1) * $perPage;

$dSt = $db->prepare("SELECT * FROM users WHERE $wSql ORDER BY $oSql LIMIT :lim OFFSET :off");
$dSt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$dSt->bindValue(':off', $offset,  PDO::PARAM_INT);
foreach ($params as $k => $v) $dSt->bindValue($k, $v);
$dSt->execute(); $users = $dSt->fetchAll(PDO::FETCH_ASSOC);

$typeCounts = $db->query("SELECT type, COUNT(*) as c FROM users GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);
$blockedCnt = (int)($db->query("SELECT COUNT(*) FROM users WHERE blocked=1")->fetchColumn());
$totalUsers = array_sum($typeCounts);

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function buildUrl(array $ov=[]): string {
    global $type,$search,$sort,$page;
    $p=['type'=>$type,'q'=>$search,'sort'=>$sort,'page'=>$page];
    foreach($ov as $k=>$v) $p[$k]=$v;
    return '?'.http_build_query(array_filter($p, fn($v)=>$v!==null&&$v!==''&&$v!=='0'&&$v!=='all'));
}
function typeLabel(string $t): string {
    return match($t) {'ADMIN'=>'Admin','TEACHER'=>'Docente','COORDINATOR'=>'Coordenador','COMMON_USER'=>'Utilizador',default=>$t};
}
function typeBadgeCls(string $t): string {
    return match($t) {'ADMIN'=>'badge-admin','TEACHER'=>'badge-teacher','COORDINATOR'=>'badge-coord',default=>'badge-user'};
}
function initials(string $name): string {
    $parts = explode(' ', trim($name));
    return strtoupper(mb_substr($parts[0],0,1).(count($parts)>1 ? mb_substr(end($parts),0,1) : ''));
}
$avatarColors = ['#1A3860','#1A4A2E','#2C1A4A','#5A3A00','#1A3A4A','#6B1020','#2D7A4F','#C47A1A'];
$jwt = $_SESSION['jwt_auth'];
$userName = $_SESSION['user_name'] ?? 'Usuário';
$userEmail = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));
?>
<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub Admin — Utilizadores</title>
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
  --inf:#1a5c8a;--inf-bg:rgba(26,92,138,.10);--pu:#5a3a8a;--pu-bg:rgba(90,58,138,.10);
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
.btn-wn{background:var(--wn);color:#fff}.btn-wn:hover{filter:brightness(.9)}
.btn-gh{background:var(--cream);color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}
.btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:100px;font-size:11px;font-weight:700;gap:3px}
.badge-admin{background:var(--er-bg);color:var(--er);border:1px solid var(--er-bdr)}
.badge-teacher{background:var(--inf-bg);color:var(--inf)}
.badge-coord{background:var(--pu-bg);color:var(--pu)}
.badge-user{background:var(--ok-bg);color:var(--ok)}
.badge-blocked{background:rgba(0,0,0,.07);color:#666;border:1px solid rgba(0,0,0,.10)}
/* SAME LAYOUT AS admin-contacts.php */
.app{display:flex;min-height:100vh}
.sidebar{width:var(--sb);flex-shrink:0;background:var(--cr-dk);height:100vh;position:sticky;top:0;overflow-y:auto;display:flex;flex-direction:column;z-index:200}
.sb-head{padding:22px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:flex-start;gap:8px}
.sb-logo{font-family:'Arial',serif;font-size:21px;font-weight:900;color:#fff}.sb-logo span{color:var(--gd-lt)}
.sb-role{font-size:10px;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.6px;margin-top:4px}
.sb-user{padding:13px 20px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.08)}
.ava{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;border:2px solid rgba(255,255,255,.20);flex-shrink:0;background:#1A3A4A}
.sb-un{font-size:13px;font-weight:600;color:#fff}.sb-ue{font-size:11px;color:rgba(255,255,255,.38)}
.nav-s{padding:12px 10px 2px}.nav-l{font-size:10px;font-weight:700;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:1.5px;padding:0 9px;margin-bottom:4px}
.nav-i{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:10px;cursor:pointer;color:rgba(255,255,255,.58);font-size:13px;font-weight:500;margin-bottom:2px;position:relative;transition:all .16s}
.nav-i:hover{background:rgba(255,255,255,.08);color:#fff}.nav-i.act{background:rgba(255,255,255,.14);color:#fff}
.nav-i.act::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gd);border-radius:0 2px 2px 0}
.ni{font-size:15px;width:18px;text-align:center;flex-shrink:0}.nb{margin-left:auto;background:#E53E3E;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px}
.sb-foot{margin-top:auto;padding:10px;border-top:1px solid rgba(255,255,255,.08)}
.main{flex:1;min-width:0;display:flex;flex-direction:column}
.topbar{background:#fff;border-bottom:1px solid var(--bdr);padding:0 clamp(14px,3vw,30px);height:var(--hdr);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:var(--sh0);gap:10px;flex-shrink:0}
.tb-l{display:flex;align-items:center;gap:10px;min-width:0;flex:1}
.tb-bc{font-size:11px;color:var(--tx-l);margin-bottom:2px}.tb-bc a{color:var(--cr);font-weight:600}
.tb-title{font-family:'Arial',serif;font-size:clamp(15px,2vw,18px);font-weight:700;color:var(--cr-dk)}
.tb-r{display:flex;align-items:center;gap:8px;flex-shrink:0}
.admin-badge{display:flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff;padding:5px 14px;border-radius:100px;font-size:12px;font-weight:700}
.page-wrap{flex:1;overflow-y:auto;padding:clamp(16px,3vw,28px) clamp(14px,3vw,32px)}
/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:clamp(10px,1.5vw,14px);margin-bottom:clamp(18px,2.5vw,24px)}
.sc{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(13px,2vw,17px);box-shadow:var(--sh0);animation:fadeUp .4s ease both;cursor:pointer;transition:all var(--t)}
.sc:hover{box-shadow:var(--sh1)}.sc.on{border-color:var(--cr);box-shadow:0 0 0 2px rgba(107,16,32,.12)}
.sc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.sc-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px}
.si-cr{background:var(--cr-xl)}.si-ok{background:var(--ok-bg)}.si-er{background:var(--er-bg)}.si-inf{background:var(--inf-bg)}.si-pu{background:var(--pu-bg)}.si-wn{background:var(--wn-bg)}
.sc-num{font-family:'Arial',serif;font-size:clamp(20px,3vw,28px);font-weight:700;color:var(--tx);line-height:1}
.sc-lbl{font-size:11px;color:var(--tx-l);margin-top:3px}
/* TOOLBAR */
.toolbar{background:#fff;border:1px solid var(--bdr);border-radius:var(--r3);padding:clamp(12px,2vw,16px) clamp(14px,2vw,22px);margin-bottom:clamp(14px,2vw,18px);display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--sh0)}
.search-wrap{flex:1;min-width:180px;position:relative}.search-wrap input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}.search-wrap input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.search-wrap input::placeholder{color:var(--tx-l)}
.s-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
.f-sel{padding:9px 28px 9px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer}
/* TABLE */
.users-table{width:100%;border-collapse:collapse;font-size:13px;background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);overflow:hidden;box-shadow:var(--sh0)}
.users-table thead tr{background:linear-gradient(to right,var(--warm),#fff);border-bottom:2px solid var(--bdr)}
.users-table th{padding:12px 14px;text-align:left;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;white-space:nowrap}
.users-table tbody tr{border-bottom:1px solid var(--bdr2);transition:background var(--t)}
.users-table tbody tr:last-child{border-bottom:none}
.users-table tbody tr:hover{background:var(--warm)}
.users-table tbody tr.blocked-row{opacity:.6;background:rgba(197,48,48,.03)}
.users-table td{padding:12px 14px;vertical-align:middle}
.u-cell{display:flex;align-items:center;gap:10px}
.u-ava{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
.u-name{font-size:13px;font-weight:700;color:var(--tx);margin-bottom:1px}
.u-email{font-size:11px;color:var(--tx-l)}
.u-id{font-size:10px;color:var(--tx-l);font-family:monospace}
.act-cell{display:flex;align-items:center;gap:5px;flex-wrap:nowrap}
/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:clamp(20px,3vw,28px);flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t)}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}.pg-btn.disabled{opacity:.3;pointer-events:none}
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr)}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:12px}.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}
/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;overflow-y:auto}.overlay.open{display:flex}
.modal{background:#fff;border-radius:var(--r4);width:100%;max-width:680px;box-shadow:var(--sh3);animation:modalIn .28s cubic-bezier(.22,1,.36,1);max-height:calc(100vh - 40px);overflow-y:auto}
@keyframes modalIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
.m-hd{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px);position:relative;border-radius:var(--r4) var(--r4) 0 0}
.mh-cr{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt))}
.m-hd h3{font-family:'Arial',serif;font-size:18px;font-weight:700;color:#fff}.m-hd p{font-size:12px;color:rgba(255,255,255,.58);margin-top:4px}
.m-close{position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center}.m-close:hover{background:rgba(255,255,255,.28)}
.m-body{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px)}.m-foot{padding:clamp(12px,1.5vw,14px) clamp(18px,2vw,24px);border-top:1px solid var(--bdr);background:var(--warm);border-radius:0 0 var(--r4) var(--r4);display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
/* HISTORY TABS */
.htab-nav{display:flex;gap:0;border-bottom:1px solid var(--bdr);margin-bottom:16px}
.htab{padding:8px 16px;font-size:13px;font-weight:600;color:var(--tx-l);cursor:pointer;border-bottom:2.5px solid transparent;margin-bottom:-1px;transition:all var(--t)}
.htab:hover{color:var(--cr)}.htab.on{color:var(--cr);border-bottom-color:var(--cr)}
.hpanel{display:none}.hpanel.open{display:block}
.hist-item{display:flex;align-items:flex-start;gap:10px;padding:10px 0;border-bottom:1px solid var(--bdr2);font-size:13px;color:var(--tx-m)}
.hist-item:last-child{border-bottom:none}
.hi-ico{font-size:18px;flex-shrink:0;width:24px;text-align:center}
.hi-body{flex:1;min-width:0}
.hi-title{font-weight:600;color:var(--tx);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hi-meta{font-size:11px;color:var(--tx-l);margin-top:2px}
.hi-right{font-size:11px;color:var(--tx-l);flex-shrink:0;text-align:right}
.no-hist{text-align:center;padding:24px;color:var(--tx-l);font-size:13px}
/* USER INFO GRID */
.u-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.u-info-item{background:var(--cream);border:1px solid var(--bdr);border-radius:var(--r2);padding:11px 14px}
.u-info-lbl{font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px}
.u-info-val{font-size:14px;font-weight:600;color:var(--tx)}
/* TYPE CHANGE SELECT */
.type-select{padding:7px 24px 7px 10px;border:1.5px solid var(--bdr);border-radius:var(--r1);font-size:12px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='4'%3E%3Cpath d='M1 1l3 2 3-2' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 7px) center;appearance:none;outline:none;cursor:pointer}
/* TOAST */
.toast{position:fixed;bottom:20px;right:20px;z-index:9999;transform:translateY(30px);background:var(--cr-dk);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;border:1px solid rgba(201,168,76,.2)}.toast.show{opacity:1;transform:translateY(0)}
.t-ok{background:var(--ok)}.t-er{background:var(--er)}.t-wn{background:var(--wn)}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media(max-width:1199px){:root{--sb:var(--sb-ico)}.sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nav-i span:not(.ni),.sidebar .nb{opacity:0;max-width:0;pointer-events:none;overflow:hidden}.sidebar .nav-i{justify-content:center;padding:9px}.sidebar .sb-user{justify-content:center;padding:12px}.sidebar .sb-head{justify-content:center;padding:16px 10px}}
@media(max-width:767px){.stats-row{grid-template-columns:repeat(2,1fr)}.topbar{padding:0 14px;height:56px}.hide-mob{display:none}.u-info-grid{grid-template-columns:1fr}}
@media(max-width:480px){.stats-row{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- MODAL: USER HISTORY -->
<div class="overlay" id="modal-history" onclick="if(event.target.id==='modal-history')closeHistory()">
  <div class="modal">
    <div class="m-hd mh-cr">
      <h3 id="hist-name">Histórico do Utilizador</h3>
      <p id="hist-email">—</p>
      <button class="m-close" onclick="closeHistory()">✕</button>
    </div>
    <div class="m-body">
      <!-- USER INFO -->
      <div class="u-info-grid" id="hist-info-grid"></div>
      <!-- CHANGE TYPE -->
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap">
        <span style="font-size:13px;font-weight:600;color:var(--tx)">Alterar tipo:</span>
        <select class="type-select" id="type-select" onchange="changeType()">
          <option value="COMMON_USER">Utilizador</option>
          <option value="TEACHER">Docente</option>
          <option value="COORDINATOR">Coordenador</option>
          <option value="ADMIN">Admin</option>
        </select>
        <button id="block-btn" class="btn btn-sm" onclick="toggleBlock()">⌛</button>
      </div>
      <!-- HISTORY TABS -->
      <div class="htab-nav">
        <div class="htab on" id="ht-docs"     onclick="switchHTab('docs',this)">📄 Documentos <span id="ht-docs-cnt" style="font-size:11px;color:var(--tx-l)"></span></div>
        <div class="htab"    id="ht-downloads" onclick="switchHTab('downloads',this)">📥 Downloads <span id="ht-dl-cnt" style="font-size:11px;color:var(--tx-l)"></span></div>
        <div class="htab"    id="ht-reviews"   onclick="switchHTab('reviews',this)">⭐ Avaliações <span id="ht-rv-cnt" style="font-size:11px;color:var(--tx-l)"></span></div>
      </div>
      <div id="hpanel-docs" class="hpanel open"><div class="no-hist">⌛ A carregar…</div></div>
      <div id="hpanel-downloads" class="hpanel"><div class="no-hist">⌛ A carregar…</div></div>
      <div id="hpanel-reviews" class="hpanel"><div class="no-hist">⌛ A carregar…</div></div>
    </div>
    <div class="m-foot">
      <button class="btn btn-gh" onclick="closeHistory()">Fechar</button>
    </div>
  </div>
</div>

<div class="app">


<aside class="sidebar" id="sidebar">
  <div class="sb-head">
    <div><div class="sb-logo">PETRO<span>PUB</span></div><div class="sb-role">Administração</div></div>
    <button class="sb-tog" id="sb-close" onclick="closeSB()">✕</button>
    <!-- <button class="sb-tog" id="sb-col" onclick="toggleCol()">◀</button> -->
  </div>
  <div class="sb-user">
    <div class="ava"><?=$userInitials?></div>
    <div><div class="sb-un"><?=$userName?></div><div class="sb-ue"><?=$userEmail?></div></div>
  </div>
  <div class="nav-s">
    <div class="nav-l">Visão Geral</div>
    <div class="nav-i" onclick="location.href='my-documents.php'"><span class="ni"><i class="fa fa-file"></i></span><span>Meus Artigos</span></div>
    <div class="nav-i" onclick="location.href='users.php'"><span class="ni"><i class="fa fa-users"></i></span><span>Utilizadores</span></div>
    <div class="nav-i" onclick="location.href='articles.php'"><span class="ni"><i class="fa fa-search"></i></span><span>Revisão por par</span></div>
    <div class="nav-i" onclick="location.href='opportunity-approve.php'"><span class="ni"><i class="fa fa-search"></i></span><span>Aprovar Oportunidades</span></div>
  </div>
  <div class="nav-s">
    <div class="nav-l">Portal</div>
    <div class="nav-i" onclick="location.href='library.php'"><span class="ni"><i class="fa fa-book"></i></span><span>Biblioteca</span></div>
    <div class="nav-i act" onclick="location.href='opportunities.php'"><span class="ni"><i class="fa fa-bomba"></i></span><span>Oportunidades</span></div>
    <div class="nav-i" onclick="location.href='noticy.php'"><span class="ni"><i class="fa fa-info"></i></span><span>Notícias</span></div>
    <!-- <div class="nav-i" onclick="location.href='admin-destaques.php'"><span class="ni">🔥</span><span>Destaques</span></div> -->
  </div>
  <div class="sb-foot">
    <div class="nav-i" onclick="location.href='logout.php'"><span class="ni">🚪</span><span>Sair</span></div>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="tb-l">
      <div>
        <div class="tb-bc"><a href="admin-dashboard.php">Dashboard</a> / Utilizadores</div>
        <div class="tb-title"><i class="fa fa-users"></i> Gestão de Utilizadores</div>
      </div>
    </div>
    <div class="tb-r">
      <div class="admin-badge"><i class="fa fa-cog"></i> Admin</div>
    </div>
  </div>
  <div class="page-wrap">

    <!-- STATS -->
    <div class="stats-row">
      <?php
      $statCards = [
        ['all',         '👥','si-cr',  $totalUsers,               'Total de utilizadores'],
        ['COMMON_USER', '👤','si-ok',  $typeCounts['COMMON_USER']??0, 'Utilizadores'],
        ['TEACHER',     '🎓','si-inf', $typeCounts['TEACHER']??0,     'Docentes'],
        // ['COORDINATOR', '🏅','si-pu',  $typeCounts['COORDINATOR']??0, 'Coordenadores'],
        ['blocked',     '🔒','si-er',  $blockedCnt,               'Bloqueados'],
      ];
      foreach ($statCards as $i => [$sv,$ico,$cls,$cnt,$lbl]):
      ?>
      <a href="<?= buildUrl(['type'=>$sv,'page'=>1]) ?>" style="text-decoration:none">
        <div class="sc <?= $type===$sv?'on':'' ?>" style="animation-delay:<?= $i*.05 ?>s">
          <div class="sc-top"><div class="sc-ico <?= $cls ?>"><?= $ico ?></div></div>
          <div class="sc-num"><?= $cnt ?></div>
          <div class="sc-lbl"><?= $lbl ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TOOLBAR -->
    <form method="GET" action="">
      <?php if($type!=='all'): ?><input type="hidden" name="type" value="<?= h($type) ?>"><?php endif; ?>
      <div class="toolbar">
        <div class="search-wrap">
          <span class="s-ico"><i class="fa fa-search"></i></span>
          <input type="text" name="q" value="<?= h($search) ?>" placeholder="Pesquisar por nome ou e-mail…">
        </div>
        <select class="f-sel" name="type" onchange="this.form.submit()">
          <option value="all"         <?= $type==='all'?'selected':'' ?>>Todos os tipos</option>
          <option value="COMMON_USER" <?= $type==='COMMON_USER'?'selected':'' ?>>Utilizadores</option>
          <option value="TEACHER"     <?= $type==='TEACHER'?'selected':'' ?>>Docentes</option>
          <option value="COORDINATOR" <?= $type==='COORDINATOR'?'selected':'' ?>>Coordenadores</option>
          <option value="ADMIN"       <?= $type==='ADMIN'?'selected':'' ?>>Admins</option>
          <option value="blocked"     <?= $type==='blocked'?'selected':'' ?>>Bloqueados</option>
        </select>
        <select class="f-sel" name="sort" onchange="this.form.submit()">
          <option value="recent"  <?= $sort==='recent' ?'selected':'' ?>>Mais recentes</option>
          <option value="name"    <?= $sort==='name'   ?'selected':'' ?>>Nome A→Z</option>
          <option value="points"  <?= $sort==='points' ?'selected':'' ?>>Mais pontos</option>
          <option value="balance" <?= $sort==='balance'?'selected':'' ?>>Maior saldo</option>
        </select>
        <button type="submit" class="btn btn-cr btn-sm"><i class="fa fa-search"></i> Pesquisar</button>
        <?php if($search||$type!=='all'): ?><a href="admin-users.php" class="btn btn-gh btn-sm">✕ Limpar</a><?php endif; ?>
        <span style="font-size:13px;color:var(--tx-l);margin-left:auto"><?= $total ?> utilizador<?= $total!=1?'es':'' ?></span>
      </div>
    </form>

    <!-- TABLE -->
    <?php if (empty($users)): ?>
    <div class="empty">
      <div class="empty-ico"><i class="fa fa-users"></i></div>
      <div class="empty-title">Nenhum utilizador encontrado</div>
      <p style="font-size:14px;color:var(--tx-l);margin-top:6px">Tente ajustar os filtros ou a pesquisa.</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;border-radius:var(--r3)">
      <table class="users-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Utilizador</th>
            <th class="hide-mob">Tipo</th>
            <th class="hide-mob">Artigos</th>
            <th class="hide-mob">Outras</th>
            <th class="hide-mob">Membro desde</th>
            <th>Estado</th>
            <th>Acções</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $i => $u):
            $blocked = !empty($u['blocked']);
            $avatarColor = $avatarColors[abs(crc32($u['id'])) % count($avatarColors)];
            $uj = h(json_encode([
              'id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],
              'type'=>$u['type'],'points'=>$u['points'],'balance'=>$u['balance'],
              'created_at'=>$u['created_at'],'blocked'=>$blocked?1:0,
            ], JSON_UNESCAPED_UNICODE));

            $doc = $db->prepare("SELECT COUNT(*) as total FROM documents WHERE user_id=?");
            $doc->execute([$u['id']]);
            $doc_count = $doc->fetch()['total'];


            $opp = $db->prepare("SELECT COUNT(*) as total FROM opportunities WHERE user_id=?");
            $opp->execute([$u['id']]);
            $opp_count = $opp->fetch()['total'];
          ?>
          <tr class="<?= $blocked?'blocked-row':'' ?>" id="urow-<?= h($u['id']) ?>">
            <td style="font-size:12px;color:var(--tx-l);text-align:center"><?= $offset+$i+1 ?></td>
            <td>
              <div class="u-cell">
                <div class="u-ava" style="background:<?= $avatarColor ?>"><?= h(initials($u['name'])) ?></div>
                <div>
                  <div class="u-name"><?= h($u['name']) ?></div>
                  <div class="u-email"><?= h($u['email']) ?></div>
                  <div class="u-id"><?= h($u['id']) ?></div>
                </div>
              </div>
            </td>
            <td class="hide-mob">
              <span class="badge <?= typeBadgeCls($u['type']) ?>"><?= typeLabel($u['type']) ?></span>
            </td>
            <td class="hide-mob" style="font-size:13px;font-weight:600;color:var(--gd-dk)">
              <?=$doc_count ?> Publicados
            </td>
            <td class="hide-mob" style="font-size:13px;font-weight:600;color:var(--ok)">
              <?=$opp_count ?> Oportunidades | - Avaliações
            </td>
            <td class="hide-mob" style="font-size:12px;color:var(--tx-l)">
              <?= date('d/m/Y', strtotime($u['created_at'])) ?>
            </td>
            <td>
              <?php if ($blocked): ?>
              <span class="badge badge-blocked">Bloqueado</span>
              <?php else: ?>
              <span class="badge badge-user" style="background:var(--ok-bg);color:var(--ok)">Activo</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="act-cell">
                <button class="btn btn-gh btn-xs" onclick="openHistory(<?= $uj ?>)" title="Ver histórico"> Histórico</button>
                <?php if ($blocked): ?>
                <button class="btn btn-ok btn-xs" onclick="toggleBlockUser('<?= h($u['id']) ?>',false,this)" title="Desbloquear">🔓</button>
                <?php else: ?>
                <button class="btn btn-er btn-xs" onclick="toggleBlockUser('<?= h($u['id']) ?>',true,this)" title="Bloquear">🔒</button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <?php if ($pages > 1): ?>
    <div class="pagination">
      <a href="<?= buildUrl(['page'=>$page-1]) ?>" class="pg-btn <?= $page<=1?'disabled':'' ?>">‹</a>
      <?php for ($p=1;$p<=$pages;$p++):
        $show=($p===1||$p===$pages||abs($p-$page)<=1);
        if($show): ?><a href="<?= buildUrl(['page'=>$p]) ?>" class="pg-btn <?= $p===$page?'on':'' ?>"><?= $p ?></a>
        <?php elseif(abs($p-$page)===2): ?><span style="color:var(--tx-l);padding:0 3px">…</span><?php endif;
      endfor; ?>
      <a href="<?= buildUrl(['page'=>$page+1]) ?>" class="pg-btn <?= $page>=$pages?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</div>
</div>

<script>
let currentUser = null;

/* ═══ HISTORY MODAL ═══ */
async function openHistory(u) {
    currentUser = u;
    document.getElementById('hist-name').textContent  = u.name;
    document.getElementById('hist-email').textContent = u.email;
    document.getElementById('type-select').value = u.type;
    document.getElementById('block-btn').textContent = u.blocked ? '🔓 Desbloquear' : '🔒 Bloquear';
    document.getElementById('block-btn').className = 'btn btn-sm ' + (u.blocked ? 'btn-ok' : 'btn-er');

    // Info grid
    document.getElementById('hist-info-grid').innerHTML = [
        ['🆔 ID',      u.id],
        ['📧 E-mail',  u.email],
        ['🏅 Tipo',    typeLabel(u.type)],
        ['⭐ Pontos',  Number(u.points).toLocaleString('pt-PT') + ' pts'],
        ['💰 Saldo',   Number(u.balance).toFixed(2) + ' Kz'],
        ['📅 Membro',  new Date(u.created_at).toLocaleDateString('pt-PT')],
    ].map(([l,v]) => `<div class="u-info-item"><div class="u-info-lbl">${l}</div><div class="u-info-val">${v}</div></div>`).join('');

    // Reset panels
    ['docs','downloads','reviews'].forEach(k => {
        document.getElementById('hpanel-'+k).innerHTML = '<div class="no-hist">⌛ A carregar…</div>';
        document.getElementById('hpanel-'+k).className = 'hpanel' + (k==='docs'?' open':'');
    });
    document.querySelectorAll('.htab').forEach((t,i)=> t.classList.toggle('on',i===0));

    // Open modal
    document.getElementById('modal-history').classList.add('open');
    document.body.style.overflow = 'hidden';

    // Load history
    try {
        const res  = await fetch('admin-users.php?api=1', {method:'POST', body:new URLSearchParams({action:'history', id:u.id})});
        const data = await res.json();
        if (!data.ok) return;

        document.getElementById('ht-docs-cnt').textContent = '(' + data.documents.length + ')';
        document.getElementById('ht-dl-cnt').textContent   = '(' + data.downloads.length + ')';
        document.getElementById('ht-rv-cnt').textContent   = '(' + data.reviews.length + ')';

        document.getElementById('hpanel-docs').innerHTML = data.documents.length ? data.documents.map(d => `
            <div class="hist-item">
                <span class="hi-ico">📄</span>
                <div class="hi-body">
                    <div class="hi-title">${d.title}</div>
                    <div class="hi-meta">${d.cat_name||'—'} · ${d.created_at||'—'}</div>
                </div>
                <div class="hi-right"><span style="padding:2px 8px;border-radius:100px;font-size:10px;font-weight:700;background:rgba(45,122,79,.1);color:#2d7a4f">${d.status}</span></div>
            </div>`).join('') : '<div class="no-hist">Sem documentos submetidos</div>';

        document.getElementById('hpanel-downloads').innerHTML = data.downloads.length ? data.downloads.map(d => `
            <div class="hist-item">
                <span class="hi-ico">📥</span>
                <div class="hi-body">
                    <div class="hi-title">${d.title}</div>
                    <div class="hi-meta">Baixado em ${new Date(d.downloaded_at).toLocaleDateString('pt-PT')}</div>
                </div>
            </div>`).join('') : '<div class="no-hist">Sem histórico de downloads</div>';

        document.getElementById('hpanel-reviews').innerHTML = data.reviews.length ? data.reviews.map(d => {
            const stars = '★'.repeat(Math.min(5,Math.max(0,+d.rating))) + '☆'.repeat(5-Math.min(5,Math.max(0,+d.rating)));
            return `<div class="hist-item">
                <span class="hi-ico">⭐</span>
                <div class="hi-body">
                    <div class="hi-title">${d.title}</div>
                    <div class="hi-meta">${d.comment?.substring(0,80)||'—'}</div>
                </div>
                <div class="hi-right" style="color:var(--gd-dk);font-weight:700">${stars} ${d.rating}</div>
            </div>`;
        }).join('') : '<div class="no-hist">Sem avaliações</div>';

    } catch(e) { console.error(e); }
}
function closeHistory() {
    document.getElementById('modal-history').classList.remove('open');
    document.body.style.overflow = '';
    currentUser = null;
}
function switchHTab(tab, btn) {
    document.querySelectorAll('.htab').forEach(t=>t.classList.remove('on'));
    btn.classList.add('on');
    document.querySelectorAll('.hpanel').forEach(p=>p.classList.remove('open'));
    document.getElementById('hpanel-'+tab).classList.add('open');
}
function typeLabel(t) {
    return {ADMIN:'Admin',TEACHER:'Docente',COORDINATOR:'Coordenador',COMMON_USER:'Utilizador'}[t]||t;
}

/* ═══ BLOCK/UNBLOCK ═══ */
async function toggleBlockUser(id, block, btn) {
    if (block && !confirm('Bloquear este utilizador? Perderá acesso à plataforma.')) return;
    const res  = await fetch('admin-users.php?api=1', {method:'POST', body:new URLSearchParams({action:'block',id,blocked:block?1:0})});
    const data = await res.json();
    showToast(data.msg || '✅ Feito', data.ok?(block?'t-er':'t-ok'):'t-wn');
    if (data.ok) setTimeout(() => location.reload(), 600);
}
async function toggleBlock() {
    if (!currentUser) return;
    await toggleBlockUser(currentUser.id, !currentUser.blocked, null);
}

/* ═══ CHANGE TYPE ═══ */
async function changeType() {
    if (!currentUser) return;
    const newType = document.getElementById('type-select').value;
    if (newType === currentUser.type) return;
    const res  = await fetch('admin-users.php?api=1', {method:'POST', body:new URLSearchParams({action:'change_type',id:currentUser.id,type:newType})});
    const data = await res.json();
    showToast(data.msg || '✅ Actualizado', data.ok?'t-ok':'t-er');
    if (data.ok) currentUser.type = newType;
}

/* ═══ TOAST ═══ */
function showToast(msg, cls='') {
    const t=document.getElementById('toast');t.textContent=msg;t.className='toast '+(cls||'');t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),3000);
}
</script>
</body>
</html>
