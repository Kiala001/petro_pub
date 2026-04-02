<?php
require_once 'includes.php';


$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: list-opportunities.php'); exit; }

// fetch and increment views
$stmt = $db->prepare("SELECT * FROM opportunities WHERE id=? AND is_approved=1 AND is_active=1");
$stmt->execute([$id]); $opp = $stmt->fetch();
if (!$opp) { header('Location: list-opportunities.php'); exit; }

$db->prepare("UPDATE opportunities SET views=views+1 WHERE id=?")->execute([$id]);

// related (same type, limit 4)
$relStmt = $db->prepare("SELECT * FROM opportunities WHERE type=? AND id!=? AND is_approved=1 AND is_active=1 ORDER BY created_at DESC LIMIT 4");
$relStmt->execute([$opp['type'],$id]); $related = $relStmt->fetchAll();

function typeCls(string $t): string { return match($t){'Curso'=>'t-curso','Equipamento'=>'t-equip','Evento'=>'t-evento','Vaga'=>'t-vaga',default=>'t-curso'}; }
$typeColors = ['Curso'=>'var(--inf)','Equipamento'=>'var(--gd-dk)','Evento'=>'var(--ok)','Vaga'=>'var(--er)'];
$typeColor  = $typeColors[$opp['type']] ?? 'var(--cr)';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — <?=h($opp['title'])?></title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= publicCss() ?>
<style>
.page-wrap{max-width:1100px;margin:0 auto;padding:clamp(20px,4vw,40px) clamp(14px,4vw,40px)}
.content-grid{display:grid;grid-template-columns:1fr 340px;gap:clamp(20px,3vw,32px);align-items:start}
/* HEADER CARD */
.opp-header{border-radius:var(--r4);overflow:hidden;margin-bottom:clamp(18px,3vw,26px);box-shadow:var(--sh1)}
.oh-banner{height:clamp(140px,18vw,220px);display:flex;align-items:center;justify-content:center;font-size:clamp(52px,8vw,80px);position:relative;overflow:hidden}
.oh-banner::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.35),transparent)}
.oh-featured{position:absolute;top:14px;left:14px;background:var(--gd);color:var(--cr-dk);font-size:11px;font-weight:800;padding:4px 12px;border-radius:100px;z-index:1}
.oh-body{background:#fff;padding:clamp(18px,2.5vw,28px)}
.oh-type-row{display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap}
.oh-type{font-size:11px;font-weight:700;padding:4px 12px;border-radius:100px}
.t-curso{background:var(--inf-bg);color:var(--inf)}.t-equip{background:var(--gd-bg);color:var(--gd-dk)}.t-evento{background:var(--ok-bg);color:var(--ok)}.t-vaga{background:var(--er-bg);color:var(--er)}
.oh-title{font-family:'Arial',serif;font-size:clamp(20px,3.5vw,32px);font-weight:900;color:var(--tx);line-height:1.25;margin-bottom:10px}
.oh-source{font-size:14px;color:var(--tx-m);margin-bottom:16px;display:flex;align-items:center;gap:7px}
.oh-meta{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;padding-top:16px;border-top:1px solid var(--bdr)}
.om-item{background:var(--cream);border:1px solid var(--bdr);border-radius:var(--r2);padding:11px 14px}
.om-lbl{font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px}
.om-val{font-size:14px;font-weight:600;color:var(--tx)}
/* DESCRIPTION */
.desc-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);padding:clamp(20px,3vw,28px);box-shadow:var(--sh0);margin-bottom:clamp(16px,2.5vw,22px);animation:fadeUp .4s ease both}
.dc-section-title{font-family:'Arial',serif;font-size:clamp(16px,2vw,19px);font-weight:700;color:var(--cr-dk);margin-bottom:14px;display:flex;align-items:center;gap:8px}
.desc-text{font-size:clamp(13px,1.4vw,15px);color:var(--tx-m);line-height:1.75}
/* RIGHT PANEL */
.right-sticky{position:sticky;top:calc(var(--nav-h) + 16px)}
.action-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);overflow:hidden;box-shadow:var(--sh1);margin-bottom:clamp(14px,2vw,18px);animation:fadeUp .4s ease .08s both}
.ac-head{padding:16px 20px;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff}
.ac-head h3{font-family:'Arial',serif;font-size:16px;font-weight:700}
.ac-head p{font-size:12px;color:rgba(255,255,255,.60);margin-top:3px}
.ac-body{padding:18px 20px}
.ac-cta{width:100%;padding:13px;border-radius:var(--r3);background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff;border:none;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;margin-bottom:8px;box-shadow:0 6px 18px rgba(107,16,32,.28);text-decoration:none}
.ac-cta:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(107,16,32,.38)}
.ac-secondary{width:100%;padding:10px;border-radius:var(--r2);background:var(--cream);border:1.5px solid var(--bdr);color:var(--tx-m);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:all var(--t);text-decoration:none}
.ac-secondary:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.ac-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:14px}
.ac-stat{background:var(--cream);border:1px solid var(--bdr);border-radius:var(--r2);padding:10px;text-align:center}
.ac-stat-n{font-family:'Arial',serif;font-size:18px;font-weight:700;color:var(--cr)}
.ac-stat-l{font-size:11px;color:var(--tx-l);margin-top:2px}
/* RELATED */
.rel-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:14px 18px;margin-bottom:10px;display:flex;gap:12px;cursor:pointer;transition:all var(--t);text-decoration:none}
.rel-card:hover{box-shadow:var(--sh1);border-color:rgba(107,16,32,.18);transform:translateX(4px)}
.rel-thumb{width:44px;height:44px;border-radius:var(--r2);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.rel-title{font-size:13px;font-weight:700;color:var(--tx);line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:3px}
.rel-src{font-size:11px;color:var(--tx-l)}
/* BREADCRUMB */
.breadcrumb{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--tx-l);margin-bottom:18px;flex-wrap:wrap}
.breadcrumb a{color:var(--cr);font-weight:600;text-decoration:none}.breadcrumb a:hover{text-decoration:underline}
@media(max-width:860px){.content-grid{grid-template-columns:1fr}.right-sticky{position:static}}
@media(max-width:600px){.oh-meta{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="toast" id="toast"></div>
<?= pubNav('oportunidades') ?>

<div class="page-wrap">
  <div class="breadcrumb">
    <a href="index.php">Home</a> ›
    <a href="list-opportunities.php">Oportunidades</a> ›
    <a href="list-opportunities.php?type=<?=urlencode($opp['type'])?>"><?=h($opp['type'])?></a> ›
    <span style="color:var(--tx-m)"><?=mb_substr(h($opp['title']),0,40)?><?=mb_strlen($opp['title'])>40?'…':''?></span>
  </div>

  <div class="content-grid">
    <!-- LEFT -->
    <div>
      <!-- HEADER -->
      <div class="opp-header">
        <!-- <div class="oh-banner" style="background:linear-gradient(135deg,<?=h($opp['grad_start'])?>,<?=h($opp['grad_end'])?>)">
          <span style="font-size:clamp(52px,8vw,80px);position:relative;z-index:1"><?=h($opp['icon'])?></span>
        </div> -->
        <div class="oh-body">
          <?php if($opp['is_featured']): ?><div class="oh-featured">⭐ Destaque</div><?php endif; ?>
          <div class="oh-type-row">       
            <span class="oh-type <?=typeCls($opp['type'])?>"><?=h($opp['type'])?></span>
            <span style="font-size:11px;color:var(--tx-l)"><i class="fa fa-eye"></i> <?=number_format($opp['views']+1)?> visualizações</span>
            <span style="font-size:11px;color:var(--tx-l)"><i class="fa fa-calendar"></i> <?=date('d/m/Y',strtotime($opp['created_at']))?></span>
          </div>
          <h1 class="oh-title"><?=h($opp['title'])?></h1>
          <?php if($opp['source']): ?>
          <div class="oh-source"><i class="fa fa-home"></i> <strong><?=h($opp['source'])?></strong></div>
          <?php endif; ?>
          <div class="oh-meta">
            <?php if($opp['location']): ?><div class="om-item"><div class="om-lbl"><i class="fa fa-map"></i> Localização</div><div class="om-val"><?=h($opp['location'])?></div></div><?php endif; ?>
            <?php if($opp['event_date']): ?><div class="om-item"><div class="om-lbl"><i class="fa fa-calendar"></i> Data</div><div class="om-val"><?=date('d/m/Y',strtotime($opp['event_date']))?></div></div><?php endif; ?>
            <div class="om-item"><div class="om-lbl"><i class="fa fa-sale"></i> Tipo</div><div class="om-val"><?=h($opp['type'])?></div></div>
            <div class="om-item"><div class="om-lbl"><i class="fa fa-eye"></i> Visualizações</div><div class="om-val"><?=number_format($opp['views']+1)?></div></div>
          </div>
        </div>
      </div>

      <!-- DESCRIPTION -->
      <div class="desc-card">
        <div class="dc-section-title"><i class="fa fa-list"></i> Descrição detalhada</div>
        <div class="desc-text">
          <?=nl2br(h($opp['description'] ?? 'Sem descrição disponível para esta oportunidade.'))?>
        </div>
      </div>

      <!-- RELATED -->
      <?php if(!empty($related)): ?>
      <div class="desc-card" style="animation-delay:.12s">
        <div class="dc-section-title"><i class="fa fa-link"></i> Oportunidades relacionadas</div>
        <?php foreach($related as $r): ?>
        <a href="detail-opportunity.php?id=<?=$r['id']?>" class="rel-card">
          <div class="rel-thumb" style="background:linear-gradient(135deg,<?=h($r['grad_start'])?>,<?=h($r['grad_end'])?>)"><?=h($r['icon'])?></div>
          <div>
            <div class="rel-title"><?=h($r['title'])?></div>
            <div class="rel-src"><i class="fa fa-home"></i> <?=h($r['source']??'—')?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT -->
    <div>
      <div class="right-sticky">
        <!-- ACTION CARD -->
        <div class="action-card">
          <div class="ac-head">
            <h3>Candidatar / Aceder</h3>
            <p>Clique para aceder a esta oportunidade</p>
          </div>
          <div class="ac-body">
            <?php if($opp['link_url']): ?>
            <a href="<?=h($opp['link_url'])?>" target="_blank" rel="noopener" class="ac-cta">
              <i class="fa fa-link"></i> Aceder à oportunidade →
            </a>
            <?php else: ?>
            <a href="contact.php" class="ac-cta">
              Contacto
            </a>
            <?php endif; ?>
            <a href="list-opportunities.php?type=<?=urlencode($opp['type'])?>" class="ac-secondary">
              ← Ver mais <?=h($opp['type'])?>s
            </a>
            <div class="ac-stats">
              <div class="ac-stat"><div class="ac-stat-n"><?=number_format($opp['views']+1)?></div><div class="ac-stat-l">Visualizações</div></div>
              <div class="ac-stat"><div class="ac-stat-n"><?=count($related)?></div><div class="ac-stat-l">Relacionadas</div></div>
            </div>
          </div>
        </div>

        <!-- INFO CARD -->
        <div style="background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:16px 18px;box-shadow:var(--sh0);animation:fadeUp .4s ease .12s both">
          <div style="font-size:13px;font-weight:700;color:var(--tx);margin-bottom:12px;display:flex;align-items:center;gap:6px">ℹ<i class="fa fa-info"></i> Informações</div>
          <?php
          $infos = [
            ['Tipo', $opp['type']],
            ['Fonte', $opp['source'] ?? '—'],
            ['Localização', $opp['location'] ?? '—'],
            ['Data evento', $opp['event_date'] ? date('d/m/Y',strtotime($opp['event_date'])) : '—'],
            ['Publicado em', date('d/m/Y',strtotime($opp['created_at']))],
          ];
          foreach ($infos as [$l,$v]): ?>
          <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--bdr2);font-size:13px">
            <span style="color:var(--tx-l)"><?=$l?></span>
            <span style="font-weight:600;color:var(--tx);text-align:right;max-width:55%"><?=h($v)?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- SHARE -->
        <div style="background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:14px 18px;box-shadow:var(--sh0);margin-top:14px;animation:fadeUp .4s ease .16s both">
          <div style="font-size:13px;font-weight:700;color:var(--tx);margin-bottom:10px">Partilhar</div>
          <div style="display:flex;gap:8px">
            <button class="btn btn-gh btn-sm" onclick="copyLink()" style="flex:1;justify-content:center"><i class="fa fa-link"></i> Copiar link</button>
            <button class="btn btn-gh btn-sm" onclick="shareWhatsApp()" style="flex:1;justify-content:center">📱   WhatsApp</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= pubFooter() ?>
<script>
function copyLink(){navigator.clipboard.writeText(window.location.href).then(()=>showToast('Link copiado!','t-ok'));}
function shareWhatsApp(){window.open('https://wa.me/?text='+encodeURIComponent('<?=addslashes(h($opp['title']))?> — '+window.location.href));}
</script>
</body>
</html>
