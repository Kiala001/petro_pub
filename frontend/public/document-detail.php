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
<link rel="stylesheet" href="assets/css/library-detail.css">
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
<script src="assets/js/util.js"></script>
<script src="assets/js/api.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
const docId = "<?php echo htmlspecialchars($id); ?>";
const readMode = <?php echo $readMode ? 'true' : 'false'; ?>;

let docDetail = null;
let docReviews = [];
async function fetchDocDetail() {
  const res = await apiRequest(`read/${docId}`);
  const data = res.data;
  if (!data.success) {
    document.getElementById('hero-inner').innerHTML = '<div style="color:red">Documento não encontrado.</div>';
    return;
  }
  docDetail = data.document;
  renderHero(docDetail);
  renderTabs(docDetail);
  renderPurchasePanel(docDetail);
  fetchReviews();
  if (readMode) openPreviewTab();
}

async function fetchReviews() {
  // Busca avaliações do backend (ajuste endpoint conforme necessário)
  const res = await apiRequest('article');
  if (res.data && res.data.documents) {
    const found = res.data.documents.find(d => d.id === docId);
    docReviews = found && found.reviews ? found.reviews : [];
    renderReviewsTab();
  }
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
  // Corrige autores e palavras-chave se vierem como JSON string
  let autores = doc.authors;
  try {
    if (typeof autores === 'string' && autores.trim().startsWith('[')) {
      autores = JSON.parse(autores).join(', ');
    }
  } catch (e) { /* fallback para string */ }

  let palavrasChave = doc.keywords;
  try {
    if (typeof palavrasChave === 'string' && palavrasChave.trim().startsWith('[')) {
      palavrasChave = JSON.parse(palavrasChave).join(', ');
    }
  } catch (e) { /* fallback para string */ }

  // Corrige páginas para inteiro se vier como float
  let paginas = doc.file_size;
  if (paginas && !isNaN(paginas)) paginas = Math.round(Number(paginas));

  document.getElementById('tab-desc').innerHTML = `
    <div class="doc-abstract">${doc.summary || ''}</div>
    <div class="kw-wrap">${(palavrasChave||'').split(',').map(k=>`<span class="kw-tag">${k.trim()}</span>`).join('')}</div>
    <div style="margin-top:18px;">
      <strong>Autores:</strong> ${autores || ''}<br>
      <strong>Curso:</strong> ${doc.course || ''}<br>
      <strong>Orientador:</strong> ${doc.advisor || ''}<br>
      <strong>Ano:</strong> ${doc.year || ''}<br>
      <strong>Páginas:</strong> ${paginas || ''}<br>
      <strong>Categoria:</strong> ${doc.category_id || ''}<br>
      <strong>Tipo de Publicação:</strong> ${doc.pub_mode || ''}<br>
      <strong>Status:</strong> ${doc.status || ''}<br>
      <strong>Palavras-chave:</strong> ${palavrasChave}
    </div>
  `;
  document.getElementById('tab-preview').innerHTML = `
    <div class="preview-wrap">
      <div id="pdf-preview"></div>
      <div style="margin-top:12px;text-align:center;">
        <button class="btn btn-gd" onclick="openFullReader()">Ler Documento Completo</button>
      </div>
    </div>
  `;
  renderReviewsTab();
}

function renderReviewsTab() {
  const el = document.getElementById('tab-reviews');
  if (!docReviews.length) {
    el.innerHTML = '<div style="color:#888">Nenhuma avaliação encontrada.</div>';
    return;
  }
  el.innerHTML = `
    <div class="rev-summary">
      <div class="rs-big">
        <div class="rs-num">${averageScore(docReviews)}</div>
        <div class="rs-stars">${renderStars(averageScore(docReviews))}</div>
        <div class="rs-count">${docReviews.length} avaliações</div>
      </div>
      <div class="rs-list">
        ${docReviews.map(r => `
          <div class="rev-item">
            <div class="rev-head">
              <span class="rev-ini">${r.rev && r.rev.ini ? r.rev.ini : ''}</span>
              <span class="rev-name">${r.rev && r.rev.name ? r.rev.name : ''}</span>
              <span class="rev-role">${r.rev && r.rev.role ? r.rev.role : ''}</span>
              <span class="rev-date">${r.date || ''}</span>
            </div>
            <div class="rev-score">${renderStars(r.score)}</div>
            <div class="rev-comment">${r.comment || ''}</div>
            <div class="rev-suggestion"><em>${r.suggestion || ''}</em></div>
            <div class="rev-decision ${r.dcls}">${r.dLbl || ''}</div>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

function averageScore(reviews) {
  if (!reviews.length) return 0;
  const sum = reviews.reduce((acc, r) => acc + (r.score || 0), 0);
  return (sum / reviews.length).toFixed(1);
}
function renderStars(score) {
  score = Math.round(score);
  return '★'.repeat(score) + '☆'.repeat(5 - score);
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
  window.open(`/petro_pub/uploads/documents/${docDetail.file_path}`, '_blank');
}


async function renderPdfPreview() {
  const previewDiv = document.getElementById('pdf-preview');

  if (!docDetail || !docDetail.file_path) {
    previewDiv.innerHTML = '<div style="color:red">Arquivo não encontrado.</div>';
    return;
  }

  const url = '/petro_pub/uploads/documents/' + docDetail.file_path;

  const pdfjsLib = window.pdfjsLib;
  pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

  try {
    previewDiv.innerHTML = '<div>Carregando PDF...</div>';

    const pdf = await pdfjsLib.getDocument(url).promise;

    previewDiv.innerHTML = ''; 

    for (let i = 1; i <= pdf.numPages; i++) {
      const page = await pdf.getPage(i);

      const scale = 1.2;
      const viewport = page.getViewport({ scale });

      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d');

      canvas.height = viewport.height;
      canvas.width = viewport.width;

      await page.render({
        canvasContext: context,
        viewport: viewport
      }).promise;

      // espaço entre páginas
      canvas.style.display = 'block';
      canvas.style.marginBottom = '20px';

      previewDiv.appendChild(canvas);
    }

  } catch (e) {
    console.error(e);
    previewDiv.innerHTML =
      '<div style="color:red">Erro ao carregar pré-visualização do PDF.</div>';
  }
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
