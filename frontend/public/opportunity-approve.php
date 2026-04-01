<?php
require_once 'includes.php';
if (!isset($_SESSION['jwt_auth'])) {
    header('Location: index.php');
    exit;
}


/* ─── API ─── */
if (isset($_GET['api'])) {
    $act = $_POST['action'] ?? $_GET['api'];
    $id  = (int)($_POST['id'] ?? 0);

    if ($act === 'approve') {
        $db->prepare("UPDATE opportunities SET is_approved=1, is_active=1 WHERE id=?")->execute([$id]);
        // create notification for submitter
        $opp = $db->prepare("SELECT title,user_id FROM opportunities WHERE id=?");
        $opp->execute([$id]); $o = $opp->fetch();
        if ($o && $o['user_id']) {
            $db->prepare("INSERT INTO notifications (user_id,role,type,title,message,icon)
                VALUES (?,\'user\',\'success\',\'Oportunidade aprovada!\',?,\'✅\')")
                ->execute([$o['user_id'], "A sua oportunidade \"{$o['title']}\" foi aprovada e publicada no portal."]);
        }
        jsonResponse(['ok'=>true,'msg'=>'Aprovada e publicada!']);
    }
    if ($act === 'reject') {
        $reason = sanitize($_POST['reason'] ?? '');
        $db->prepare("UPDATE opportunities SET is_approved=0, is_active=0 WHERE id=?")->execute([$id]);
        $opp = $db->prepare("SELECT title,user_id FROM opportunities WHERE id=?");
        $opp->execute([$id]); $o = $opp->fetch();
        if ($o && $o['user_id']) {
            $db->prepare("INSERT INTO notifications (user_id,role,type,title,message,icon)
                VALUES (?,\'user\',\'warning\',\'Oportunidade não aprovada\',?,\'⚠️\')")
                ->execute([$o['user_id'], "A sua oportunidade \"{$o['title']}\" não foi aprovada. Motivo: $reason"]);
        }
        jsonResponse(['ok'=>true,'msg'=>'Rejeitada.']);
    }
    if ($act === 'toggle') {
        $col = in_array($_POST['col']??'',['is_active','is_featured']) ? $_POST['col'] : 'is_active';
        $val = (int)(bool)($_POST['value']??0);
        $db->prepare("UPDATE opportunities SET $col=? WHERE id=?")->execute([$val,$id]);
        jsonResponse(['ok'=>true]);
    }
    if ($act === 'delete') {
        $db->prepare("DELETE FROM opportunities WHERE id=?")->execute([$id]);
        jsonResponse(['ok'=>true,'msg'=>'Eliminada.']);
    }
    jsonResponse(['ok'=>false],400);
}

/* ─── LOAD ─── */
$tab    = sanitize($_GET['tab'] ?? 'all');    
$type   = sanitize($_GET['type'] ?? '');
$search = sanitize($_GET['q'] ?? '');
$page   = max(1,(int)($_GET['page']??1));
$perPage= 10;

$where  = ['1=1'];
$params = [];

if ($tab === 'pending')  { $where[]='is_approved=0'; }
if ($tab === 'approved') { $where[]='is_approved=1'; }
if ($tab === 'rejected') { $where[]="is_approved=0 AND is_active=0 AND user_id IS NOT NULL"; }
if ($type) { $where[]='type=:type'; $params[':type']=$type; }
if ($search) { $where[]='(title LIKE :q OR source LIKE :q OR description LIKE :q)'; $params[':q']="%$search%"; }


$wSql = implode(' AND ',$where);
$cSt = $db->prepare("SELECT COUNT(*) FROM opportunities WHERE $wSql");
$cSt->execute($params); $total=(int)$cSt->fetchColumn();
$pg = paginate($total,$page,$perPage);

$dSt = $db->prepare("SELECT * FROM opportunities WHERE $wSql ORDER BY created_at DESC LIMIT :l OFFSET :o");
$dSt->bindValue(':l',$pg['per_page'],PDO::PARAM_INT);
$dSt->bindValue(':o',$pg['offset'],PDO::PARAM_INT);
foreach($params as $k=>$v) $dSt->bindValue($k,$v);
$dSt->execute(); 
$rows = $dSt->fetchAll();

// counts for tabs
$pendingCount  = (int)$db->query("SELECT COUNT(*) FROM opportunities WHERE is_approved=0")->fetchColumn();
$approvedCount = (int)$db->query("SELECT COUNT(*) FROM opportunities WHERE is_approved=1")->fetchColumn();
$typeCounts    = $db->query("SELECT type,COUNT(*) FROM opportunities GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);

function typeClass(string $t): string {
  return match($t){'Curso'=>'bb','Equipamento'=>'bgd','Evento'=>'bg','Vaga'=>'br',default=>'bw'};
}


$jwt = $_SESSION['jwt_auth'];
$userName = $_SESSION['user_name'] ?? 'Usuário';
$userEmail = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub Admin — Aprovações</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= baseCss() ?>
<style>
.opp-table{width:100%;border-collapse:collapse;font-size:13px}
.opp-table thead tr{background:linear-gradient(to right,var(--warm),#fff);border-bottom:2px solid var(--bdr)}
.opp-table th{padding:11px 14px;text-align:left;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;white-space:nowrap}
.opp-table tbody tr{border-bottom:1px solid var(--bdr2);transition:background var(--t)}
.opp-table tbody tr:last-child{border-bottom:none}
.opp-table tbody tr:hover{background:var(--warm)}
.opp-table td{padding:12px 14px;vertical-align:middle}
.oc-thumb{width:42px;height:42px;border-radius:var(--r2);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.u-cell{display:flex;align-items:center;gap:10px}
.status-chip{font-size:10px;font-weight:700;padding:3px 9px;border-radius:100px}
.sc-pend{background:var(--wn-bg);color:var(--wn);border:1px solid var(--wn-bdr)}
.sc-apro{background:var(--ok-bg);color:var(--ok);border:1px solid var(--ok-bdr)}
.sc-reje{background:var(--er-bg);color:var(--er);border:1px solid var(--er-bdr)}
.act-cell{display:flex;align-items:center;gap:6px;flex-wrap:nowrap}
.tab-nav{display:flex;gap:0;border-bottom:2px solid var(--bdr);margin-bottom:20px}
.tab-link{padding:10px 20px;font-size:13px;font-weight:600;color:var(--tx-l);text-decoration:none;border-bottom:2.5px solid transparent;margin-bottom:-2px;transition:all var(--t);white-space:nowrap;display:flex;align-items:center;gap:6px}
.tab-link:hover{color:var(--cr)}.tab-link.on{color:var(--cr);border-bottom-color:var(--cr);font-weight:700}
.tab-badge{background:var(--er);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:100px;min-width:18px;text-align:center}
.tab-badge.ok{background:var(--ok)}.tab-badge.blue{background:var(--inf)}
.detail-row{background:var(--cream)!important}
.detail-cell{padding:20px!important}
.detail-inner{padding:14px 20px;font-size:13px;color:var(--tx-m);line-height:1.65;display:none;animation:fadeUp .25s ease both}
.detail-inner.open{display:block}
@media(max-width:767px){.hide-mob{display:none}}
</style>
</head>
<body>
<div class='toast t-def' id='toast'></div>
<div class='sb-ov' id='sb-ov' onclick='closeSB()'></div>

<!-- MODAL: REJECT -->
<div class='overlay' id='modal-reject' onclick='ovClose(event,"modal-reject")'>
  <div class='modal modal-sm'>
    <div class='m-hd mh-er'><h3><i class="fa fa-close"></i> Rejeitar Oportunidade</h3><p>O autor será notificado</p><button class='m-close' onclick='closeModal("modal-reject")'>✕</button></div>
    <div class='m-body'>
      <input type='hidden' id='reject-id'>
      <div class='f-group'>
        <label class='f-lbl'>Motivo da rejeição <span style='color:var(--cr)'>*</span></label>
        <textarea class='f-ta' id='reject-reason' placeholder='Explique o motivo da rejeição para o autor…'></textarea>
      </div>
    </div>
    <div class='m-foot'>
      <button class='btn btn-gh' onclick='closeModal("modal-reject")'>Cancelar</button>
      <button class='btn btn-er' onclick='confirmReject()'><i class="fa fa-close"></i> Confirmar Rejeição</button>
    </div>
  </div>
</div>

<div class='app'>
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
<div class='main'>
  <div class='topbar'>
    <div class='tb-l'>
      <button class='tb-ham' onclick='openSB()'>☰</button>
      <div>
        <div class='tb-bc'><a href='admin-seccoes.php'>Portal</a> / Aprovações</div>
        <div class='tb-title'><i class="fa fa-check"></i> Aprovação de Oportunidades</div>
      </div>
    </div>
    <div class='tb-r'>
      <div class='admin-badge'><i class="fa fa-cog"></i> Admin</div>
      <a href='admin-notificacoes.php' class='notif-btn' style='position:relative'>🔔<span class='notif-dot'></span></a>
      <div class='ava ava-dk' style='width:36px;height:36px;font-size:12px'>AD</div>
    </div>
  </div>
  <div class='page-wrap'>

    <!-- STATS -->
    <div class='stats-row' style='grid-template-columns:repeat(4,1fr)'>
      <div class='sc' style='--i:1'><div class='sc-top'><div class='sc-ico si-wn'><i class="fa fa-warning"></i></div><span class='sc-pill sp-wn'>Urgente</span></div><div class='sc-num'><?= $pendingCount ?></div><div class='sc-lbl'>Aguardam aprovação</div></div>
      <div class='sc' style='--i:2'><div class='sc-top'><div class='sc-ico si-ok'><i class="fa fa-check"></i></div><span class='sc-pill sp-ok'>Publicados</span></div><div class='sc-num'><?= $approvedCount ?></div><div class='sc-lbl'>Aprovados e activos</div></div>
      <div class='sc' style='--i:3'><div class='sc-top'><div class='sc-ico si-inf'><i class="fa fa-send" style="color: var(--cr)"></i></div><span class='sc-pill' style='background:var(--inf-bg);color:var(--inf)'>Total</span></div><div class='sc-num'><?= array_sum($typeCounts) ?></div><div class='sc-lbl'>Total submetidos</div></div>
      <div class='sc' style='--i:4'><div class='sc-top'><div class='sc-ico si-gd'>⛽</div><span class='sc-pill sp-gd'>Tipos</span></div><div class='sc-num'><?= count($typeCounts) ?></div><div class='sc-lbl'>Tipos de oportunidades</div></div>
    </div>

    <!-- TAB NAV -->
    <div class='tab-nav'>
      <a href='?tab=pending&type=<?= urlencode($type) ?>&q=<?= urlencode($search) ?>' class='tab-link <?= $tab==='pending'?'on':'' ?>'>⏳ Pendentes <span class='tab-badge'><?= $pendingCount ?></span></a>
      <a href='?tab=approved&type=<?= urlencode($type) ?>&q=<?= urlencode($search) ?>' class='tab-link <?= $tab==='approved'?'on':'' ?>'><i class="fa fa-check" style="color: green;"></i> Aprovados <span class='tab-badge ok'><?= $approvedCount ?></span></a>
      <a href='?tab=all&type=<?= urlencode($type) ?>&q=<?= urlencode($search) ?>' class='tab-link <?= $tab==='all'?'on':'' ?>'>📦 Todos <span class='tab-badge blue'><?= array_sum($typeCounts) ?></span></a>
    </div>

    <!-- TOOLBAR -->
    <form method='GET' action=''>
      <input type='hidden' name='tab' value='<?= sanitize($tab) ?>'>
      <div class='toolbar'>
        <div class='search-wrap'>
          <span class='s-ico'><i class="fa fa-search"></i></span>
          <input type='text' name='q' value='<?= sanitize($search) ?>' placeholder='Pesquisar por título, fonte…'>
        </div>
        <select class='f-sel' name='type' onchange='this.form.submit()'>
          <option value=''>Todos os tipos</option>
          <?php foreach(['Curso','Equipamento','Evento','Vaga'] as $t): ?>
          <option value='<?= $t ?>' <?= $type===$t?'selected':'' ?>><?= $t ?> (<?= $typeCounts[$t]??0 ?>)</option>
          <?php endforeach; ?>
        </select>
        <button type='submit' class='btn btn-cr btn-sm'><i class="fa fa-search"></i> Filtrar</button>
        <?php if($search||$type): ?><a href='?tab=<?= sanitize($tab) ?>&type=<?= urlencode($type) ?>&q=<?= urlencode($search) ?>' class='btn btn-gh btn-sm'>✕ Limpar</a><?php endif; ?>
        <span style='font-size:13px;color:var(--tx-l);margin-left:auto'><?= $total ?> resultado<?= $total!=1?'s':'' ?></span>
      </div>
    </form>

    <!-- TABLE -->
    <?php if(empty($rows)): ?>
    <div class='empty'>
      <div class='empty-ico'><?= $tab==='pending'?'🎉':'🔍' ?></div>
      <div class='empty-title'><?= $tab==='pending'?'Nenhuma aprovação pendente!':'Nenhum resultado' ?></div>
      <p style='font-size:14px;color:var(--tx-l);margin-top:6px'><?= $tab==='pending'?'Todas as oportunidades foram processadas.':'Tente ajustar os filtros.' ?></p>
    </div>
    <?php else: ?>
    <div class='card'>
      <div class='card-body' style='padding:0;overflow-x:auto'>
        <table class='opp-table'>
          <thead>
            <tr>
              <th style='width:44px'>#</th>
              <th>Oportunidade</th>
              <th class='hide-mob'>Tipo</th>
              <th class='hide-mob'>Fonte</th>
              <th class='hide-mob'>Submetido</th>
              <th>Estado</th>
              <th>Acções</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($rows as $i=>$r):
              $isApproved = (int)$r['is_approved'] === 1;
              $isRejected = (int)$r['is_approved'] === 0 && (int)($r['is_active'] ?? 1) === 0;
              if ($isApproved) {
                  $statusLbl = '<span class="status-chip sc-apro"><i class="fa fa-check"></i> Aprovado</span>';
              } elseif ($isRejected) {
                  $statusLbl = '<span class="status-chip sc-reje"><i class="fa fa-close"></i> Rejeitado</span>';
              } else {
                  $statusLbl = '<span class="status-chip sc-pend">⏳ Pendente</span>';
              }
              $rowJson    = sanitize(json_encode($r,JSON_UNESCAPED_UNICODE));
            ?>
            <tr id='row-<?= $r['id'] ?>'>
              <td style='font-size:12px;color:var(--tx-l);text-align:center'><?= $pg['offset']+$i+1 ?></td>
              <td>
                <div class='u-cell'>
                  <div>
                    <div style='font-size:13px;font-weight:700;color:var(--tx);margin-bottom:2px'><?= $r['title'] ?></div>
                    <div style='font-size:11px;color:var(--tx-l)'><?= $r['description']??'' ?></div>
                  </div>
                </div>
              </td>
              <td class='hide-mob'><span class='badge <?= typeClass($r['type']) ?>'><?= $r['type'] ?></span></td>
              <td class='hide-mob' style='font-size:12px;color:var(--tx-m)'><?= $r['source']??'—' ?></td>
              <td class='hide-mob' style='font-size:12px;color:var(--tx-l)'><?= $r['created_at'] ? date('d/m/Y',strtotime($r['created_at'])) : '—' ?></td>
              <td><?= $statusLbl ?></td>
              <td>
                <div class='act-cell'>
                  <?php if($isRejected): ?>
                    <button class='btn btn-gh btn-xs' onclick="approveOpp(<?= $r['id'] ?>,this,true)"><i class="fa fa-reload"></i> Revogar</button>
                  <?php elseif(!$isApproved): ?>
                      <button class='btn btn-ok btn-xs' onclick="approveOpp(<?= $r['id'] ?>,this)"><i class="fa fa-check"></i> </button>
                      <button class='btn btn-er btn-xs' onclick="openReject(<?= $r['id'] ?>,<?= $rowJson ?>)"><i class="fa fa-close"></i> Rejeitar</button>
                  <?php else: ?>
                    <button class='btn btn-gh btn-xs' onclick="approveOpp(<?= $r['id'] ?>,this,true)"><i class="fa fa-reload"></i> Revogar</button>
                  <?php endif; ?>
                    <button class='btn btn-gh btn-xs' onclick="toggleDetail(<?= $r['id'] ?>)"><i class="fa fa-eye"></i></button>
                    <button class='btn btn-er btn-xs' onclick="deleteOpp(<?= $r['id'] ?>)"><i class="fa fa-trash"></i></button>
                </div>
              </td>
            </tr>
            <tr class='detail-row' id='detail-row-<?= $r['id'] ?>'>
              <td colspan='7' class='detail-cell'>
                <div class='detail-inner' id='detail-<?= $r['id'] ?>'>
                  <div style='display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-bottom:10px'>
                    <div><div style='font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px'>Localização</div><div style='font-size:13px;font-weight:600'><?= $r['location']??'—' ?></div></div>
                    <div><div style='font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px'>Data do evento</div><div style='font-size:13px;font-weight:600'><?= $r['event_date']?date('d/m/Y',strtotime($r['event_date'])):'—' ?></div></div>
                    <div><div style='font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px'>Visualizações</div><div style='font-size:13px;font-weight:600'><?= number_format($r['views']) ?></div></div>
                    <div><div style='font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px'>Destaque</div><div><?= $r['is_featured']?'<span class="badge bg">Sim</span>':'<span class="badge bw">Não</span>' ?></div></div>
                  </div>
                  <?php if($r['link_url']): ?>
                  <a href='<?= sanitize($r['link_url']) ?>' target='_blank' style='font-size:12px;color:var(--cr);font-weight:600'>🔗 <?= h($r['link_url']) ?></a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class='card-foot'>
        <span style='font-size:12px;color:var(--tx-l)'>Página <?= $pg['page'] ?> de <?= $pg['pages'] ?></span>
        <?= paginationHtml($pg,'?tab='.urlencode($tab).'&type='.urlencode($type).'&q='.urlencode($search)) ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
</div>

<script>
async function approveOpp(id, btn, revoke=false) {
    btn.disabled = true;
    const origText = btn.innerHTML;
    btn.innerHTML = '⌛…';
    const action = revoke ? 'reject' : 'approve';
    const body   = new URLSearchParams({action, id, reason: 'Revogado pelo administrador'});
    try {
        const res  = await fetch('opportunity-approve.php?api=1', {method:'POST', body});
        const data = await res.json();
        showToast(data.msg || (data.ok ? '✅ Feito!' : '❌ Erro'), data.ok ? 't-ok' : 't-er');
        if (data.ok) {
            const row = document.getElementById('row-' + id);
            if (row) { row.style.opacity = '0.4'; }
            setTimeout(() => location.reload(), 700);
        } else {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    } catch(e) {
        btn.disabled = false;
        btn.innerHTML = origText;
        showToast('❌ Erro de rede', 't-er');
    }
}

let rejectId=null;
function openReject(id,row){
    rejectId=id;
    document.getElementById('reject-id').value=id;
    document.getElementById('reject-reason').value='';
    openModal('modal-reject');
}
async function confirmReject(){
    const reason=document.getElementById('reject-reason').value.trim();
    if(!reason){showToast('⚠️ Introduza o motivo','t-wn');return;}
    const res  = await fetch('opportunity-approve.php?api=1',{method:'POST',body:new URLSearchParams({action:'reject',id:rejectId,reason})});
    const data = await res.json();
    closeModal('modal-reject');
    showToast(data.msg||'Rejeitada',data.ok?'t-er':'t-er');
    if(data.ok) setTimeout(()=>location.reload(),600);
}

async function deleteOpp(id){
    if(!confirm('Eliminar esta oportunidade definitivamente?')) return;
    const res  = await fetch('opportunity-approve.php?api=1',{method:'POST',body:new URLSearchParams({action:'delete',id})});
    const data = await res.json();
    showToast(data.msg||'✅ Eliminada','t-er');
    if(data.ok){const row=document.getElementById('row-'+id);if(row){row.style.opacity=0;setTimeout(()=>row.remove(),400);}}
}

function toggleDetail(id){
    const d=document.getElementById('detail-'+id);
    d.classList.toggle('open');
}

const sidebar=document.getElementById('sidebar'),sbOv=document.getElementById('sb-ov');
const sbClose=document.getElementById('sb-close'),sbCol=document.getElementById('sb-col');
let collapsed=false;
function checkBP(){
  const w=window.innerWidth;
  if(w<768){sbClose.style.display=sidebar.classList.contains('open')?'flex':'none';sbCol.style.display='none';sidebar.classList.remove('collapsed');}
  else if(w<1200){sbClose.style.display='none';sbCol.style.display='none';sidebar.classList.remove('open');sbOv&&sbOv.classList.remove('open');document.body.style.overflow='';}
  else{sbClose.style.display='none';sbCol.style.display='flex';sbCol.textContent=collapsed?'▶':'◀';}
}
function openSB(){sidebar.classList.add('open');sbOv.style.display='block';setTimeout(()=>sbOv.classList.add('open'),10);sbClose.style.display='flex';document.body.style.overflow='hidden';}
function closeSB(){sidebar.classList.remove('open');sbOv&&sbOv.classList.remove('open');setTimeout(()=>{if(sbOv)sbOv.style.display='none';},300);sbClose.style.display='none';document.body.style.overflow='';}
function toggleCol(){collapsed=!collapsed;sidebar.classList.toggle('collapsed',collapsed);sbCol.textContent=collapsed?'▶':'◀';}
document.querySelectorAll('.nav-i').forEach(i=>i.addEventListener('click',()=>{if(window.innerWidth<768)closeSB();}));
window.addEventListener('resize',checkBP);checkBP();
function showToast(msg,cls='t-def'){const t=document.getElementById('toast');if(!t)return;t.textContent=msg;t.className='toast '+cls;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
function openModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
function ovClose(e,id){if(e.target.id===id)closeModal(id);}
</script>
</body>
</html>
