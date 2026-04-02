// ══════════════════════════════════════════════════════════════
// upload.js  — Lógica de submissão e edição de documentos
// Suporta modo: físico (preço + localização + contacto)
//               digital (PDF obrigatório + leitura no portal)
// ══════════════════════════════════════════════════════════════

// ── STATE ──────────────────────────────────────────────────────
let selectedDocType       = '';
let selectedFile          = null;
let selectedCover         = null;
let pubMode               = 'immediate';
let bookMode              = '';          // 'fisico' | 'digital'
let file_size             = 0;

// ── BOOK MODE SELECTOR ─────────────────────────────────────────
function selectBookMode(mode) {
  bookMode = mode;

  document.querySelectorAll('.bm-option').forEach(o => o.classList.remove('selected'));
  const el = document.getElementById('bm-' + mode);
  if (el) el.classList.add('selected');

  // Mostrar/ocultar secções condicionais
  const secFile     = document.getElementById('sec-file');
  const secPrice    = document.getElementById('sec-price');
  const secContact  = document.getElementById('sec-contact');
  const secDigInfo  = document.getElementById('sec-dig-info');

  if (mode === 'digital') {
    if (secFile)    secFile.style.display    = '';
    if (secPrice)   secPrice.style.display   = 'none';
    if (secContact) secContact.style.display = 'none';
    if (secDigInfo) secDigInfo.style.display = '';
    // Limpar campos de físico
    const priceEl = document.getElementById('doc-price');
    if (priceEl) priceEl.value = '';
    const locEl = document.getElementById('doc-localization');
    if (locEl) locEl.value = '';
  } else if (mode === 'fisico') {
    if (secFile)    secFile.style.display    = 'none'; // PDF não obrigatório para físico
    if (secPrice)   secPrice.style.display   = '';
    if (secContact) secContact.style.display = '';
    if (secDigInfo) secDigInfo.style.display = 'none';
  }

  // Atualizar badge no step ①
  const modeLabel = document.getElementById('mode-label');
  if (modeLabel) {
    modeLabel.textContent = mode === 'fisico' ? '📦 Livro Físico' : '💻 Livro Digital';
    modeLabel.style.background = mode === 'fisico' ? 'rgba(176,122,26,.12)' : 'rgba(26,92,138,.10)';
    modeLabel.style.color = mode === 'fisico' ? 'var(--warn)' : 'var(--info)';
  }

  updateSummary();
}

// ── SUBMIT ─────────────────────────────────────────────────────
function submitDocument() {
  if (!bookMode) {
    showToast('⚠️ Seleccione se é Livro Físico ou Digital');
    return;
  }

  const title    = (document.getElementById('doc-title')?.value    ?? '').trim();
  const abstract = (document.getElementById('doc-abstract')?.value  ?? '').trim();
  const course   = (document.getElementById('doc-inst')?.value      ?? '').trim();
  const authorsRaw = (document.getElementById('doc-authors')?.value ?? '').trim();
  const tagsRaw  = (document.getElementById('doc-tags')?.value      ?? '').trim();

  const doc_authors = authorsRaw.split(',').map(a => a.trim()).filter(Boolean);
  const keywords    = tagsRaw.split(',').map(k => k.trim()).filter(Boolean);

  // Validações comuns
  if (!title)                          { showToast('⚠️ Preencha o título do documento'); return; }
  if (!abstract || abstract.length < 50) { showToast('⚠️ Preencha o resumo (mínimo 50 caracteres)'); return; }
  if (!course)                          { showToast('⚠️ Preencha o curso ou área'); return; }
  if (doc_authors.length === 0)         { showToast('⚠️ Preencha pelo menos um autor'); return; }
  if (keywords.length < 2)             { showToast('⚠️ Adicione pelo menos duas palavras-chave'); return; }
  if (!selectedDocType)                { showToast('⚠️ Seleccione o tipo de documento'); return; }

  // Validações por modo
  if (bookMode === 'digital') {
    if (!selectedFile) { showToast('⚠️ Faça upload do ficheiro PDF para o livro digital'); return; }
  }

  if (bookMode === 'fisico') {
    const price = (document.getElementById('doc-price')?.value ?? '').trim();
    const loc   = (document.getElementById('doc-localization')?.value ?? '').trim();
    if (!price || parseFloat(price) <= 0) { showToast('⚠️ Indique o preço de venda do livro físico'); return; }
    if (!loc)                              { showToast('⚠️ Indique a localização de venda do livro'); return; }
  }

  if (pubMode === 'scheduled') {
    if (!document.getElementById('sched-date')?.value) { showToast('⚠️ Defina a data de publicação'); return; }
    if (!document.getElementById('sched-time')?.value) { showToast('⚠️ Defina a hora de publicação'); return; }
  }

  // Recolher todos os campos
  const date       = document.getElementById('doc-date')?.value      ?? '';
  const advisor    = document.getElementById('doc-advisor')?.value   ?? '';
  const sched_date = document.getElementById('sched-date')?.value    ?? '';
  const sched_time = document.getElementById('sched-time')?.value    ?? '';
  const price      = document.getElementById('doc-price')?.value     ?? '0';
  const localization = document.getElementById('doc-localization')?.value ?? '';
  const phone      = document.getElementById('doc-phone')?.value     ?? '';
  const whatsapp   = document.getElementById('doc-whatsapp')?.value  ?? '';
  const email_contact = document.getElementById('doc-email-contact')?.value ?? '';

  const authors     = JSON.stringify(doc_authors);
  const doc_keywords = JSON.stringify(keywords);

  uploadForm({
    document_file: selectedFile,
    cover_file:    selectedCover,
    docType:       selectedDocType,
    advisor,
    date,
    title,
    authors,
    course,
    summary:       abstract,
    keywords:      doc_keywords,
    pubMode,
    sched_date,
    sched_time,
    price:         bookMode === 'fisico' ? price : '0',
    localization:  bookMode === 'fisico' ? localization : '',
    phone:         bookMode === 'fisico' ? phone : '',
    whatsapp:      bookMode === 'fisico' ? whatsapp : '',
    email_contact: bookMode === 'fisico' ? email_contact : '',
    book_mode:     bookMode,
    file_size,
  });
}

async function uploadForm(data) {
  const fd = new FormData();
  if (data.document_file) fd.append('document',       data.document_file);
  if (data.cover_file)    fd.append('cover_file',     data.cover_file);
  fd.append('docType',      data.docType);
  fd.append('advisor',      data.advisor);
  fd.append('date',         data.date);
  fd.append('title',        data.title);
  fd.append('authors',      data.authors);
  fd.append('course',       data.course);
  fd.append('summary',      data.summary);
  fd.append('keywords',     data.keywords);
  fd.append('pubMode',      data.pubMode);
  fd.append('sched_date',   data.sched_date);
  fd.append('sched_time',   data.sched_time);
  fd.append('file_size',    data.file_size);
  fd.append('price',        data.price);
  fd.append('location',     data.localization);
  fd.append('phone',        data.phone);
  fd.append('whatsapp',     data.whatsapp);
  fd.append('email_contact',data.email_contact);
  fd.append('book_mode',    data.book_mode);

  const submitBtn = document.getElementById('submit-btn');
  if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = '⌛ A enviar…'; }

  try {
    const response = await fetch(`${API_BASE_URL}/documents`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${getToken()}` },
      body: fd,
    });
    const result = await response.json();

    if (!result.success) {
      showToast(result.error || result.message || '❌ Erro ao submeter');
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = '📤 Submeter para Revisão'; }
      return;
    }

    showToast(result.message || '✅ Documento submetido com sucesso!');
    setTimeout(() => { window.location.href = 'my-documents.php'; }, 1800);
  } catch (err) {
    console.error(err);
    showToast('❌ Erro de rede ao enviar o documento');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = '📤 Submeter para Revisão'; }
  }
}

// ── EDIT (PUT) ─────────────────────────────────────────────────
async function updateDocument(docId) {
  if (!bookMode) { showToast('⚠️ Seleccione se é Livro Físico ou Digital'); return; }

  const title    = (document.getElementById('doc-title')?.value    ?? '').trim();
  const abstract = (document.getElementById('doc-abstract')?.value  ?? '').trim();
  const course   = (document.getElementById('doc-inst')?.value      ?? '').trim();
  const authorsRaw = (document.getElementById('doc-authors')?.value ?? '').trim();
  const tagsRaw  = (document.getElementById('doc-tags')?.value      ?? '').trim();

  if (!title)  { showToast('⚠️ Preencha o título'); return; }
  if (!abstract || abstract.length < 20) { showToast('⚠️ Resumo muito curto'); return; }
  if (!course) { showToast('⚠️ Preencha o curso'); return; }

  const doc_authors  = authorsRaw.split(',').map(a => a.trim()).filter(Boolean);
  const keywords     = tagsRaw.split(',').map(k => k.trim()).filter(Boolean);
  const price        = document.getElementById('doc-price')?.value ?? '0';
  const localization = document.getElementById('doc-localization')?.value ?? '';
  const phone        = document.getElementById('doc-phone')?.value ?? '';
  const whatsapp     = document.getElementById('doc-whatsapp')?.value ?? '';
  const email_contact = document.getElementById('doc-email-contact')?.value ?? '';
  const date         = document.getElementById('doc-date')?.value ?? '';
  const advisor      = document.getElementById('doc-advisor')?.value ?? '';

  const fd = new FormData();
  fd.append('_method',       'PUT');
  fd.append('title',         title);
  fd.append('summary',       abstract);
  fd.append('course',        course);
  fd.append('authors',       JSON.stringify(doc_authors));
  fd.append('keywords',      JSON.stringify(keywords));
  fd.append('docType',       selectedDocType);
  fd.append('advisor',       advisor);
  fd.append('date',          date);
  fd.append('price',         bookMode === 'fisico' ? price : '0');
  fd.append('location',      bookMode === 'fisico' ? localization : '');
  fd.append('phone',         bookMode === 'fisico' ? phone : '');
  fd.append('whatsapp',      bookMode === 'fisico' ? whatsapp : '');
  fd.append('email_contact', bookMode === 'fisico' ? email_contact : '');
  fd.append('book_mode',     bookMode);
  if (selectedFile)  fd.append('document',   selectedFile);
  if (selectedCover) fd.append('cover_file', selectedCover);
  if (file_size)     fd.append('file_size',  file_size);

  const saveBtn = document.getElementById('save-btn');
  if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = '⌛ A guardar…'; }

  try {
    const response = await fetch(`${API_BASE_URL}/documents/${docId}`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${getToken()}` },
      body: fd,
    });
    const result = await response.json();

    if (!result.success) {
      showToast(result.error || result.message || '❌ Erro ao guardar');
      if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = '💾 Guardar alterações'; }
      return;
    }

    showToast('✅ Documento actualizado com sucesso!');
    setTimeout(() => { window.location.href = 'my-documents.php'; }, 1600);
  } catch (err) {
    console.error(err);
    showToast('❌ Erro de rede');
    if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = '💾 Guardar alterações'; }
  }
}

// ── COVER ──────────────────────────────────────────────────────
function coverDragOver(e)  { e.preventDefault(); document.getElementById('cover-drop')?.classList.add('drag-over'); }
function coverDragLeave()  { document.getElementById('cover-drop')?.classList.remove('drag-over'); }
function coverDrop(e)      { e.preventDefault(); document.getElementById('cover-drop')?.classList.remove('drag-over'); if (e.dataTransfer.files[0]) processCoverFile(e.dataTransfer.files[0]); }
function coverFileSelect(e){ if (e.target.files[0]) processCoverFile(e.target.files[0]); }

function processCoverFile(file) {
  const allowed = ['image/jpeg','image/png','image/webp'];
  if (!allowed.includes(file.type)) { showToast('⚠️ Use JPG, PNG ou WEBP'); return; }
  if (file.size > 5*1024*1024)      { showToast('⚠️ Imagem maior que 5 MB'); return; }
  selectedCover = file;
  const reader = new FileReader();
  reader.onload = ev => {
    const img = document.getElementById('cover-preview-img');
    if (img) { img.src = ev.target.result; }
    document.getElementById('cover-drop')?.style.setProperty('display','none');
    document.getElementById('cover-preview-wrap')?.classList.add('visible');
    // sfp
    const sfpImg = document.getElementById('sfp-img');
    if (sfpImg) { sfpImg.src = ev.target.result; sfpImg.style.display = 'block'; }
    document.getElementById('sfp-no-img')?.style.setProperty('display','none');
    document.getElementById('sfp-wrap')?.classList.add('visible');
    setS('sum-cover', '🖼️ ' + file.name);
    updateSfp();
  };
  reader.readAsDataURL(file);
}

function removeCover(e) {
  if (e) e.stopPropagation();
  selectedCover = null;
  document.getElementById('cover-preview-wrap')?.classList.remove('visible');
  const drop = document.getElementById('cover-drop');
  if (drop) drop.style.display = '';
  const ci = document.getElementById('cover-input');
  if (ci) ci.value = '';
  const sfpImg = document.getElementById('sfp-img');
  if (sfpImg) sfpImg.style.display = 'none';
  const sfpNi = document.getElementById('sfp-no-img');
  if (sfpNi) sfpNi.style.display = 'flex';
  setS('sum-cover', 'Sem capa', true);
  updateSfp();
}
function removeCoverSilent() { removeCover(null); }

// ── FILE ───────────────────────────────────────────────────────
function handleDragOver(e)      { e.preventDefault(); document.getElementById('drop-zone')?.classList.add('drag-over'); }
function handleDragLeave()      { document.getElementById('drop-zone')?.classList.remove('drag-over'); }
function handleDrop(e)          { e.preventDefault(); document.getElementById('drop-zone')?.classList.remove('drag-over'); if (e.dataTransfer.files[0]) processFile(e.dataTransfer.files[0]); }
function handleFileSelectEvt(e) { if (e.target.files[0]) processFile(e.target.files[0]); }

function processFile(file) {
  const allowed = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
  if (file.size > 50*1024*1024) { showToast('⚠️ Ficheiro maior que 50 MB'); return; }
  if (!allowed.includes(file.type)) { showToast('⚠️ Tipo não permitido (PDF, DOC, DOCX)'); return; }
  selectedFile = file;
  showFilePreview(file);
}

function showFilePreview(file) {
  const ext  = file.name.split('.').pop().toUpperCase();
  const size = (file.size/1024/1024).toFixed(1);
  file_size  = size;
  const nameEl = document.getElementById('file-name');
  const sizeEl = document.getElementById('file-size');
  if (nameEl) nameEl.textContent = file.name;
  if (sizeEl) sizeEl.textContent = size + ' MB · ' + ext;
  document.getElementById('file-preview')?.classList.add('visible');
  updateSfp();
  simulateUpload();
  updateSummary();
}

function removeFile() {
  selectedFile = null;
  file_size    = 0;
  document.getElementById('file-preview')?.classList.remove('visible');
  document.getElementById('upload-progress')?.classList.remove('visible');
  const fi = document.getElementById('file-input');
  if (fi) fi.value = '';
  if (!selectedCover) document.getElementById('sfp-wrap')?.classList.remove('visible');
  updateSummary();
}

function simulateUpload() {
  const prog  = document.getElementById('upload-progress');
  const fill  = document.getElementById('prog-fill');
  const pct   = document.getElementById('prog-pct');
  const label = document.getElementById('prog-label');
  if (!prog || !fill || !pct || !label) return;
  prog.classList.add('visible');
  let p = 0;
  const iv = setInterval(() => {
    p += Math.random() * 20;
    if (p >= 100) { p = 100; clearInterval(iv); label.textContent = '✓ Ficheiro pronto'; }
    fill.style.width  = p + '%';
    pct.textContent   = Math.round(p) + '%';
  }, 160);
}

function updateSfp() {
  const wrap = document.getElementById('sfp-wrap');
  const nameEl = document.getElementById('sfp-name');
  const sizeEl = document.getElementById('sfp-size');
  if (!wrap) return;
  if (selectedFile || selectedCover) {
    wrap.classList.add('visible');
    if (selectedFile) {
      if (nameEl) nameEl.textContent = selectedFile.name;
      if (sizeEl) sizeEl.textContent = file_size + ' MB · ' + selectedFile.name.split('.').pop().toUpperCase();
    } else {
      if (nameEl) nameEl.textContent = selectedCover ? selectedCover.name : '—';
      if (sizeEl) sizeEl.textContent = '';
    }
  } else {
    wrap.classList.remove('visible');
  }
}

// ── DOC TYPE ───────────────────────────────────────────────────
function selectDocType(el, type) {
  document.querySelectorAll('.doc-type-option').forEach(o => o.classList.remove('selected'));
  el.classList.add('selected');
  selectedDocType = type;
  updateSummary();
}

// ── PUBLICATION MODE ───────────────────────────────────────────
function selectPublication(mode) {
  pubMode = mode;
  document.getElementById('pub-immediate')?.classList.toggle('selected', mode === 'immediate');
  document.getElementById('pub-scheduled')?.classList.toggle('selected', mode === 'scheduled');
  document.getElementById('scheduler-box')?.classList.toggle('visible', mode === 'scheduled');
  const row = document.getElementById('sum-sched-row');
  if (row) row.style.display = mode === 'scheduled' ? 'flex' : 'none';
  const pubEl = document.getElementById('sum-pub');
  if (pubEl) pubEl.textContent = mode === 'immediate' ? '🚀 Imediata' : '⏰ Programada';
  updateSummary();
}

// ── SUMMARY ────────────────────────────────────────────────────
function updateSummary() {
  const title     = document.getElementById('doc-title')?.value    ?? '';
  const date      = document.getElementById('doc-date')?.value     ?? '';
  const inst      = document.getElementById('doc-inst')?.value     ?? '';
  const price     = document.getElementById('doc-price')?.value    ?? '';
  const schedDate = document.getElementById('sched-date')?.value   ?? '';
  const schedTime = document.getElementById('sched-time')?.value   ?? '';

  setS('sum-title', title || null, !title);
  setS('sum-type',  selectedDocType || null, !selectedDocType);
  setS('sum-date',  date ? formatDate(date) : null, !date);
  setS('sum-inst',  inst || null, !inst);
  setS('sum-mode',  bookMode === 'fisico' ? '📦 Físico' : bookMode === 'digital' ? '💻 Digital' : null, !bookMode);

  if (bookMode === 'fisico') {
    const priceEl = document.getElementById('sum-price');
    if (priceEl) priceEl.textContent = price ? parseInt(price).toLocaleString('pt-PT') + ' Kz' : '—';
  }

  if (pubMode === 'scheduled' && schedDate) {
    const schedEl = document.getElementById('sum-sched');
    if (schedEl) schedEl.textContent = formatDate(schedDate) + (schedTime ? ' às ' + schedTime : '');
  }
}

function setS(id, val, isEmpty) {
  const el = document.getElementById(id);
  if (!el) return;
  if (val) { el.textContent = val; el.className = 's-val'; }
  else      { el.textContent = isEmpty ? (el.dataset.empty || '—') : val; el.className = 's-val empty'; }
}

function formatDate(d) {
  const [y,m,day] = d.split('-');
  const months = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
  return `${day} ${months[parseInt(m)-1]} ${y}`;
}

// ── RESET ──────────────────────────────────────────────────────
function resetUploadForm() {
  ['doc-title','doc-date','doc-inst','doc-abstract','doc-tags',
   'doc-price','doc-authors','doc-advisor','doc-localization',
   'doc-phone','doc-whatsapp','doc-email-contact'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.querySelectorAll('.doc-type-option').forEach(o => o.classList.remove('selected'));
  selectedDocType = '';
  bookMode        = '';
  document.querySelectorAll('.bm-option').forEach(o => o.classList.remove('selected'));
  selectPublication('immediate');
  removeFile();
  removeCoverSilent();
  // Reset mode sections
  const secFile    = document.getElementById('sec-file');
  const secPrice   = document.getElementById('sec-price');
  const secContact = document.getElementById('sec-contact');
  const secDigInfo = document.getElementById('sec-dig-info');
  if (secFile)    secFile.style.display    = 'none';
  if (secPrice)   secPrice.style.display   = 'none';
  if (secContact) secContact.style.display = 'none';
  if (secDigInfo) secDigInfo.style.display = 'none';
  updateSummary();
  showToast('🔄 Formulário limpo');
}
