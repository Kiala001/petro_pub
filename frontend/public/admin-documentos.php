<?php
require_once 'includes.php';

/* ─── API ─── */
if (isset($_GET['api'])) {
    $act = $_POST['action'] ?? '';
    $id  = sanitize($_POST['id'] ?? '');

    if ($act === 'save') {
        $f   = fn($k) => sanitize($_POST[$k] ?? '');
        $isNew = empty($id);
        $status = in_array($f('status'),['PENDENTE','APROVADO','PUBLICADO','AGUARDA_REVISAO','REJEITADO']) ? $f('status') : 'PENDENTE';
        $data = [
            'title'       => $f('title'),
            'authors'     => $f('authors'),
            'advisor'     => $f('advisor') ?: null,
            'course'      => $f('course') ?: null,
            'category_id' => $f('category_id'),
            'summary'     => $f('summary'),
            'keywords'    => $f('keywords'),
            'file_path'   => $f('file_path'),
            'file_size'   => $f('file_size') ?: '0 KB',
            'file_type'   => $f('file_type') ?: null,
            'pub_mode'    => $f('pub_mode') ?: null,
            'status'      => $status,
            'download_link'    => $f('download_link') ?: null,
            'plagiarism_score' => $f('plagiarism_score') !== '' ? (float)$f('plagiarism_score') : null,
            'created_at'  => date('Y-m-d'),
        ];
        if(!$data['title']) jsonResponse(['ok'=>false,'msg'=>'Título obrigatório'],422);
        if(!$data['category_id']) jsonResponse(['ok'=>false,'msg'=>'Categoria obrigatória'],422);
        if ($isNew) {
            $data['id'] = $data['user_id'] = bin2hex(random_bytes(16));
            $data['user_id'] = 'admin-'.substr(bin2hex(random_bytes(8)),0,12);
            $db->prepare("INSERT INTO documents (id,user_id,category_id,title,authors,advisor,course,summary,keywords,file_path,file_size,file_type,pub_mode,status,download_link,plagiarism_score,created_at)
                VALUES (:id,:user_id,:category_id,:title,:authors,:advisor,:course,:summary,:keywords,:file_path,:file_size,:file_type,:pub_mode,:status,:download_link,:plagiarism_score,:created_at)")
                ->execute($data);
            jsonResponse(['ok'=>true,'msg'=>'Documento criado com sucesso!','id'=>$data['id']]);
        } else {
            unset($data['created_at']);
            $data['id'] = $id;
            $db->prepare("UPDATE documents SET category_id=:category_id,title=:title,authors=:authors,advisor=:advisor,course=:course,summary=:summary,keywords=:keywords,file_path=:file_path,file_size=:file_size,file_type=:file_type,pub_mode=:pub_mode,status=:status,download_link=:download_link,plagiarism_score=:plagiarism_score WHERE id=:id")
                ->execute($data);
            jsonResponse(['ok'=>true,'msg'=>'Documento actualizado!','id'=>$id]);
        }
    }
    if ($act === 'status') {
        $status = in_array($_POST['status']??'',['PENDENTE','APROVADO','PUBLICADO','AGUARDA_REVISAO','REJEITADO']) ? $_POST['status'] : 'PENDENTE';
        $db->prepare("UPDATE documents SET status=? WHERE id=?")->execute([$status,$id]);
        jsonResponse(['ok'=>true,'msg'=>"Estado alterado para $status"]);
    }
    if ($act === 'delete') {
        $db->prepare("DELETE FROM documents WHERE id=?")->execute([$id]);
        jsonResponse(['ok'=>true,'msg'=>'Documento eliminado.']);
    }
    if ($act === 'get') {
        $row = $db->prepare("SELECT * FROM documents WHERE id=?");
        $row->execute([$id]); $doc=$row->fetch();
        jsonResponse($doc ?: ['ok'=>false]);
    }
    jsonResponse(['ok'=>false],400);
}

/* ─── LOAD ─── */
$status = sanitize($_GET['status'] ?? '');
$cat    = sanitize($_GET['cat']    ?? '');
$search = sanitize($_GET['q']      ?? '');
$sort   = sanitize($_GET['sort']   ?? 'recent');
$page   = max(1,(int)($_GET['page']??1));
$perPage= 12;

$where=['1=1']; $params=[];
if ($status) { $where[]='d.status=:status'; $params[':status']=$status; }
if ($cat)    { $where[]='d.category_id=:cat'; $params[':cat']=$cat; }
if ($search) { $where[]='(d.title LIKE :q OR d.authors LIKE :q OR d.summary LIKE :q)'; $params[':q']="%$search%"; }
$orderMap=['recent'=>'d.created_at DESC','title'=>'d.title ASC','status'=>'d.status ASC'];
$oSql=$orderMap[$sort]??$orderMap['recent'];
$wSql=implode(' AND ',$where);

$cSt=$db->prepare("SELECT COUNT(*) FROM documents d WHERE $wSql"); $cSt->execute($params); $total=(int)$cSt->fetchColumn();
$pg=paginate($total,$page,$perPage);
$dSt=$db->prepare("SELECT d.*,c.name AS cat_name,c.icon AS cat_icon FROM documents d LEFT JOIN doc_categories c ON c.id=d.category_id WHERE $wSql ORDER BY $oSql LIMIT :l OFFSET :o");
$dSt->bindValue(':l',$pg['per_page'],PDO::PARAM_INT); $dSt->bindValue(':o',$pg['offset'],PDO::PARAM_INT);
foreach($params as $k=>$v) $dSt->bindValue($k,$v); $dSt->execute(); $rows=$dSt->fetchAll();

$categories = $db->query("SELECT * FROM doc_categories ORDER BY name")->fetchAll();
$statusCounts = $db->query("SELECT status,COUNT(*) as c FROM documents GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$totalDocs = array_sum($statusCounts);

function statusBadge(string $s): string {
    return match($s) {
        'APROVADO'       => "<span class='badge bg'>✅ Aprovado</span>",
        'PUBLICADO'      => "<span class='badge' style='background:#1A3060;color:#90CDF4'>📢 Publicado</span>",
        'AGUARDA_REVISAO'=> "<span class='badge bo'>🔄 Aguarda Revisão</span>",
        'REJEITADO'      => "<span class='badge br'>❌ Rejeitado</span>",
        default          => "<span class='badge bw'>⏳ Pendente</span>",
    };
}
function h(string $v): string { return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub Admin — Documentos</title>
<?= baseCss() ?>
<style>
.doc-table{width:100%;border-collapse:collapse;font-size:13px}
.doc-table thead tr{background:linear-gradient(to right,var(--warm),#fff);border-bottom:2px solid var(--bdr)}
.doc-table th{padding:11px 14px;text-align:left;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;white-space:nowrap}
.doc-table tbody tr{border-bottom:1px solid var(--bdr2);transition:background var(--t)}
.doc-table tbody tr:hover{background:var(--warm)}.doc-table td{padding:12px 14px;vertical-align:middle}
.dt-title{font-size:13px;font-weight:700;color:var(--tx);margin-bottom:2px}
.dt-meta{font-size:11px;color:var(--tx-l)}
.act-cell{display:flex;align-items:center;gap:5px}
.grad-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:100px;font-size:10px;font-weight:700}
.status-btns{display:flex;flex-direction:column;gap:4px}
@media(max-width:767px){.hide-mob{display:none}}
</style>
</head>
<body>
<div class="toast t-def" id="toast"></div>
<div class="sb-ov" id="sb-ov" onclick="closeSB()"></div>

<!-- MODAL FORM DOCUMENTO -->
<div class="overlay" id="modal-doc" onclick="ovClose(event,'modal-doc')">
  <div class="modal modal-xl">
    <div class="m-hd mh-cr" id="doc-mhd"><h3 id="doc-mtitle">📄 Novo Documento</h3><p>Preencha todos os campos obrigatórios</p><button class="m-close" onclick="closeModal('modal-doc')">✕</button></div>
    <form id="doc-form">
      <div class="m-body">
        <input type="hidden" id="df-id" name="id" value="">
        <input type="hidden" name="action" value="save">
        <div class="f-row">
          <div class="f-group" style="grid-column:1/-1">
            <label class="f-lbl">Título <span style="color:var(--cr)">*</span></label>
            <input class="f-input" id="df-title" name="title" placeholder="Título completo do documento" required>
          </div>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Autor(es) <span style="color:var(--cr)">*</span></label>
            <input class="f-input" id="df-authors" name="authors" placeholder="Nome completo dos autores" required>
          </div>
          <div class="f-group">
            <label class="f-lbl">Orientador</label>
            <input class="f-input" id="df-advisor" name="advisor" placeholder="Professor orientador">
          </div>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Categoria <span style="color:var(--cr)">*</span></label>
            <select class="f-sel-full" id="df-cat" name="category_id" required>
              <option value="">— Seleccionar —</option>
              <?php foreach($categories as $c): ?>
              <option value="<?= h($c['id']) ?>"><?= h($c['icon'].' '.$c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="f-group">
            <label class="f-lbl">Curso / Disciplina</label>
            <input class="f-input" id="df-course" name="course" placeholder="Ex: Engenharia Informática">
          </div>
        </div>
        <div class="f-group">
          <label class="f-lbl">Resumo / Abstract <span style="color:var(--cr)">*</span></label>
          <textarea class="f-ta" id="df-summary" name="summary" rows="4" placeholder="Resumo do documento…" required style="min-height:100px"></textarea>
        </div>
        <div class="f-group">
          <label class="f-lbl">Palavras-chave <span style="color:var(--cr)">*</span></label>
          <input class="f-input" id="df-keywords" name="keywords" placeholder="petróleo, Angola, sustentabilidade (separadas por vírgula)" required>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Caminho do ficheiro <span style="color:var(--cr)">*</span></label>
            <input class="f-input" id="df-path" name="file_path" placeholder="/uploads/docs/ficheiro.pdf" required>
          </div>
          <div class="f-group">
            <label class="f-lbl">Tamanho</label>
            <input class="f-input" id="df-size" name="file_size" placeholder="Ex: 2.4 MB">
          </div>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Tipo de ficheiro</label>
            <select class="f-sel-full" id="df-ftype" name="file_type">
              <option value="">— Seleccionar —</option>
              <option value="pdf">PDF</option><option value="docx">DOCX</option><option value="pptx">PPTX</option>
            </select>
          </div>
          <div class="f-group">
            <label class="f-lbl">Modo de publicação</label>
            <select class="f-sel-full" id="df-pubmode" name="pub_mode">
              <option value="">— Seleccionar —</option>
              <option value="publico">Público</option>
              <option value="restrito">Restrito</option>
              <option value="privado">Privado</option>
            </select>
          </div>
        </div>
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Estado</label>
            <select class="f-sel-full" id="df-status" name="status">
              <option value="PENDENTE">⏳ Pendente</option>
              <option value="APROVADO">✅ Aprovado</option>
              <option value="PUBLICADO">📢 Publicado</option>
              <option value="AGUARDA_REVISAO">🔄 Aguarda Revisão</option>
              <option value="REJEITADO">❌ Rejeitado</option>
            </select>
          </div>
          <div class="f-group">
            <label class="f-lbl">Score de plágio (%)</label>
            <input class="f-input" type="number" id="df-plagiarism" name="plagiarism_score" min="0" max="100" step="0.01" placeholder="Ex: 12.50">
          </div>
        </div>
        <div class="f-group">
          <label class="f-lbl">Link de download</label>
          <input class="f-input" type="url" id="df-link" name="download_link" placeholder="https://…">
        </div>
      </div>
      <div class="m-foot">
        <button type="button" class="btn btn-gh" onclick="closeModal('modal-doc')">Cancelar</button>
        <button type="submit" class="btn btn-cr" id="doc-save-btn">💾 Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL STATUS -->
<div class="overlay" id="modal-status" onclick="ovClose(event,'modal-status')">
  <div class="modal modal-sm">
    <div class="m-hd mh-wn"><h3>🔄 Alterar Estado</h3><p id="status-doc-title">—</p><button class="m-close" onclick="closeModal('modal-status')">✕</button></div>
    <div class="m-body">
      <input type="hidden" id="status-id">
      <div class="f-group">
        <label class="f-lbl">Novo estado</label>
        <select class="f-sel-full" id="status-select">
          <option value="PENDENTE">⏳ Pendente</option>
          <option value="APROVADO">✅ Aprovado</option>
          <option value="PUBLICADO">📢 Publicado</option>
          <option value="AGUARDA_REVISAO">🔄 Aguarda Revisão</option>
          <option value="REJEITADO">❌ Rejeitado</option>
        </select>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn btn-gh" onclick="closeModal('modal-status')">Cancelar</button>
      <button class="btn btn-wn" onclick="confirmStatus()">🔄 Alterar</button>
    </div>
  </div>
</div>

<div class="app">
<?= sidebar('docs') ?>
<div class="main">
  <div class="topbar">
    <div class="tb-l">
      <button class="tb-ham" onclick="openSB()">☰</button>
      <div>
        <div class="tb-bc"><a href="admin-seccoes.php">Portal</a> / Documentos</div>
        <div class="tb-title">📚 Gestão de Documentos</div>
      </div>
    </div>
    <div class="tb-r">
      <button class="btn btn-cr btn-sm" onclick="openNew()">+ Novo documento</button>
      <div class="ava ava-dk" style="width:36px;height:36px;font-size:12px">AD</div>
    </div>
  </div>
  <div class="page-wrap">

    <!-- STATS -->
    <div class="stats-row">
      <div class="sc"><div class="sc-top"><div class="sc-ico si-cr">📚</div></div><div class="sc-num"><?= $totalDocs ?></div><div class="sc-lbl">Total documentos</div></div>
      <div class="sc"><div class="sc-top"><div class="sc-ico si-wn">⏳</div></div><div class="sc-num"><?= $statusCounts['PENDENTE']??0 ?></div><div class="sc-lbl">Pendentes</div></div>
      <div class="sc"><div class="sc-top"><div class="sc-ico si-ok">✅</div></div><div class="sc-num"><?= $statusCounts['APROVADO']??0 ?></div><div class="sc-lbl">Aprovados</div></div>
      <div class="sc"><div class="sc-top"><div class="sc-ico si-inf">📢</div></div><div class="sc-num"><?= $statusCounts['PUBLICADO']??0 ?></div><div class="sc-lbl">Publicados</div></div>
      <div class="sc"><div class="sc-top"><div class="sc-ico si-er">❌</div></div><div class="sc-num"><?= $statusCounts['REJEITADO']??0 ?></div><div class="sc-lbl">Rejeitados</div></div>
    </div>

    <!-- TOOLBAR -->
    <form method="GET" action="">
      <div class="toolbar">
        <div class="search-wrap">
          <span class="s-ico">🔍</span>
          <input type="text" name="q" value="<?= h($search) ?>" placeholder="Título, autor, resumo…">
        </div>
        <select class="f-sel" name="status" onchange="this.form.submit()">
          <option value="">Todos os estados</option>
          <?php foreach(['PENDENTE'=>'⏳ Pendente','APROVADO'=>'✅ Aprovado','PUBLICADO'=>'📢 Publicado','AGUARDA_REVISAO'=>'🔄 Aguarda Revisão','REJEITADO'=>'❌ Rejeitado'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= $status===$v?'selected':'' ?>><?= $l ?> (<?= $statusCounts[$v]??0 ?>)</option>
          <?php endforeach; ?>
        </select>
        <select class="f-sel" name="cat" onchange="this.form.submit()">
          <option value="">Todas as categorias</option>
          <?php foreach($categories as $c): ?><option value="<?= h($c['id']) ?>" <?= $cat===$c['id']?'selected':'' ?>><?= h($c['name']) ?></option><?php endforeach; ?>
        </select>
        <select class="f-sel" name="sort" onchange="this.form.submit()">
          <option value="recent" <?= $sort==='recent'?'selected':'' ?>>Mais recentes</option>
          <option value="title" <?= $sort==='title'?'selected':'' ?>>Título A→Z</option>
          <option value="status" <?= $sort==='status'?'selected':'' ?>>Por estado</option>
        </select>
        <button type="submit" class="btn btn-cr btn-sm">🔍 Filtrar</button>
        <?php if($search||$status||$cat): ?><a href="admin-documentos.php" class="btn btn-gh btn-sm">✕ Limpar</a><?php endif; ?>
        <span style="font-size:13px;color:var(--tx-l);margin-left:auto"><?= $total ?> doc<?= $total!=1?'s':'' ?></span>
      </div>
    </form>

    <!-- TABLE -->
    <?php if(empty($rows)): ?>
    <div class="empty">
      <div class="empty-ico">📚</div><div class="empty-title">Nenhum documento</div>
      <p style="font-size:14px;color:var(--tx-l);margin:6px 0 18px">Crie o primeiro documento.</p>
      <button class="btn btn-cr" onclick="openNew()">+ Criar documento</button>
    </div>
    <?php else: ?>
    <div class="card">
      <div style="padding:0;overflow-x:auto">
        <table class="doc-table">
          <thead>
            <tr>
              <th>#</th><th>Documento</th>
              <th class="hide-mob">Categoria</th>
              <th class="hide-mob">Ficheiro</th>
              <th class="hide-mob">Plágio</th>
              <th>Estado</th>
              <th>Acções</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($rows as $i=>$r):
            $rj=h(json_encode($r,JSON_UNESCAPED_UNICODE));
          ?>
          <tr id="drow-<?= h($r['id']) ?>">
            <td style="font-size:12px;color:var(--tx-l)"><?= $pg['offset']+$i+1 ?></td>
            <td>
              <div class="dt-title"><?= h($r['title']) ?></div>
              <div class="dt-meta">👤 <?= h($r['authors']) ?><?= $r['advisor']?' · 🎓 '.h($r['advisor']):'' ?></div>
              <div class="dt-meta" style="margin-top:2px">📅 <?= $r['created_at']?date('d/m/Y',strtotime($r['created_at'])):'—' ?> · v<?= $r['version']??1 ?></div>
            </td>
            <td class="hide-mob">
              <?php if($r['cat_icon']||$r['cat_name']): ?>
              <span style="font-size:12px;color:var(--tx-m)"><?= h($r['cat_icon']??'') ?> <?= h($r['cat_name']??'—') ?></span>
              <?php endif; ?>
            </td>
            <td class="hide-mob">
              <div style="font-size:12px;color:var(--tx-m)"><?= h(strtoupper($r['file_type']??'—')) ?> · <?= h($r['file_size']) ?></div>
              <?php if($r['pub_mode']): ?><div style="font-size:11px;color:var(--tx-l)"><?= h(ucfirst($r['pub_mode'])) ?></div><?php endif; ?>
            </td>
            <td class="hide-mob">
              <?php if($r['plagiarism_score'] !== null): ?>
              <?php $ps=(float)$r['plagiarism_score']; $pcls=$ps>30?'var(--er)':($ps>15?'var(--wn)':'var(--ok)'); ?>
              <span style="font-size:13px;font-weight:700;color:<?= $pcls ?>"><?= number_format($ps,1) ?>%</span>
              <?php else: ?><span style="font-size:12px;color:var(--tx-l)">—</span><?php endif; ?>
            </td>
            <td><?= statusBadge($r['status']) ?></td>
            <td>
              <div class="act-cell">
                <button class="btn btn-gh btn-xs" onclick="editDoc(<?= $rj ?>)">✏️</button>
                <button class="btn btn-wn btn-xs" onclick="openStatus(<?= $rj ?>)">🔄</button>
                <button class="btn btn-er btn-xs" onclick="delDoc('<?= h($r['id']) ?>')">🗑️</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card-foot">
        <span style="font-size:12px;color:var(--tx-l)">Página <?= $pg['page'] ?>/<?= $pg['pages'] ?></span>
        <?= paginationHtml($pg,'?status='.urlencode($status).'&cat='.urlencode($cat).'&q='.urlencode($search).'&sort='.urlencode($sort)) ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
</div>

<?= sidebarJs() ?>
<script>
const categories = <?= json_encode(array_column($categories,null,'id'),JSON_UNESCAPED_UNICODE) ?>;

function openNew(){
    clearForm();
    document.getElementById('doc-mtitle').textContent='📄 Novo Documento';
    document.getElementById('doc-save-btn').textContent='💾 Criar';
    openModal('modal-doc');
}
function editDoc(row){
    clearForm();
    document.getElementById('df-id').value        = row.id;
    document.getElementById('df-title').value     = row.title||'';
    document.getElementById('df-authors').value   = row.authors||'';
    document.getElementById('df-advisor').value   = row.advisor||'';
    document.getElementById('df-course').value    = row.course||'';
    document.getElementById('df-cat').value       = row.category_id||'';
    document.getElementById('df-summary').value   = row.summary||'';
    document.getElementById('df-keywords').value  = row.keywords||'';
    document.getElementById('df-path').value      = row.file_path||'';
    document.getElementById('df-size').value      = row.file_size||'';
    document.getElementById('df-ftype').value     = row.file_type||'';
    document.getElementById('df-pubmode').value   = row.pub_mode||'';
    document.getElementById('df-status').value    = row.status||'PENDENTE';
    document.getElementById('df-plagiarism').value= row.plagiarism_score||'';
    document.getElementById('df-link').value      = row.download_link||'';
    document.getElementById('doc-mtitle').textContent='✏️ Editar Documento';
    document.getElementById('doc-save-btn').textContent='💾 Actualizar';
    openModal('modal-doc');
}
function clearForm(){
    document.getElementById('doc-form').reset();
    document.getElementById('df-id').value='';
}
document.getElementById('doc-form').addEventListener('submit',async function(e){
    e.preventDefault();
    const btn=document.getElementById('doc-save-btn');btn.disabled=true;btn.textContent='⌛…';
    const res=await fetch('admin-documentos.php?api=1',{method:'POST',body:new URLSearchParams(new FormData(this))});
    const d=await res.json();btn.disabled=false;btn.textContent='💾 Guardar';
    showToast(d.msg||(d.ok?'✅':'❌'),d.ok?'t-ok':'t-er');
    if(d.ok){closeModal('modal-doc');setTimeout(()=>location.reload(),600);}
});

let statusDocId=null;
function openStatus(row){
    statusDocId=row.id;
    document.getElementById('status-id').value=row.id;
    document.getElementById('status-doc-title').textContent=row.title;
    document.getElementById('status-select').value=row.status||'PENDENTE';
    openModal('modal-status');
}
async function confirmStatus(){
    const id=document.getElementById('status-id').value;
    const status=document.getElementById('status-select').value;
    const res=await fetch('admin-documentos.php?api=1',{method:'POST',body:new URLSearchParams({action:'status',id,status})});
    const d=await res.json();
    closeModal('modal-status');
    showToast(d.msg||'✅ Estado alterado',d.ok?'t-ok':'t-er');
    if(d.ok) setTimeout(()=>location.reload(),600);
}
async function delDoc(id){
    if(!confirm('Eliminar este documento definitivamente? Esta acção é irreversível.'))return;
    const res=await fetch('admin-documentos.php?api=1',{method:'POST',body:new URLSearchParams({action:'delete',id})});
    const d=await res.json();
    showToast(d.msg||'✅ Eliminado','t-er');
    if(d.ok){const row=document.getElementById('drow-'+id);if(row){row.style.opacity=0;row.style.transition='opacity .4s';setTimeout(()=>row.remove(),400);}}
}
</script>
</body>
</html>
