
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sb-overlay');
const closeBtn = document.getElementById('sb-close-btn');
const collapseBtn = document.getElementById('sb-collapse-btn');

let isCollapsed = false;
let isMobile = false;

function checkBreakpoint() {
  const w = window.innerWidth;
  isMobile = w < 768;

  if (isMobile) {
    // Mobile: hide collapse btn, show close btn only when open
    collapseBtn.style.display = 'none';
    closeBtn.style.display = sidebar.classList.contains('open') ? 'flex' : 'none';
    sidebar.classList.remove('collapsed');
  } else if (w < 1024) {
    // Tablet: icon-only (handled purely by CSS), hide both toggle buttons
    collapseBtn.style.display = 'none';
    closeBtn.style.display = 'none';
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  } else {
    // Desktop: show collapse btn
    collapseBtn.style.display = 'flex';
    closeBtn.style.display = 'none';
    sidebar.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    collapseBtn.textContent = isCollapsed ? '▶' : '◀';
  }
}

function openSidebar() {
  sidebar.classList.add('open');
  overlay.style.display = 'block';
  setTimeout(() => overlay.classList.add('open'), 10);
  closeBtn.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeSidebar() {
  sidebar.classList.remove('open');
  overlay.classList.remove('open');
  setTimeout(() => { overlay.style.display = 'none'; }, 300);
  closeBtn.style.display = 'none';
  document.body.style.overflow = '';
}

function toggleCollapse() {
  isCollapsed = !isCollapsed;
  sidebar.classList.toggle('collapsed', isCollapsed);
  collapseBtn.textContent = isCollapsed ? '▶' : '◀';
}

// Close sidebar on nav item click (mobile)
document.querySelectorAll('.nav-it').forEach(it => {
  it.addEventListener('click', () => { if (isMobile) closeSidebar(); });
});

window.addEventListener('resize', checkBreakpoint);
checkBreakpoint();