<?php
session_start();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub – Upload & Meios de Pagamento</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/document.css" rel="stylesheet">
</head>
<body>

<!-- PAGE NAV -->
<div class="page-nav">
  <!-- <button class="page-btn active" onclick="switchPage('upload')">📤 Upload de Artigos</button>
  <button class="page-btn" onclick="switchPage('payments')">💳 Meios de Pagamento</button> -->
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>


<div class="page active" id="page-upload">
  <!-- SIDEBAR -->

  <!-- MAIN -->
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <div class="breadcrumb">PetroPub <span>/ Submeter Artigo</span></div>
        <h1>Submeter Artigo Académico</h1>
      </div>
      <div class="topbar-right">
        <button class="btn btn-ghost" onclick="resetUploadForm()">↺ Limpar</button>
        <button class="btn btn-primary" onclick="submitDocument()">Submeter Artigo</button>
      </div>
    </div>

    <div class="page-content">
      <div class="upload-layout">

        <!-- LEFT — FORM -->
        <div>

          <!-- STEP 1: FILE -->
          <div class="card anim anim-d1" style="margin-bottom:20px">
            <div class="card-header">
              <div>
                <div class="card-title">① Ficheiro do Artigo</div>
                <div class="card-sub">Carregue o ficheiro académico a publicar</div>
              </div>
              <span class="badge badge-crimson">Obrigatório</span>
            </div>
            <div class="card-body">
              <div class="drop-zone" id="drop-zone" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
                <input type="file" id="file-input" accept=".pdf,.docx,.doc" onchange="handleFileSelect(event)">
                <div class="dz-icon">📄</div>
                <div class="dz-title">Arraste o ficheiro aqui ou clique para seleccionar</div>
                <div class="dz-subtitle">Suporte a <strong>PDF</strong> até <strong>50 MB</strong></div>
                <div class="dz-formats">
                  <span class="dz-format">PDF</span>
                </div>
              </div>
              <div class="file-preview" id="file-preview">
                <span class="file-icon-big">📄</span>
                <div class="file-info">
                  <div class="file-name" id="file-name">Artigo.pdf</div>
                  <div class="file-size" id="file-size">2,4 MB · PDF</div>
                </div>
                <button class="file-remove" onclick="removeFile()">✕</button>
              </div>
              <div class="upload-progress" id="upload-progress">
                <div class="prog-bar-wrap"><div class="prog-fill" id="prog-fill" style="width:0%"></div></div>
                <div class="prog-text"><span id="prog-label">A carregar…</span><span id="prog-pct">0%</span></div>
              </div>
            </div>
          </div>

          <!-- STEP 2: DOCUMENT INFO -->
          <div class="card anim anim-d2" style="margin-bottom:20px">
            <div class="card-header">
              <div>
                <div class="card-title">② Informações do Artigo</div>
                <div class="card-sub">Preencha os metadados para indexação no acervo</div>
              </div>
            </div>
            <div class="card-body">
              <div class="form-field">
                <label>Título do Trabalho <span class="req">*</span></label>
                <input type="text" id="doc-title" placeholder="Ex: Análise da Produção de Petróleo no Bloco 0 de Cabinda" oninput="updateSummary()">
              </div>
              <div class="form-field">
                <label>Autores (Separar por vírgulas) <span class="req">*</span></label>
                <input type="text" id="doc-authors" placeholder="Ex: Nsumbo Kitekua, Kiala Emanuel, Nádio Mavinga" oninput="updateSummary()">
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-field">
                  <label>Data de Criação <span class="req">*</span></label>
                  <input type="date" id="doc-date" oninput="updateSummary()">
                </div>
                <div class="form-field">
                  <label>Curso <span class="req">*</span></label>
                  <input type="text" id="doc-inst" oninput="updateSummary()" placeholder="Área de formação">
                </div>
              </div>
              <div class="form-field">
                <label>Orientador(a)</label>
                <input type="text" id="doc-advisor" placeholder="Nome do Professor Orientador">
              </div>
              <div class="form-field">
                <label>Resumo / Abstract <span class="req">*</span></label>
                <textarea id="doc-abstract" placeholder="Escreva um resumo claro do trabalho (mínimo 100 caracteres)…" style="min-height:120px"></textarea>
                <div class="field-hint">💡 Um bom resumo aumenta a visibilidade e os downloads do seu trabalho.</div>
              </div>
              <div class="form-field">
                <label>Palavras-Chave (Separar por vírgula) <span class="req">*</span></label>
                <input type="text" id="doc-tags" placeholder="Ex: petróleo, angola, cabinda, exploração (separadas por vírgula)">
              </div>

              <!-- DOC TYPE -->
              <div class="form-field">
                <label>Tipo de Artigo <span class="req">*</span></label>
                <div class="doc-type-grid">
                  <div class="doc-type-option" onclick="selectDocType(this,'Dissertação')">
                    <input type="radio" name="doc-type"><div class="doc-type-icon"><i class="fa fa-student"></i></div><div class="doc-type-label">Dissertação</div>
                  </div>
                  <div class="doc-type-option" onclick="selectDocType(this,'Monografia')">
                    <input type="radio" name="doc-type"><div class="doc-type-icon"><i class="fa fa-book"></i></div><div class="doc-type-label">Monografia</div>
                  </div>
                  <div class="doc-type-option" onclick="selectDocType(this,'PAP')">
                    <input type="radio" name="doc-type"><div class="doc-type-icon"><i class="fa fa-book"></i></div><div class="doc-type-label">PAP</div>
                  </div>
                  <div class="doc-type-option" onclick="selectDocType(this,'Artigo Científico')">
                    <input type="radio" name="doc-type"><div class="doc-type-icon"><i class="fa fa-book"></i></div><div class="doc-type-label">Artigo Científico</div>
                  </div>
                  <div class="doc-type-option" onclick="selectDocType(this,'Tese de Doutoramento')">
                    <input type="radio" name="doc-type"><div class="doc-type-icon"><i class="fa fa-file"></i></div><div class="doc-type-label">Tese Doutoramento</div>
                  </div>
                  <div class="doc-type-option" onclick="selectDocType(this,'Relatório')">
                    <input type="radio" name="doc-type"><div class="doc-type-icon"><i class="fa fa-file"></i></div><div class="doc-type-label">Relatório</div>
                  </div>
                  <div class="doc-type-option" onclick="selectDocType(this,'Outro')">
                    <input type="radio" name="doc-type"><div class="doc-type-icon"><i class="fa fa-file"></i></div><div class="doc-type-label">Outro</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- STEP 3: PUBLICATION -->
          <div class="card anim anim-d3" style="margin-bottom:20px">
            <div class="card-header">
              <div>
                <div class="card-title">③ Modo de Publicação</div>
                <div class="card-sub">Defina quando o Artigo será disponibilizado</div>
              </div>
            </div>
            <div class="card-body">
              <div class="pub-toggle">
                <div class="pub-option selected" id="pub-immediate" onclick="selectPublication('immediate')">
                  <div class="pub-icon"><i class="fa fa-up"></i></div>
                  <div>
                    <div class="pub-title">Publicar Imediatamente</div>
                    <div class="pub-desc">O Artigo fica disponível assim que for aprovado pela equipa editorial.</div>
                  </div>
                </div>
                <div class="pub-option" id="pub-scheduled" onclick="selectPublication('scheduled')">
                  <div class="pub-icon"><i class="fa fa-time"></i></div>
                  <div>
                    <div class="pub-title">Publicação Programada</div>
                    <div class="pub-desc">Escolha uma data e hora futura para a publicação automática.</div>
                  </div>
                </div>
              </div>

              <!-- Scheduler box -->
              <div class="scheduler-box" id="scheduler-box">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
                  <span style="font-size:18px"><i class="fa fa-time"></i></span>
                  <span style="font-size:13px;font-weight:700;color:var(--warn)">Defina a data e hora de publicação</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                  <div class="form-field" style="margin-bottom:0">
                    <label>Data de Publicação <span class="req">*</span></label>
                    <input type="date" id="sched-date" oninput="updateSummary()" style="background:white">
                  </div>
                  <div class="form-field" style="margin-bottom:0">
                    <label>Hora de Publicação <span class="req">*</span></label>
                    <input type="time" id="sched-time" oninput="updateSummary()" style="background:white">
                  </div>
                </div>
                <div class="field-hint" style="margin-top:10px"><i class="fa fa-alert"></i> A publicação ocorre automaticamente na data/hora indicada, após aprovação editorial.</div>
              </div>

              <!-- Access type -->
              <div class="form-field" style="margin-top:20px">
                <label>Tipo de Acesso</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                  <div class="pub-option selected" id="access-paid" onclick="selectAccess('paid')" style="padding:14px">
                    <div class="pub-icon" style="width:36px;height:36px;font-size:16px"><i class="fa fa-money"></i></div>
                    <div><div class="pub-title" style="font-size:13px">Acesso Pago</div><div class="pub-desc">Utilizadores pagam para aceder</div></div>
                  </div>
                  <div class="pub-option" id="access-free" onclick="selectAccess('free')" style="padding:14px">
                    <div class="pub-icon" style="width:36px;height:36px;font-size:16px"><i class="fa fa-free"></i></div>
                    <div><div class="pub-title" style="font-size:13px">Acesso Livre</div><div class="pub-desc">Disponível gratuitamente a todos</div></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- STEP 4: PRICING & PAYMENTS -->
          <div class="card anim anim-d4" style="margin-bottom:20px" id="pricing-card">
            <div class="card-header">
              <div>
                <div class="card-title">④ Preço e Meios de Pagamento</div>
                <div class="card-sub">Defina o valor e as formas de pagamento aceites</div>
              </div>
            </div>
            <div class="card-body">
              <!-- Price -->
              <div class="form-field">
                <label>Valor de Acesso <span class="req">*</span></label>
                <div class="input-suffix">
                  <input type="number" id="doc-price" placeholder="0" min="0" step="50" oninput="updateSummary()">
                  <div class="suffix-tag">Kz</div>
                </div>
                <div class="field-hint">💡 Artigos gratuitos têm mais downloads e aumentam a sua visibilidade.</div>
              </div>

              <!-- Payment Methods -->
              <div class="form-field">
                <label>Meios de Pagamento Aceites <span class="req">*</span> <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:11px;color:var(--text-light)">(pode seleccionar vários)</span></label>
                <div class="payment-methods-grid">
                  <div class="pm-option" onclick="togglePaymentMethod(this,'PAG-001')">
                    <input type="checkbox">
                    <div class="pm-logo">🏦</div>
                    <div><div class="pm-name">Transferência Bancária</div><div class="pm-type">Via IBAN</div></div>
                    <div class="pm-check"></div>
                  </div>
                  <div class="pm-option" onclick="togglePaymentMethod(this,'PAG-002')">
                    <input type="checkbox">
                    <div class="pm-logo">🏧</div>
                    <div><div class="pm-name">Depósito Bancário</div><div class="pm-type">Depósito em conta</div></div>
                    <div class="pm-check"></div>
                  </div>
                  <div class="pm-option" onclick="togglePaymentMethod(this,'PAG-003')">
                    <input type="checkbox">
                    <div class="pm-logo">📱</div>
                    <div><div class="pm-name">Multicaixa Express</div><div class="pm-type">App móvel</div></div>
                    <div class="pm-check"></div>
                  </div>
                  <div class="pm-option" onclick="togglePaymentMethod(this,'PAG-004')">
                    <input type="checkbox">
                    <div class="pm-logo">⚡</div>
                    <div><div class="pm-name">Kwik</div><div class="pm-type">Pagamento digital</div></div>
                    <div class="pm-check"></div>
                  </div>
                </div>
                <div class="field-hint">💡 Oferecer múltiplos meios aumenta as vendas até 40%.</div>
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT — SUMMARY -->
        <div>
          <div class="summary-card">
            <div class="summary-header">
              <h3>Resumo da Submissão</h3>
              <p>Verifique antes de enviar</p>
            </div>
            <div class="summary-body">
              <div class="summary-row">
                <span class="s-label">Título</span>
                <span class="s-val" id="sum-title"><em class="empty">Não preenchido</em></span>
              </div>
              <div class="summary-row">
                <span class="s-label">Tipo</span>
                <span class="s-val" id="sum-type"><em class="empty">Não seleccionado</em></span>
              </div>
              <div class="summary-row">
                <span class="s-label">Data Criação</span>
                <span class="s-val" id="sum-date"><em class="empty">—</em></span>
              </div>
              <div class="summary-row">
                <span class="s-label">Curso</span>
                <span class="s-val" id="sum-inst"><em class="empty">—</em></span>
              </div>
              <div class="summary-row">
                <span class="s-label">Publicação</span>
                <span class="s-val" id="sum-pub">🚀 Imediata</span>
              </div>
              <div class="summary-row" id="sum-sched-row" style="display:none">
                <span class="s-label">Programado para</span>
                <span class="s-val crimson" id="sum-sched">—</span>
              </div>
              <div class="summary-row">
                <span class="s-label">Acesso</span>
                <span class="s-val" id="sum-access">💰 Pago</span>
              </div>
              <div class="summary-row">
                <span class="s-label">Pagamentos</span>
                <span class="s-val" id="sum-payments"><em class="empty">Nenhum</em></span>
              </div>
              <div class="summary-row">
                <span class="s-label">Ficheiro</span>
                <span class="s-val" id="sum-file"><em class="empty">Nenhum</em></span>
              </div>
            </div>
            <div class="summary-price">
              <div class="summary-price-label">Valor de Acesso</div>
              <div class="summary-price-val" id="sum-price">0 Kz</div>
            </div>

            <div style="padding:18px 24px;display:flex;flex-direction:column;gap:10px">
              <button class="btn btn-primary btn-lg" style="width:100%;justify-content:center" onclick="submitDocument()">
                📤 Submeter para Revisão
              </button>
              <button class="btn btn-ghost" style="width:100%;justify-content:center" onclick="saveDraft()">
                💾 Guardar Rascunho
              </button>
            </div>

            <div class="tip-card" style="margin:0 20px 20px">
              <div class="tip-title">💡 Dicas de Publicação</div>
              <ul>
                <li>Títulos descritivos aumentam as pesquisas</li>
                <li>Resumo entre 150–300 palavras é ideal</li>
                <li>Use palavras-chave relevantes à área</li>
                <li>Artigos com capa têm +60% de cliques</li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="assets/js/upload.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/api.js"></script>
<script>
// ═══════════════════════════════
// PAGE NAVIGATION
// ═══════════════════════════════
function switchPage(page) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.page-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  const btns = document.querySelectorAll('.page-btn');
  if (page === 'upload') btns[0].classList.add('active');
  else btns[1].classList.add('active');
}

function saveDraft() {
  showToast('Rascunho guardado com sucesso!');
}

function resetUploadForm() {
  document.getElementById('doc-title').value = '';
  document.getElementById('doc-date').value = '';
  document.getElementById('doc-inst').value = '';
  document.getElementById('doc-abstract').value = '';
  document.getElementById('doc-tags').value = '';
  document.getElementById('doc-price').value = '';
  document.querySelectorAll('.doc-type-option').forEach(o => o.classList.remove('selected'));
  document.querySelectorAll('.pm-option').forEach(o => { o.classList.remove('selected'); o.querySelector('.pm-check').textContent = ''; });
  selectedDocType = ''; selectedPaymentMethods = [];
  selectPublication('immediate'); selectAccess('paid');
  removeFile(); updateSummary();
  showToast('🔄 Formulário limpo');
}

// ═══════════════════════════════
// MODAL HELPERS
// ═══════════════════════════════
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalOutside(e, id) { if (e.target.id === id) closeModal(id); }


// Init
document.getElementById('doc-date').valueAsDate = new Date();
updateSummary();
</script>
</body>
</html>
