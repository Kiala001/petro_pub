<?php
// Página pública da Biblioteca PetroPub
session_start();
if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php');
    exit;
}
$userName = $_SESSION['user_name'] ?? 'Usuário';
$userEmail = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Biblioteca</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
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
<div class="header">
  <div class="hdr-top">
    <div class="logo">PETRO<span>PUB</span></div>
    <div class="search-bar">
      <input type="text" class="search-input" id="search-input" placeholder="Pesquisar por título, autor, palavra-chave...">
      <span class="search-ico"><i class="fa fa-search"></i></span>
      <button class="search-clear" id="search-clear">✕</button>
    </div>
    <div class="hdr-actions">
      <a href="upload-document.php" class="hdr-btn hdr-btn-cr">+ Novo</a>
      <a href="my-documents.php" class="ava-sm"><?php echo $userInitials; ?></a>
    </div>
  </div>
  <div class="hdr-filters" id="hdr-filters">
    <span class="qf-lbl">Filtros rápidos:</span>
    <button class="qf on" data-filter="all">Todos</button>
    <span class="qf-sep"></span>
    <button class="qf" data-filter="recent">Recentes</button>
    <button class="qf" data-filter="popular">Populares</button>
  </div>
</div>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <div class="sb-section">
      <div class="sb-title">Categorias <span class="sb-clear-lnk" id="clear-cat">Limpar</span></div>
      <div class="cb-list" id="cat-list">Carregando...</div>
    </div>
    <!-- Filtro de preço removido -->
    <div class="sb-section">
      <div class="sb-title">Titulo</div>
      <div class="rb-list" id="type-list"></div>
    </div>
    <button class="sb-apply" id="apply-filters">Aplicar Filtros</button>
  </aside>
  <main class="main">
    <div class="results-bar">
      <div class="results-count">Mostrando <strong id="doc-count">0</strong> documentos</div>
      <div class="view-toggle">
        <button class="vt-btn on" id="grid-view">🔲</button>
        <button class="vt-btn" id="list-view">📄</button>
      </div>
    </div>
    <div class="active-tags" id="active-tags"></div>
    <div class="doc-grid" id="doc-grid"></div>
  </main>
</div>
    
<script src="assets/js/util.js"></script>
<script src="assets/js/api.js"></script>
<script>
let allDocs = [], filteredDocs = [], categories = [], types = [], minPrice = 0, maxPrice = 0;
let filters = { search: '', category: [], type: '', price: 0, quick: 'all' };

async function fetchDocuments() {
    const response = await apiRequest("article");
    const data = response.data

    allDocs = data.documents || [];
    extractFilters();
    renderCategories();
    renderTypes();
    // setPriceRange();
    applyFilters();
}

function extractFilters() {
  categories = [...new Set(allDocs.map(d => d.category_id || d.cat || 'Outros'))];
  types = [...new Set(allDocs.map(d => d.pub_mode || d.type || 'Outro'))];
}

function renderCategories() {
  const el = document.getElementById('cat-list');
  el.innerHTML = categories.map(cat => `
    <div class="cb-item" data-cat="${cat}">
      <div class="cb-box"></div>
      <div class="cb-lbl">${cat}</div>
    </div>
  `).join('');
}

function renderTypes() {
  const el = document.getElementById('type-list');
  el.innerHTML = types.map(type => `
    <div class="rb-item" data-type="${type}">
      <div class="rb-dot"></div>
      <div class="rb-lbl">${type}</div>
    </div>
  `).join('');
}

function setPriceRange() {
  document.getElementById('min-price').textContent = minPrice;
  document.getElementById('max-price').textContent = maxPrice;
  const range = document.getElementById('price-range');
  range.min = minPrice;
  range.max = maxPrice;
  range.value = maxPrice;
  document.getElementById('range-fill').style.width = '100%';
}

function applyFilters() {
  filteredDocs = allDocs.filter(doc => {
    if (doc.status && doc.status.toLowerCase() !== 'publicado') return false;
    if (filters.search && !doc.title.includes(filters.search) && !doc.authors.includes(filters.search)) return false;
    if (filters.category.length && !filters.category.includes(doc.category_id || doc.cat)) return false;
    if (filters.type && doc.pub_mode !== filters.type && doc.type !== filters.type) return false;
    if (filters.quick === 'free' && +doc.price_kz > 0) return false;
    if (filters.quick === 'paid' && +doc.price_kz === 0) return false;
    // TODO: filtros recent/popular
    return true;
  });
  renderDocs();
  renderActiveTags();
}

function renderDocs() {
  const el = document.getElementById('doc-grid');
  document.getElementById('doc-count').textContent = filteredDocs.length;
  el.innerHTML = filteredDocs.map(doc => `
    <div class="doc-card">
      <div class="dc-thumb">
        <div class="dc-thumb-inner">
            ${doc.file_cover 
                ? `
                    <img class="dc-thumb-img" src="../../uploads/documents/cover/${doc.file_cover}"
                ` : `
                    <div class="dc-doc-ico"><i class='fa fa-file'></i></div>
                    <div class="dc-doc-lines"><div class="dc-doc-line"></div><div class="dc-doc-line"></div><div class="dc-doc-line"></div></div>
                `}
        </div>
        <div class="dc-badge-cat">${doc.category_id || doc.cat}</div>
      </div>
      <div class="dc-body">
        <div style="font-weight:700;font-size:15px; margin-bottom: 5px; color: var(--cr);">${doc.title}</div>
        <div style="font-size:12px;color:var(--tx-l);">${doc.course || ''} | ${doc.file_size} MB | ${doc.year}</div>
        <div style="font-size:12px;color:var(--tx-l); margin-top: 5px; color: var(--dg);">${doc.user_name || ''}</div>
      </div>
      <div class="dc-overlay">
        <button class="dc-ov-btn dc-ov-detail" onclick="openDetail('${doc.id}')">Detalhes</button>
        <button class="dc-ov-btn dc-ov-buy" onclick="openReader('${doc.id}')">Ler</button>
      </div>
    </div>
  `).join('') || '<div style="padding:40px;text-align:center;color:var(--tx-l);">Nenhum documento encontrado.</div>';
}

function openDetail(id) {
  window.location.href = `detail-doc.php?id=${encodeURIComponent(id)}`;
}

function openReader(id) {
  window.location.href = `detail-doc.php?id=${encodeURIComponent(id)}&read=1`;
}

function renderActiveTags() {
  const el = document.getElementById('active-tags');
  let tags = [];
  if (filters.search) tags.push(`<div class='af-tag'>Pesquisa: ${filters.search} <button onclick='clearSearch()'>✕</button></div>`);
  if (filters.category.length) tags.push(...filters.category.map(cat => `<div class='af-tag'>${cat} <button onclick='removeCat("${cat}")'>✕</button></div>`));
  if (filters.type) tags.push(`<div class='af-tag'>Tipo: ${filters.type} <button onclick='clearType()'>✕</button></div>`);
  if (filters.price < maxPrice) tags.push(`<div class='af-tag'>Até ${filters.price} Kz <button onclick='clearPrice()'>✕</button></div>`);
  if (filters.quick !== 'all') tags.push(`<div class='af-tag'>${filters.quick === 'free' ? 'Grátis' : filters.quick === 'paid' ? 'Pagos' : filters.quick.charAt(0).toUpperCase()+filters.quick.slice(1)} <button onclick='clearQuick()'>✕</button></div>`);
  el.innerHTML = tags.join('');
}

function clearSearch() { filters.search = ''; document.getElementById('search-input').value = ''; applyFilters(); }
function removeCat(cat) { filters.category = filters.category.filter(c => c !== cat); applyFilters(); }
function clearType() { filters.type = ''; applyFilters(); }
function clearPrice() { filters.price = maxPrice; document.getElementById('price-range').value = maxPrice; applyFilters(); }
function clearQuick() { filters.quick = 'all'; document.querySelectorAll('.qf').forEach(q => q.classList.remove('on')); document.querySelector('.qf[data-filter="all"]').classList.add('on'); applyFilters(); }

document.getElementById('search-input').addEventListener('input', e => { filters.search = e.target.value; applyFilters(); });
document.getElementById('search-clear').addEventListener('click', clearSearch);
document.getElementById('hdr-filters').addEventListener('click', e => {
  if (e.target.classList.contains('qf')) {
    document.querySelectorAll('.qf').forEach(q => q.classList.remove('on'));
    e.target.classList.add('on');
    filters.quick = e.target.dataset.filter;
    applyFilters();
  }
});
document.getElementById('cat-list').addEventListener('click', e => {
  const item = e.target.closest('.cb-item');
  if (!item) return;
  const cat = item.dataset.cat;
  if (filters.category.includes(cat)) {
    filters.category = filters.category.filter(c => c !== cat);
    item.classList.remove('checked');
  } else {
    filters.category.push(cat);
    item.classList.add('checked');
  }
  applyFilters();
});
document.getElementById('clear-cat').addEventListener('click', () => {
  filters.category = [];
  document.querySelectorAll('.cb-item').forEach(i => i.classList.remove('checked'));
  applyFilters();
});
document.getElementById('type-list').addEventListener('click', e => {
  const item = e.target.closest('.rb-item');
  if (!item) return;
  document.querySelectorAll('.rb-item').forEach(i => i.classList.remove('sel'));
  item.classList.add('sel');
  filters.type = item.dataset.type;
  applyFilters();
});
// document.getElementById('price-range').addEventListener('input', e => {
//   filters.price = +e.target.value;
//   document.getElementById('range-fill').style.width = ((filters.price-minPrice)/(maxPrice-minPrice)*100)+"%";
//   applyFilters();
// });
document.getElementById('apply-filters').addEventListener('click', applyFilters);
document.getElementById('grid-view').addEventListener('click', () => {
  document.getElementById('grid-view').classList.add('on');
  document.getElementById('list-view').classList.remove('on');
  document.getElementById('doc-grid').classList.remove('list-view');
});
document.getElementById('list-view').addEventListener('click', () => {
  document.getElementById('list-view').classList.add('on');
  document.getElementById('grid-view').classList.remove('on');
  document.getElementById('doc-grid').classList.add('list-view');
});
window.onload = fetchDocuments;
</script>
</body>
</html>
