<?php
require_once 'includes.php';

// track view
if (isset($_GET['view_id'])) {
    $vid = (int)$_GET['view_id'];
    $db->prepare("UPDATE opportunities SET views=views+1 WHERE id=?")->execute([$vid]);
    jsonResponse(['ok'=>true]);
}

$type   = sanitize($_GET['type']   ?? '');
$search = sanitize($_GET['q']      ?? '');
$sort   = sanitize($_GET['sort']   ?? 'recent');
$page   = max(1,(int)($_GET['page']??1));
$perPage= 9;

$where  = ['is_approved=1','is_active=1'];
$params = [];
if ($type) { $where[]='type=:type'; $params[':type']=$type; }
if ($search){ $where[]='(title LIKE :q OR description LIKE :q OR source LIKE :q)'; $params[':q']="%$search%"; }

$orderMap=['recent'=>'created_at DESC','popular'=>'views DESC','title'=>'title ASC','date'=>'COALESCE(event_date,\'9999-01-01\') ASC'];
$oSql = $orderMap[$sort] ?? $orderMap['recent'];
$wSql = implode(' AND ',$where);

$cSt=$db->prepare("SELECT COUNT(*) FROM opportunities WHERE $wSql"); $cSt->execute($params); $total=(int)$cSt->fetchColumn();
$pg=paginate($total,$page,$perPage);
$dSt=$db->prepare("SELECT * FROM opportunities WHERE $wSql ORDER BY $oSql LIMIT :l OFFSET :o");
$dSt->bindValue(':l',$pg['per_page'],PDO::PARAM_INT); $dSt->bindValue(':o',$pg['offset'],PDO::PARAM_INT);
foreach($params as $k=>$v) $dSt->bindValue($k,$v); $dSt->execute(); $rows=$dSt->fetchAll();

$typeCounts = $db->query("SELECT type,COUNT(*) FROM opportunities WHERE is_approved=1 AND is_active=1 GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalApproved = array_sum($typeCounts);

function h(string $v): string { return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
function typeTagCls(string $t): string { return match($t){'Curso'=>'t-curso','Equipamento'=>'t-equip','Evento'=>'t-evento','Vaga'=>'t-vaga',default=>'t-curso'}; }
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
<title>PetroPub — Oportunidades Oil & Gas</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<?= publicCss() ?>
<style>
:root{--cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);--gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;--gd-bg:rgba(201,168,76,.11);--cream:#FAF7F2;--warm:#FEF9F3;--bdr:rgba(107,16,32,.10);--tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;--ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);--wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);--er:#C53030;--er-bg:rgba(197,48,48,.10);--inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);--sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);--sh2:0 8px 32px rgba(107,16,32,.13);--r1:7px;--r2:11px;--r3:15px;--r4:20px;--t:.22s cubic-bezier(.4,0,.2,1);--max:1280px}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button{font-family:inherit}a{color:inherit;text-decoration:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--r2);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-gh{background:#fff;color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}
/* NAV */
.nav{background:#fff;border-bottom:1px solid var(--bdr);position:sticky;top:0;z-index:200;box-shadow:var(--sh0)}
.nav-inner{display:flex;align-items:center;gap:12px;height:60px;max-width:var(--max);margin:0 auto;padding:0 clamp(14px,4vw,40px)}
.nav-logo{font-family:'Arial',serif;font-weight:900;font-size:20px;color:var(--cr-dk)}.nav-logo span{color:var(--gd)}
.nav-links{display:flex;align-items:center;gap:0;margin-left:clamp(12px,2vw,24px);flex:1}
.nav-link{padding:0 clamp(10px,1.5vw,16px);height:60px;display:flex;align-items:center;font-size:13px;font-weight:600;color:var(--tx-l);cursor:pointer;border-bottom:2.5px solid transparent;transition:all var(--t);white-space:nowrap;text-decoration:none}
.nav-link:hover,.nav-link.on{color:var(--cr);border-bottom-color:var(--cr)}
.nav-r{display:flex;align-items:center;gap:8px;flex-shrink:0;margin-left:auto}
/* HERO */
.hero{background:linear-gradient(150deg,var(--cr-dk) 0%,var(--cr-lt) 40%,#1A2A50 72%,#0E1A30 100%);padding:clamp(44px,8vw,80px) clamp(14px,4vw,40px);position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;width:450px;height:450px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-120px;right:-60px;pointer-events:none}
.hero-inner{max-width:var(--max);margin:0 auto;position:relative;z-index:1}
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);padding:5px 14px;border-radius:100px;font-size:11px;font-weight:700;color:rgba(255,255,255,.80);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:14px}
.hero-title{font-family:'Arial',serif;font-size:clamp(26px,5vw,48px);font-weight:900;color:#fff;line-height:1.2;margin-bottom:10px}
.hero-title em{color:var(--gd-lt);font-style:normal}
.hero-sub{font-size:clamp(13px,1.5vw,16px);color:rgba(255,255,255,.65);max-width:560px;line-height:1.65;margin-bottom:28px}
/* SEARCH HERO */
.hero-search{display:flex;gap:8px;max-width:580px;flex-wrap:wrap}
.hs-input-wrap{flex:1;position:relative;min-width:260px}
.hs-input{width:100%;padding:13px 14px 13px 42px;border:none;border-radius:var(--r3);font-size:14px;color:var(--tx);background:rgba(255,255,255,.95);outline:none;box-shadow:0 8px 28px rgba(0,0,0,.25)}
.hs-ico{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:16px;pointer-events:none}
.hero-stats{display:flex;gap:clamp(20px,4vw,44px);margin-top:28px;padding-top:22px;border-top:1px solid rgba(255,255,255,.12);flex-wrap:wrap}
.hs-item{}.hs-num{font-family:'Arial',serif;font-size:clamp(22px,3.5vw,32px);font-weight:900;color:var(--gd-lt)}.hs-lbl{font-size:11px;color:rgba(255,255,255,.50);margin-top:2px}
/* FILTER BAR */
.filter-bar{background:#fff;border-bottom:1px solid var(--bdr);box-shadow:var(--sh0)}
.fb-inner{max-width:var(--max);margin:0 auto;padding:0 clamp(14px,4vw,40px);display:flex;align-items:center;gap:10px;height:52px;overflow-x:auto;scrollbar-width:none}
.fb-inner::-webkit-scrollbar{display:none}
.fchip{padding:6px 14px;border-radius:100px;border:1.5px solid var(--bdr);background:#fff;font-size:12px;font-weight:600;color:var(--tx-l);white-space:nowrap;flex-shrink:0;transition:all var(--t);text-decoration:none}
.fchip:hover,.fchip.on{background:var(--cr);color:#fff;border-color:var(--cr)}
.fb-sort{margin-left:auto;flex-shrink:0;padding:6px 26px 6px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:12px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer}
.fb-sort:focus{border-color:var(--cr)}
/* CONTENT */
.content{max-width:var(--max);margin:0 auto;padding:clamp(24px,4vw,40px) clamp(14px,4vw,40px)}
.section-header{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:clamp(20px,3vw,28px);flex-wrap:wrap}
.sh-left .sh-eyebrow{font-size:11px;font-weight:700;color:var(--cr);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:4px}
.sh-title{font-family:'Arial',serif;font-size:clamp(20px,3vw,28px);font-weight:700;color:var(--tx)}
.sh-count{font-size:14px;color:var(--tx-l)}
/* GRID */
.opp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(260px,28vw,320px),1fr));gap:clamp(14px,2vw,20px);margin-bottom:clamp(20px,3vw,32px)}
.opp-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);overflow:hidden;transition:all var(--t);animation:fadeUp .4s ease both}
.opp-card:hover{box-shadow:var(--sh2);transform:translateY(-3px);border-color:rgba(107,16,32,.20)}
.oc-banner{height:clamp(80px,10vw,110px);display:flex;align-items:center;justify-content:center;font-size:clamp(34px,5vw,46px);position:relative;overflow:hidden}
.oc-banner::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.10),transparent)}
.oc-feat{position:absolute;top:8px;left:8px;background:var(--gd);color:var(--cr-dk);font-size:9px;font-weight:800;padding:2px 9px;border-radius:100px}
.oc-body{padding:clamp(14px,2vw,18px)}
.oc-tag{font-size:10px;font-weight:700;padding:3px 9px;border-radius:100px;margin-bottom:9px;display:inline-flex;align-items:center;gap:4px}
.t-curso{background:var(--inf-bg);color:var(--inf)}.t-equip{background:var(--gd-bg);color:var(--gd-dk)}.t-evento{background:var(--ok-bg);color:var(--ok)}.t-vaga{background:var(--er-bg);color:var(--er)}
.oc-title{font-family:'Arial',serif;font-size:clamp(14px,1.6vw,16px);font-weight:700;color:var(--tx);line-height:1.35;margin-bottom:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.oc-desc{font-size:12px;color:var(--tx-l);line-height:1.55;margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.oc-meta{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
.oc-meta-item{font-size:11px;color:var(--tx-l);display:flex;align-items:center;gap:3px}
.oc-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;padding-top:12px;border-top:1px solid var(--bdr)}
.oc-views{font-size:11px;color:var(--tx-l)}
/* pagination */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t);text-decoration:none}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}.pg-btn.disabled{opacity:.3;pointer-events:none}
/* empty */
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);grid-column:1/-1}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:12px}.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}
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

<?= pubNav('oportunidades') ?>
<!-- <nav class="nav">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">PETRO<span>PUB</span></a>
    <div class="nav-links">
      <a href="index.php" class="nav-link">Home</a>
      <a href="library.php" class="nav-link">Biblioteca</a>
      <a href="list-opportunities.php" class="nav-link on">Oportunidades</a>
      <a href="list-noticies.php" class="nav-link">Notícias</a>
      <a href="about.php" class="nav-link">Sobre</a>
      <a href="contact.php" class="nav-link">Contacto</a>
    </div>
    <div class="nav-r">
      <a href="index.php" class="nav-link">Home</a>
      <?php
      if (isset($_SESSION['jwt_auth'])) {
        echo '<a href="my-documents.php" class="btn btn-cr btn-sm"><i class="fa fa-user"></i>'.getInitials($_SESSION['user_name']).'</a>';
      } else {
        echo '<a href="auth.php" class="btn btn-cr btn-sm">Entrar</a>';
      }
      
      ?>
    </div>
  </div>
</nav> -->

<section class="hero">
  <div class="hero-inner">
    <div class="hero-eyebrow">⛽ Oil & Gas Angola</div>
    <h1 class="hero-title">Oportunidades & <em>Recursos</em></h1>
    <p class="hero-sub">Cursos, equipamentos, eventos e vagas do sector petrolífero e energético de Angola.</p>
    <form class="hero-search" method="GET" action="">
      <div class="hs-input-wrap">
        <span class="hs-ico"><i class="fa fa-search"></i></span>
        <input class="hs-input" name="q" type="text" value="<?= h($search) ?>" placeholder="Pesquisar cursos, vagas, eventos…">
        <?php if($type): ?><input type="hidden" name="type" value="<?= h($type) ?>"><?php endif; ?>
        <?php if($sort): ?><input type="hidden" name="sort" value="<?= h($sort) ?>"><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-cr"><i class="fa fa-search"></i> Pesquisar</button>
      <?php if($search||$type): ?><a href="lista-oportunidades.php" class="btn btn-gh">✕ Limpar</a><?php endif; ?>
    </form>
    <div class="hero-stats">
      <div class="hs-item"><div class="hs-num"><?= $totalApproved ?></div><div class="hs-lbl">Oportunidades activas</div></div>
      <div class="hs-item"><div class="hs-num"><?= $typeCounts['Vaga']??0 ?></div><div class="hs-lbl">Vagas de emprego</div></div>
      <div class="hs-item"><div class="hs-num"><?= $typeCounts['Curso']??0 ?></div><div class="hs-lbl">Cursos disponíveis</div></div>
      <div class="hs-item"><div class="hs-num"><?= ($typeCounts['Evento']??0)+($typeCounts['Equipamento']??0) ?></div><div class="hs-lbl">Eventos & Equipamentos</div></div>
    </div>
  </div>
</section>

<div class="filter-bar">
  <div class="fb-inner">
    <a href="<?= buildUrl(['type'=>'','page'=>1]) ?>" class="fchip <?= $type===''?'on':'' ?>">📦 Todos (<?= $totalApproved ?>)</a>
    <?php foreach(['Curso'=>'🎓','Equipamento'=>'<i class="fa fa-cog"></i>','Evento'=>'<i class="fa fa-calendar"></i>','Vaga'=>'💼'] as $t=>$ico): ?>
    <a href="<?= buildUrl(['type'=>$t,'page'=>1]) ?>" class="fchip <?= $type===$t?'on':'' ?>"><?= $ico ?> <?= $t ?> (<?= $typeCounts[$t]??0 ?>)</a>
    <?php endforeach; ?>
    <form method="GET" action="" style="margin-left:auto;flex-shrink:0;display:flex;align-items:center">
      <?php if($type): ?><input type="hidden" name="type" value="<?= h($type) ?>"><?php endif; ?>
      <?php if($search): ?><input type="hidden" name="q" value="<?= h($search) ?>"><?php endif; ?>
      <select class="fb-sort" name="sort" onchange="this.form.submit()">
        <option value="recent" <?= $sort==='recent'?'selected':'' ?>>Mais recentes</option>
        <option value="popular" <?= $sort==='popular'?'selected':'' ?>>Mais vistos</option>
        <option value="date" <?= $sort==='date'?'selected':'' ?>>Por data do evento</option>
        <option value="title" <?= $sort==='title'?'selected':'' ?>>A→Z</option>
      </select>
    </form>
  </div>
</div>

<div class="content">
  <div class="section-header">
    <div class="sh-left">
      <div class="sh-eyebrow">Resultados</div>
      <div class="sh-title"><?= $type ? h($type).'s' : 'Todas as Oportunidades' ?></div>
    </div>
    <div class="sh-count"><?= $total ?> resultado<?= $total!=1?'s':'' ?></div>
  </div>

  <?php if(empty($rows)): ?>
  <div class="empty">
    <div class="empty-ico">⛽</div><div class="empty-title">Nenhuma oportunidade encontrada</div>
    <p style="font-size:14px;color:var(--tx-l);margin-top:6px;margin-bottom:18px">Tente um filtro diferente ou remova a pesquisa.</p>
    <a href="lista-oportunidades.php" class="btn btn-cr">Ver todas</a>
  </div>
  <?php else: ?>
  <div class="opp-grid">
    <?php foreach($rows as $i=>$r): ?>
    <div class="opp-card" style="animation-delay:<?= $i*.05 ?>s">
      <div class="oc-body">
        <span class="oc-tag <?= typeTagCls($r['type']) ?>"><?= h($r['type']) ?></span>
        <div class="oc-title"><?= h($r['title']) ?></div>
        <div class="oc-desc"><?= h($r['description']??'') ?></div>
        <div class="oc-meta">
          <?php if($r['source']): ?><span class="oc-meta-item"><i class="fa fa-home"></i> <?= h($r['source']) ?></span><?php endif; ?>
          <?php if($r['location']): ?><span class="oc-meta-item"><i class="fa fa-spin"></i> <?= h($r['location']) ?></span><?php endif; ?>
          <?php if($r['event_date']): ?><span class="oc-meta-item"><i class="fa fa-calendar"></i> <?= date('d/m/Y',strtotime($r['event_date'])) ?></span><?php endif; ?>
        </div>
        <div class="oc-footer">
          <span class="oc-views">👁 <?= number_format($r['views']) ?> views</span>
          <?php if($r['link_url']): ?>
          <a href="<?= h($r['link_url']) ?>" target="_blank" class="btn btn-cr btn-sm" onclick="trackView(<?= $r['id'] ?>)">Saiba mais →</a>
          <?php else: ?>
          <a href="detail-opportunity.php?id=<?=$r['id']?>" class="btn btn-cr btn-sm" >Ver detalhes →</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?= paginationHtml($pg,buildUrl(['page'=>'__PAGE__'])) ?>
  <?php endif; ?>
</div>


<!-- FOOTER -->
<?= pubFooter() ?>

<script>
async function trackView(id) {
    await fetch('list-opportunities.php?view_id='+id);
}
</script>
</body>
</html>
