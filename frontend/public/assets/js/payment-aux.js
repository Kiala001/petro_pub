
const isEdition = false;

const typeConfig = {
  iban:    { ico:'', cls:'ico-iban',    label:'Transferência Bancária', badgeCls:'bb' },
  express: { ico:'', cls:'ico-express', label:'Multicaixa Express',     badgeCls:'bo' },
  kwik:    { ico:'', cls:'ico-kwik',    label:'Kwik',                   badgeCls:'bgd' },
};

function renderList(data) {
  const list  = document.getElementById('pm-list');
  const empty = document.getElementById('empty-state');

  if (!data.length) {
    list.innerHTML = '';
    empty.style.display = 'block';
    updateStats();
    return;
  }
  empty.style.display = 'none';


  list.innerHTML = data.map((pm,i) => {
    const step1 = JSON.parse(pm.data)
    const paymentMethod = JSON.parse(step1)
    
    const tc = typeConfig[paymentMethod.type];
    const delay = (i * 0.05).toFixed(2);

    localStorage.setItem(pm.id, JSON.stringify(paymentMethod))

    return `
    <div class="pm-card type-${paymentMethod.type}" data-id="${pm.id}" style="animation-delay:${delay}s">
      <div class="pm-icon-col">
        <div class="pm-ico-box ${tc.cls}"><i class="fa fa-bank"></i></div>
      </div>
      <div class="pm-body">
        <div class="pm-top-row">
          <span class="pm-name">${paymentMethod.name}</span>
          <span class="badge ${tc.badgeCls}">${tc.label}</span>
          ${(paymentMethod.active == 1)
            ? '<span class="badge bg">✓ Activo</span>'
            : '<span class="badge bw">⏸ Inactivo</span>'}
        </div>
        <div class="pm-meta">
          <div class="pm-meta-item"><i class="fa fa-key"></i> <strong>${paymentMethod.detail}</strong></div>
        </div>
      </div>
      <div class="pm-actions">
        <div class="toggle-wrap" onclick="togglePM(${pm.id})">
          <div class="toggle-track ${paymentMethod.active?'on':'off'}" id="tt-${pm.id}"><div class="toggle-knob"></div></div>
          <span class="toggle-lbl">${paymentMethod.active?'Activo':'Inactivo'}</span>
        </div>
        <div class="pm-actions-row">
          <button class="btn btn-ok btn-sm" onclick="openEdit('${pm.id}')">✎ Editar</button>
          <button class="btn btn-er btn-sm" onclick="openDelete('${pm.id}')">🗑 Excluir</button>
        </div>
      </div>
    </div>`;
  }).join('');
  updateStats();
}

function updateStats() {
  document.getElementById('stat-total').textContent  = pmData.length;
  document.getElementById('stat-active').textContent = pmData.filter(p=>p.active).length;
}

function getFiltered() {
  const q = (document.getElementById('search-input').value||'').toLowerCase();
  return pmData.filter(pm => {
    const matchType   = currentFilter === 'all' || pm.type === currentFilter;
    const matchSearch = !q ||
      pm.name.toLowerCase().includes(q) ||
      pm.detail.toLowerCase().includes(q) ||
      (pm.extra||'').toLowerCase().includes(q);
    return matchType && matchSearch;
  });
}

function refreshList() { renderList(getFiltered()); }

function filterTab(type, btn) {
  currentFilter = type;
  document.querySelectorAll('.ft-btn').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  refreshList();
}

function filterList(q) { refreshList(); }

/* ══════════════════════════════════════
   TOGGLE ACTIVE
══════════════════════════════════════ */
function togglePM(id) {
  const pm = pmData.find(p=>p.id===id);
  if (!pm) return;
  pm.active = !pm.active;
  refreshList();
  showToast(pm.active ? '✅ Meio activado com sucesso' : '⏸ Meio desactivado', pm.active ? 'success' : '');
}

function toggleSwitch(trackId) {
  const track = document.getElementById(trackId);
  const lbl   = document.getElementById(trackId+'-lbl');
  if (!track) return;
  const isOn = track.classList.contains('on');
  track.classList.toggle('on',  !isOn);
  track.classList.toggle('off',  isOn);
  if (lbl) lbl.textContent = isOn ? 'Inactivo' : 'Activo';
  modalActive = !isOn;
}

/* ══════════════════════════════════════
   METHOD TYPE SELECTOR
══════════════════════════════════════ */
function selectMType(type) {
  currentMType = type;
  ['iban','express','kwik'].forEach(t => {
    document.getElementById('mt-'+t).classList.toggle('sel', t===type);
    document.getElementById('fields-'+t).classList.toggle('visible', t===type);
  });
}

/* ══════════════════════════════════════
   KWIK TYPE
══════════════════════════════════════ */
function selKwik(type) {
  currentKwik = type;
  ['alcunha','iban','numero','email'].forEach(t => {
    document.getElementById('kc-'+t).classList.toggle('sel', t===type);
    document.getElementById('kf-'+t).style.display = t===type ? 'block' : 'none';
  });
}

/* ══════════════════════════════════════
   IBAN FORMATTER
══════════════════════════════════════ */
function formatIBAN(el) {
  let v = el.value.replace(/\s/g,'').toUpperCase();
  el.value = v.match(/.{1,4}/g)?.join(' ')||v;
}
