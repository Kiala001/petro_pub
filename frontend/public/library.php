<?php
require_once 'includes.php';
$sid = $_SESSION['user_uuid'] ?? 0;

/* ─── API: favorites ─── */
if (isset($_GET['api']) && $_GET['api'] === 'fav') {
  $docId = sanitize($_POST['doc_id'] ?? '');
  if (!$docId) jsonResponse(['ok'=>false],400);
  $ex = $db->prepare("SELECT id FROM favorites WHERE document_id=? AND user_id=?");
  $ex->execute([$docId, $sid]);
  if ($ex->fetch()) {
    $db->prepare("DELETE FROM favorites WHERE document_id=? AND user_id=?")->execute([$docId, $sid]);
    $active = false;
  } else {
    $db->prepare("INSERT IGNORE INTO favorites (user_id, document_id) VALUES (?,?)")->execute([$sid,$docId]);
    $active = true;
  }
  $cnt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id=?");
  $cnt->execute([$sid]);
  jsonResponse(['ok'=>true,'active'=>$active,'total'=>(int)$cnt->fetchColumn()]);
}

/* ─── PARAMS ─── */
$q        = sanitize($_GET['q']        ?? '');
$tipo     = sanitize($_GET['tipo']     ?? '');
$catSlug  = sanitize($_GET['cat']      ?? '');
$yearFrom = (int)($_GET['year_from']   ?? 0);
$yearTo   = (int)($_GET['year_to']     ?? 0);
$acesso   = sanitize($_GET['acesso']   ?? '');  // livre|pago
$sort     = sanitize($_GET['sort']     ?? 'recent');
$view     = in_array($_GET['view']??'',['grid','list']) ? $_GET['view'] : 'grid';
$page     = max(1,(int)($_GET['page']  ?? 1));
$perPage  = 12;

// fetch categories
$categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$catMap     = array_column($categories, null, 'name');
$catIdMap   = array_column($categories, 'name', 'id');

// resolve category id from slug
$catId = '';
if ($catSlug && isset($catMap[$catSlug])) {
  $catId = $catMap[$catSlug]['id'];
}

/* ─── BUILD QUERY ─── */
$where  = ["d.status IN ('PUBLICADO')"];
$params = [];

if ($q) {
  $where[]        = '(d.title LIKE :q OR d.authors LIKE :q OR d.summary LIKE :q OR d.keywords LIKE :q)';
  $params[':q']   = "%$q%";
}
// if ($catId) { $where[] = 'd.category_id=:cat'; $params[':cat'] = $catId; }
if ($yearFrom > 0) { $where[] = 'YEAR(d.created_at)>=:yf'; $params[':yf'] = $yearFrom; }
if ($yearTo   > 0) { $where[] = 'YEAR(d.created_at)<=:yt'; $params[':yt'] = $yearTo; }

$orderMap = [
  'recent'  => 'd.created_at DESC',
  'title'   => 'd.title ASC',
  'year'    => 'd.created_at DESC',
];
$oSql  = $orderMap[$sort] ?? $orderMap['recent'];
$wSql  = implode(' AND ', $where);

// counts per category for sidebar
$catCountsRaw = $db->query("SELECT category_id,COUNT(*) as c FROM documents WHERE status IN ('PUBLICADO') GROUP BY category_id")->fetchAll();
$catCounts    = array_column($catCountsRaw,'c','category_id');

$cSt = $db->prepare("SELECT COUNT(*) FROM documents d WHERE $wSql");
$cSt->execute($params); $total = (int)$cSt->fetchColumn();
$pg  = paginate($total, $page, $perPage);


$dSt = $db->prepare("
    SELECT *
    FROM documents d
    WHERE $wSql
    ORDER BY $oSql
    LIMIT :lim OFFSET :off
");
$dSt->bindValue(':lim', $pg['per_page'], PDO::PARAM_INT);
$dSt->bindValue(':off', $pg['offset'],   PDO::PARAM_INT);
foreach ($params as $k => $v) $dSt->bindValue($k, $v);
$dSt->execute(); $docs = $dSt->fetchAll();

$favStmt = $db->prepare("SELECT document_id FROM favorites WHERE user_id=?");
$favStmt->execute([$sid]);
$favIds = array_flip($favStmt->fetchAll(PDO::FETCH_COLUMN));
$favCount = count($favIds);

// active filter count
$filterCount = (($q?1:0)+($catSlug?1:0)+($yearFrom?1:0)+($yearTo?1:0)+($acesso?1:0));

function buildUrl(array $ov=[]): string {
    global $q,$tipo,$catSlug,$yearFrom,$yearTo,$acesso,$sort,$view,$page;
    $p=['q'=>$q,'tipo'=>$tipo,'cat'=>$catSlug,'year_from'=>$yearFrom?:null,'year_to'=>$yearTo?:null,'acesso'=>$acesso,'sort'=>$sort,'view'=>$view,'page'=>$page];
    foreach ($ov as $k=>$v) $p[$k]=$v;
    return '?'.http_build_query(array_filter($p,fn($v)=>$v!==null&&$v!==''&&$v!==0&&$v!=='0'));
}

function bgForIndex(int $i): string {
    $bgs=['hsl(200,55%,92%)','hsl(140,45%,92%)','hsl(220,55%,92%)','hsl(40,55%,92%)','hsl(0,45%,92%)','hsl(280,45%,92%)','hsl(60,55%,92%)','hsl(180,45%,92%)','hsl(300,45%,92%)','hsl(20,45%,92%)'];
    return $bgs[$i % count($bgs)];
}

$typeIcons = ['TCC'=>'🎓','Artigo'=>'📄','Livro'=>'📖','Dissertação'=>'📘','Relatório'=>'📊','Apresentação'=>'📑'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Biblioteca</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= publicCss() ?>
<style>
/* ── LAYOUT ── */
.layout{display:flex;max-width:var(--max);margin:0 auto;min-height:calc(100vh - var(--nav-h))}
/* SIDEBAR */
.sidebar{width:272px;flex-shrink:0;background:#fff;border-right:1px solid var(--bdr);position:sticky;top:var(--nav-h);height:calc(100vh - var(--nav-h));overflow-y:auto}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:var(--bdr)}
.sb-head{padding:16px 20px 13px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between}
.sb-title{font-size:13px;font-weight:800;color:var(--tx);text-transform:uppercase;letter-spacing:.8px}
.sb-reset{font-size:11px;font-weight:600;color:var(--cr);text-decoration:none}
.sb-block{padding:14px 20px;border-bottom:1px solid var(--bdr2)}
.sb-block:last-child{border-bottom:none}
.sb-lbl{font-size:11px;font-weight:800;color:var(--tx-l);text-transform:uppercase;letter-spacing:1px;margin-bottom:11px;display:flex;align-items:center;justify-content:space-between}
.sb-lbl a{font-size:10px;font-weight:600;color:var(--cr);text-transform:none;letter-spacing:0;text-decoration:none}
.sb-lbl a:hover{text-decoration:underline}
/* checkboxes */
.cb-item{display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer}
.cb-item:hover .cb-lbl{color:var(--cr)}
.cb-box{width:15px;height:15px;border-radius:4px;border:1.5px solid var(--bdr);background:#fff;flex-shrink:0;appearance:none;-webkit-appearance:none;cursor:pointer;transition:all .15s;position:relative}
.cb-box:checked{background:var(--cr);border-color:var(--cr)}
.cb-box:checked::after{content:'✓';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-size:9px;font-weight:800}
.cb-lbl{font-size:13px;color:var(--tx-m);flex:1;transition:color var(--t)}.cb-cnt{font-size:11px;color:var(--tx-l)}
/* radio */
.rb-item{display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer;text-decoration:none}
.rb-item:hover .rb-lbl{color:var(--cr)}
.rb-dot{width:15px;height:15px;border-radius:50%;border:1.5px solid var(--bdr);background:#fff;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s}
.rb-item.sel .rb-dot{border-color:var(--cr);background:var(--cr)}
.rb-item.sel .rb-dot::after{content:'';width:5px;height:5px;border-radius:50%;background:#fff}
.rb-lbl{font-size:13px;color:var(--tx-m);transition:color var(--t)}
/* year */
.yr-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.yr-input{padding:7px 10px;border:1.5px solid var(--bdr);border-radius:var(--r1);font-size:13px;color:var(--tx);background:var(--cream);outline:none;width:100%;transition:all var(--t)}
.yr-input:focus{border-color:var(--cr);background:#fff}
.sb-apply{display:block;margin:14px 20px;width:calc(100% - 40px);padding:10px;border-radius:var(--r2);background:var(--cr);color:#fff;border:none;font-size:13px;font-weight:700;cursor:pointer;text-align:center;transition:background var(--t);box-shadow:0 3px 10px rgba(107,16,32,.22)}
.sb-apply:hover{background:var(--cr-dk)}
/* MAIN */
.main{flex:1;min-width:0;padding:clamp(16px,2.5vw,24px) clamp(14px,3vw,28px)}
/* top bar */
.top-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.result-info{font-size:13px;color:var(--tx-l)}.result-info strong{color:var(--tx);font-weight:700}
.bar-r{display:flex;align-items:center;gap:8px;flex-shrink:0}
.sort-sel{padding:7px 26px 7px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}
.sort-sel:focus{border-color:var(--cr)}
.vt-btn{width:32px;height:32px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;transition:all var(--t);color:var(--tx-l);text-decoration:none}
.vt-btn.on,.vt-btn:hover{background:var(--cr);color:#fff;border-color:var(--cr)}
.mob-filter-btn{display:none;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--r2);border:1.5px solid var(--bdr);background:#fff;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t)}
.mob-filter-btn:hover{border-color:var(--cr-bdr);color:var(--cr)}
.f-bdg{background:var(--cr);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:100px}
/* active tags */
.active-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.a-tag{display:flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;background:var(--cr-xl);border:1px solid var(--cr-bdr);font-size:12px;font-weight:600;color:var(--cr)}
.a-tag a{color:var(--cr);font-size:12px;font-weight:700;text-decoration:none;margin-left:2px}
/* CARDS */
.docs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(200px,22vw,230px),1fr));gap:clamp(12px,2vw,16px)}
.docs-grid.list-view{grid-template-columns:1fr;gap:10px}
.doc-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);overflow:hidden;cursor:pointer;transition:all var(--t);animation:fadeUp .38s ease both;position:relative}
.doc-card:hover{box-shadow:var(--sh2);transform:translateY(-3px);border-color:rgba(107,16,32,.20)}
.dc-thumb{height:clamp(150px, 110vw, 300px);display:flex;align-items:center;justify-content:center;font-size:clamp(32px,5vw,42px);position:relative}
.dc-thumb-img{height:100%; width: 100%;}
.fav-btn{position:absolute;top:8px;right:8px;width:28px;height:28px;background:rgba(255,255,255,.85);border-radius:50%;border:none;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;transition:all .2s;backdrop-filter:blur(4px)}
.fav-btn:hover{background:#fff;transform:scale(1.15)}.fav-btn.active{background:var(--gd);box-shadow:0 2px 8px rgba(201,168,76,.4)}
.dc-body{padding:clamp(11px,1.6vw,14px)}
.dc-type{font-size:10px;font-weight:700;padding:2px 8px;border-radius:100px;display:inline-flex;margin-bottom:7px}
.dt-tcc{background:var(--inf-bg);color:var(--inf)}.dt-art{background:var(--ok-bg);color:var(--ok)}.dt-liv{background:var(--gd-bg);color:var(--gd-dk)}.dt-dis{background:var(--pu-bg);color:var(--pu)}.dt-rel{background:var(--wn-bg);color:var(--wn)}.dt-apr{background:var(--cr-xl);color:var(--cr)}
.dc-title{font-family:'Arial',serif;font-size:clamp(13px,1.4vw,14px);font-weight:700;color:var(--tx);line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:5px}
.dc-author{font-size:12px;color:var(--tx-l);margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dc-meta{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:10px}
.dc-cat{font-size:11px;color:var(--tx-l)}.dc-year{font-size:11px;color:var(--tx-l)}
.dc-actions{display:block;}
.dc-btn{padding:7px;border-radius:var(--r1);font-size:11px;font-weight:700;cursor:pointer;text-align:center;transition:all var(--t);border:none;display:block}
.dc-btn-see{background:var(--cream);border:1px solid var(--bdr);color:var(--tx-m)}.dc-btn-see:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.dc-btn-read{background:var(--cr);color:#fff}.dc-btn-read:hover{background:var(--cr-dk)}
/* list view */
.docs-grid.list-view .doc-card{display:flex}
.docs-grid.list-view .dc-thumb{width:80px;height:auto;flex-shrink:0;min-height:110px;border-radius:var(--r3) 0 0 var(--r3)}
.docs-grid.list-view .dc-body{flex:1;display:flex;flex-direction:column}
.docs-grid.list-view .dc-actions{width:fit-content;margin-top:auto}
/* mobile sidebar */
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:500;backdrop-filter:blur(3px);opacity:0;transition:opacity .28s}.sb-ov.open{opacity:1}
.mob-sb{position:fixed;left:0;top:0;bottom:0;width:min(300px,88vw);background:#fff;z-index:600;transform:translateX(-100%);transition:transform .3s cubic-bezier(.4,0,.2,1);overflow-y:auto}
.mob-sb.open{transform:translateX(0)}
.mob-sb-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;border-bottom:1px solid var(--bdr)}
.mob-sb-close{width:28px;height:28px;border-radius:50%;background:var(--cream);border:1px solid var(--bdr);font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center}
/* login prompt */
.lp{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:400;background:#fff;border-radius:var(--r4);box-shadow:var(--sh3);border:1px solid var(--bdr);padding:14px 20px;display:none;align-items:center;gap:14px;max-width:min(480px,90vw);width:100%}
.lp.show{display:flex;animation:slideUp .3s cubic-bezier(.22,1,.36,1)}
@keyframes slideUp{from{opacity:0;transform:translateX(-50%) translateY(16px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}
@media(max-width:860px){
  .sidebar{
    display:none
  }
  .mob-filter-btn{
    display:flex
  } 
  .dc-thumb {
    height: clamp(140px, 120vw, 200px);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(32px, 5vw, 44px);
  }
}
@media(max-width:600px){
  .docs-grid{
    grid-template-columns:repeat(2,1fr)
  }
  .dc-thumb {
    height: clamp(140px, 120vw, 200px);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(32px, 5vw, 44px);
  }
}
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- LOGIN PROMPT -->
<div class="lp" id="lp">
  <span style="font-size:26px;flex-shrink:0">🔐</span>
  <div style="flex:1;min-width:0">
    <div style="font-size:13px;font-weight:700;color:var(--tx);margin-bottom:2px" id="lp-title">Login necessário</div>
    <div style="font-size:12px;color:var(--tx-l)">Crie uma conta gratuita para acesso completo</div>
  </div>
  <a href="auth.php" class="btn btn-gh btn-sm">Registar</a>
  <a href="auth.php" class="btn btn-cr btn-sm">Entrar</a>
  <button style="width:24px;height:24px;border-radius:50%;background:var(--cream);border:1px solid var(--bdr);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0" onclick="document.getElementById('lp').classList.remove('show')">✕</button>
</div>

<!-- MOBILE SIDEBAR OVERLAY -->
<div class="sb-ov" id="sb-ov" onclick="closeMobSB()"></div>
<div class="mob-sb" id="mob-sb">
  <div class="mob-sb-head">
    <div style="font-size:14px;font-weight:800;color:var(--tx)"><i class="fa fa-filter"></i> Filtros</div>
    <button class="mob-sb-close" onclick="closeMobSB()">✕</button>
  </div>
  <div id="mob-sb-body"></div>
</div>

<?= pubNav('biblioteca') ?>

<div class="layout">
  <!-- ═══ SIDEBAR ═══ -->
  <aside class="sidebar" id="sidebar-desktop">
    <div class="sb-head">
      <div class="sb-title"><i class="fa fa-filter"></i> Filtros</div>
      <a href="library.php" class="sb-reset">Limpar</a>
    </div>

    <!-- SEARCH -->
    <form method="GET" action="">
      <div class="sb-block">
        <div class="sb-lbl">Pesquisar</div>
        <div style="position:relative">
          <input type="text" name="q" value="<?= h($q) ?>"
                 placeholder="Título, autor, palavras-chave…"
                 style="width:100%;padding:8px 12px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;outline:none;background:var(--cream);color:var(--tx)"
                 onfocus="this.style.borderColor='var(--cr)';this.style.boxShadow='0 0 0 3px var(--cr-xl)'"
                 onblur="this.style.borderColor='';this.style.boxShadow=''">
        </div>
        <?php if($catSlug):?><input type="hidden" name="cat" value="<?=h($catSlug)?>"><?php endif;?>
        <?php if($sort):?><input type="hidden" name="sort" value="<?=h($sort)?>"><?php endif;?>
        <?php if($view):?><input type="hidden" name="view" value="<?=h($view)?>"><?php endif;?>
      </div>

      <!-- CATEGORIES -->
      <div class="sb-block">
        <div class="sb-lbl">Categoria
          <?php if($catSlug): ?><a href="<?=buildUrl(['cat'=>'','page'=>1])?>">Limpar</a><?php endif; ?>
        </div>
        <?php foreach ($categories as $cat): 
          $cnt = $catCounts[$cat['id']] ?? 0;
        ?>
        <label class="cb-item">
          <input type="checkbox" class="cb-box" name="cat_check"
                 <?= $catSlug===$cat['slug']?'checked':'' ?>
                 onchange="window.location='<?=buildUrl(['cat'=>$cat['slug'],'page'=>1])?>'" >
          <span class="cb-lbl"><?=h($cat['name'])?></span>
          <span class="cb-cnt"><?=$cnt?></span>
        </label>
        <?php endforeach; ?>
      </div>

      <!-- YEAR -->
      <div class="sb-block">
        <div class="sb-lbl">Ano de publicação</div>
        <div class="yr-row">
          <input class="yr-input" type="number" name="year_from"
                 value="<?=$yearFrom?:''?>" placeholder="De…" min="2000" max="<?=date('Y')?>"
                 onchange="this.form.submit()">
          <input class="yr-input" type="number" name="year_to"
                 value="<?=$yearTo?:''?>" placeholder="Até…" min="2000" max="<?=date('Y')?>"
                 onchange="this.form.submit()">
        </div>
      </div>

      <!-- SORT -->
      <div class="sb-block">
        <div class="sb-lbl">Ordenar por</div>
        <?php foreach (['recent'=>'Mais recentes','title'=>'Título A→Z'] as $sv=>$sl): ?>
        <a href="<?=buildUrl(['sort'=>$sv,'page'=>1])?>" class="rb-item <?=$sort===$sv?'sel':''?>">
          <div class="rb-dot"></div><span class="rb-lbl"><?=$sl?></span>
        </a>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="sb-apply">✓ Aplicar filtros (<?=$total?>)</button>
    </form>
  </aside>

  <!-- ═══ MAIN ═══ -->
  <main class="main">
    <!-- TOP BAR -->
    <div class="top-bar">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <button class="mob-filter-btn" onclick="openMobSB()">
          <i class="fa fa-filter"></i> Filtros<?php if($filterCount>0): ?> <span class="f-bdg"><?=$filterCount?></span><?php endif; ?>
        </button>
        <div class="result-info">
          A mostrar <strong><?=min($perPage,$total)?></strong>–<strong><?=min($pg['offset']+$perPage,$total)?></strong>
          de <strong><?=$total?></strong> documentos
        </div>
      </div>
      <div class="bar-r">
        <form method="GET" action="" id="sort-form" style="display:flex;align-items:center;gap:6px">
          <?php if($q):?><input type="hidden" name="q" value="<?=h($q)?>"><?php endif;?>
          <?php if($catSlug):?><input type="hidden" name="cat" value="<?=h($catSlug)?>"><?php endif;?>
          <?php if($yearFrom):?><input type="hidden" name="year_from" value="<?=$yearFrom?>"><?php endif;?>
          <?php if($yearTo):?><input type="hidden" name="year_to" value="<?=$yearTo?>"><?php endif;?>
          <input type="hidden" name="view" value="<?=h($view)?>">
          <select class="sort-sel" name="sort" onchange="this.form.submit()">
            <option value="recent" <?=$sort==='recent'?'selected':''?>>Mais recentes</option>
            <option value="title"  <?=$sort==='title' ?'selected':''?>>A → Z</option>
          </select>
        </form>
        <div style="display:flex;gap:3px">
          <a href="<?=buildUrl(['view'=>'grid'])?>" class="vt-btn <?=$view==='grid'?'on':''?>" title="Grelha">⊞</a>
          <a href="<?=buildUrl(['view'=>'list'])?>" class="vt-btn <?=$view==='list'?'on':''?>" title="Lista">☰</a>
        </div>
      </div>
    </div>

    <!-- ACTIVE TAGS -->
    <div class="active-tags">
      <?php if($q): ?><span class="a-tag">"<?=h($q)?>"<a href="<?=buildUrl(['q'=>'','page'=>1])?>">✕</a></span><?php endif; ?>
      <?php if($catSlug && isset($catMap[$catSlug])): ?>
      <span class="a-tag"><?=h($catMap[$catSlug]['icon'].' '.$catMap[$catSlug]['name'])?><a href="<?=buildUrl(['cat'=>'','page'=>1])?>">✕</a></span>
      <?php endif; ?>
      <?php if($yearFrom||$yearTo): ?>
      <span class="a-tag"><i class="fa fa-calendar"></i> <?=$yearFrom?:'-'?> – <?=$yearTo?:'presente'?><a href="<?=buildUrl(['year_from'=>null,'year_to'=>null,'page'=>1])?>">✕</a></span>
      <?php endif; ?>
    </div>

    <!-- GRID -->
    <?php if(empty($docs)): ?>
    <div class="empty-state">
      <div class="es-ico"><i class="fa fa-search"></i></div>
      <div class="es-title">Nenhum documento encontrado</div>
      <div class="es-sub">Tente ajustar os filtros ou use termos diferentes.</div>
      <a href="biblioteca-pub.php" class="btn btn-cr">Limpar filtros</a>
    </div>
    <?php else: ?>
    <div class="docs-grid<?=$view==='list'?' list-view':''?>" id="docs-grid">
      <?php foreach ($docs as $i => $d):
        $isFav = isset($favIds[$d['id']]);
        $delay  = number_format($i * 0.045, 3);
        $bg     = bgForIndex($i);
        $catIcon= $d['file_cover'] ?? '📂';
        $typeClsMap=['TCC'=>'dt-tcc','Artigo'=>'dt-art','Livro'=>'dt-liv','Dissertação'=>'dt-dis','Relatório'=>'dt-rel','Apresentação'=>'dt-apr'];
      ?>
      <div class="doc-card" id="dc-<?=h($d['id'])?>" style="animation-delay:<?=$delay?>s">
        <div class="dc-thumb" style="background:<?=$bg?>">
          <img src="../../uploads/documents/cover/<?=$catIcon?>" alt="<?=$d['name']?>" class="dc-thumb-img"> 
          <button class="fav-btn <?=$isFav?'active':''?>"
                  id="fav-<?=h($d['id'])?>"
                  onclick="toggleFav('<?=$d['id']?>')"
                  title="<?=$isFav?'Remover favorito':'Adicionar favorito'?>">
            <?=$isFav?'<i class="fa fa-heart" style="color: red"></i>':'<i class="fa fa-heart"></i>'?>
          </button>
        </div>
        <div class="dc-body">
          <div class="dc-meta">
            <span class="dc-cat"><?=$d['category_id']??'—'?></span>
            <span class="dc-year"><?=$d['created_at']?date('Y',strtotime($d['created_at'])):'—'?></span>
          </div>
          <div class="dc-title"><?=h($d['title'])?></div>
          <div class="dc-actions">
            <?php
              $price = ($d['download_link'] == 'fisico') ?
                '<span class="dc-price" style="color: #6b1020; font-size: 12px; font-weight: bold; display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                  <span> '.number_format(($d['price']),2,',','.').' Kz </span>
                  <span>Físico</span>
              </span>' : 
              '<span class="dc-price" style="color: #6b1020; font-size: 12px; font-weight: bold; display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                Digital
              </span>';
              echo $price;
            ?>
            <!-- <button class="dc-btn dc-btn-see"
                    onclick="showSummary(<?=h(json_encode($d,JSON_UNESCAPED_UNICODE))?>)">Ver resumo</button> -->
            <?php
            if (isset($_SESSION['jwt_auth'])) {
              echo '<a href="detail-doc.php?id='.$d['id'].'" class="dc-btn dc-btn-read">Ver Detalhes</a>';
            } else {
              ?>  
              <button class="dc-btn dc-btn-read" onclick="requireLogin('ler')">Ver Detalhes</button>
              <?php
            }
            ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?= pubPagination($pg, buildUrl(['page'=>'__PAGE__'])) ?>
    <?php endif; ?>
  </main>
</div>

<!-- SUMMARY MODAL -->
<div id="sum-ov" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:800;align-items:center;justify-content:center;padding:20px" onclick="if(event.target===this)closeSum()">
  <div style="background:#fff;border-radius:20px;max-width:540px;width:100%;max-height:calc(100vh - 40px);overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.22);animation:fadeUp .28s ease both">
    <div id="sum-hd" style="padding:20px 24px;border-radius:20px 20px 0 0;position:relative;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt))">
      <div id="sum-cat" style="font-size:10px;font-weight:700;color:rgba(255,255,255,.7);margin-bottom:6px;text-transform:uppercase;letter-spacing:1px"></div>
      <div id="sum-title" style="font-family:'Arial',serif;font-size:19px;font-weight:700;color:#fff;line-height:1.3"></div>
      <div id="sum-author" style="font-size:13px;margin-top:6px;color:rgba(255,255,255,.7)"></div>
      <button onclick="closeSum()" style="position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer">✕</button>
    </div>
    <div style="padding:20px 24px">
      <div id="sum-summary" style="font-size:14px;color:var(--tx-m);line-height:1.7;margin-bottom:18px"></div>
      <div id="sum-keywords" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px"></div>
      <div id="sum-meta" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px"></div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn btn-cr" onclick="requireLogin('ler');closeSum()">📖 Ler completo 🔒</button>
        <button class="btn btn-gh" id="sum-fav-btn" onclick="toggleFavFromModal()">☆ Favoritar</button>
      </div>
    </div>
  </div>
</div>

<?= pubFooter() ?>

<script>
/* ═══ FAV ═══ */
async function toggleFav(docId) {
    const btn = document.getElementById('fav-'+docId);
    if (!btn) return;
    btn.style.transform='scale(.8)';
    const res  = await fetch('library.php?api=fav',{method:'POST',body:new URLSearchParams({doc_id:docId})});
    const data = await res.json();
    btn.style.transform='';
    if (data.active) { btn.textContent='★'; btn.classList.add('active'); showToast('★ Adicionado aos favoritos!','t-ok'); }
    else             { btn.textContent='☆'; btn.classList.remove('active'); showToast('Removido dos favoritos'); }
}
let currentSumDocId = null;
function toggleFavFromModal() { if(currentSumDocId) toggleFav(currentSumDocId); }

/* ═══ SUMMARY MODAL ═══ */
function showSummary(doc) {
    currentSumDocId = doc.id;
    document.getElementById('sum-cat').textContent    = (doc.cat_icon||'📂')+' '+(doc.cat_name||'—');
    document.getElementById('sum-title').textContent  = doc.title;
    document.getElementById('sum-author').textContent = ' '+doc.authors;
    document.getElementById('sum-summary').textContent= doc.summary || 'Resumo não disponível.';
    // keywords
    const kws = (doc.keywords||'').split(',').map(k=>k.trim()).filter(Boolean);
    document.getElementById('sum-keywords').innerHTML = kws.map(k=>`<span style="background:var(--cr-xl);border:1px solid var(--cr-bdr);color:var(--cr);font-size:11px;font-weight:600;padding:3px 10px;border-radius:100px">${k}</span>`).join('');
    // meta
    document.getElementById('sum-meta').innerHTML = [
        ['Ano', doc.created_at ? doc.created_at.substring(0,4) : '—'],
        ['Tamanho', doc.file_size||'—'],
        ['Estado', doc.status||'—'],
        ['Categoria', (doc.cat_name||'—')],
    ].map(([l,v])=>`<div style="background:var(--cream);border:1px solid var(--bdr);border-radius:10px;padding:10px 13px"><div style="font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px">${l}</div><div style="font-size:13px;font-weight:600;color:var(--tx)">${v}</div></div>`).join('');
    // fav button
    const favBtn = document.getElementById('fav-'+doc.id);
    const isActive = favBtn && favBtn.classList.contains('active');
    document.getElementById('sum-fav-btn').textContent = isActive ? '★ Nos favoritos' : '☆ Adicionar favorito';
    document.getElementById('sum-ov').style.display='flex';
    document.body.style.overflow='hidden';
}
function closeSum(){document.getElementById('sum-ov').style.display='none';document.body.style.overflow='';}

/* ═══ LOGIN GATE ═══ */
function requireLogin(type){
    const msgs={ler:'Login para ler o documento completo',baixar:'Login para descarregar'};
    document.getElementById('lp-title').textContent=msgs[type]||'Login necessário';
    document.getElementById('lp').classList.add('show');
}

/* ═══ MOBILE SIDEBAR ═══ */
function openMobSB(){
    document.getElementById('mob-sb-body').innerHTML=document.getElementById('sidebar-desktop').innerHTML;
    const o=document.getElementById('sb-ov'),s=document.getElementById('mob-sb');
    o.style.display='block';setTimeout(()=>o.classList.add('open'),10);s.classList.add('open');document.body.style.overflow='hidden';
}
function closeMobSB(){
    const o=document.getElementById('sb-ov'),s=document.getElementById('mob-sb');
    o.classList.remove('open');s.classList.remove('open');setTimeout(()=>o.style.display='none',300);document.body.style.overflow='';
}
</script>
</body>
</html>
