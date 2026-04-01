<?php
require_once 'includes.php';

$type   = sanitize($_GET['type']   ?? '');
$search = sanitize($_GET['q']      ?? '');
$sort   = sanitize($_GET['sort']   ?? 'recent');
$page   = max(1,(int)($_GET['page']??1));
$perPage= 10;
$where  = ['is_active=1','(expires_at IS NULL OR expires_at > NOW())']; $params=[];
if ($type){ $where[]='type=:type'; $params[':type']=$type; }
if ($search){ $where[]='(title LIKE :q OR description LIKE :q)'; $params[':q']="%$search%"; }
$orderMap=['recent'=>'published_at DESC','pinned'=>'is_pinned DESC, published_at DESC','title'=>'title ASC'];
$oSql=$orderMap[$sort]??$orderMap['recent'];
$wSql=implode(' AND ',$where);
$cSt=$db->prepare("SELECT COUNT(*) FROM notices WHERE $wSql"); $cSt->execute($params); $total=(int)$cSt->fetchColumn();
$pg=paginate($total,$page,$perPage);
$dSt=$db->prepare("SELECT * FROM notices WHERE $wSql ORDER BY $oSql LIMIT :l OFFSET :o");
$dSt->bindValue(':l',$pg['per_page'],PDO::PARAM_INT); $dSt->bindValue(':o',$pg['offset'],PDO::PARAM_INT);
foreach($params as $k=>$v) $dSt->bindValue($k,$v); $dSt->execute(); $rows=$dSt->fetchAll();
$typeCounts=$db->query("SELECT type,COUNT(*) FROM notices WHERE is_active=1 GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);
function h(string $v): string { return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
function typeCls(string $t): string { return match($t){'success'=>'ok','warning'=>'wn','update'=>'gd',default=>'inf'}; }
function typeLabel(string $t): string { return match($t){'success'=>'Novidade','warning'=>'Aviso','update'=>'Actualização',default=>'ℹInformação'}; }
function buildUrl(array $ov=[]): string {
    global $type,$search,$sort,$page;
    $p=['type'=>$type,'q'=>$search,'sort'=>$sort,'page'=>$page];
    foreach($ov as $k=>$v) $p[$k]=$v;
    return '?'.http_build_query(array_filter($p,fn($v)=>$v!==''&&$v!==0&&$v!=='0'));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Avisos & Novidades</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<?= publicCss() ?>
<style>
:root{--cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);--gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;--gd-bg:rgba(201,168,76,.11);--cream:#FAF7F2;--warm:#FEF9F3;--bdr:rgba(107,16,32,.10);--tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;--ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);--ok-bdr:rgba(45,122,79,.25);--wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);--wn-bdr:rgba(196,122,26,.25);--er:#C53030;--er-bg:rgba(197,48,48,.10);--inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);--inf-bdr:rgba(26,92,138,.25);--sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);--sh2:0 8px 32px rgba(107,16,32,.13);--r1:7px;--r2:11px;--r3:15px;--r4:20px;--t:.22s cubic-bezier(.4,0,.2,1);--max:900px}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button{font-family:inherit}a{color:inherit;text-decoration:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--r2);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap}
.btn-cr{background:var(--cr);color:#fff}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-gh{background:#fff;color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-sm{padding:5px 12px;font-size:12px;border-radius:var(--r1)}
.nav{background:#fff;border-bottom:1px solid var(--bdr);position:sticky;top:0;z-index:200;box-shadow:var(--sh0)}
.nav-inner{display:flex;align-items:center;gap:12px;height:60px;max-width:1280px;margin:0 auto;padding:0 clamp(14px,4vw,40px)}
.nav-logo{font-family:'Arial',serif;font-weight:900;font-size:20px;color:var(--cr-dk)}.nav-logo span{color:var(--gd)}
.nav-links{display:flex;align-items:center;gap:0;margin-left:clamp(12px,2vw,24px);flex:1}
.nav-link{padding:0 clamp(10px,1.5vw,16px);height:60px;display:flex;align-items:center;font-size:13px;font-weight:600;color:var(--tx-l);cursor:pointer;border-bottom:2.5px solid transparent;transition:all var(--t);white-space:nowrap;text-decoration:none}
.nav-link:hover,.nav-link.on{color:var(--cr);border-bottom-color:var(--cr)}
.nav-r{display:flex;align-items:center;gap:8px;flex-shrink:0;margin-left:auto}
/* HERO */
.hero{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 50%,#1A3060 100%);padding:clamp(40px,7vw,70px) clamp(14px,4vw,40px);position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;width:380px;height:380px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-100px;right:-40px;pointer-events:none}
.hero-inner{max-width:1280px;margin:0 auto;position:relative;z-index:1}
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);padding:5px 14px;border-radius:100px;font-size:11px;font-weight:700;color:rgba(255,255,255,.80);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px}
.hero-title{font-family:'Arial',serif;font-size:clamp(24px,5vw,42px);font-weight:900;color:#fff;margin-bottom:8px}
.hero-title em{color:var(--gd-lt);font-style:normal}
.hero-sub{font-size:clamp(13px,1.5vw,15px);color:rgba(255,255,255,.65);max-width:480px;line-height:1.65;margin-bottom:22px}
.hero-search{display:flex;gap:8px;max-width:520px;flex-wrap:wrap}
.hs-wrap{flex:1;position:relative;min-width:240px}
.hs-input{width:100%;padding:12px 14px 12px 40px;border:none;border-radius:var(--r3);font-size:13px;color:var(--tx);background:rgba(255,255,255,.95);outline:none;box-shadow:0 6px 22px rgba(0,0,0,.22)}
.hs-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:15px;pointer-events:none}
/* FILTER BAR */
.filter-bar{background:#fff;border-bottom:1px solid var(--bdr);box-shadow:var(--sh0)}
.fb-inner{max-width:1280px;margin:0 auto;padding:0 clamp(14px,4vw,40px);display:flex;align-items:center;gap:8px;height:50px;overflow-x:auto;scrollbar-width:none}
.fb-inner::-webkit-scrollbar{display:none}
.fchip{padding:5px 13px;border-radius:100px;border:1.5px solid var(--bdr);background:#fff;font-size:12px;font-weight:600;color:var(--tx-l);white-space:nowrap;flex-shrink:0;transition:all var(--t);text-decoration:none}
.fchip:hover,.fchip.on{background:var(--cr);color:#fff;border-color:var(--cr)}
.fb-sort{margin-left:auto;flex-shrink:0;padding:5px 24px 5px 10px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:12px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 7px) center;appearance:none;outline:none;cursor:pointer}
/* CONTENT */
.content{max-width:1280px;margin:0 auto;padding:clamp(22px,4vw,36px) clamp(14px,4vw,40px);display:grid;grid-template-columns:1fr clamp(240px,30vw,300px);gap:clamp(20px,3vw,32px);align-items:start}
.main-col{min-width:0}
/* NOTICE CARD */
.notice-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(16px,2vw,22px) clamp(18px,2.5vw,26px);margin-bottom:clamp(10px,1.5vw,14px);display:flex;gap:14px;transition:all var(--t);animation:fadeUp .38s ease both;position:relative;overflow:hidden}
.notice-card:hover{box-shadow:var(--sh1);border-color:rgba(107,16,32,.18)}
.notice-card.pinned::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--gd)}
.nc-ico-wrap{font-size:clamp(24px,3.5vw,32px);flex-shrink:0;margin-top:2px}
.nc-body{flex:1;min-width:0}
.nc-header{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px;flex-wrap:wrap}
.nc-title{font-family:'Arial',serif;font-size:clamp(14px,1.8vw,17px);font-weight:700;color:var(--tx);line-height:1.35}
.nc-tag{font-size:10px;font-weight:700;padding:2px 9px;border-radius:100px;flex-shrink:0}
.t-ok{background:var(--ok-bg);color:var(--ok);border:1px solid var(--ok-bdr)}.t-wn{background:var(--wn-bg);color:var(--wn);border:1px solid var(--wn-bdr)}.t-gd{background:var(--gd-bg);color:var(--gd-dk)}.t-inf{background:var(--inf-bg);color:var(--inf);border:1px solid var(--inf-bdr)}
.nc-desc{font-size:13px;color:var(--tx-m);line-height:1.65;margin-bottom:10px}
.nc-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.nc-date{font-size:12px;color:var(--tx-l)}
/* sidebar */
.side-col{position:sticky;top:80px}
.side-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(14px,2vw,18px);margin-bottom:clamp(14px,2vw,18px);box-shadow:var(--sh0)}
.sc-title{font-family:'Arial',serif;font-size:14px;font-weight:700;color:var(--cr-dk);margin-bottom:12px;display:flex;align-items:center;gap:6px}
.stat-list{display:flex;flex-direction:column;gap:10px}
.stat-row{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid var(--bdr);font-size:13px}
.stat-row:last-child{border-bottom:none}
.stat-row .sl{color:var(--tx-l)}.stat-row .sv{font-weight:700;color:var(--tx)}
/* pagination */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:clamp(20px,3vw,28px);flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t);text-decoration:none}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}.pg-btn.disabled{opacity:.3;pointer-events:none}
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr)}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:12px}.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
@media(max-width:768px){.nav-links{display:none}.content{grid-template-columns:1fr}.side-col{position:static}}
/* footer */

      .footer {
        background: var(--cr-dk);
        color: rgba(255, 255, 255, 0.7);
        padding-left: 20px!important;
        padding: clamp(40px, 6vw, 64px) 0 clamp(20px, 3vw, 32px);
      }
      .footer-grid {
        display: grid;
        grid-template-columns: 2fr repeat(3, 1fr);
        gap: clamp(24px, 4vw, 48px);
        margin-bottom: clamp(28px, 4vw, 44px);
      }
      .ft-brand .fb-logo {
        font-family: "Arial", serif;
        font-size: 22px;
        font-weight: 900;
        color: #fff;
        margin-bottom: 10px;
      }
      .ft-brand .fb-logo span {
        color: var(--gd-lt);
      }
      .ft-brand p {
        font-size: 13px;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.55);
        margin-bottom: 14px;
      }
      .ft-col h4 {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: rgba(255, 255, 255, 0.35);
        margin-bottom: 14px;
      }
      .ft-link {
        display: block;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.58);
        margin-bottom: 10px;
        cursor: pointer;
        transition: color var(--t);
      }
      .ft-link:hover {
        color: var(--gd-lt);
      }
      .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: clamp(16px, 2.5vw, 22px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.35);
      }
      .footer-bottom a {
        color: rgba(255, 255, 255, 0.45);
      }
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media(max-width:768px){.nav-links{display:none}.hero-search{flex-direction:column}}
@media(max-width:540px){.opp-grid{grid-template-columns:1fr}}
      @media (max-width: 960px) {
        .footer-grid {
          grid-template-columns: 1fr 1fr;
        }
      }
      @media (max-width: 640px) {
        .footer-grid {
          grid-template-columns: 1fr;
        }
      }
      
</style>
</head>
<body>
<?= pubNav('noticias') ?>

<section class="hero">
  <div class="hero-inner">
    <div class="hero-eyebrow">📢 Portal PetroPub</div>
    <h1 class="hero-title">Avisos & <em>Novidades</em></h1>
    <p class="hero-sub">Actualizações, notícias e informações importantes sobre o portal e o sector académico angolano.</p>
    <form class="hero-search" method="GET" action="">
      <div class="hs-wrap">
        <span class="hs-ico"><i class="fa fa-search"></i></span>
        <input class="hs-input" name="q" type="text" value="<?= h($search) ?>" placeholder="Pesquisar avisos…">
        <?php if($type): ?><input type="hidden" name="type" value="<?= h($type) ?>"><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-cr"><i class="fa fa-search"></i></button>
      <?php if($search||$type): ?><a href="lista-avisos.php" class="btn btn-gh">✕</a><?php endif; ?>
    </form>
  </div>
</section>

<div class="filter-bar">
  <div class="fb-inner">
    <a href="<?= buildUrl(['type'=>'','page'=>1]) ?>" class="fchip <?= $type===''?'on':'' ?>">📦 Todos (<?= array_sum($typeCounts) ?>)</a>
    <?php foreach(['info'=>'<i class="fa fa-info"></i> Informação','success'=>'<i class="fa fa-check" style="color: yellowgreen;"></i> Novidade','warning'=>'<i class="fa fa-warning" style="color: orange;"></i> Aviso','update'=>'<i class="fa fa-refresh"></i> Actualização'] as $t=>$l): ?>
    <a href="<?= buildUrl(['type'=>$t,'page'=>1]) ?>" class="fchip <?= $type===$t?'on':'' ?>"><?= $l ?> (<?= $typeCounts[$t]??0 ?>)</a>
    <?php endforeach; ?>
    <form method="GET" action="" style="margin-left:auto;flex-shrink:0">
      <?php if($type): ?><input type="hidden" name="type" value="<?= h($type) ?>"><?php endif; ?>
      <?php if($search): ?><input type="hidden" name="q" value="<?= h($search) ?>"><?php endif; ?>
      <select class="fb-sort" name="sort" onchange="this.form.submit()">
        <option value="recent" <?= $sort==='recent'?'selected':'' ?>>Mais recentes</option>
        <option value="pinned" <?= $sort==='pinned'?'selected':'' ?>>Fixados primeiro</option>
        <option value="title" <?= $sort==='title'?'selected':'' ?>>A→Z</option>
      </select>
    </form>
  </div>
</div>

<div class="content">
  <div class="main-col">
    <div style="font-size:13px;color:var(--tx-l);margin-bottom:16px"><strong style="color:var(--tx)"><?= $total ?></strong> aviso<?= $total!=1?'s':'' ?> encontrado<?= $total!=1?'s':'' ?></div>
    <?php if(empty($rows)): ?>
    <div class="empty"><div class="empty-ico">📢</div><div class="empty-title">Nenhum aviso encontrado</div><p style="font-size:14px;color:var(--tx-l);margin-top:6px">Tente outros filtros.</p></div>
    <?php else: ?>
    <?php foreach($rows as $i=>$r):
      $tcls = typeCls($r['type']);
      $tlbl = typeLabel($r['type']);
    ?>
    <div class="notice-card <?= $r['is_pinned']?'pinned':'' ?>" style="animation-delay:<?= $i*.05 ?>s">
      <div class="nc-ico-wrap"><?= h($r['icon']) ?></div>
      <div class="nc-body">
        <div class="nc-header">
          <div class="nc-title"><?= h($r['title']) ?></div>
          <span class="nc-tag t-<?= $tcls ?>"><?= $tlbl ?></span>
        </div>
        <?php if($r['description']): ?><div class="nc-desc"><?= h($r['description']) ?></div><?php endif; ?>
        <div class="nc-meta">
          <span class="nc-date"><i class="fa fa-calendar"></i> <?= date('d/m/Y H:i',strtotime($r['published_at'])) ?></span>
          <?php if($r['is_pinned']): ?><span style="font-size:11px;font-weight:700;color:var(--gd-dk);background:var(--gd-bg);padding:2px 8px;border-radius:100px"><i class="fa fa-pin"></i> Fixado</span><?php endif; ?>
          <?php if($r['expires_at']): ?><span style="font-size:11px;color:var(--wn)">⏳ Expira: <?= date('d/m/Y',strtotime($r['expires_at'])) ?></span><?php endif; ?>
          <?php if($r['link_url']): ?><a href="<?= h($r['link_url']) ?>" target="_blank" style="font-size:12px;color:var(--cr);font-weight:600"><i class="fa fa-link"></i> Saber mais</a><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?= paginationHtml($pg,buildUrl(['page'=>'__PAGE__'])) ?>
    <?php endif; ?>
  </div>

  <div class="side-col">
    <div class="side-card">
      <div class="sc-title"> Resumo</div>
      <div class="stat-list">
        <div class="stat-row"><span class="sl">Total de avisos</span><span class="sv"><?= array_sum($typeCounts) ?></span></div>
        <div class="stat-row"><span class="sl"><i class="fa fa-check"></i> Novidades</span><span class="sv"><?= $typeCounts['success']??0 ?></span></div>
        <div class="stat-row"><span class="sl"><i class="fa fa-refresh"></i> Actualizações</span><span class="sv"><?= $typeCounts['update']??0 ?></span></div>
        <div class="stat-row"><span class="sl"><i class="fa fa-warning"></i> Avisos</span><span class="sv"><?= $typeCounts['warning']??0 ?></span></div>
        <div class="stat-row"><span class="sl"><i class="fa fa-info"></i> Informações</span><span class="sv"><?= $typeCounts['info']??0 ?></span></div>
      </div>
    </div>
    <div class="side-card">
      <div class="sc-title"><i class="fa fa-link"></i> Links úteis</div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <a href="library.php" style="font-size:13px;color:var(--cr);font-weight:600;padding:8px 12px;background:var(--cr-xl);border-radius:var(--r2);display:flex;align-items:center;gap:6px"> Ir para Biblioteca</a>
        <a href="list-opportunities.php" style="font-size:13px;color:var(--cr);font-weight:600;padding:8px 12px;background:var(--cr-xl);border-radius:var(--r2);display:flex;align-items:center;gap:6px"> Oportunidades</a>
        <a href="contact.php" style="font-size:13px;color:var(--cr);font-weight:600;padding:8px 12px;background:var(--cr-xl);border-radius:var(--r2);display:flex;align-items:center;gap:6px"> Contactos</a>
        <a href="about.php" style="font-size:13px;color:var(--cr);font-weight:600;padding:8px 12px;background:var(--cr-xl);border-radius:var(--r2);display:flex;align-items:center;gap:6px"> Sobre</a>
        <a href="auth.php" style="font-size:13px;color:#fff;font-weight:600;padding:8px 12px;background:var(--cr);border-radius:var(--r2);display:flex;align-items:center;gap:6px"> Entrar / Registar</a>
      </div>
    </div>
  </div>
</div>

<?= pubFooter() ?>

</body>
</html>
