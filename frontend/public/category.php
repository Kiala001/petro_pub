<?php
require_once 'includes.php';

$slug = sanitize($_GET['categoria'] ?? $_GET['cat'] ?? '');
$q    = sanitize($_GET['q']   ?? '');
$sort = sanitize($_GET['sort'] ?? 'recent');
$page = max(1,(int)($_GET['page'] ?? 1));
$perPage = 12;



if (!$slug) { header('Location: index.php'); exit; }

// fetch category
// $catStmt = $db->prepare("SELECT * FROM doc_categories WHERE slug=?");
// $catStmt->execute([$slug]);
// $cat = $catStmt->fetch();
// if (!$cat) { header('Location: biblioteca-pub.php'); exit; }

// all categories for sidebar
$allCats = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$catCounts = $db->query("SELECT category_id,COUNT(*) as c FROM documents WHERE status IN ('PUBLICADO','APROVADO') GROUP BY category_id")->fetchAll(PDO::FETCH_KEY_PAIR);

// documents in this category
$where  = ["d.status IN ('PUBLICADO')","d.category_id=:catId"];
$params = [':catId'=>$slug];
if ($q) { $where[]='(d.title LIKE :q OR d.authors LIKE :q OR d.summary LIKE :q)'; $params[':q']="%$q%"; }
$oSql = $sort==='title' ? 'd.title ASC' : 'd.created_at DESC';
$wSql = implode(' AND ',$where);

$cSt=$db->prepare("SELECT COUNT(*) FROM documents d WHERE $wSql"); $cSt->execute($params); $total=(int)$cSt->fetchColumn();
$pg=paginate($total,$page,$perPage);
$dSt=$db->prepare("SELECT * FROM documents d WHERE $wSql ORDER BY $oSql LIMIT :lim OFFSET :off");
$dSt->bindValue(':lim',$pg['per_page'],PDO::PARAM_INT); $dSt->bindValue(':off',$pg['offset'],PDO::PARAM_INT);
foreach($params as $k=>$v) $dSt->bindValue($k,$v); $dSt->execute(); $docs=$dSt->fetchAll();


function bgForIndex(int $i): string {
    $bgs=['hsl(200,55%,92%)','hsl(140,45%,92%)','hsl(220,55%,92%)','hsl(40,55%,92%)','hsl(0,45%,92%)','hsl(280,45%,92%)','hsl(60,55%,92%)','hsl(180,45%,92%)','hsl(300,45%,92%)','hsl(20,45%,92%)'];
    return $bgs[$i % count($bgs)];
}
function buildCatUrl(array $ov=[]): string {
    global $slug,$q,$sort,$page;
    $p=['categoria'=>$slug,'q'=>$q,'sort'=>$sort,'page'=>$page];
    foreach($ov as $k=>$v) $p[$k]=$v;
    return '?'.http_build_query(array_filter($p,fn($v)=>$v!==null&&$v!==''&&$v!==0));
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — <?=h($slug)?></title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= publicCss() ?>
<style>
.hero-cat{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 50%,#1A2A50 100%);padding:clamp(36px,6vw,60px) clamp(14px,4vw,40px);position:relative;overflow:hidden}
.hero-cat::before{content:'';position:absolute;width:350px;height:350px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-100px;right:-40px;pointer-events:none}
.hc-inner{max-width:var(--max);margin:0 auto;position:relative;z-index:1;display:flex;align-items:center;gap:clamp(16px,3vw,28px);flex-wrap:wrap}
.hc-ico{font-size:clamp(44px,7vw,64px);flex-shrink:0}
.hc-breadcrumb{font-size:12px;color:rgba(255,255,255,.55);margin-bottom:8px;display:flex;align-items:center;gap:6px}
.hc-breadcrumb a{color:rgba(255,255,255,.7);text-decoration:none}.hc-breadcrumb a:hover{color:var(--gd-lt)}
.hc-title{font-family:'Arial',serif;font-size:clamp(22px,4vw,38px);font-weight:900;color:#fff;margin-bottom:6px}
.hc-sub{font-size:clamp(13px,1.4vw,15px);color:rgba(255,255,255,.65);line-height:1.55}
.hc-count{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.20);padding:5px 14px;border-radius:100px;font-size:12px;font-weight:700;color:#fff;margin-top:12px}
/* LAYOUT */
.layout{display:flex;max-width:var(--max);margin:0 auto;min-height:40vh}
.sidebar{width:248px;flex-shrink:0;background:#fff;border-right:1px solid var(--bdr);position:sticky;top:var(--nav-h);height:calc(100vh - var(--nav-h));overflow-y:auto}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:var(--bdr)}
.sb-head{padding:14px 18px 11px;border-bottom:1px solid var(--bdr);font-size:12px;font-weight:800;color:var(--tx);text-transform:uppercase;letter-spacing:.8px}
.cat-link{display:flex;align-items:center;gap:8px;padding:10px 18px;font-size:13px;font-weight:500;color:var(--tx-l);text-decoration:none;border-bottom:1px solid var(--bdr2);transition:all var(--t)}
.cat-link:hover{background:var(--cream);color:var(--cr)}.cat-link.on{background:var(--cr-xl);color:var(--cr);font-weight:700}
.cat-cnt{margin-left:auto;font-size:11px;color:var(--tx-l);background:var(--cream);padding:1px 7px;border-radius:100px}
.main{flex:1;min-width:0;padding:clamp(16px,2.5vw,24px) clamp(14px,3vw,28px)}
/* TOOLBAR */
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.tb-search{position:relative;flex:1;min-width:200px}
.tb-s-input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}
.tb-s-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.tb-s-input::placeholder{color:var(--tx-l)}
.s-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
.sort-sel{padding:7px 26px 7px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer}
/* CARDS */
.docs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(200px,22vw,230px),1fr));gap:clamp(12px,2vw,16px)}
.doc-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);overflow:hidden;transition:all var(--t);animation:fadeUp .38s ease both}
.doc-card:hover{box-shadow:var(--sh2);transform:translateY(-3px);border-color:rgba(107,16,32,.20)}
.dc-thumb{height:clamp(120px,80vw,228px);display:flex;align-items:center;justify-content:center;font-size:clamp(32px,5vw,42px)}
.dc-thumb-img{width: 100%; height: 100%;}
.dc-body{padding:clamp(11px,1.6vw,14px)}
.dc-title{font-family:'Arial',serif;font-size:clamp(13px,1.4vw,14px);font-weight:700;color:var(--tx);line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:5px}
.dc-author{font-size:12px;color:var(--tx-l);margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dc-meta{font-size:11px;color:var(--tx-l);margin-bottom:10px}
.dc-actions{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.dc-btn{padding:7px;border-radius:var(--r1);font-size:11px;font-weight:700;cursor:pointer;text-align:center;transition:all var(--t);border:none;width:100%;}
.dc-see{background:var(--cream);border:1px solid var(--bdr);color:var(--tx-m)}.dc-see:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.dc-read{background:var(--cr);color:#fff;width: 100%;}.dc-read:hover{background:var(--cr-dk)}
@media(max-width:860px){.sidebar{display:none}}
@media(max-width:600px){.docs-grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>
<div class="toast" id="toast"></div>
<?= pubNav('biblioteca') ?>

<!-- HERO -->
<section class="hero-cat">
  <div class="hc-inner">
    <!-- <div class="hc-ico"><i class="fa fa-book" style="color: var(--cr-dk)"></i></div> -->
    <div>
      <div class="hc-breadcrumb">
        <a href="petropub-dashboard.php">Home</a> ›
        <a href="biblioteca-pub.php">Biblioteca</a> ›
        <span style="color:#fff"><?=$slug?></span>
      </div>
      <h1 class="hc-title"><?=$slug?></h1>
      <p class="hc-sub">Documentos académicos e científicos desta área</p>
      <div class="hc-count"><i class="fa fa-file"></i> <?=$total?> documento<?=$total!=1?'s':''?> publicado<?=$total!=1?'s':''?></div>
    </div>
  </div>
</section>

<div class="layout">
  <!-- SIDEBAR: other categories -->
  <aside class="sidebar">
    <div class="sb-head">Outras Categorias</div>
    <?php foreach ($allCats as $c): ?>
    <a href="category.php?categoria=<?=$c['name']?>" class="cat-link <?=$c['name']===$slug?'on':''?>">
      <span><?=h($c['icon'])?></span>
      <span style="flex:1"><?=$c['name']?></span>
      <span class="cat-cnt"><?=$catCounts[$c['id']]??0?></span>
    </a>
    <?php endforeach; ?>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <!-- TOOLBAR -->
    <form method="GET" action="">
      <input type="hidden" name="categoria" value="<?=$slug?>">
      <div class="toolbar">
        <div class="tb-search">
          <span class="s-ico"><i class="fa fa-search"></i></span>
          <input class="tb-s-input" type="text" name="q" value="<?=$q?>" placeholder="Pesquisar em <?=$slug?>…">
        </div>
        <select class="sort-sel" name="sort" onchange="this.form.submit()">
          <option value="recent" <?=$sort==='recent'?'selected':''?>>Mais recentes</option>
          <option value="title"  <?=$sort==='title' ?'selected':''?>>Título A→Z</option>
        </select>
        <button type="submit" class="btn btn-cr btn-sm"><i class="fa fa-search"></i></button>
        <?php if($q): ?><a href="category.php?categoria=<?=$slug?>" class="btn btn-gh btn-sm">✕</a><?php endif; ?>
      </div>
    </form>

    <div style="font-size:13px;color:var(--tx-l);margin-bottom:16px">
      <?php if($q): ?>
      Resultados para <strong style="color:var(--tx)">"<?=h($q)?>"</strong> em <strong style="color:var(--cr)"><?=$slug?></strong> — <?=$total?> documento<?=$total!=1?'s':''?>
      <?php else: ?>
      <strong style="color:var(--tx)"><?=$total?></strong> documento<?=$total!=1?'s':''?> em <strong style="color:var(--cr)"><?=$slug?></strong>
      <?php endif; ?>
    </div>

    <?php if(empty($docs)): ?>
    <div class="empty-state">
      <div class="es-ico"><i class="fa fa-search"></i></div>
      <div class="es-title">Nenhum documento encontrado</div>
      <div class="es-sub">Tente outros termos de pesquisa.</div>
      <a href="categoria.php?categoria=<?=h($slug)?>" class="btn btn-cr">Limpar pesquisa</a>
    </div>
    <?php else: ?>
    <div class="docs-grid">
      <?php foreach ($docs as $i => $d):   
      $authors = json_decode($d['authors']);
      $authors_list = explode(",", $authors);
      ?>
      <div class="doc-card" style="animation-delay:<?=$i*.045?>s">
        <div class="dc-thumb" style="background:<?=bgForIndex($i)?>">
          <!-- <i class="fa fa-folder" style="color: var(--cr-dk)"></i> -->
           <img src="../../uploads/documents/cover/<?=$d['file_cover']?>" alt="<?=$d['title']?>" class="dc-thumb-img">
        </div>
        <div class="dc-body">
          <div class="dc-title"><?=h($d['title'])?></div>
          <div class="dc-author"><?=arrayForString($authors_list)?></div>
          <div class="dc-meta"><?=$d['created_at']?date('Y',strtotime($d['created_at'])):'—'?> · </div>
          <div class="dc-actions">
            <!-- <button class="dc-btn dc-see" onclick="showSum(<?=h(json_encode($d,JSON_UNESCAPED_UNICODE))?>)">Ver resumo</button> -->
            <?php
            if (isset($_SESSION['jwt_auth'])) {
              echo '<a href="detail-doc.php?id='.$d['id'].'" class="dc-btn dc-read">📖 Ler</a>';
            } else {
              echo '<button class="dc-btn dc-read" onclick="requireLogin()">📖 Ler</button>';
            }
            ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?= pubPagination($pg, buildCatUrl()) ?>
    <?php endif; ?>
  </main>
</div>

<?= pubFooter() ?>
<script>
function showSum(doc){
    const ov=document.createElement('div');
    ov.style.cssText='position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:800;display:flex;align-items:center;justify-content:center;padding:20px;animation:fadeUp .25s ease';
    ov.onclick=function(e){if(e.target===ov)ov.remove();};
    ov.innerHTML=`<div style="background:#fff;border-radius:20px;max-width:520px;width:100%;max-height:calc(100vh - 40px);overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.22)">
      <div style="padding:20px 24px;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));border-radius:20px 20px 0 0;position:relative">
        <div style="font-family:'Arial',serif;font-size:18px;font-weight:700;color:#fff;line-height:1.3">${doc.title}</div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:6px">👤 ${doc.authors}</div>
        <button onclick="this.closest('div[style]').remove()" style="position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer">✕</button>
      </div>
      <div style="padding:20px 24px">
        <p style="font-size:14px;color:var(--tx-m);line-height:1.7;margin-bottom:14px">${doc.summary||'Resumo não disponível.'}</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px">
          ${[['Ano',doc.created_at?(doc.created_at.substring(0,4)):'—'],['Tamanho',doc.file_size||'—']].map(([l,v])=>`<div style="background:var(--cream);border:1px solid var(--bdr);border-radius:10px;padding:10px 13px"><div style="font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px">${l}</div><div style="font-size:13px;font-weight:600;color:var(--tx)">${v}</div></div>`).join('')}
        </div>
        <button class="btn btn-cr" onclick="requireLogin()">📖 Ler completo 🔒</button>
      </div>
    </div>`;
    document.body.appendChild(ov);
}
function requireLogin(){showToast('🔐 Faça login para aceder ao documento completo');}
</script>
</body>
</html>
