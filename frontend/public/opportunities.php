<?php
require_once 'includes.php';

if (!isset($_SESSION['jwt_auth'])) {
    header('Location: index.php');
    exit;
}
// ─── API AJAX ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $type  = in_array($_POST['type']??'', ['Curso','Equipamento','Evento','Vaga']) ? $_POST['type'] : 'Curso';
        $f = fn($k) => sanitize($_POST[$k] ?? '');
        $data  = [
          'user_id'       => $_SESSION['user_uuid'],
            'type'        => $type,
            'title'       => $f('title'),
            'description' => $f('description'),
            'source'      => $f('source'),
            'icon'        => $f('icon') ?: '📌',
            'link_url'    => $f('link_url'),
            'location'    => $f('location'),
            'event_date'  => $f('event_date') ?: null,
            'grad_start'  => $f('grad_start') ?: '#4A0B16',
            'grad_end'    => $f('grad_end')   ?: '#8C1A2E',
            'is_featured' => (int)(bool)($_POST['is_featured'] ?? 0),
            'is_active'   => (int)(bool)($_POST['is_active']   ?? 1),
        ];
        if (!$data['title']) { jsonResponse(['ok'=>false,'msg'=>'Título obrigatório'],422); }

        if ($id > 0) {
            $sql = "UPDATE opportunities SET type=:type,title=:title,description=:description,
                    source=:source,icon=:icon,link_url=:link_url,location=:location,
                    event_date=:event_date,grad_start=:grad_start,grad_end=:grad_end,
                    is_featured=:is_featured,is_active=:is_active WHERE id=:id";
            $data['id'] = $id;
            $db->prepare($sql)->execute($data);
            jsonResponse(['ok'=>true,'msg'=>'Oportunidade actualizada com sucesso.','id'=>$id]);
        } else {
            $sql = "INSERT INTO opportunities (user_id,type,title,description,source,icon,link_url,location,
                    event_date,grad_start,grad_end,is_featured,is_active)
                    VALUES (:user_id,:type,:title,:description,:source,:icon,:link_url,:location,
                    :event_date,:grad_start,:grad_end,:is_featured,:is_active)";
            $db->prepare($sql)->execute($data);
            $newId = (int)$db->lastInsertId();
            jsonResponse(['ok'=>true,'msg'=>'Oportunidade criada com sucesso.','id'=>$newId]);
        }
    }

    if ($action === 'toggle') {
        $id  = (int)($_POST['id'] ?? 0);
        $col = in_array($_POST['col']??'', ['is_active','is_featured']) ? $_POST['col'] : 'is_active';
        $val = (int)(bool)($_POST['value'] ?? 0);
        $db->prepare("UPDATE opportunities SET {$col}=? WHERE id=?")->execute([$val, $id]);
        jsonResponse(['ok'=>true]);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->prepare("DELETE FROM opportunities WHERE id=?")->execute([$id]);
        jsonResponse(['ok'=>true,'msg'=>'Oportunidade eliminada.']);
    }

    if ($action === 'reorder') {
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        $stmt = db()->prepare("UPDATE opportunities SET sort_order=? WHERE id=?");
        foreach ($ids as $i => $id) $stmt->execute([$i, (int)$id]);
        jsonResponse(['ok'=>true]);
    }

    jsonResponse(['ok'=>false,'msg'=>'Acção inválida'], 400);
}

// ─── LOAD DATA ────────────────────────────────────────────────
$filter = sanitize($_GET['filter'] ?? 'all');
$search = sanitize($_GET['q']      ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = ['1=1'];
$params = [];

if ($filter !== 'all') {
    $where[]  = 'type = :type';
    $params[':type'] = $filter;
}
if ($search) {
    $where[]  = 'title LIKE :q OR source LIKE :q OR description LIKE :q';
    $params[':q'] = "%{$search}%";
}

$whereSql = implode(' AND ', $where);

$total    = (int)$db->prepare("SELECT COUNT(*) FROM opportunities WHERE {$whereSql}")
->execute($params) ? $db->prepare("SELECT COUNT(*) FROM opportunities WHERE {$whereSql}")
->execute($params) || true : 0;


$countStmt = $db->prepare("SELECT COUNT(*) FROM opportunities WHERE {$whereSql}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$pg     = paginate($total, $page, $perPage);
$dataStmt = $db->prepare("SELECT * FROM opportunities WHERE {$whereSql} ORDER BY sort_order ASC, id DESC LIMIT :limit OFFSET :offset");
$dataStmt->bindValue(':limit',  $pg['per_page'], PDO::PARAM_INT);
$dataStmt->bindValue(':offset', $pg['offset'],   PDO::PARAM_INT);
foreach ($params as $k => $v) $dataStmt->bindValue($k, $v);
$dataStmt->execute();
$rows = $dataStmt->fetchAll();

// Type counts
$typeCounts = $db->query("SELECT type, COUNT(*) as cnt FROM opportunities GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalCount = array_sum($typeCounts);

$flash = getFlash();

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
<title>PetroPub Admin — Oportunidades Oil & Gas</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<style>
:root{
  --cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);
  --gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;--gd-bg:rgba(201,168,76,.11);
  --cream:#FAF7F2;--warm:#FEF9F3;--bdr:rgba(107,16,32,.10);--bdr2:rgba(107,16,32,.06);
  --tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;
  --ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);--ok-bdr:rgba(45,122,79,.25);
  --wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);
  --er:#C53030;--er-bg:rgba(197,48,48,.10);--er-bdr:rgba(197,48,48,.22);
  --inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);
  --sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);
  --sh2:0 8px 32px rgba(107,16,32,.13);--sh3:0 24px 64px rgba(107,16,32,.18);
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

/* SIDEBAR — igual ao admin-seccoes.php */
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
.nb{margin-left:auto;background:#E53E3E;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px}
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
.tb-bc a{color:var(--cr);font-weight:600;cursor:pointer;text-decoration:none}
.tb-title{font-family:'Arial',serif;font-size:clamp(15px,2vw,18px);font-weight:700;color:var(--cr-dk)}
.tb-r{display:flex;align-items:center;gap:8px;flex-shrink:0}
.admin-badge{display:flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff;padding:5px 14px;border-radius:100px;font-size:12px;font-weight:700}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--r2);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-gh{background:var(--cream);color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#246640}
.btn-er{background:var(--er);color:#fff}.btn-er:hover{background:#a62424}
.btn-wn{background:var(--wn);color:#fff}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}
.btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}

/* PAGE */
.page-wrap{flex:1;overflow-y:auto;padding:clamp(16px,3vw,28px) clamp(14px,3vw,32px)}

/* FLASH */
.flash{padding:12px 18px;border-radius:var(--r2);margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
.flash-ok{background:var(--ok-bg);color:var(--ok);border:1px solid rgba(45,122,79,.2)}
.flash-er{background:var(--er-bg);color:var(--er);border:1px solid rgba(197,48,48,.2)}

/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:clamp(10px,1.5vw,12px);margin-bottom:clamp(18px,2.5vw,24px)}
.sc{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(13px,2vw,16px);box-shadow:var(--sh0);transition:box-shadow var(--t);cursor:pointer}
.sc:hover{box-shadow:var(--sh1)}
.sc.on{border-color:var(--cr);box-shadow:0 0 0 2px rgba(107,16,32,.12)}
.sc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.sc-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px}
.sc-num{font-family:'Arial',serif;font-size:clamp(20px,3vw,26px);font-weight:700;color:var(--tx);line-height:1}
.sc-lbl{font-size:11px;color:var(--tx-l);margin-top:3px}

/* TOOLBAR */
.toolbar{background:#fff;border:1px solid var(--bdr);border-radius:var(--r3);padding:clamp(12px,2vw,16px) clamp(14px,2vw,20px);margin-bottom:clamp(14px,2vw,18px);display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--sh0)}
.search-wrap{flex:1;min-width:180px;position:relative}
.search-wrap input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}
.search-wrap input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
.search-wrap input::placeholder{color:var(--tx-l)}
.s-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
.f-sel{padding:9px 28px 9px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}
.f-sel:focus{border-color:var(--cr)}

/* GRID */
.opp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(280px,32vw,340px),1fr));gap:clamp(14px,2vw,18px);margin-bottom:clamp(20px,3vw,28px)}
.opp-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);overflow:hidden;box-shadow:var(--sh0);transition:box-shadow var(--t),transform var(--t);animation:fadeUp .4s ease both}
.opp-card:hover{box-shadow:var(--sh2);transform:translateY(-2px)}
.opp-card.inactive{opacity:.6}
.oc-banner{height:clamp(70px,9vw,90px);display:flex;align-items:center;justify-content:center;font-size:clamp(30px,4vw,40px);position:relative;overflow:hidden}
.oc-banner::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.12),transparent)}
.oc-featured{position:absolute;top:8px;left:8px;background:var(--gd);color:var(--cr-dk);font-size:9px;font-weight:800;padding:2px 9px;border-radius:100px}
.oc-inactive{position:absolute;top:8px;right:8px;background:rgba(0,0,0,.5);color:#fff;font-size:9px;font-weight:800;padding:2px 9px;border-radius:100px}
.oc-approved{position:absolute;top:30px;right:8px;background:var(--gd);color:var(--cr-dk);font-size:9px;font-weight:800;padding:2px 9px;border-radius:100px}
.oc-not-approved{position:absolute;top:30px;right:8px;background:rgba(0,0,0,.5);color:#fff;font-size:9px;font-weight:800;padding:2px 9px;border-radius:100px}
.oc-body{padding:clamp(14px,2vw,18px)}
.oc-type-row{display:flex;align-items:center;gap:6px;margin-bottom:8px}
.oc-tag{font-size:10px;font-weight:700;padding:3px 9px;border-radius:100px}
.t-curso{background:var(--inf-bg);color:var(--inf)}
.t-equip{background:var(--gd-bg);color:var(--gd-dk)}
.t-evento{background:var(--ok-bg);color:var(--ok)}
.t-vaga{background:var(--er-bg);color:var(--er)}
.oc-title{font-family:'Arial',serif;font-size:clamp(13px,1.5vw,15px);font-weight:700;color:var(--tx);line-height:1.35;margin-bottom:5px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.oc-desc{font-size:12px;color:var(--tx-l);line-height:1.5;margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.oc-meta{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.oc-meta-item{font-size:11px;color:var(--tx-l);display:flex;align-items:center;gap:4px}
.oc-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;border-top:1px solid var(--bdr);padding-top:12px}
.oc-views{font-size:11px;color:var(--tx-l)}
.oc-actions{display:flex;gap:6px}

/* TOGGLE */
.toggle{position:relative;width:34px;height:18px;cursor:pointer;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;background:var(--bdr);border-radius:100px;transition:background var(--t)}
.toggle input:checked + .toggle-track{background:var(--ok)}
.toggle-thumb{position:absolute;top:2px;left:2px;width:14px;height:14px;background:#fff;border-radius:50%;transition:transform var(--t);box-shadow:0 1px 4px rgba(0,0,0,.2)}
.toggle input:checked ~ .toggle-thumb{transform:translateX(16px)}

/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:clamp(20px,3vw,28px);flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t);text-decoration:none}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}
.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}
.pg-btn.disabled{opacity:.3;pointer-events:none}

/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;overflow-y:auto}
.overlay.open{display:flex}
.modal{background:#fff;border-radius:var(--r4);width:100%;max-width:600px;box-shadow:var(--sh3);animation:modalIn .28s cubic-bezier(.22,1,.36,1);max-height:calc(100vh - 40px);overflow-y:auto}
@keyframes modalIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
.m-hd{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px);position:relative;border-radius:var(--r4) var(--r4) 0 0}
.mh-cr{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt))}
.mh-er{background:linear-gradient(135deg,#6B0808,var(--er))}
.m-hd h3{font-family:'Arial',serif;font-size:18px;font-weight:700;color:#fff}
.m-hd p{font-size:12px;color:rgba(255,255,255,.58);margin-top:4px}
.m-close{position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.m-close:hover{background:rgba(255,255,255,.28)}
.m-body{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px)}
.m-foot{padding:clamp(12px,1.5vw,14px) clamp(18px,2vw,24px);border-top:1px solid var(--bdr);background:var(--warm);border-radius:0 0 var(--r4) var(--r4);display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
.f-group{margin-bottom:14px}
.f-lbl{display:block;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}
.f-lbl span{color:var(--cr);font-weight:400;text-transform:none;letter-spacing:0}
.f-input{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}
.f-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
.f-input::placeholder{color:var(--tx-l)}
.f-sel-f{width:100%;padding:10px 28px 10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 10px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}
.f-sel-f:focus{border-color:var(--cr)}
.f-ta{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;resize:vertical;min-height:80px;line-height:1.6;transition:all var(--t);font-family:inherit}
.f-ta:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
.f-ta::placeholder{color:var(--tx-l)}
.f-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.f-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.f-check-row{display:flex;align-items:center;gap:8px}
.f-cb{width:15px;height:15px;border-radius:4px;border:1.5px solid var(--bdr);cursor:pointer;accent-color:var(--cr)}
.f-cb-lbl{font-size:13px;color:var(--tx-m);cursor:pointer}
.grad-preview{width:100%;height:40px;border-radius:var(--r1);margin-top:6px;border:1px solid var(--bdr);transition:background .3s}

/* CONFIRM MODAL */
.conf-ico{text-align:center;padding:clamp(20px,3vw,28px) 0 clamp(10px,1.5vw,14px);font-size:clamp(44px,6vw,52px)}
.conf-title{font-family:'Arial',serif;font-size:clamp(17px,2vw,20px);text-align:center;margin-bottom:8px;font-weight:700}
.conf-desc{font-size:13px;color:var(--tx-l);text-align:center;line-height:1.65}

/* EMPTY */
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr)}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:14px}
.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}

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
  .stats-row{grid-template-columns:repeat(3,1fr)}
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
  .f-row-3{grid-template-columns:1fr 1fr}
  .m-foot{flex-wrap:wrap}.m-foot .btn{flex:1;justify-content:center}
}
@media(max-width:480px){.stats-row{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="toast t-def" id="toast"></div>
<div class="sb-ov" id="sb-ov" onclick="closeSB()"></div>

<!-- MODAL: FORM -->
<div class="overlay" id="modal-form" onclick="if(event.target.id==='modal-form')closeForm()">
  <div class="modal">
    <div class="m-hd mh-cr" id="form-head">
      <h3 id="form-title">➕ Nova Oportunidade</h3>
      <p id="form-sub">Preencha todos os campos obrigatórios</p>
      <button class="m-close" onclick="closeForm()">✕</button>
    </div>
    <form id="opp-form">
      <div class="m-body">
        <input type="hidden" id="f-id" name="id" value="0">
        <input type="hidden" name="action" value="save">
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Tipo <span>*</span></label>
            <select class="f-sel-f" id="f-type" name="type">
              <option value="Curso">Curso</option>
              <option value="Equipamento">Equipamento</option>
              <option value="Evento">Evento</option>
              <option value="Vaga">Vaga</option>
            </select>
          </div>
          <div class="f-group">
            <label class="f-lbl">Ícone (emoji)</label>
            <input class="f-input" id="f-icon" name="icon" maxlength="4" placeholder="🎓">
          </div>
        </div>
        <div class="f-group">
          <label class="f-lbl">Título <span>*</span></label>
          <input class="f-input" id="f-title" name="title" placeholder="Ex: Curso de Engenharia de Petróleo" required>
        </div>
        <div class="f-group">
          <label class="f-lbl">Descrição</label>
          <textarea class="f-ta" id="f-desc" name="description" placeholder="Descrição detalhada da oportunidade…"></textarea>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Fonte / Organização</label>
            <input class="f-input" id="f-source" name="source" placeholder="Ex: Sonangol EP">
          </div>
          <div class="f-group">
            <label class="f-lbl">Localização</label>
            <input class="f-input" id="f-location" name="location" placeholder="Ex: Luanda">
          </div>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Data do evento</label>
            <input class="f-input" type="date" id="f-date" name="event_date">
          </div>
          <div class="f-group">
            <label class="f-lbl">Link / URL</label>
            <input class="f-input" type="url" id="f-link" name="link_url" placeholder="https://…">
          </div>
        </div>
        <div class="f-group">
          <label class="f-lbl">Gradiente do banner</label>
          <div class="f-row">
            <div>
              <label class="f-lbl" style="font-size:10px">Cor inicial</label>
              <input class="f-input" type="color" id="f-grad-start" name="grad_start" value="#4A0B16" oninput="updatePreview()" style="height:40px;cursor:pointer">
            </div>
            <div>
              <label class="f-lbl" style="font-size:10px">Cor final</label>
              <input class="f-input" type="color" id="f-grad-end" name="grad_end" value="#8C1A2E" oninput="updatePreview()" style="height:40px;cursor:pointer">
            </div>
          </div>
          <div class="grad-preview" id="grad-preview"></div>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap">
          <label class="f-check-row">
            <input type="checkbox" class="f-cb" id="f-featured" name="is_featured" value="1">
            <span class="f-cb-lbl"><i class="fa fa-star"></i> Destacado</span>
          </label>
          <label class="f-check-row">
            <input type="checkbox" class="f-cb" id="f-active" name="is_active" value="1" checked>
            <span class="f-cb-lbl"><i class="fa fa-check"></i> Activo</span>
          </label>
        </div>
      </div>
      <div class="m-foot">
        <button type="button" class="btn btn-gh" onclick="closeForm()">Cancelar</button>
        <button type="submit" class="btn btn-cr" id="form-submit-btn"><i class="fa fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: CONFIRM DELETE -->
<div class="overlay" id="modal-del" onclick="if(event.target.id==='modal-del')document.getElementById('modal-del').classList.remove('open')">
  <div class="modal" style="max-width:420px">
    <div class="m-hd mh-er">
      <h3>🗑️ Eliminar Oportunidade</h3>
      <p>Esta acção não pode ser desfeita</p>
      <button class="m-close" onclick="document.getElementById('modal-del').classList.remove('open')">✕</button>
    </div>
    <div class="m-body">
      <div class="conf-ico">🗑️</div>
      <div class="conf-title">Confirmar eliminação</div>
      <div class="conf-desc" id="del-desc">Esta oportunidade será permanentemente eliminada da base de dados.</div>
    </div>
    <div class="m-foot">
      <button class="btn btn-gh" onclick="document.getElementById('modal-del').classList.remove('open')">Cancelar</button>
      <button class="btn btn-er" id="del-confirm" onclick="confirmDelete()">🗑️ Eliminar definitivamente</button>
    </div>
  </div>
</div>

<div class="app">
<!-- SIDEBAR -->
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

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="tb-l">
      <button class="tb-ham" onclick="openSB()">☰</button>
      <div>
        <div class="tb-bc"><a href="admin-seccoes.php">Secções</a> / Oportunidades</div>
        <div class="tb-title">⛽ Oportunidades Oil & Gas</div>
      </div>
    </div>
    <div class="tb-r">
      <button class="btn btn-cr" onclick="openForm()">+ Nova oportunidade</button>
      <div class="ava" style="width:36px;height:36px;font-size:12px;cursor:pointer">AD</div>
    </div>
  </div>

  <div class="page-wrap">

    <?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type']==='ok'?'ok':'er' ?>">
      <?= $flash['type']==='ok' ? '<i class=""fa fa-check></i>' : '<i class="fa fa-close"></i>' ?> <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- STATS / FILTER CHIPS -->
    <div class="stats-row">
      <?php
      $filterCards = [
        ['f'=>'all',        'ico'=>'📦','bg'=>'background:var(--cr-xl)','lbl'=>'Todos',        'cnt'=> $totalCount],
        ['f'=>'Curso',      'ico'=>'🎓','bg'=>'background:var(--inf-bg)','lbl'=>'Cursos',       'cnt'=> $typeCounts['Curso']??0],
        ['f'=>'Equipamento','ico'=>'<i class="fa fa-cog"></i>','bg'=>'background:var(--gd-bg)','lbl'=>'Equipamentos',  'cnt'=> $typeCounts['Equipamento']??0],
        ['f'=>'Evento',     'ico'=>'<i class="fa fa-calendar"></i>','bg'=>'background:var(--ok-bg)', 'lbl'=>'Eventos',      'cnt'=> $typeCounts['Evento']??0],
        ['f'=>'Vaga',       'ico'=>'<i class="fa fa-users"></i>','bg'=>'background:var(--er-bg)', 'lbl'=>'Vagas',         'cnt'=> $typeCounts['Vaga']??0],
      ];
      foreach ($filterCards as $fc): ?>
      <a href="?filter=<?= urlencode($fc['f']) ?>&q=<?= urlencode($search) ?>" style="text-decoration:none">
        <div class="sc <?= $filter===$fc['f']?'on':'' ?>">
          <div class="sc-top">
            <div class="sc-ico" style="<?= $fc['bg'] ?>"><?= $fc['ico'] ?></div>
          </div>
          <div class="sc-num"><?= $fc['cnt'] ?></div>
          <div class="sc-lbl"><?= $fc['lbl'] ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TOOLBAR -->
    <form method="GET" action="">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
      <div class="toolbar">
        <div class="search-wrap">
          <span class="s-ico"><i class="fa fa-search"></i></span>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Pesquisar por título, fonte…">
        </div>
        <select class="f-sel" name="filter" onchange="this.form.submit()">
          <option value="all"         <?= $filter==='all'?'selected':'' ?>>Todos os tipos</option>
          <option value="Curso"       <?= $filter==='Curso'?'selected':'' ?>>Cursos</option>
          <option value="Equipamento" <?= $filter==='Equipamento'?'selected':'' ?>>Equipamentos</option>
          <option value="Evento"      <?= $filter==='Evento'?'selected':'' ?>>Eventos</option>
          <option value="Vaga"        <?= $filter==='Vaga'?'selected':'' ?>>Vagas</option>
        </select>
        <button type="submit" class="btn btn-cr btn-sm"><i class="fa fa-search"></i> Pesquisar</button>
        <?php if ($search || $filter!=='all'): ?>
        <a href="?" class="btn btn-gh btn-sm">✕ Limpar</a>
        <?php endif; ?>
        <span style="font-size:13px;color:var(--tx-l);margin-left:auto">
          <?= $total ?> resultado<?= $total!=1?'s':'' ?>
        </span>
      </div>
    </form>

    <!-- GRID -->
    <?php if (empty($rows)): ?>
    <div class="empty">
      <div class="empty-ico">⛽</div>
      <div class="empty-title">Nenhuma oportunidade encontrada</div>
      <p style="font-size:14px;color:var(--tx-l);margin-top:6px;margin-bottom:18px">
        <?= $search ? 'Tente outro termo de pesquisa.' : 'Crie a primeira oportunidade.' ?>
      </p>
      <button class="btn btn-cr" onclick="openForm()">+ Criar oportunidade</button>
    </div>
    <?php else: ?>
    <div class="opp-grid" id="opp-grid">
      <?php foreach ($rows as $i => $row):
        $typeCls = match($row['type']) {
          'Curso'       => 't-curso',
          'Equipamento' => 't-equip',
          'Evento'      => 't-evento',
          'Vaga'        => 't-vaga',
          default       => 't-curso',
        };
        $rowJson = htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES);
      ?>
      <div class="opp-card <?= !$row['is_active']?'inactive':'' ?>" id="card-<?= $row['id'] ?>" style="animation-delay:<?= $i*.05 ?>s">
        <div class="oc-body">
          <div class="oc-type-row">
              <?php if ($row['is_featured']): ?><span class="oc-featured" style="margin-top: -5px; margin-left: 10px;">⭐ Destaque</span><?php endif; ?>
              <?php if (!$row['is_active']): ?><span class="oc-inactive">Inactivo</span><?php endif; ?>
              <?php echo $is_approved = ($row['is_approved'] == 0) ? '<span class="oc-not-approved">Aguardando Aprovação</span>' : '<span class="oc-approved">Aprovado</span>' ?>
              <span class="oc-tag <?= $typeCls ?>"><?= htmlspecialchars($row['type']) ?></span>
            </div>
            <div class="oc-title"><?= htmlspecialchars($row['title']) ?></div>
            <div class="oc-desc"><?= htmlspecialchars($row['description'] ?: '—') ?></div>
            <div class="oc-meta">
              <?php if ($row['source']): ?>
              <span class="oc-meta-item"><?= htmlspecialchars($row['source']) ?></span>
              <?php endif; ?>
              <?php if ($row['location']): ?>
              <span class="oc-meta-item"><?= htmlspecialchars($row['location']) ?></span>
              <?php endif; ?>
              <?php if ($row['event_date']): ?>
              <span class="oc-meta-item"><?= date('d/m/Y', strtotime($row['event_date'])) ?></span>
              <?php endif; ?>
              <?php if ($row['link_url']): ?>
              <span class="oc-meta-item"><a href="<?= htmlspecialchars($row['link_url']) ?>" target="_blank" style="color:var(--cr);font-weight:600">🔗 Link</a></span>
              <?php endif; ?>
            </div>
            <div class="oc-footer">
              <div style="display:flex;align-items:center;gap:8px">
                <label class="toggle" title="Activo/Inactivo">
                  <input type="checkbox" <?= $row['is_active']?'checked':'' ?>
                    onchange="toggleOpp(<?= $row['id'] ?>,'is_active',this.checked)">
                  <div class="toggle-track"></div>
                  <div class="toggle-thumb"></div>
                </label>
                <span class="oc-views"><i class="fa fa-eye"></i> <?= number_format($row['views']) ?></span>
              </div>
              <div class="oc-actions">
                <button class="btn btn-gh btn-xs" onclick="openEdit(<?= $rowJson ?>)" title="Editar"><i class="fa fa-pencil"></i></button>
                <button class="btn btn-er btn-xs" onclick="openDelete(<?= $row['id'] ?>, '<?=$row['title']?>')" title="Eliminar"><i class="fa fa-trash"></i></button>
              </div>
            </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($pg['pages'] > 1): ?>
    <div class="pagination">
      <a href="?filter=<?= urlencode($filter) ?>&q=<?= urlencode($search) ?>&page=<?= $pg['page']-1 ?>"
         class="pg-btn <?= !$pg['has_prev']?'disabled':'' ?>">‹</a>
      <?php for ($p = 1; $p <= $pg['pages']; $p++): ?>
        <?php if ($p===1 || $p===$pg['pages'] || abs($p-$pg['page'])<=1): ?>
        <a href="?filter=<?= urlencode($filter) ?>&q=<?= urlencode($search) ?>&page=<?= $p ?>"
           class="pg-btn <?= $p===$pg['page']?'on':'' ?>"><?= $p ?></a>
        <?php elseif (abs($p-$pg['page'])===2): ?>
        <span style="color:var(--tx-l);padding:0 4px;font-size:13px">…</span>
        <?php endif; ?>
      <?php endfor; ?>
      <a href="?filter=<?= urlencode($filter) ?>&q=<?= urlencode($search) ?>&page=<?= $pg['page']+1 ?>"
         class="pg-btn <?= !$pg['has_next']?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</div>
</div>

<script>
/* ═══ SIDEBAR ═══ */
const sidebar=document.getElementById('sidebar'),sbOv=document.getElementById('sb-ov');
const sbClose=document.getElementById('sb-close'),sbCol=document.getElementById('sb-col');
let collapsed=false;
function checkBP(){const w=window.innerWidth;if(w<768){sbClose.style.display=sidebar.classList.contains('open')?'flex':'none';sbCol.style.display='none';sidebar.classList.remove('collapsed');}else if(w<1200){sbClose.style.display='none';sbCol.style.display='none';}else{sbClose.style.display='none';sbCol.style.display='flex';sbCol.textContent=collapsed?'▶':'◀';}}
function openSB(){sidebar.classList.add('open');sbOv.style.display='block';setTimeout(()=>sbOv.classList.add('open'),10);sbClose.style.display='flex';document.body.style.overflow='hidden';}
function closeSB(){sidebar.classList.remove('open');sbOv.classList.remove('open');setTimeout(()=>sbOv.style.display='none',300);sbClose.style.display='none';document.body.style.overflow='';}
function toggleCol(){collapsed=!collapsed;sidebar.classList.toggle('collapsed',collapsed);sbCol.textContent=collapsed?'▶':'◀';}
window.addEventListener('resize',checkBP);checkBP();

/* ═══ GRADIENT PREVIEW ═══ */
function updatePreview() {
  const s = document.getElementById('f-grad-start').value;
  const e = document.getElementById('f-grad-end').value;
  document.getElementById('grad-preview').style.background = `linear-gradient(135deg,${s},${e})`;
}
updatePreview();

/* ═══ FORM ═══ */
function openForm() {
  document.getElementById('f-id').value    = 0;
  document.getElementById('f-type').value  = 'Curso';
  document.getElementById('f-icon').value  = '🎓';
  document.getElementById('f-title').value = '';
  document.getElementById('f-desc').value  = '';
  document.getElementById('f-source').value   = '';
  document.getElementById('f-location').value = '';
  document.getElementById('f-date').value  = '';
  document.getElementById('f-link').value  = '';
  document.getElementById('f-grad-start').value = '#4A0B16';
  document.getElementById('f-grad-end').value   = '#8C1A2E';
  document.getElementById('f-featured').checked = false;
  document.getElementById('f-active').checked   = true;
  document.getElementById('form-title').textContent = '➕ Nova Oportunidade';
  document.getElementById('form-sub').textContent   = 'Preencha todos os campos obrigatórios';
  document.getElementById('form-submit-btn').textContent = '💾 Criar';
  updatePreview();
  document.getElementById('modal-form').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function openEdit(row) {
  document.getElementById('f-id').value          = row.id;
  document.getElementById('f-type').value        = row.type;
  document.getElementById('f-icon').value        = row.icon;
  document.getElementById('f-title').value       = row.title;
  document.getElementById('f-desc').value        = row.description || '';
  document.getElementById('f-source').value      = row.source || '';
  document.getElementById('f-location').value    = row.location || '';
  document.getElementById('f-date').value        = row.event_date || '';
  document.getElementById('f-link').value        = row.link_url || '';
  document.getElementById('f-grad-start').value  = row.grad_start || '#4A0B16';
  document.getElementById('f-grad-end').value    = row.grad_end   || '#8C1A2E';
  document.getElementById('f-featured').checked  = !!+row.is_featured;
  document.getElementById('f-active').checked    = !!+row.is_active;
  document.getElementById('form-title').textContent = '✏️ Editar Oportunidade';
  document.getElementById('form-sub').textContent   = '#' + row.id + ' — ' + row.type;
  document.getElementById('form-submit-btn').textContent = '💾 Guardar alterações';
  updatePreview();
  document.getElementById('modal-form').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeForm() {
  document.getElementById('modal-form').classList.remove('open');
  document.body.style.overflow = '';
}

document.getElementById('opp-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn = document.getElementById('form-submit-btn');
  btn.disabled = true; btn.textContent = '⌛ A guardando…';
  const fd = new FormData(this);

  // handle unchecked checkboxes
  if (!this.querySelector('#f-featured').checked) fd.append('is_featured', '0');
  if (!this.querySelector('#f-active').checked)   fd.append('is_active',   '0');
  fetch('?api=1', { method:'POST', body: new URLSearchParams(fd) })
    .then(r => r.json()).then(d => {
      btn.disabled = false;
      btn.textContent = 'Guardar';
      showToast(d.msg || (d.ok ? 'Registo realizado com sucesso!' : 'Erro ao realizar o registo'), d.ok ? 't-ok' : 't-er');
      if (d.ok) { closeForm(); setTimeout(() => location.reload(), 700); }
    }).catch(() => { btn.disabled=false; showToast('Erro de rede','t-er'); });
});

/* ═══ TOGGLE ═══ */
function toggleOpp(id, col, val) {
  fetch('?api=1', { method:'POST', body: new URLSearchParams({action:'toggle',id,col,value:val?1:0}) })
    .then(r=>r.json()).then(d=>{
      showToast(d.ok?'Estado actualizado!':'Erro ao actualizar o estado', d.ok?'t-ok':'t-er');
      if(d.ok) {
        const card = document.getElementById('card-'+id);
        if(card) card.classList.toggle('inactive', !val);
      }
    });
}

/* ═══ DELETE ═══ */
let deleteId = null;
function openDelete(id, title) {
  deleteId = id;
  document.getElementById('del-desc').textContent = `"${title}" será eliminado permanentemente.`;
  document.getElementById('modal-del').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function confirmDelete() {
  if (!deleteId) return;
  const btn = document.getElementById('del-confirm');
  btn.disabled = true; btn.textContent = '⌛ A eliminar…';
  fetch('?api=1', { method:'POST', body: new URLSearchParams({action:'delete',id:deleteId}) })
    .then(r=>r.json()).then(d=>{
      showToast(d.msg || '✅ Eliminado!', d.ok?'t-ok':'t-er');
      document.getElementById('modal-del').classList.remove('open');
      document.body.style.overflow = '';
      if(d.ok) {
        const card = document.getElementById('card-'+deleteId);
        if(card) { card.style.transition='opacity .4s,transform .4s'; card.style.opacity='0'; card.style.transform='scale(.9)'; setTimeout(()=>card.remove(),400); }
      }
      btn.disabled=false; btn.textContent='🗑️ Eliminar definitivamente';
      deleteId=null;
    });
}

/* ═══ TOAST ═══ */
function showToast(msg,cls='t-def'){const t=document.getElementById('toast');t.textContent=msg;t.className='toast '+cls;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
</script>
</body>
</html>
