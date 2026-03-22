
function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3200);
}

function checkStrength(val) {
    const fill = document.getElementById('s-fill');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const widths = ['0%', '25%', '50%', '75%', '100%'];
    const colors = ['#6B1020', '#B83030', '#D4852A', '#8CC43C', '#2D9E5E'];
    fill.style.width = widths[score];
    fill.style.background = colors[score];
}

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('visible'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('section-' + tab).classList.add('visible');
}


/* ══════════════════════════════════════
   SIDEBAR
══════════════════════════════════════ */
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sb-overlay');
const sbClose  = document.getElementById('sb-close');
const sbCollapse=document.getElementById('sb-collapse');
let collapsed  = false;

function checkBP() {
  const w = window.innerWidth;
  if (w < 768) {
    sbClose.style.display   = sidebar.classList.contains('open') ? 'flex' : 'none';
    sbCollapse.style.display= 'none';
    sidebar.classList.remove('collapsed');
  } else if (w < 1024) {
    sbClose.style.display   = 'none';
    sbCollapse.style.display= 'none';
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow='';
  } else {
    sbClose.style.display    = 'none';
    sbCollapse.style.display = 'flex';
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow='';
    sbCollapse.textContent = collapsed ? '▶' : '◀';
  }
}
function openSidebar() {
  sidebar.classList.add('open');
  overlay.style.display='block';
  setTimeout(()=>overlay.classList.add('open'),10);
  sbClose.style.display='flex';
  document.body.style.overflow='hidden';
}
function closeSidebar() {
  sidebar.classList.remove('open');
  overlay.classList.remove('open');
  setTimeout(()=>overlay.style.display='none',300);
  sbClose.style.display='none';
  document.body.style.overflow='';
}
function toggleCollapse() {
  collapsed = !collapsed;
  sidebar.classList.toggle('collapsed', collapsed);
  sbCollapse.textContent = collapsed ? '▶' : '◀';
}
document.querySelectorAll('.nav-it').forEach(it => {
  it.addEventListener('click', () => { if (window.innerWidth<768) closeSidebar(); });
});
window.addEventListener('resize', checkBP);
checkBP();

/* ══════════════════════════════════════
   TOAST
══════════════════════════════════════ */
function showToast(msg, type='') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast' + (type ? ' '+type : '');
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 3400);
}