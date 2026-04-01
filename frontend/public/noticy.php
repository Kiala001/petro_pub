<?php
require_once 'includes.php';
if (!isset($_SESSION['jwt_auth'])) {
    header('Location: index.php');
    exit;
}

// ─── API ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['api'])) {
    $action = $_POST['action'] ?? '';
    $f = fn($k) => sanitize($_POST[$k] ?? '');

    if ($action === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $type = in_array($f('type'), ['info','success','warning','update']) ? $f('type') : 'info';
        $data = [
            'icon'        => $f('icon') ?: '📢',
            'title'       => $f('title'),
            'description' => $f('description'),
            'link_url'    => $f('link_url'),
            'type'        => $type,
            'is_active'   => (int)(bool)($_POST['is_active'] ?? 1),
            'expires_at'  => $f('expires_at') ?: null,
            'published_at'=> $f('published_at') ?: date('Y-m-d H:i:s'),
        ];
        if (!$data['title']) jsonResponse(['ok'=>false,'msg'=>'Título obrigatório'],422);
        if ($id > 0) {
            $db->prepare("UPDATE notices SET icon=:icon,title=:title,description=:description,
                link_url=:link_url,type=:type,is_active=:is_active,expires_at=:expires_at,
                published_at=:published_at WHERE id=:id")->execute([...$data,'id'=>$id]);
            jsonResponse(['ok'=>true,'msg'=>'Aviso actualizado.','id'=>$id]);
        } else {
            $db->prepare("INSERT INTO notices (icon,title,description,link_url,type,is_active,expires_at,published_at)
                VALUES (:icon,:title,:description,:link_url,:type,:is_active,:expires_at,:published_at)")
                ->execute($data);
            jsonResponse(['ok'=>true,'msg'=>'Aviso criado.','id'=>(int)$db->lastInsertId()]);
        }
    }
    if ($action === 'toggle') {
        $id=(int)($_POST['id']??0);$val=(int)(bool)($_POST['value']??0);
        $db->prepare("UPDATE notices SET is_active=? WHERE id=?")->execute([$val,$id]);
        jsonResponse(['ok'=>true]);
    }
    if ($action === 'delete') {
        $db->prepare("DELETE FROM notices WHERE id=?")->execute([(int)($_POST['id']??0)]);
        jsonResponse(['ok'=>true,'msg'=>'Aviso eliminado.']);
    }
    jsonResponse(['ok'=>false,'msg'=>'Acção inválida'],400);
}

// ─── LOAD ─────────────────────────────────────────────────────
$filter = sanitize($_GET['filter'] ?? 'all');
$search = sanitize($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['page']??1));
$perPage= 12;
$where  = ['1=1']; $params = [];
if ($filter === 'active')   { $where[] = 'is_active=1'; }
if ($filter === 'inactive') { $where[] = 'is_active=0'; }
if ($filter !== 'all' && $filter !== 'active' && $filter !== 'inactive') {
    $where[] = 'type=:type'; $params[':type'] = $filter;
}
if ($search) { $where[]='(title LIKE :q OR description LIKE :q)'; $params[':q']="%$search%"; }
$whereSql = implode(' AND ', $where);
$cStmt = $db->prepare("SELECT COUNT(*) FROM notices WHERE $whereSql");
$cStmt->execute($params); $total=(int)$cStmt->fetchColumn();
$pg = paginate($total,$page,$perPage);
$dStmt = $db->prepare("SELECT * FROM notices WHERE $whereSql ORDER BY sort_order ASC, published_at DESC LIMIT :l OFFSET :o");
$dStmt->bindValue(':l',$pg['per_page'],PDO::PARAM_INT);$dStmt->bindValue(':o',$pg['offset'],PDO::PARAM_INT);
foreach($params as $k=>$v) $dStmt->bindValue($k,$v);
$dStmt->execute(); $rows = $dStmt->fetchAll();
$totals = $db->query("SELECT COUNT(*) as t, SUM(is_active) as a FROM notices")->fetch();
$typeCounts = $db->query("SELECT type,COUNT(*) FROM notices GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PetroPub Admin — Avisos</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
  <style>
  /* ── reuse same design tokens and sidebar ── */
  :root{--cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);--gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;--gd-bg:rgba(201,168,76,.11);--cream:#FAF7F2;--warm:#FEF9F3;--bdr:rgba(107,16,32,.10);--bdr2:rgba(107,16,32,.06);--tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;--ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);--ok-bdr:rgba(45,122,79,.25);--wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);--er:#C53030;--er-bg:rgba(197,48,48,.10);--inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);--sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);--sh2:0 8px 32px rgba(107,16,32,.13);--sh3:0 24px 64px rgba(107,16,32,.18);--r1:7px;--r2:11px;--r3:15px;--r4:20px;--sb:260px;--sb-ico:66px;--sb-mob:248px;--hdr:62px;--t:.2s cubic-bezier(.4,0,.2,1)}
  *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
  body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
  ::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
  input,select,button,textarea{font-family:inherit}.app{display:flex;min-height:100vh}
  .sidebar{width:var(--sb);flex-shrink:0;background:var(--cr-dk);height:100vh;position:sticky;top:0;overflow-y:auto;overflow-x:hidden;display:flex;flex-direction:column;transition:width var(--t),transform var(--t);z-index:200}
  .sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12)}
  .sb-head{padding:22px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:flex-start;justify-content:space-between;min-height:70px;flex-shrink:0}
  .sb-logo{font-family:'Arial',serif;font-size:21px;font-weight:900;color:#fff}.sb-logo span{color:var(--gd-lt)}
  .sb-role{font-size:10px;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.6px;margin-top:4px}
  .sb-tog{display:none;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);color:#fff;width:30px;height:30px;border-radius:var(--r1);font-size:14px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0;transition:background var(--t);margin-top:2px}.sb-tog:hover{background:rgba(255,255,255,.22)}
  .sb-user{padding:13px 20px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0}
  .ava{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;border:2px solid rgba(255,255,255,.20);flex-shrink:0;background:#1A3A4A}
  .sb-un{font-size:13px;font-weight:600;color:#fff}.sb-ue{font-size:11px;color:rgba(255,255,255,.38)}
  .nav-s{padding:12px 10px 2px}.nav-l{font-size:10px;font-weight:700;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:1.5px;padding:0 9px;margin-bottom:4px}
  .nav-i{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:10px;cursor:pointer;color:rgba(255,255,255,.58);font-size:13px;font-weight:500;margin-bottom:2px;white-space:nowrap;overflow:hidden;position:relative;transition:all .16s}
  .nav-i:hover{background:rgba(255,255,255,.08);color:#fff}.nav-i.act{background:rgba(255,255,255,.14);color:#fff}
  .nav-i.act::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gd);border-radius:0 2px 2px 0}
  .ni{font-size:15px;width:18px;text-align:center;flex-shrink:0}.nb{margin-left:auto;background:#E53E3E;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px}
  .sb-foot{margin-top:auto;padding:10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0}
  .sidebar.collapsed{width:var(--sb-ico)}.sidebar.collapsed .sb-logo,.sidebar.collapsed .sb-role,.sidebar.collapsed .sb-un,.sidebar.collapsed .sb-ue,.sidebar.collapsed .nav-l,.sidebar.collapsed .nav-i span:not(.ni),.sidebar.collapsed .nb{opacity:0;max-width:0;pointer-events:none;overflow:hidden}
  .sidebar.collapsed .nav-i{justify-content:center;padding:9px}.sidebar.collapsed .sb-user{justify-content:center;padding:12px}.sidebar.collapsed .sb-head{justify-content:center;padding:16px 10px}
  .sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:190;backdrop-filter:blur(3px);opacity:0;transition:opacity .28s}.sb-ov.open{opacity:1}
  .main{flex:1;min-width:0;display:flex;flex-direction:column}
  .topbar{background:#fff;border-bottom:1px solid var(--bdr);padding:0 clamp(14px,3vw,30px);height:var(--hdr);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:var(--sh0);gap:10px;flex-shrink:0}
  .tb-l{display:flex;align-items:center;gap:10px;min-width:0;flex:1}.tb-ham{display:none;width:36px;height:36px;border-radius:var(--r1);background:var(--cr-xl);border:1px solid var(--bdr);color:var(--cr);font-size:18px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0}
  .tb-bc{font-size:11px;color:var(--tx-l);margin-bottom:2px}.tb-bc a{color:var(--cr);font-weight:600;cursor:pointer;text-decoration:none}
  .tb-title{font-family:'Arial',serif;font-size:clamp(15px,2vw,18px);font-weight:700;color:var(--cr-dk)}
  .tb-r{display:flex;align-items:center;gap:8px;flex-shrink:0}
  .btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--r2);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
  .btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk)}
  .btn-gh{background:var(--cream);color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
  .btn-er{background:var(--er);color:#fff}.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}
  .btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}
  .page-wrap{flex:1;overflow-y:auto;padding:clamp(16px,3vw,28px) clamp(14px,3vw,32px)}
  .flash{padding:12px 18px;border-radius:var(--r2);margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
  .flash-ok{background:var(--ok-bg);color:var(--ok);border:1px solid rgba(45,122,79,.2)}.flash-er{background:var(--er-bg);color:var(--er);border:1px solid rgba(197,48,48,.2)}
  /* STATS */
  .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:clamp(10px,1.5vw,12px);margin-bottom:clamp(18px,2.5vw,22px)}
  .sc{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(13px,2vw,16px);box-shadow:var(--sh0);transition:box-shadow var(--t);cursor:pointer}
  .sc:hover,.sc.on{box-shadow:var(--sh1)}.sc.on{border-color:var(--cr);box-shadow:0 0 0 2px rgba(107,16,32,.12)}
  .sc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
  .sc-ico{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px}
  .sc-num{font-family:'Arial',serif;font-size:clamp(20px,3vw,26px);font-weight:700;color:var(--tx);line-height:1}
  .sc-lbl{font-size:11px;color:var(--tx-l);margin-top:3px}
  /* TOOLBAR */
  .toolbar{background:#fff;border:1px solid var(--bdr);border-radius:var(--r3);padding:clamp(12px,2vw,16px) clamp(14px,2vw,20px);margin-bottom:clamp(14px,2vw,18px);display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--sh0)}
  .search-wrap{flex:1;min-width:180px;position:relative}.search-wrap input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}.search-wrap input:focus{border-color:var(--cr);background:#fff}.search-wrap input::placeholder{color:var(--tx-l)}
  .s-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
  .f-sel{padding:9px 28px 9px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer}
  .f-sel:focus{border-color:var(--cr)}
  /* NOTICES LIST */
  .notices-list{display:flex;flex-direction:column;gap:10px;margin-bottom:clamp(20px,3vw,28px)}
  .notice-row{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(14px,2vw,18px) clamp(16px,2vw,22px);display:flex;align-items:center;gap:clamp(12px,2vw,18px);box-shadow:var(--sh0);transition:all var(--t);animation:fadeUp .35s ease both}
  .notice-row:hover{box-shadow:var(--sh1);border-color:var(--cr-bdr)}
  .notice-row.inactive{opacity:.6}
  .nr-ico{font-size:clamp(22px,3vw,28px);flex-shrink:0;width:38px;text-align:center}
  .nr-body{flex:1;min-width:0}
  .nr-title{font-size:clamp(13px,1.5vw,15px);font-weight:700;color:var(--tx);margin-bottom:3px}
  .nr-desc{font-size:12px;color:var(--tx-l);line-height:1.5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .nr-meta{display:flex;align-items:center;gap:10px;margin-top:6px;flex-wrap:wrap}
  .nr-meta span{font-size:11px;color:var(--tx-l)}
  .nr-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
  .type-pill{font-size:10px;font-weight:700;padding:2px 8px;border-radius:100px}
  .tp-info{background:var(--inf-bg);color:var(--inf)}.tp-success{background:var(--ok-bg);color:var(--ok)}
  .tp-warning{background:var(--wn-bg);color:var(--wn)}.tp-update{background:var(--gd-bg);color:var(--gd-dk)}
  .toggle{position:relative;width:34px;height:18px;cursor:pointer;flex-shrink:0}
  .toggle input{opacity:0;width:0;height:0;position:absolute}
  .toggle-track{position:absolute;inset:0;background:var(--bdr);border-radius:100px;transition:background var(--t)}
  .toggle input:checked + .toggle-track{background:var(--ok)}
  .toggle-thumb{position:absolute;top:2px;left:2px;width:14px;height:14px;background:#fff;border-radius:50%;transition:transform var(--t);box-shadow:0 1px 4px rgba(0,0,0,.2)}
  .toggle input:checked ~ .toggle-thumb{transform:translateX(16px)}
  .nr-actions{display:flex;gap:6px}
  /* PAGINATION */
  .pagination{display:flex;align-items:center;justify-content:center;gap:5px;flex-wrap:wrap}
  .pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t);text-decoration:none}
  .pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}.pg-btn.disabled{opacity:.3;pointer-events:none}
  /* MODAL */
  .overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;overflow-y:auto}.overlay.open{display:flex}
  .modal{background:#fff;border-radius:var(--r4);width:100%;max-width:540px;box-shadow:var(--sh3);animation:modalIn .28s cubic-bezier(.22,1,.36,1);max-height:calc(100vh - 40px);overflow-y:auto}
  @keyframes modalIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
  .m-hd{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px);position:relative;border-radius:var(--r4) var(--r4) 0 0}
  .mh-cr{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt))}.mh-er{background:linear-gradient(135deg,#6B0808,var(--er))}
  .m-hd h3{font-family:'Arial',serif;font-size:18px;font-weight:700;color:#fff}.m-hd p{font-size:12px;color:rgba(255,255,255,.58);margin-top:4px}
  .m-close{position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center}.m-close:hover{background:rgba(255,255,255,.28)}
  .m-body{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px)}.m-foot{padding:clamp(12px,1.5vw,14px) clamp(18px,2vw,24px);border-top:1px solid var(--bdr);background:var(--warm);border-radius:0 0 var(--r4) var(--r4);display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
  .f-group{margin-bottom:14px}.f-lbl{display:block;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}
  .f-input{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}.f-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.f-input::placeholder{color:var(--tx-l)}
  .f-sel-f{width:100%;padding:10px 28px 10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 10px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}.f-sel-f:focus{border-color:var(--cr)}
  .f-ta{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;resize:vertical;min-height:80px;line-height:1.6;transition:all var(--t);font-family:inherit}.f-ta:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.f-ta::placeholder{color:var(--tx-l)}
  .f-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .f-check-row{display:flex;align-items:center;gap:8px}.f-cb{width:15px;height:15px;border-radius:4px;border:1.5px solid var(--bdr);cursor:pointer;accent-color:var(--cr)}.f-cb-lbl{font-size:13px;color:var(--tx-m);cursor:pointer}
  /* EMPTY */
  .empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr)}.empty-ico{font-size:48px;opacity:.18;margin-bottom:14px}.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}
  .toast{position:fixed;bottom:clamp(14px,3vw,22px);right:clamp(14px,3vw,22px);z-index:9999;transform:translateY(30px);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;line-height:1.4;border:1px solid rgba(201,168,76,.2)}.toast.show{opacity:1;transform:translateY(0)}
  .t-ok{background:var(--ok)}.t-er{background:var(--er)}.t-def{background:var(--cr-dk)}
  @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
  @media(max-width:1199px){:root{--sb:var(--sb-ico)}.sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nav-i span:not(.ni),.sidebar .nb{opacity:0;max-width:0;pointer-events:none;overflow:hidden}.sidebar .nav-i{justify-content:center;padding:9px}.sidebar .sb-user{justify-content:center;padding:12px}.sidebar .sb-head{justify-content:center;padding:16px 10px}.sb-tog{display:none!important}.stats-row{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:767px){.sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sb-mob)!important;height:100vh;z-index:300;transform:translateX(-100%);box-shadow:var(--sh3)}.sidebar.open{transform:translateX(0)}.sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nav-i span:not(.ni),.sidebar .nb{opacity:1!important;max-width:unset!important;pointer-events:auto!important}.sidebar .nav-i{justify-content:flex-start;padding:8px 10px;gap:9px}.sidebar .sb-user{justify-content:flex-start;padding:13px 20px}.sidebar .sb-head{justify-content:space-between;padding:18px 20px}.sb-ov{display:block}.sb-tog{display:flex!important}.tb-ham{display:flex}.topbar{padding:0 14px;height:56px}.stats-row{grid-template-columns:1fr 1fr}.f-row{grid-template-columns:1fr}.nr-desc{display:none}.m-foot{flex-wrap:wrap}.m-foot .btn{flex:1;justify-content:center}}
</style>
</head>
<body>
<div class="toast t-def" id="toast"></div>
<div class="sb-ov" id="sb-ov" onclick="closeSB()"></div>

<!-- MODAL FORM -->
<div class="overlay" id="modal-form" onclick="if(event.target.id==='modal-form')closeForm()">
  <div class="modal">
    <div class="m-hd mh-cr">
      <h3 id="form-title">📢 Novo Aviso</h3>
      <p id="form-sub">Preencha os campos obrigatórios</p>
      <button class="m-close" onclick="closeForm()">✕</button>
    </div>
    <form id="notice-form">
      <div class="m-body">
        <input type="hidden" id="f-id" name="id" value="0">
        <input type="hidden" name="action" value="save">
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Ícone (emoji)</label>
            <input class="f-input" id="f-icon" name="icon" maxlength="4" placeholder="📢">
          </div>
          <div class="f-group">
            <label class="f-lbl">Tipo</label>
            <select class="f-sel-f" id="f-type" name="type">
              <option value="info"><i class="fa fa-info"></i> Informação</option>
              <option value="success"><i class="fa fa-check"></i> Novidade</option>
              <option value="warning"><i class="fa fa-alert"></i> Aviso</option>
              <option value="update"> Actualização</option>
            </select>
          </div>
        </div>
        <div class="f-group">
          <label class="f-lbl">Título <span style="color:var(--cr)">*</span></label>
          <input class="f-input" id="f-title" name="title" placeholder="Título do aviso" required>
        </div>
        <div class="f-group">
          <label class="f-lbl">Descrição</label>
          <textarea class="f-ta" id="f-desc" name="description" placeholder="Descrição detalhada…"></textarea>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Data de publicação</label>
            <input class="f-input" type="datetime-local" id="f-pub" name="published_at">
          </div>
          <div class="f-group">
            <label class="f-lbl">Expira em (opcional)</label>
            <input class="f-input" type="datetime-local" id="f-exp" name="expires_at">
          </div>
        </div>
        <div class="f-group">
          <label class="f-lbl">Link (opcional)</label>
          <input class="f-input" type="url" id="f-link" name="link_url" placeholder="https://…">
        </div>
        <label class="f-check-row">
          <input type="checkbox" class="f-cb" id="f-active" name="is_active" value="1" checked>
          <span class="f-cb-lbl"><i class="fa fa-check"></i> Publicar imediatamente</span>
        </label>
      </div>
      <div class="m-foot">
        <button type="button" class="btn btn-gh" onclick="closeForm()">Cancelar</button>
        <button type="submit" class="btn btn-cr" id="form-btn"><i class="fa fa-save"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL DELETE -->
<div class="overlay" id="modal-del" onclick="if(event.target.id==='modal-del')closeDelete()">
  <div class="modal" style="max-width:400px">
    <div class="m-hd mh-er"><h3>🗑️ Eliminar aviso</h3><p>Esta acção não pode ser desfeita</p><button class="m-close" onclick="closeDelete()">✕</button></div>
    <div class="m-body" style="text-align:center;padding-bottom:20px">
      <div style="font-size:48px;margin:20px 0 12px;opacity:.7">🗑️</div>
      <div style="font-family:'Arial',serif;font-size:18px;font-weight:700;margin-bottom:8px">Confirmar eliminação</div>
      <div style="font-size:13px;color:var(--tx-l)" id="del-title">—</div>
    </div>
    <div class="m-foot"><button class="btn btn-gh" onclick="closeDelete()">Cancelar</button><button class="btn btn-er" id="del-btn" onclick="confirmDelete()">🗑️ Eliminar</button></div>
  </div>
</div>

<div class="app">
<aside class="sidebar" id="sidebar">
  <div class="sb-head"><div><div class="sb-logo">PETRO<span>PUB</span></div><div class="sb-role">Administração</div></div><button class="sb-tog" id="sb-close" onclick="closeSB()">✕</button><button class="sb-tog" id="sb-col" onclick="toggleCol()">◀</button></div>
  <div class="sb-user"><div class="ava">AD</div><div><div class="sb-un">Ana Domingos</div><div class="sb-ue">admin@petropub.ao</div></div></div>
  <div class="nav-s">
    <div class="nav-l">Portal</div>
    <div class="nav-i" onclick="location.href='admin-seccoes.php'"><span class="ni">🗂️</span><span>Secções</span></div>
    <div class="nav-i" onclick="location.href='admin-oportunidades.php'"><span class="ni">⛽</span><span>Oportunidades</span></div>
    <div class="nav-i act"><span class="ni">📢</span><span>Avisos</span></div>
    <div class="nav-i" onclick="location.href='admin-destaques.php'"><span class="ni">🔥</span><span>Destaques</span></div>
  </div>
  <div class="sb-foot"><div class="nav-i"><span class="ni">🚪</span><span>Sair</span></div></div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="tb-l">
      <button class="tb-ham" onclick="openSB()">☰</button>
      <div><div class="tb-bc"><a href="admin-seccoes.php">Secções</a> / Avisos</div><div class="tb-title">📢 Avisos & Novidades</div></div>
    </div>
    <div class="tb-r">
      <button class="btn btn-cr" onclick="openForm()">+ Novo aviso</button>
      <div class="ava" style="width:36px;height:36px;font-size:12px">AD</div>
    </div>
  </div>

  <div class="page-wrap">
    <?php if ($flash): ?>
    <div class="flash flash-<?= $flash['type']==='ok'?'ok':'er' ?>"><?= $flash['type']==='ok'?'✅':'❌' ?> <?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="stats-row">
      <?php $fcs=[['f'=>'all','ico'=>'📢','bg'=>'background:var(--cr-xl)','lbl'=>'Todos','cnt'=>$totals['t']??0],['f'=>'active','ico'=>'<i class="fa fa-check"></i>','bg'=>'background:var(--ok-bg)','lbl'=>'Activos','cnt'=>$totals['a']??0],['f'=>'inactive','ico'=>'🔕','bg'=>'background:var(--er-bg)','lbl'=>'Inactivos','cnt'=>($totals['t']??0)-($totals['a']??0)],['f'=>'info','ico'=>'<i class="fa fa-info"></i>','bg'=>'background:var(--inf-bg)','lbl'=>'Informação','cnt'=>$typeCounts['info']??0]];
      foreach($fcs as $fc): ?>
      <a href="?filter=<?= urlencode($fc['f']) ?>&q=<?= urlencode($search) ?>" style="text-decoration:none">
        <div class="sc <?= $filter===$fc['f']?'on':'' ?>">
          <div class="sc-top"><div class="sc-ico" style="<?= $fc['bg'] ?>"><?= $fc['ico'] ?></div></div>
          <div class="sc-num"><?= $fc['cnt'] ?></div><div class="sc-lbl"><?= $fc['lbl'] ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <form method="GET">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
      <div class="toolbar">
        <div class="search-wrap"><span class="s-ico"><i class="fa fa-search"></i></span><input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Pesquisar avisos…"></div>
        <select class="f-sel" name="filter" onchange="this.form.submit()">
          <option value="all" <?= $filter==='all'?'selected':'' ?>>Todos</option>
          <option value="active" <?= $filter==='active'?'selected':'' ?>>Activos</option>
          <option value="inactive" <?= $filter==='inactive'?'selected':'' ?>>Inactivos</option>
          <option value="info" <?= $filter==='info'?'selected':'' ?>>Informação</option>
          <option value="success" <?= $filter==='success'?'selected':'' ?>>Novidade</option>
          <option value="warning" <?= $filter==='warning'?'selected':'' ?>>Aviso</option>
          <option value="update" <?= $filter==='update'?'selected':'' ?>>Actualização</option>
        </select>
        <button type="submit" class="btn btn-cr btn-sm"><i class="fa fa-search"></i> Filtrar</button>
        <?php if ($search||$filter!=='all'): ?><a href="?" class="btn btn-gh btn-sm">✕ Limpar</a><?php endif; ?>
        <span style="font-size:13px;color:var(--tx-l);margin-left:auto"><?= $total ?> resultado<?= $total!=1?'s':'' ?></span>
      </div>
    </form>

    <?php if(empty($rows)): ?>
    <div class="empty"><div class="empty-ico">📢</div><div class="empty-title">Nenhum aviso encontrado</div><p style="font-size:14px;color:var(--tx-l);margin:6px 0 18px"><?= $search?'Tente outro termo.':'Crie o primeiro aviso.' ?></p><button class="btn btn-cr" onclick="openForm()">+ Criar aviso</button></div>
    <?php else: ?>
    <div class="notices-list">
      <?php foreach($rows as $i=>$row):
        $typeCls = match($row['type']){'success'=>'tp-success','warning'=>'tp-warning','update'=>'tp-update',default=>'tp-info'};
        $typeLabel = match($row['type']){'success'=>'Novidade','warning'=>'Aviso','update'=>'Actualização',default=>'Informação'};
        $rowJson = htmlspecialchars(json_encode($row,JSON_UNESCAPED_UNICODE),ENT_QUOTES);
      ?>
      <div class="notice-row <?= !$row['is_active']?'inactive':'' ?>" id="row-<?= $row['id'] ?>" style="animation-delay:<?= $i*.04 ?>s">
        <div class="nr-ico"><?= htmlspecialchars($row['icon']) ?></div>
        <div class="nr-body">
          <div class="nr-title"><?= htmlspecialchars($row['title']) ?></div>
          <div class="nr-desc"><?= htmlspecialchars($row['description']??'') ?></div>
          <div class="nr-meta">
            <span class="type-pill <?= $typeCls ?>"><?= $typeLabel ?></span>
            <span>📅 <?= date('d/m/Y H:i', strtotime($row['published_at'])) ?></span>
            <?php if($row['expires_at']): ?><span>⏳ Expira: <?= date('d/m/Y', strtotime($row['expires_at'])) ?></span><?php endif; ?>
            <?php if($row['link_url']): ?><a href="<?= htmlspecialchars($row['link_url']) ?>" target="_blank" style="color:var(--cr);font-size:11px;font-weight:600">🔗 Link</a><?php endif; ?>
          </div>
        </div>
        <div class="nr-right">
          <label class="toggle" title="Activo/Inactivo">
            <input type="checkbox" <?= $row['is_active']?'checked':'' ?> onchange="toggleNotice(<?= $row['id'] ?>,this.checked)">
            <div class="toggle-track"></div><div class="toggle-thumb"></div>
          </label>
          <div class="nr-actions">
            <button class="btn btn-gh btn-xs" onclick="openEdit(<?= $rowJson ?>)"><i class="fa fa-pencil"></i></button>
            <button class="btn btn-er btn-xs" onclick="openDelete(<?= $row['id'] ?>,'<?=$row['title']?>' )"><i class="fa fa-trash"></i></button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if($pg['pages']>1): ?>
    <div class="pagination">
      <a href="?filter=<?= urlencode($filter) ?>&q=<?= urlencode($search) ?>&page=<?= $pg['page']-1 ?>" class="pg-btn <?= !$pg['has_prev']?'disabled':'' ?>">‹</a>
      <?php for($p=1;$p<=$pg['pages'];$p++): ?>
        <?php if($p===1||$p===$pg['pages']||abs($p-$pg['page'])<=1): ?><a href="?filter=<?= urlencode($filter) ?>&q=<?= urlencode($search) ?>&page=<?= $p ?>" class="pg-btn <?= $p===$pg['page']?'on':'' ?>"><?= $p ?></a>
        <?php elseif(abs($p-$pg['page'])===2): ?><span style="color:var(--tx-l);padding:0 3px">…</span><?php endif; ?>
      <?php endfor; ?>
      <a href="?filter=<?= urlencode($filter) ?>&q=<?= urlencode($search) ?>&page=<?= $pg['page']+1 ?>" class="pg-btn <?= !$pg['has_next']?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</div>

<script>
const sidebar=document.getElementById('sidebar'),sbOv=document.getElementById('sb-ov'),sbClose=document.getElementById('sb-close'),sbCol=document.getElementById('sb-col');
let collapsed=false;
function checkBP(){const w=window.innerWidth;if(w<768){sbClose.style.display=sidebar.classList.contains('open')?'flex':'none';sbCol.style.display='none';sidebar.classList.remove('collapsed');}else if(w<1200){sbClose.style.display='none';sbCol.style.display='none';}else{sbClose.style.display='none';sbCol.style.display='flex';sbCol.textContent=collapsed?'▶':'◀';}}
function openSB(){sidebar.classList.add('open');sbOv.style.display='block';setTimeout(()=>sbOv.classList.add('open'),10);sbClose.style.display='flex';document.body.style.overflow='hidden';}
function closeSB(){sidebar.classList.remove('open');sbOv.classList.remove('open');setTimeout(()=>sbOv.style.display='none',300);sbClose.style.display='none';document.body.style.overflow='';}
function toggleCol(){collapsed=!collapsed;sidebar.classList.toggle('collapsed',collapsed);sbCol.textContent=collapsed?'▶':'◀';}
window.addEventListener('resize',checkBP);checkBP();

function openForm(){setForm({});document.getElementById('form-title').textContent='📢 Novo Aviso';document.getElementById('form-sub').textContent='Preencha os campos';document.getElementById('form-btn').textContent='💾 Criar';document.getElementById('modal-form').classList.add('open');document.body.style.overflow='hidden';}
function openEdit(row){setForm(row);document.getElementById('form-title').textContent='✏️ Editar Aviso';document.getElementById('form-sub').textContent='#'+row.id;document.getElementById('form-btn').textContent='💾 Guardar';document.getElementById('modal-form').classList.add('open');document.body.style.overflow='hidden';}
function setForm(r){
  document.getElementById('f-id').value    = r.id||0;
  document.getElementById('f-icon').value  = r.icon||'📢';
  document.getElementById('f-type').value  = r.type||'info';
  document.getElementById('f-title').value = r.title||'';
  document.getElementById('f-desc').value  = r.description||'';
  document.getElementById('f-link').value  = r.link_url||'';
  document.getElementById('f-pub').value   = (r.published_at||'').replace(' ','T').substring(0,16);
  document.getElementById('f-exp').value   = (r.expires_at||'').replace(' ','T').substring(0,16);
  document.getElementById('f-active').checked = r.id ? !!+r.is_active : true;
}
function closeForm(){document.getElementById('modal-form').classList.remove('open');document.body.style.overflow='';}
document.getElementById('notice-form').addEventListener('submit',function(e){
  e.preventDefault();
  const btn=document.getElementById('form-btn');btn.disabled=true;btn.textContent='⌛ A guardar…';
  const fd=new FormData(this);
  if(!this.querySelector('#f-active').checked) fd.set('is_active','0');
  fetch('?api=1',{method:'POST',body:new URLSearchParams(fd)}).then(r=>r.json()).then(d=>{
    btn.disabled=false;btn.textContent='💾 Guardar';
    showToast(d.msg||(d.ok?'✅ Guardado!':'❌ Erro'),d.ok?'t-ok':'t-er');
    if(d.ok){closeForm();setTimeout(()=>location.reload(),700);}
  }).catch(()=>{btn.disabled=false;showToast('❌ Erro de rede','t-er');});
});

let delId=null;
function openDelete(id,title){delId=id;document.getElementById('del-title').textContent='"'+title+'" será eliminado.';document.getElementById('modal-del').classList.add('open');document.body.style.overflow='hidden';}
function closeDelete(){document.getElementById('modal-del').classList.remove('open');document.body.style.overflow='';}
function confirmDelete(){
  if(!delId)return;
  const btn=document.getElementById('del-btn');btn.disabled=true;btn.textContent='⌛…';
  fetch('?api=1',{method:'POST',body:new URLSearchParams({action:'delete',id:delId})}).then(r=>r.json()).then(d=>{
    showToast(d.msg||'✅ Eliminado!',d.ok?'t-ok':'t-er');
    closeDelete();
    if(d.ok){const row=document.getElementById('row-'+delId);if(row){row.style.transition='opacity .4s,transform .4s';row.style.opacity='0';row.style.transform='translateX(20px)';setTimeout(()=>row.remove(),400);}}
    btn.disabled=false;btn.textContent='🗑️ Eliminar';delId=null;
  });
}
function toggleNotice(id,val){
  fetch('?api=1',{method:'POST',body:new URLSearchParams({action:'toggle',id,value:val?1:0})}).then(r=>r.json()).then(d=>{
    showToast(d.ok?'✅ Actualizado!':'❌ Erro',d.ok?'t-ok':'t-er');
    if(d.ok){const row=document.getElementById('row-'+id);if(row)row.classList.toggle('inactive',!val);}
  });
}
function showToast(msg,cls='t-def'){const t=document.getElementById('toast');t.textContent=msg;t.className='toast '+cls;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
</script>
</body>
</html>
