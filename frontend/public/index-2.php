<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Biblioteca</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<style>
:root{
  --cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);
  --gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;--gd-bg:rgba(201,168,76,.11);
  --cream:#FAF7F2;--warm:#FEF9F3;
  --bdr:rgba(107,16,32,.10);--bdr2:rgba(107,16,32,.06);
  --tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;
  --ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);
  --wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);
  --er:#C53030;--er-bg:rgba(197,48,48,.10);
  --inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);
  --pu:#5A3A8A;--pu-bg:rgba(90,58,138,.10);
  --sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);--sh2:0 8px 32px rgba(107,16,32,.13);--sh3:0 24px 64px rgba(107,16,32,.18);
  --r1:7px;--r2:11px;--r3:15px;--r4:20px;
  --sb-w:272px;--nav-h:60px;
  --t:.22s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button{font-family:inherit}a{color:inherit;text-decoration:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--r2);font-size:13px;font-weight:700;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-gh{background:#fff;color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-sm{padding:6px 14px;font-size:12px;border-radius:var(--r1)}

/* ─── NAV ─── */
.nav{background:#fff;border-bottom:1px solid var(--bdr);position:sticky;top:0;z-index:300;box-shadow:var(--sh0)}
.nav-inner{display:flex;align-items:center;gap:10px;height:var(--nav-h);max-width:1280px;margin:0 auto;padding:0 clamp(14px,4vw,40px)}
.nav-logo{font-family:'Arial',serif;font-weight:900;font-size:20px;color:var(--cr-dk);white-space:nowrap;flex-shrink:0}
.nav-logo span{color:var(--gd)}
.nav-bc{font-size:12px;color:var(--tx-l);display:flex;align-items:center;gap:5px}
.nav-bc a{color:var(--cr);font-weight:600;cursor:pointer}.nav-bc a:hover{text-decoration:underline}
.nav-search{flex:1;position:relative;max-width:500px;margin:0 10px}
.nav-s-input{width:100%;padding:8px 14px 8px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}
.nav-s-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
.nav-s-input::placeholder{color:var(--tx-l)}
.ns-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
.nav-r{display:flex;align-items:center;gap:7px;flex-shrink:0;margin-left:auto}
.ham{width:36px;height:36px;border-radius:var(--r1);background:none;border:1.5px solid var(--bdr);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;cursor:pointer;transition:all var(--t);flex-shrink:0;padding:0}
.ham:hover{border-color:var(--cr-bdr);background:var(--cr-xl)}
.ham-line{width:14px;height:1.5px;background:var(--tx-m);border-radius:1px}

/* ─── LAYOUT ─── */
.layout{display:flex;max-width:1280px;margin:0 auto;min-height:calc(100vh - var(--nav-h))}

/* ─── SIDEBAR ─── */
.sidebar{width:var(--sb-w);flex-shrink:0;background:#fff;border-right:1px solid var(--bdr);padding:0;position:sticky;top:var(--nav-h);height:calc(100vh - var(--nav-h));overflow-y:auto;transition:transform var(--t)}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:var(--bdr)}
.sb-header{padding:16px 20px 12px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;gap:8px}
.sb-title{font-size:13px;font-weight:800;color:var(--tx);text-transform:uppercase;letter-spacing:.8px;display:flex;align-items:center;gap:6px}
.sb-reset{font-size:11px;font-weight:600;color:var(--cr);cursor:pointer;white-space:nowrap}
.sb-reset:hover{text-decoration:underline}
.sb-block{padding:14px 20px;border-bottom:1px solid var(--bdr2)}
.sb-block:last-child{border-bottom:none}
.sb-block-title{font-size:11px;font-weight:800;color:var(--tx-l);text-transform:uppercase;letter-spacing:1px;margin-bottom:11px;display:flex;align-items:center;justify-content:space-between}
.sb-block-title button{font-size:10px;font-weight:600;color:var(--cr);background:none;border:none;cursor:pointer;text-transform:none;letter-spacing:0}
/* checkbox */
.cb-item{display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer}
.cb-item:hover .cb-lbl{color:var(--cr)}
.cb-box{width:15px;height:15px;border-radius:4px;border:1.5px solid var(--bdr);background:#fff;flex-shrink:0;appearance:none;-webkit-appearance:none;cursor:pointer;transition:all .15s;position:relative}
.cb-box:checked{background:var(--cr);border-color:var(--cr)}
.cb-box:checked::after{content:'✓';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-size:9px;font-weight:800}
.cb-lbl{font-size:13px;color:var(--tx-m);flex:1}
.cb-count{font-size:11px;color:var(--tx-l)}
/* radio */
.rb-item{display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer}
.rb-item:hover .rb-lbl{color:var(--cr)}
.rb-dot{width:15px;height:15px;border-radius:50%;border:1.5px solid var(--bdr);background:#fff;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s}
.rb-item.sel .rb-dot{border-color:var(--cr);background:var(--cr)}
.rb-item.sel .rb-dot::after{content:'';width:5px;height:5px;border-radius:50%;background:#fff}
.rb-lbl{font-size:13px;color:var(--tx-m)}
/* year input */
.year-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.yr-input{padding:7px 10px;border:1.5px solid var(--bdr);border-radius:var(--r1);font-size:13px;color:var(--tx);background:var(--cream);outline:none;width:100%;transition:all var(--t)}
.yr-input:focus{border-color:var(--cr);background:#fff}
/* star filter */
.star-row{display:flex;flex-direction:column;gap:6px}
.star-item{display:flex;align-items:center;gap:8px;cursor:pointer;padding:4px 6px;border-radius:var(--r1);transition:background var(--t)}
.star-item:hover{background:var(--cr-xl)}
.star-item.sel{background:var(--gd-bg)}
.star-lbl{font-size:12px;color:var(--tx-m);font-weight:500}

/* apply button */
.sb-apply-btn{width:100%;padding:10px;border-radius:var(--r2);background:var(--cr);color:#fff;border:none;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;margin:16px 20px 16px;width:calc(100% - 40px);transition:background var(--t);box-shadow:0 3px 10px rgba(107,16,32,.22)}
.sb-apply-btn:hover{background:var(--cr-dk)}

/* ─── MAIN ─── */
.main{flex:1;min-width:0;padding:clamp(16px,2.5vw,24px) clamp(14px,3vw,28px)}

/* top bar */
.top-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:clamp(14px,2vw,18px);flex-wrap:wrap}
.result-info{font-size:14px;color:var(--tx-l)}
.result-info strong{color:var(--tx);font-weight:700}
.bar-r{display:flex;align-items:center;gap:8px}
.sort-sel{padding:7px 28px 7px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 9px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}
.sort-sel:focus{border-color:var(--cr)}
.vt{display:flex;gap:3px}
.vt-btn{width:32px;height:32px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;transition:all var(--t);color:var(--tx-l)}
.vt-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}
.mob-filter-btn{display:none;gap:6px;padding:7px 14px;border-radius:var(--r2);border:1.5px solid var(--bdr);background:#fff;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;align-items:center;transition:all var(--t)}
.mob-filter-btn:hover{border-color:var(--cr-bdr);color:var(--cr)}
.f-badge{background:var(--cr);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:100px}

/* active tags */
.active-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.a-tag{display:flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;background:var(--cr-xl);border:1px solid var(--cr-bdr);font-size:12px;font-weight:600;color:var(--cr)}
.a-tag button{background:none;border:none;color:var(--cr);cursor:pointer;font-size:12px;padding:0;line-height:1}

/* ─── CARDS ─── */
.docs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(200px,22vw,230px),1fr));gap:clamp(12px,2vw,16px)}
.docs-grid.list-view{grid-template-columns:1fr;gap:10px}

.doc-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);overflow:hidden;cursor:pointer;transition:all var(--t);animation:fadeUp .35s ease both}
.doc-card:hover{box-shadow:var(--sh2);transform:translateY(-3px);border-color:rgba(107,16,32,.20)}

/* grid card */
.dc-thumb{height:clamp(88px,11vw,120px);display:flex;align-items:center;justify-content:center;font-size:clamp(32px,5vw,42px);position:relative}
.dc-body{padding:clamp(11px,1.6vw,14px)}
.dc-type-row{display:flex;align-items:center;gap:6px;margin-bottom:7px;flex-wrap:wrap}
.dc-tag{font-size:10px;font-weight:700;padding:2px 8px;border-radius:100px}
.dt-tcc{background:var(--inf-bg);color:var(--inf)}.dt-art{background:var(--ok-bg);color:var(--ok)}.dt-liv{background:var(--gd-bg);color:var(--gd-dk)}.dt-dis{background:var(--pu-bg);color:var(--pu)}.dt-rel{background:var(--wn-bg);color:var(--wn)}.dt-apr{background:var(--cr-xl);color:var(--cr)}
.free-tag{background:var(--ok-bg);color:var(--ok);border:1px solid rgba(45,122,79,.2);font-size:9px;font-weight:800;padding:2px 7px;border-radius:100px}
.dc-title{font-family:'Arial',serif;font-size:clamp(13px,1.4vw,14px);font-weight:700;color:var(--tx);line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:5px}
.dc-author{font-size:12px;color:var(--tx-l);margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dc-meta-row{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:10px}
.dc-rating{font-size:12px;color:var(--gd-dk);font-weight:600}
.dc-pages{font-size:11px;color:var(--tx-l)}
.dc-actions{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.dc-btn-see{padding:6px;border-radius:var(--r1);background:var(--cream);border:1px solid var(--bdr);font-size:11px;font-weight:700;color:var(--tx-m);cursor:pointer;text-align:center;transition:all var(--t)}
.dc-btn-see:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.dc-btn-read{padding:6px;border-radius:var(--r1);background:var(--cr);color:#fff;border:none;font-size:11px;font-weight:700;cursor:pointer;text-align:center;transition:all var(--t);display:flex;align-items:center;justify-content:center;gap:4px}
.dc-btn-read:hover{background:var(--cr-dk)}
.dc-btn-buy{padding:6px;border-radius:var(--r1);background:linear-gradient(135deg,var(--gd-dk),var(--gd));color:var(--cr-dk);border:none;font-size:11px;font-weight:800;cursor:pointer;text-align:center;transition:all var(--t);grid-column:1/-1}
.dc-btn-buy:hover{filter:brightness(1.05)}

/* list view overrides */
.docs-grid.list-view .doc-card{display:flex;gap:0}
.docs-grid.list-view .dc-thumb{width:80px;height:auto;flex-shrink:0;border-radius:var(--r3) 0 0 var(--r3);min-height:120px}
.docs-grid.list-view .dc-body{flex:1;display:flex;flex-direction:column}
.docs-grid.list-view .dc-actions{grid-template-columns:auto auto auto;width:fit-content;margin-top:auto}
.docs-grid.list-view .dc-btn-buy{grid-column:unset}

/* skeleton */
@keyframes shimmer{0%{background-position:-600px 0}100%{background-position:600px 0}}
.skel{background:linear-gradient(90deg,#f0e8e0 25%,#e4d8ce 50%,#f0e8e0 75%);background-size:600px 100%;animation:shimmer 1.4s infinite;border-radius:4px}
.skel-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);overflow:hidden}
.skel-thumb{height:100px}.skel-body{padding:12px}.skel-line{height:10px;margin-bottom:8px;border-radius:4px}

/* pagination */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:clamp(24px,4vw,36px);flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t)}
.pg-btn:hover:not(.on):not(:disabled){border-color:var(--cr-bdr);color:var(--cr)}
.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}
.pg-btn:disabled{opacity:.3;cursor:not-allowed}

/* empty */
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);grid-column:1/-1}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:12px}
.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}

/* ─── MOBILE SIDEBAR MODAL ─── */
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:500;backdrop-filter:blur(3px);opacity:0;transition:opacity .28s}
.sb-ov.open{opacity:1}
.sidebar-modal{position:fixed;left:0;top:0;bottom:0;width:min(310px,90vw);background:#fff;z-index:600;transform:translateX(-100%);transition:transform .3s cubic-bezier(.4,0,.2,1);overflow-y:auto}
.sidebar-modal.open{transform:translateX(0)}
.sm-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;border-bottom:1px solid var(--bdr)}
.sm-head-title{font-size:15px;font-weight:800;color:var(--tx)}
.sm-close{width:28px;height:28px;border-radius:50%;background:var(--cream);border:1px solid var(--bdr);font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--tx-m)}

/* ─── LOGIN PROMPT ─── */
.login-prompt{position:fixed;bottom:clamp(16px,3vw,24px);left:50%;transform:translateX(-50%);z-index:400;background:#fff;border-radius:var(--r4);box-shadow:var(--sh3);border:1px solid var(--bdr);padding:clamp(14px,2vw,18px) clamp(18px,3vw,24px);display:none;align-items:center;gap:clamp(10px,2vw,16px);max-width:min(500px,90vw);width:100%;animation:slideUp .3s cubic-bezier(.22,1,.36,1)}
.login-prompt.show{display:flex}
@keyframes slideUp{from{opacity:0;transform:translateX(-50%) translateY(18px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}
.lp-body{flex:1;min-width:0}
.lp-title{font-size:13px;font-weight:700;color:var(--tx);margin-bottom:2px}
.lp-sub{font-size:12px;color:var(--tx-l)}
.lp-close{width:26px;height:26px;border-radius:50%;background:var(--cream);border:1px solid var(--bdr);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--tx-l);flex-shrink:0}

/* toast */
.toast{position:fixed;bottom:20px;right:20px;z-index:9999;transform:translateY(24px);background:var(--cr-dk);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;line-height:1.4;border:1px solid rgba(201,168,76,.2)}
.toast.show{opacity:1;transform:translateY(0)}

@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}

/* ─── RESPONSIVE ─── */
@media(max-width:860px){.sidebar{display:none}.mob-filter-btn{display:flex}}
@media(max-width:600px){.docs-grid{grid-template-columns:repeat(2,1fr)}.top-bar .bar-r .sort-sel{display:none}}
@media(max-width:440px){.nav-search{display:none}.nav-bc{display:none}.docs-grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>

<div class="toast" id="toast"></div>
<div class="sb-ov" id="sb-ov" onclick="closeMobSB()"></div>
<div class="sidebar-modal" id="sidebar-modal">
  <div class="sm-head">
    <div class="sm-head-title">⚙️ Filtros</div>
    <button class="sm-close" onclick="closeMobSB()">✕</button>
  </div>
  <div id="mob-sb-content"></div>
</div>

<!-- LOGIN PROMPT -->
<div class="login-prompt" id="login-prompt">
  <span style="font-size:26px;flex-shrink:0">🔐</span>
  <div class="lp-body">
    <div class="lp-title" id="lp-title">Faça login para continuar</div>
    <div class="lp-sub" id="lp-sub">Crie uma conta gratuita para acesso completo</div>
  </div>
  <button class="btn btn-gh btn-sm" onclick="window.location.href='petropub-auth.html'">Registar</button>
  <button class="btn btn-cr btn-sm" onclick="window.location.href='petropub-auth.html'">Entrar</button>
  <button class="lp-close" onclick="document.getElementById('login-prompt').classList.remove('show')">✕</button>
</div>

<!-- NAV -->
<nav class="nav">
  <div class="nav-inner">
    <div class="nav-logo" onclick="window.location.href='petropub-dashboard.html'">PETRO<span>PUB</span></div>
    <div class="nav-bc">
      <a onclick="window.location.href='petropub-dashboard.html'">Home</a>
      <span style="color:var(--tx-l)">›</span>
      <span style="font-weight:600;color:var(--tx-m)">Biblioteca</span>
    </div>
    <div class="nav-search">
      <span class="ns-ico">🔍</span>
      <input class="nav-s-input" id="nav-search" type="text" placeholder="Pesquisar documentos…" oninput="onSearch(this.value)" onkeydown="if(event.key==='Enter')applyFilters()">
    </div>
    <div class="nav-r">
      <button class="btn btn-cr btn-sm" onclick="requireLogin('upload')">📤 Submeter</button>
      <button class="btn btn-gh btn-sm" onclick="window.location.href='petropub-auth.html'">Entrar</button>
      <div class="ham" onclick="window.location.href='petropub-dashboard.html'" title="Menu">
        <div class="ham-line"></div><div class="ham-line" style="width:10px;align-self:flex-start"></div><div class="ham-line"></div>
      </div>
    </div>
  </div>
</nav>

<div class="layout">

  <!-- SIDEBAR DESKTOP -->
  <aside class="sidebar" id="sidebar-desktop">
    <div id="sidebar-inner"></div>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <div class="top-bar">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <button class="mob-filter-btn" onclick="openMobSB()">⚙️ Filtros <span class="f-badge" id="f-badge" style="display:none">0</span></button>
        <div class="result-info">A mostrar <strong id="result-num">—</strong> documentos</div>
      </div>
      <div class="bar-r">
        <select class="sort-sel" id="sort-sel" onchange="applyFilters()">
          <option value="recent">Mais recentes</option>
          <option value="popular">Mais populares</option>
          <option value="rated">Melhor avaliados</option>
          <option value="price-asc">Preço ↑</option>
          <option value="price-desc">Preço ↓</option>
        </select>
        <div class="vt">
          <button class="vt-btn on" id="vt-grid" onclick="setView('grid',this)" title="Grelha">⊞</button>
          <button class="vt-btn" id="vt-list" onclick="setView('list',this)" title="Lista">☰</button>
        </div>
      </div>
    </div>

    <div class="active-tags" id="active-tags"></div>
    <div id="doc-grid" class="docs-grid"></div>
    <div class="pagination" id="pagination"></div>
  </main>

</div>

<script>
/* ══════════════════════════════════
   DATA — 30 DOCS
══════════════════════════════════ */
const types=['TCC','Artigo','Livro','Dissertação','Relatório','Apresentação'];
const tCls= ['dt-tcc','dt-art','dt-liv','dt-dis','dt-rel','dt-apr'];
const icos= ['🎓','📄','📖','📘','📊','📑'];
const cats= ['Eng. Informática','Eng. Petróleo','Gestão','Medicina','Direito','Electrotécnica','Matemática','Arquitectura'];
const insts=['ISPTEC','UAN','UCAN','Metodista','Jean Piaget','Lusíada'];
const authors=['Kiala Emanuel','Filomena Luvualu','João Manuel','Sónia Pimentel','Carlos Neto','Ana Rodrigues','Pedro Matos','Ricardo Dias','Luísa Baptista','Prof. Helena Lima','Rui Ferreira','Marta Costa'];
const prices=[0,0,500,0,1200,1500,0,2000,800,0,3000,2500,600,0,1800,0,900,1400,0,2800,1100,700,0,1600,2200,0,3200,500,1000,0];
const titles=[
  'Algoritmos de Aprendizagem Profunda para Detecção de Falhas em Sistemas Petrolíferos',
  'Gestão Estratégica de Projectos no Sector Petrolífero Angolano',
  'Redes Neurais Convolucionais Aplicadas à Diagnose Médica',
  'Sistemas Distribuídos e Computação em Nuvem para Grandes Empresas',
  'Direito Empresarial Angolano: Contratos e Responsabilidade Civil',
  'Análise de Dados com Python para Engenharia do Petróleo',
  'Energias Renováveis e Sustentabilidade no Sistema Eléctrico de Angola',
  'Fundamentos de Cibersegurança para Infraestruturas Críticas',
  'Arquitectura de Microserviços e DevOps: Práticas Modernas',
  'Macroeconomia Angolana: Petróleo, Diversificação e Desenvolvimento',
  'Cirurgia Minimamente Invasiva: Técnicas Avançadas e Protocolos',
  'Computação Quântica: Princípios, Algoritmos e Aplicações Futuras',
  'Gestão de Recursos Humanos em Contexto Multicultural Africano',
  'Processamento de Linguagem Natural com Modelos Transformers',
  'Estruturas de Betão Armado: Cálculo e Dimensionamento',
  'Finanças Corporativas e Mercados de Capitais em Angola',
  'Inteligência Artificial na Medicina: Da Teoria à Prática Clínica',
  'Sistemas de Informação Geográfica para Engenharia Civil',
  'Marketing Digital e E-commerce no Mercado Angolano',
  'Física Computacional: Simulação de Sistemas Complexos',
  'Direito Internacional Privado: Casos e Soluções Actuais',
  'Bioinformática e Genómica Computacional: Fundamentos',
  'Contabilidade Financeira para Empresas Petrolíferas',
  'Robótica Industrial: Programação e Integração de Sistemas',
  'Geologia de Reservatórios da Bacia do Congo',
  'Introdução ao Machine Learning para Engenheiros',
  'Epidemiologia das Doenças Tropicais em Angola',
  'Telecomunicações e Redes 5G: Impacto para Angola',
  'Química Industrial Aplicada à Refinação do Petróleo',
  'Saúde Pública e Políticas de Saúde em Angola',
];
const bgCols=['hsl(200,55%,92%)','hsl(140,45%,92%)','hsl(220,55%,92%)','hsl(40,55%,92%)','hsl(0,45%,92%)','hsl(280,45%,92%)','hsl(60,55%,92%)','hsl(180,45%,92%)','hsl(300,45%,92%)','hsl(20,45%,92%)'];

const allDocs=Array.from({length:30},(_,i)=>{
  const ti=i%types.length;
  return{id:i+1,title:titles[i],author:authors[i%authors.length],inst:insts[i%insts.length],
    type:types[ti],tcls:tCls[ti],ico:icos[ti],bg:bgCols[i%bgCols.length],
    cat:cats[i%cats.length],pages:12+i*4,price:prices[i]||0,isFree:prices[i]===0,
    rating:+Math.min(5,Math.max(3,(3.2+(Math.sin(i)*1.4+1)))).toFixed(1),
    downloads:80+i*67,year:2022+(i%4),
  };
});

/* ══ STATE ══ */
let st={q:'',types:[],cats:[],author:'',yearFrom:'',yearTo:'',rating:'',free:null,sort:'recent',view:'grid',page:1};
let debT;
const PER=12;

/* ══ FILTER ENGINE ══ */
function getFiltered(){
  let d=[...allDocs];
  if(st.q){const q=st.q.toLowerCase();d=d.filter(x=>x.title.toLowerCase().includes(q)||x.author.toLowerCase().includes(q)||x.cat.toLowerCase().includes(q));}
  if(st.types.length) d=d.filter(x=>st.types.includes(x.type));
  if(st.cats.length)  d=d.filter(x=>st.cats.includes(x.cat));
  if(st.author)       d=d.filter(x=>x.author.toLowerCase().includes(st.author.toLowerCase()));
  if(st.yearFrom)     d=d.filter(x=>x.year>=+st.yearFrom);
  if(st.yearTo)       d=d.filter(x=>x.year<=+st.yearTo);
  if(st.rating==='4') d=d.filter(x=>x.rating>=4);
  if(st.rating==='5') d=d.filter(x=>x.rating>=4.5);
  if(st.free===true)  d=d.filter(x=>x.isFree);
  if(st.free===false) d=d.filter(x=>!x.isFree);
  if(st.sort==='popular')   d.sort((a,b)=>b.downloads-a.downloads);
  else if(st.sort==='rated')d.sort((a,b)=>b.rating-a.rating);
  else if(st.sort==='price-asc') d.sort((a,b)=>a.price-b.price);
  else if(st.sort==='price-desc')d.sort((a,b)=>b.price-a.price);
  else d.sort((a,b)=>b.year-a.year);
  return d;
}

/* ══ RENDER ══ */
function stars(r){const f=Math.min(5,Math.max(0,Math.round(+r)));return'★'.repeat(f)+'☆'.repeat(5-f);}
function renderCard(d,list=false){
  return`<div class="doc-card" style="animation-delay:${Math.random()*.2}s">
  <div class="dc-thumb" style="background:${d.bg}">${d.ico}</div>
  <div class="dc-body">
    <div class="dc-type-row">
      <span class="dc-tag ${d.tcls}">${d.type}</span>
      ${d.isFree?'<span class="free-tag">GRÁTIS</span>':''}
    </div>
    <div class="dc-title">${d.title}</div>
    <div class="dc-author">👤 ${d.author} · ${d.inst}</div>
    <div class="dc-meta-row">
      <span class="dc-rating">${stars(d.rating)} ${d.rating}</span>
      <span class="dc-pages">📄 ${d.pages} págs · ${d.year}</span>
    </div>
    <div class="dc-actions">
      <button class="dc-btn-see" onclick="showToast('📄 Resumo de: '+${JSON.stringify(d.title.substring(0,30))}+'…')">Ver resumo</button>
      ${d.isFree
        ?`<button class="dc-btn-read" onclick="requireLogin('read')">📖 Ler completo 🔒</button>`
        :``}
      ${!d.isFree
        ?`<button class="dc-btn-buy" onclick="requireLogin('download')">🛒 Comprar — ${d.price.toLocaleString('pt-PT')} Kz</button>`
        :''}
    </div>
  </div>
</div>`;
}
function renderSkeleton(){
  return Array.from({length:8},()=>`<div class="skel-card"><div class="skel-thumb skel"></div><div class="skel-body"><div class="skel-line skel" style="width:100%;height:10px"></div><div class="skel-line skel" style="width:70%;height:8px"></div><div class="skel-line skel" style="width:50%;height:8px"></div></div></div>`).join('');
}

function applyFilters(skipSkeleton=false){
  renderActiveTags(); updateFilterBadge();
  if(!skipSkeleton){
    document.getElementById('doc-grid').innerHTML=renderSkeleton();
    document.getElementById('result-num').textContent='…';
    setTimeout(_doRender,300);
  } else _doRender();
}
function _doRender(){
  const data=getFiltered(), total=data.length;
  const pages=Math.max(1,Math.ceil(total/PER));
  st.page=Math.min(st.page,pages);
  const slice=data.slice((st.page-1)*PER, st.page*PER);
  const grid=document.getElementById('doc-grid');
  grid.className='docs-grid'+(st.view==='list'?' list-view':'');
  document.getElementById('result-num').textContent=total;
  grid.innerHTML=!slice.length
    ?`<div class="empty"><div class="empty-ico">🔍</div><div class="empty-title">Nenhum resultado</div><p style="font-size:14px;color:var(--tx-l);margin-top:6px">Tente ajustar os filtros.</p><button class="btn btn-cr" style="margin:16px auto 0;display:flex" onclick="resetAll()">Limpar filtros</button></div>`
    :slice.map(d=>renderCard(d,st.view==='list')).join('');
  // pagination
  const pgEl=document.getElementById('pagination');
  if(pages<=1){pgEl.innerHTML='';return;}
  let h=`<button class="pg-btn" onclick="goPage(${st.page-1})" ${st.page<=1?'disabled':''}>‹</button>`;
  for(let i=1;i<=pages;i++){
    if(i===1||i===pages||Math.abs(i-st.page)<=1) h+=`<button class="pg-btn${i===st.page?' on':''}" onclick="goPage(${i})">${i}</button>`;
    else if(Math.abs(i-st.page)===2) h+=`<span style="padding:0 3px;color:var(--tx-l);font-size:13px">…</span>`;
  }
  h+=`<button class="pg-btn" onclick="goPage(${st.page+1})" ${st.page>=pages?'disabled':''}>›</button>`;
  pgEl.innerHTML=h;
}
function goPage(p){st.page=p;applyFilters(true);window.scrollTo({top:0,behavior:'smooth'});}
function setView(v,btn){st.view=v;document.querySelectorAll('.vt-btn').forEach(b=>b.classList.remove('on'));btn.classList.add('on');applyFilters(true);}

/* ══ ACTIVE TAGS ══ */
function renderActiveTags(){
  const tags=[];
  if(st.q) tags.push({lbl:`"${st.q}"`,rm:()=>{st.q='';document.getElementById('nav-search').value='';document.getElementById('q-sb')&&(document.getElementById('q-sb').value='');}});
  st.types.forEach(t=>tags.push({lbl:t,rm:()=>{st.types=st.types.filter(x=>x!==t);buildSidebar();}}));
  st.cats.forEach(c=>tags.push({lbl:c,rm:()=>{st.cats=st.cats.filter(x=>x!==c);buildSidebar();}}));
  if(st.free===true) tags.push({lbl:'Gratuito',rm:()=>{st.free=null;buildSidebar();}});
  if(st.free===false) tags.push({lbl:'Pago',rm:()=>{st.free=null;buildSidebar();}});
  if(st.rating) tags.push({lbl:`⭐ ${st.rating}+`,rm:()=>{st.rating='';buildSidebar();}});
  window._tagRm=tags.map(t=>t.rm);
  document.getElementById('active-tags').innerHTML=tags.map((t,i)=>`<span class="a-tag">${t.lbl}<button onclick="rmTag(${i})">✕</button></span>`).join('');
}
function rmTag(i){window._tagRm[i]&&window._tagRm[i]();applyFilters();}
function updateFilterBadge(){
  const n=st.types.length+st.cats.length+(st.free!==null?1:0)+(st.rating?1:0)+(st.yearFrom?1:0);
  const b=document.getElementById('f-badge');
  if(b){b.textContent=n;b.style.display=n>0?'inline-flex':'none';}
}

/* ══ SIDEBAR HTML ══ */
function sidebarHTML(){
  return`
<div class="sb-header">
  <div class="sb-title">🔧 Filtros</div>
  <span class="sb-reset" onclick="resetAll()">Limpar tudo</span>
</div>
<div class="sb-block">
  <div class="sb-block-title">Pesquisar</div>
  <input id="q-sb" type="text" placeholder="Palavras-chave, autor…"
    style="width:100%;padding:8px 12px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;outline:none;background:var(--cream);transition:all var(--t)"
    value="${st.q}" oninput="onSearch(this.value)"
    onfocus="this.style.borderColor='var(--cr)';this.style.boxShadow='0 0 0 3px var(--cr-xl)'"
    onblur="this.style.borderColor='';this.style.boxShadow=''">
</div>
<div class="sb-block">
  <div class="sb-block-title">Tipo de documento <button onclick="st.types=[];buildSidebar()">Limpar</button></div>
  ${types.map(t=>`
  <label class="cb-item">
    <input type="checkbox" class="cb-box" ${st.types.includes(t)?'checked':''} onchange="toggleType('${t}',this)">
    <span class="cb-lbl">${t}</span>
    <span class="cb-count">${allDocs.filter(d=>d.type===t).length}</span>
  </label>`).join('')}
</div>
<div class="sb-block">
  <div class="sb-block-title">Categoria <button onclick="st.cats=[];buildSidebar()">Limpar</button></div>
  ${cats.map(c=>`
  <label class="cb-item">
    <input type="checkbox" class="cb-box" ${st.cats.includes(c)?'checked':''} onchange="toggleCat('${c}',this)">
    <span class="cb-lbl">${c}</span>
    <span class="cb-count">${allDocs.filter(d=>d.cat===c).length}</span>
  </label>`).join('')}
</div>
<div class="sb-block">
  <div class="sb-block-title">Ano de publicação</div>
  <div class="year-row">
    <input class="yr-input" type="number" id="yr-from" placeholder="De…" min="2000" max="2025" value="${st.yearFrom}" oninput="st.yearFrom=this.value;applyFilters()">
    <input class="yr-input" type="number" id="yr-to"   placeholder="Até…" min="2000" max="2025" value="${st.yearTo}"   oninput="st.yearTo=this.value;applyFilters()">
  </div>
</div>
<div class="sb-block">
  <div class="sb-block-title">Avaliação mínima</div>
  <div class="star-row">
    ${[['','Qualquer'],['4','4+ ★★★★☆'],['5','5 ★★★★★']].map(([v,l])=>`
    <div class="star-item${st.rating===v?' sel':''}" onclick="setRating('${v}')"><span class="star-lbl">${l}</span></div>`).join('')}
  </div>
</div>
<div class="sb-block">
  <div class="sb-block-title">Acesso</div>
  <div class="rb-item${st.free===null?' sel':''}" onclick="setFree(null)"><div class="rb-dot"></div><span class="rb-lbl">Todos</span></div>
  <div class="rb-item${st.free===true?' sel':''}" onclick="setFree(true)"><div class="rb-dot"></div><span class="rb-lbl">🆓 Gratuito</span></div>
  <div class="rb-item${st.free===false?' sel':''}" onclick="setFree(false)"><div class="rb-dot"></div><span class="rb-lbl">💳 Pago</span></div>
</div>
<button class="sb-apply-btn" onclick="closeMobSB()">✓ Aplicar filtros</button>`;
}
function buildSidebar(){
  const h=sidebarHTML();
  document.getElementById('sidebar-inner').innerHTML=h;
  document.getElementById('mob-sb-content').innerHTML=h;
  applyFilters();
}
function resetAll(){st={...st,q:'',types:[],cats:[],yearFrom:'',yearTo:'',rating:'',free:null,page:1};document.getElementById('nav-search').value='';buildSidebar();}

/* ══ HANDLERS ══ */
function onSearch(v){clearTimeout(debT);debT=setTimeout(()=>{st.q=v;st.page=1;applyFilters();},320);}
function toggleType(t,cb){if(cb.checked)st.types.push(t);else st.types=st.types.filter(x=>x!==t);applyFilters();}
function toggleCat(c,cb){if(cb.checked)st.cats.push(c);else st.cats=st.cats.filter(x=>x!==c);applyFilters();}
function setRating(v){st.rating=v;buildSidebar();}
function setFree(v){st.free=v;buildSidebar();}

/* ══ MOBILE SIDEBAR ══ */
function openMobSB(){const o=document.getElementById('sb-ov'),s=document.getElementById('sidebar-modal');o.style.display='block';setTimeout(()=>o.classList.add('open'),10);s.classList.add('open');document.body.style.overflow='hidden';}
function closeMobSB(){const o=document.getElementById('sb-ov'),s=document.getElementById('sidebar-modal');o.classList.remove('open');s.classList.remove('open');setTimeout(()=>o.style.display='none',300);document.body.style.overflow='';}

/* ══ LOGIN GATE ══ */
function requireLogin(type){
  const msgs={read:{t:'Login para ler o documento completo',s:'Crie uma conta gratuita'},upload:{t:'Login para submeter conteúdo',s:'Registe-se e partilhe o seu conhecimento'},download:{t:'Login para baixar',s:'Acesso gratuito — basta criar uma conta'}};
  const m=msgs[type]||msgs.read;
  document.getElementById('lp-title').textContent=m.t;
  document.getElementById('lp-sub').textContent=m.s;
  document.getElementById('login-prompt').classList.add('show');
}

/* ══ TOAST ══ */
function showToast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2500);}

/* ══ INIT ══ */
// read URL params
const urlP=new URLSearchParams(window.location.search);
if(urlP.get('q'))st.q=urlP.get('q');
buildSidebar();
</script>
</body>
</html>
