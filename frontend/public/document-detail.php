<?php
// Detalhe do documento
session_start();
if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php');
    exit;
}
$id = $_GET['id'] ?? '';
$readMode = isset($_GET['read']);
// Aqui você buscaria os dados do documento pelo backend/api
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Detalhes do Documento</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/library.css">
<style>
:root {
  --sb-w: 260px;
  --hdr: 64px;
  --r3: 18px;
  --transition: .18s cubic-bezier(.4,0,.2,1);
}
</style>
</head>
<body>
<!-- Topbar -->
<div class="topbar">
  <div class="tb-l">
    <div class="logo">PETRO<span>PUB</span></div>
    <div class="breadcrumb"><a href="library.php">Biblioteca</a> / Detalhes do Documento</div>
  </div>
  <div class="tb-r">
    <a href="library.php" class="btn btn-gh">Voltar</a>
  </div>
</div>
<div class="doc-hero">
  <div class="hero-inner" id="hero-inner">
    <!-- Conteúdo dinâmico via JS -->
  </div>
</div>
<div class="page-wrap">
  <div class="content-grid">
    <div>
      <div class="tabs-bar">
        <button class="tab-btn on" data-tab="desc">Resumo</button>
        <button class="tab-btn" data-tab="preview">Pré-visualização</button>
        <button class="tab-btn" data-tab="reviews">Avaliações</button>
      </div>
      <div class="tab-panel on" id="tab-desc"></div>
      <div class="tab-panel" id="tab-preview"></div>
      <div class="tab-panel" id="tab-reviews"></div>
    </div>
    <div id="purchase-panel"></div>
  </div>
</div>
<script src="assets/js/api.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
const docId = "<?php echo htmlspecialchars($id); ?>";
const readMode = <?php echo $readMode ? 'true' : 'false'; ?>;

async function fetchDocDetail() {
  const res = await apiRequest('documents/' + docId);
  if (!res.success) {
    document.getElementById('hero-inner').innerHTML = '<div style="color:red">Documento não encontrado.</div>';
    return;
  }
  const doc = res.document;
  renderHero(doc);
  renderTabs(doc);
  renderPurchasePanel(doc);
  if (readMode) openPreviewTab();
}

function renderHero(doc) {
  document.getElementById('hero-inner').innerHTML = `
    <div class="doc-cover">
      <div class="cover-card">
        <div class="cover-ico">📄</div>
        <div class="cover-lines"><div class="cover-line"></div><div class="cover-line"></div><div class="cover-line"></div></div>
        ${+doc.price_kz === 0 ? '<div class="cover-badge">Grátis</div>' : ''}
      </div>
    </div>
    <div class="hero-body">
      <div class="hero-tags">
        <span class="h-tag ht-cat">${doc.category_id || ''}</span>
        <span class="h-tag ht-type">${doc.pub_mode || ''}</span>
        ${+doc.price_kz === 0 ? '<span class="h-tag ht-free">Grátis</span>' : ''}
      </div>
      <div class="hero-title">${doc.title}</div>
      <div class="hero-authors">${doc.authors}</div>
      <div class="hero-meta">
        <div class="hm-item"><div class="hm-val">${doc.year || ''}</div><div class="hm-lbl">Ano</div></div>
        <div class="hm-item"><div class="hm-val">${doc.course || ''}</div><div class="hm-lbl">Curso</div></div>
        <div class="hm-item"><div class="hm-val">${doc.file_size || ''}</div><div class="hm-lbl">Páginas</div></div>
      </div>
    </div>
  `;
}

function renderTabs(doc) {
  document.getElementById('tab-desc').innerHTML = `
    <div class="doc-abstract">${doc.summary || ''}</div>
    <div class="kw-wrap">${(doc.keywords||'').split(',').map(k=>`<span class="kw-tag">${k.trim()}</span>`).join('')}</div>
  `;
  document.getElementById('tab-preview').innerHTML = `
    <div class="preview-wrap">
      <div id="pdf-preview"></div>
      <div style="margin-top:12px;text-align:center;">
        <button class="btn btn-gd" onclick="openFullReader()">Ler Documento Completo</button>
      </div>
    </div>
  `;
  document.getElementById('tab-reviews').innerHTML = `<div style="color:#888">Avaliações em breve...</div>`;
}

function renderPurchasePanel(doc) {
  document.getElementById('purchase-panel').innerHTML = `
    <div class="hero-purchase">
      <div class="hp-price-lbl">Preço</div>
      <div class="hp-price${+doc.price_kz === 0 ? '-free' : ''}">${+doc.price_kz === 0 ? 'Grátis' : doc.price_kz + ' Kz'}</div>
      <div class="hp-price-sub">Acesso imediato ao PDF</div>
      <button class="hp-cta hp-cta-buy" onclick="openPreviewTab()">Ler Documento</button>
      <div class="hp-divider"></div>
      <div class="hp-features">
        <div class="hp-feat"><span class="hp-feat-ico">📄</span> PDF Completo</div>
        <div class="hp-feat"><span class="hp-feat-ico">🔒</span> Protegido</div>
      </div>
    </div>
  `;
}

function openPreviewTab() {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('on'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('on'));
  document.querySelector('.tab-btn[data-tab="preview"]').classList.add('on');
  document.getElementById('tab-preview').classList.add('on');
  renderPdfPreview();
}

function openFullReader() {
  // Abre o PDF completo em nova aba ou modal
  window.open(`/uploads/documents/${docId}.pdf`, '_blank');
}

async function renderPdfPreview() {
  const url = `/uploads/documents/${docId}.pdf`;
  const pdfjsLib = window['pdfjs-dist/build/pdf'];
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  const loadingTask = pdfjsLib.getDocument(url);
  const pdf = await loadingTask.promise;
  const page = await pdf.getPage(1);
  const scale = 1.2;
  const viewport = page.getViewport({ scale });
  const canvas = document.createElement('canvas');
  const context = canvas.getContext('2d');
  canvas.height = viewport.height;
  canvas.width = viewport.width;
  await page.render({ canvasContext: context, viewport: viewport }).promise;
  const previewDiv = document.getElementById('pdf-preview');
  previewDiv.innerHTML = '';
  previewDiv.appendChild(canvas);
}

document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('on'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('on'));
    btn.classList.add('on');
    document.getElementById('tab-' + btn.dataset.tab).classList.add('on');
    if (btn.dataset.tab === 'preview') renderPdfPreview();
  });
});

fetchDocDetail();
if (readMode) openPreviewTab();
</script>
</body>
</html>
