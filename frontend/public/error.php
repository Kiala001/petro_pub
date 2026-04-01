<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Erro do Sistema</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Mono:wght@400;500&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<style>
:root {
  --cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;
  --gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;
  --er:#E53E3E;--er-dk:#9B2C2C;--er-lt:#FC8181;
  --wn:#C47A1A;--wn-lt:#F6AD55;
  --ok:#2D7A4F;--ok-lt:#68D391;
  /* dark bg palette */
  --bg0:#0A0608;--bg1:#110C0E;--bg2:#1A1014;--bg3:#24151A;--bg4:#2E1B21;
  --line:rgba(255,255,255,.06);--line2:rgba(255,255,255,.10);
  --tx:#F5EDE8;--tx-m:#C8AFA6;--tx-l:#7A5C55;--tx-xl:#3D2C28;
  --mono:'DM Mono',monospace;
  --sh-er:0 0 40px rgba(229,62,62,.20);--sh-gd:0 0 30px rgba(201,168,76,.15);
  --r1:6px;--r2:10px;--r3:14px;--r4:18px;
}

*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth;font-size:16px;height:100%}
body{
  font-family:'DM Sans',sans-serif;
  background:var(--bg0);
  color:var(--tx);
  -webkit-font-smoothing:antialiased;
  min-height:100vh;
  overflow-x:hidden;
  position:relative;
}

/* ── BACKGROUND TEXTURE ── */
body::before{
  content:'';position:fixed;inset:0;
  background:
    radial-gradient(ellipse 80% 60% at 20% 0%,rgba(107,16,32,.22) 0%,transparent 60%),
    radial-gradient(ellipse 60% 40% at 80% 100%,rgba(74,11,22,.18) 0%,transparent 60%),
    radial-gradient(ellipse 40% 30% at 50% 50%,rgba(36,21,26,.8) 0%,transparent 100%);
  pointer-events:none;z-index:0;
}

/* scanline effect */
body::after{
  content:'';position:fixed;inset:0;
  background:repeating-linear-gradient(0deg,transparent,transparent 2px,rgba(0,0,0,.04) 2px,rgba(0,0,0,.04) 4px);
  pointer-events:none;z-index:1;
  animation:scanMove 8s linear infinite;
}
@keyframes scanMove{from{background-position:0 0}to{background-position:0 100px}}

/* grain */
.grain{
  position:fixed;inset:0;z-index:2;pointer-events:none;opacity:.04;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/%3E%3C/svg%3E");
  background-repeat:repeat;background-size:128px 128px;
}

/* ── TOPBAR ── */
.topbar{
  position:fixed;top:0;left:0;right:0;z-index:100;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 clamp(16px,4vw,40px);height:56px;
  background:rgba(10,6,8,.80);
  backdrop-filter:blur(12px);
  border-bottom:1px solid var(--line);
}
.logo{font-family:'Arial',serif;font-weight:900;font-size:19px;color:var(--tx);letter-spacing:.5px}
.logo span{color:var(--gd)}
.tb-r{display:flex;align-items:center;gap:10px}
.status-dot{width:8px;height:8px;border-radius:50%;background:var(--er);box-shadow:0 0 8px var(--er),0 0 20px rgba(229,62,62,.4);animation:blink 1.2s ease-in-out infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.status-lbl{font-size:11px;font-weight:600;color:var(--er-lt);letter-spacing:.5px;font-family:var(--mono)}

/* ── MAIN LAYOUT ── */
.page{
  position:relative;z-index:10;
  min-height:100vh;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:clamp(80px,12vw,100px) clamp(16px,5vw,40px) clamp(40px,6vw,60px);
}

/* ── GLITCH ICON ── */
.err-icon-wrap{position:relative;margin-bottom:clamp(24px,4vw,36px);animation:iconFloat 3s ease-in-out infinite}
@keyframes iconFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}

.err-icon{
  width:clamp(90px,14vw,120px);height:clamp(90px,14vw,120px);
  border-radius:24px;
  background:linear-gradient(135deg,var(--bg3),var(--bg4));
  border:1px solid rgba(229,62,62,.25);
  display:flex;align-items:center;justify-content:center;
  font-size:clamp(42px,6vw,56px);
  position:relative;
  box-shadow:0 0 0 1px rgba(229,62,62,.10),var(--sh-er),inset 0 1px 0 rgba(255,255,255,.05);
}
.err-icon::before,.err-icon::after{
  content:'🗄️';
  position:absolute;font-size:clamp(42px,6vw,56px);
  pointer-events:none;
}
.err-icon::before{color:rgba(229,62,62,.6);animation:glitchR .8s step-end infinite alternate;clip-path:polygon(0 30%,100% 30%,100% 55%,0 55%)}
.err-icon::after {color:rgba(26,92,138,.6);animation:glitchL .9s step-end infinite alternate;clip-path:polygon(0 60%,100% 60%,100% 80%,0 80%)}
@keyframes glitchR{0%,85%{transform:none;opacity:0}86%,100%{transform:translate(3px,-1px);opacity:1}}
@keyframes glitchL{0%,78%{transform:none;opacity:0}79%,100%{transform:translate(-3px,2px);opacity:1}}

/* badge above */
.err-badge{
  position:absolute;top:-12px;right:-12px;
  background:var(--er);color:#fff;
  font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;
  padding:4px 10px;border-radius:100px;font-family:var(--mono);
  box-shadow:0 0 0 2px var(--bg0),var(--sh-er);
  animation:badgePop .5s cubic-bezier(.22,1,.36,1) both;
}
@keyframes badgePop{from{opacity:0;transform:scale(.6)}to{opacity:1;transform:scale(1)}}

/* ── MAIN CONTENT ── */
.err-main{max-width:580px;width:100%;text-align:center}

.err-code{
  font-family:var(--mono);font-size:clamp(11px,1.3vw,13px);font-weight:500;
  color:var(--er-lt);letter-spacing:2px;text-transform:uppercase;
  margin-bottom:12px;
  animation:fadeUp .5s ease .1s both;
}

.err-title{
  font-family:'Arial',serif;
  font-size:clamp(26px,5vw,42px);font-weight:900;
  color:var(--tx);line-height:1.2;
  margin-bottom:clamp(12px,2vw,18px);
  animation:fadeUp .5s ease .18s both;
}
.err-title em{color:var(--er-lt);font-style:normal}

.err-sub{
  font-size:clamp(13px,1.5vw,15px);color:var(--tx-m);line-height:1.70;
  margin-bottom:clamp(24px,4vw,36px);
  animation:fadeUp .5s ease .26s both;
}

/* ── DIAGNOSTICS PANEL ── */
.diag-panel{
  background:var(--bg1);
  border:1px solid var(--line2);
  border-radius:var(--r3);
  overflow:hidden;
  margin-bottom:clamp(20px,3vw,30px);
  text-align:left;
  animation:fadeUp .5s ease .34s both;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.04);
}
.diag-head{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:11px 16px;
  background:rgba(255,255,255,.03);
  border-bottom:1px solid var(--line);
}
.diag-title{font-family:var(--mono);font-size:11px;color:var(--tx-l);letter-spacing:1px;text-transform:uppercase}
.diag-ts{font-family:var(--mono);font-size:11px;color:var(--tx-xl)}

.diag-row{
  display:flex;align-items:center;gap:0;
  padding:10px 16px;border-bottom:1px solid var(--line);
  transition:background .18s;
}
.diag-row:last-child{border-bottom:none}
.diag-row:hover{background:rgba(255,255,255,.02)}
.diag-key{
  font-family:var(--mono);font-size:12px;color:var(--tx-l);
  width:160px;flex-shrink:0;
}
.diag-val{font-family:var(--mono);font-size:12px;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.dv-er{color:var(--er-lt)}.dv-wn{color:var(--wn-lt)}.dv-ok{color:var(--ok-lt)}.dv-tx{color:var(--tx-m)}
.diag-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-right:10px}
.dd-er{background:var(--er);box-shadow:0 0 6px var(--er)}.dd-wn{background:var(--wn);box-shadow:0 0 6px var(--wn)}.dd-ok{background:var(--ok)}

/* ── RETRY TIMER ── */
.retry-wrap{
  display:flex;align-items:center;gap:14px;
  background:var(--bg2);border:1px solid var(--line2);
  border-radius:var(--r3);padding:clamp(14px,2vw,18px) clamp(16px,2.5vw,22px);
  margin-bottom:clamp(20px,3vw,28px);
  animation:fadeUp .5s ease .42s both;
}
.retry-ring{
  position:relative;width:52px;height:52px;flex-shrink:0;
}
.retry-ring svg{transform:rotate(-90deg);width:52px;height:52px}
.rr-bg{fill:none;stroke:var(--bg3);stroke-width:4}
.rr-fill{fill:none;stroke:var(--gd);stroke-width:4;stroke-linecap:round;transition:stroke-dashoffset .9s linear;stroke-dasharray:138;stroke-dashoffset:0}
.retry-secs{
  position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
  font-family:var(--mono);font-size:14px;font-weight:500;color:var(--gd-lt);
}
.retry-body{flex:1;min-width:0}
.rb-title{font-size:14px;font-weight:700;color:var(--tx);margin-bottom:3px}
.rb-sub{font-size:12px;color:var(--tx-m);line-height:1.5}
.retry-cancel{
  font-size:11px;font-weight:600;color:var(--tx-l);cursor:pointer;
  background:none;border:1.5px solid rgba(255,255,255,.08);
  border-radius:var(--r2);padding:5px 11px;flex-shrink:0;
  transition:all .18s;font-family:'DM Sans',sans-serif;white-space:nowrap;
}
.retry-cancel:hover{border-color:rgba(255,255,255,.18);color:var(--tx)}

/* ── ACTION BUTTONS ── */
.actions{
  display:flex;gap:10px;justify-content:center;flex-wrap:wrap;
  animation:fadeUp .5s ease .50s both;
  margin-bottom:clamp(28px,4vw,40px);
}
.btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:11px 22px;border-radius:var(--r3);
  font-size:13px;font-weight:700;cursor:pointer;border:none;
  transition:all .20s;white-space:nowrap;font-family:'DM Sans',sans-serif;
  letter-spacing:.2px;
}
.btn-retry{
  background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));
  color:#fff;box-shadow:0 4px 16px rgba(107,16,32,.35);
}
.btn-retry:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(107,16,32,.5)}
.btn-retry.spinning .btn-ico{animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.btn-ico{display:inline-block;transition:transform .2s}
.btn-home{
  background:rgba(255,255,255,.06);color:var(--tx-m);
  border:1.5px solid rgba(255,255,255,.10);
}
.btn-home:hover{background:rgba(255,255,255,.10);color:var(--tx);border-color:rgba(255,255,255,.18)}
.btn-report{
  background:rgba(229,62,62,.10);color:var(--er-lt);
  border:1.5px solid rgba(229,62,62,.20);
}
.btn-report:hover{background:rgba(229,62,62,.18);border-color:rgba(229,62,62,.35)}

/* ── ERROR STACK (expandable) ── */
.stack-toggle{
  display:flex;align-items:center;gap:7px;
  font-size:12px;font-weight:600;color:var(--tx-l);cursor:pointer;
  background:none;border:none;font-family:'DM Sans',sans-serif;
  margin:0 auto clamp(14px,2vw,20px);padding:6px 12px;
  border-radius:var(--r2);border:1px solid var(--line);
  transition:all .18s;animation:fadeUp .5s ease .58s both;
}
.stack-toggle:hover{border-color:var(--line2);color:var(--tx-m)}
.st-arr{transition:transform .25s}
.stack-toggle.open .st-arr{transform:rotate(180deg)}

.stack-body{
  display:none;
  background:var(--bg1);border:1px solid var(--line);
  border-radius:var(--r3);padding:16px;
  text-align:left;margin-bottom:clamp(20px,3vw,28px);
  animation:fadeUp .25s ease both;
}
.stack-body.open{display:block}
.stack-line{
  font-family:var(--mono);font-size:11px;line-height:1.8;
  color:var(--tx-xl);display:block;
}
.stack-line.err-line{color:var(--er-lt)}
.stack-line.at-line{color:var(--tx-l)}
.stack-line.at-line span{color:var(--gd-lt)}

/* ── FOOTER STATUS ROW ── */
.status-row{
  display:flex;align-items:center;justify-content:center;gap:clamp(16px,3vw,28px);
  flex-wrap:wrap;animation:fadeUp .5s ease .66s both;
}
.sr-item{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--tx-l)}
.sr-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.srd-ok{background:var(--ok);box-shadow:0 0 6px var(--ok)}.srd-er{background:var(--er);box-shadow:0 0 6px var(--er)}.srd-wn{background:var(--wn);box-shadow:0 0 6px var(--wn)}
.sr-val{font-weight:600;color:var(--tx-m)}

/* ── BOTTOM LINK ── */
.bottom-link{
  position:fixed;bottom:clamp(14px,3vw,22px);right:clamp(16px,3vw,24px);
  font-size:11px;color:var(--tx-xl);z-index:100;font-family:var(--mono);
}

/* toast */
.toast{position:fixed;top:70px;left:50%;z-index:999;transform:translateX(-50%) translateY(-20px);background:var(--bg3);color:var(--tx);padding:10px 20px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.5);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);border:1px solid var(--line2);white-space:nowrap}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}

/* responsive */
@media(max-width:540px){
  .diag-key{width:120px}
  .actions{flex-direction:column;align-items:stretch}
  .btn{justify-content:center}
  .status-row{gap:12px}
}
</style>
</head>
<body>

<div class="grain"></div>
<div class="toast" id="toast"></div>

<!-- TOPBAR -->
<div class="topbar">
  <div class="logo">PETRO<span>PUB</span></div>
  <div class="tb-r">
    <div class="status-dot"></div>
    <div class="status-lbl">DB_ERROR</div>
  </div>
</div>

<!-- MAIN PAGE -->
<div class="page">

  <!-- ICON -->
  <div class="err-icon-wrap">
    <div class="err-icon">
      🗄️
      <div class="err-badge">503</div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="err-main">

    <div class="err-code">PETROPUB · DATABASE_CONNECTION_ERROR</div>

    <h1 class="err-title">
      Falha na ligação<br>à <em>base de dados</em>
    </h1>

    <p class="err-sub">
      O servidor de base de dados está temporariamente inacessível. Os seus dados estão seguros — este é um problema de conectividade, não de perda de informação. A equipa técnica foi notificada automaticamente.
    </p>

    <!-- DIAGNOSTICS -->
    <div class="diag-panel">
      <div class="diag-head">
        <div class="diag-title">Diagnóstico do sistema</div>
        <div class="diag-ts" id="diag-ts">—</div>
      </div>
      <div class="diag-row">
        <div class="diag-dot dd-er"></div>
        <div class="diag-key">Ligação BD</div>
        <div class="diag-val dv-er">ECONNREFUSED — PostgreSQL:5432</div>
      </div>
      <div class="diag-row">
        <div class="diag-dot dd-er"></div>
        <div class="diag-key">Pool de ligações</div>
        <div class="diag-val dv-er">0 / 20 activas · timeout 30s</div>
      </div>
      <div class="diag-row">
        <div class="diag-dot dd-wn"></div>
        <div class="diag-key">Cache (Redis)</div>
        <div class="diag-val dv-wn">DEGRADED — hit ratio 12%</div>
      </div>
      <div class="diag-row">
        <div class="diag-dot dd-ok"></div>
        <div class="diag-key">Servidor web</div>
        <div class="diag-val dv-ok">ONLINE — Node.js v20.11</div>
      </div>
      <div class="diag-row">
        <div class="diag-dot dd-ok"></div>
        <div class="diag-key">CDN / Estáticos</div>
        <div class="diag-val dv-ok">ONLINE — Cloudflare</div>
      </div>
      <div class="diag-row">
        <div class="diag-dot dd-er"></div>
        <div class="diag-key">Último acesso BD</div>
        <div class="diag-val dv-tx" id="last-access">há 4 minutos e 12 segundos</div>
      </div>
      <div class="diag-row">
        <div class="diag-dot dd-wn"></div>
        <div class="diag-key">Tentativas</div>
        <div class="diag-val dv-wn" id="attempt-count">3 de 5 · próxima em 30s</div>
      </div>
    </div>

    <!-- AUTO RETRY TIMER -->
    <div class="retry-wrap" id="retry-wrap">
      <div class="retry-ring">
        <svg viewBox="0 0 52 52">
          <circle class="rr-bg" cx="26" cy="26" r="22"/>
          <circle class="rr-fill" id="rr-fill" cx="26" cy="26" r="22"/>
        </svg>
        <div class="retry-secs" id="retry-secs">30</div>
      </div>
      <div class="retry-body">
        <div class="rb-title">Nova tentativa automática</div>
        <div class="rb-sub">O sistema vai tentar reconectar-se automaticamente.<br>Pode tentar manualmente a qualquer momento.</div>
      </div>
      <button class="retry-cancel" id="retry-cancel-btn" onclick="cancelAutoRetry()">Cancelar</button>
    </div>

    <!-- ACTIONS -->
    <div class="actions">
      <button class="btn btn-retry" id="retry-btn" onclick="doRetry()">
        <span class="btn-ico" id="retry-ico">↻</span> Tentar novamente
      </button>
      <button class="btn btn-home" onclick="goHome()">← Voltar ao início</button>
      <button class="btn btn-report" onclick="doReport()">🚨 Reportar</button>
    </div>

    <!-- STACK TRACE (expandable) -->
    <button class="stack-toggle" id="stack-toggle" onclick="toggleStack()">
      <span>Ver detalhes técnicos</span>
      <span class="st-arr">▾</span>
    </button>
    <div class="stack-body" id="stack-body">
      <span class="stack-line err-line">Error: ECONNREFUSED — connect ECONNREFUSED 127.0.0.1:5432</span>
      <span class="stack-line at-line">  at <span>TCPConnectWrap.afterConnect</span> [as oncomplete] (node:net:1247:16)</span>
      <span class="stack-line at-line">  at <span>DatabasePool.connect</span> (/app/lib/db/pool.js:88:22)</span>
      <span class="stack-line at-line">  at <span>QueryBuilder.execute</span> (/app/lib/db/query.js:142:14)</span>
      <span class="stack-line at-line">  at <span>UserRepository.findById</span> (/app/repositories/user.js:56:9)</span>
      <span class="stack-line at-line">  at <span>AuthMiddleware.verify</span> (/app/middleware/auth.js:33:18)</span>
      <span class="stack-line at-line">  at Layer.handle [as handle_request] (express/lib/router/layer.js:95:5)</span>
      <span class="stack-line" style="color:var(--tx-xl);margin-top:10px;display:block">Código: ECONNREFUSED · PID: 18234 · Memória: 342MB / 512MB</span>
    </div>

    <!-- STATUS ROW -->
    <div class="status-row">
      <div class="sr-item"><div class="sr-dot srd-ok"></div><span>API online</span></div>
      <div class="sr-item"><div class="sr-dot srd-er"></div><span>BD <span class="sr-val">offline</span></span></div>
      <div class="sr-item"><div class="sr-dot srd-wn"></div><span>Cache <span class="sr-val">degradada</span></span></div>
      <div class="sr-item"><div class="sr-dot srd-ok"></div><span>CDN online</span></div>
      <div class="sr-item">🕐 <span>Uptime: <span class="sr-val" id="uptime-val">4m 12s</span></span></div>
    </div>

  </div>
</div>

<div class="bottom-link">REF: ERR_DB_503 · v2.4.1</div>

<script>
/* ══ TIMESTAMP ══ */
const startTime = Date.now();
function pad(n){return String(n).padStart(2,'0');}
function fmtTime(d){return`${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;}

document.getElementById('diag-ts').textContent = fmtTime(new Date()) + ' UTC+1';

/* ══ UPTIME COUNTER ══ */
let downSecs = 252; // 4m12s down time
setInterval(()=>{
  downSecs++;
  const m = Math.floor(downSecs/60);
  const s = downSecs % 60;
  const el = document.getElementById('uptime-val');
  const la = document.getElementById('last-access');
  if(el) el.textContent = m > 0 ? `${m}m ${s}s` : `${s}s`;
  if(la) la.textContent = `há ${m > 0 ? m+'m ' : ''}${s}s`;
}, 1000);

/* ══ AUTO RETRY COUNTDOWN ══ */
const RETRY_SECS = 30;
let retryTimer = RETRY_SECS;
let retryInterval = null;
let autoCancelled = false;
let attempts = 3;

const rFill = document.getElementById('rr-fill');
const circumference = 2 * Math.PI * 22; // r=22 → C≈138.2

function startRetryTimer(){
  retryTimer = RETRY_SECS;
  retryInterval = setInterval(()=>{
    retryTimer--;
    const el = document.getElementById('retry-secs');
    if(el) el.textContent = retryTimer;
    // arc
    const offset = circumference * (retryTimer / RETRY_SECS);
    if(rFill) rFill.style.strokeDashoffset = offset;
    if(retryTimer <= 0){
      clearInterval(retryInterval);
      if(!autoCancelled) doRetry(true);
    }
  }, 1000);
}

function cancelAutoRetry(){
  autoCancelled = true;
  clearInterval(retryInterval);
  const wrap = document.getElementById('retry-wrap');
  if(wrap){
    wrap.style.opacity = '.4';
    wrap.style.pointerEvents = 'none';
    document.getElementById('retry-secs').textContent = '—';
    document.getElementById('retry-cancel-btn').textContent = 'Cancelado';
  }
  showToast('⏸ Reconexão automática cancelada');
}

startRetryTimer();

/* ══ RETRY ══ */
function doRetry(auto = false){
  attempts++;
  const btn = document.getElementById('retry-btn');
  const ico = document.getElementById('retry-ico');
  btn.classList.add('spinning');
  btn.disabled = true;
  ico.textContent = '↻';

  document.getElementById('attempt-count').textContent = `${attempts} de 5 · a tentar…`;
  showToast(auto ? '🔄 Tentativa automática em curso…' : '🔄 A tentar reconectar…');

  setTimeout(()=>{
    btn.classList.remove('spinning');
    btn.disabled = false;
    // simulate still failing
    document.getElementById('attempt-count').textContent = `${attempts} de 5 · próxima em 30s`;
    showToast('⚠️ Falha na ligação. A base de dados continua inacessível.');
    // restart timer
    if(!autoCancelled){
      clearInterval(retryInterval);
      startRetryTimer();
    }
  }, 2800);
}

/* ══ STACK TOGGLE ══ */
function toggleStack(){
  const btn = document.getElementById('stack-toggle');
  const body = document.getElementById('stack-body');
  btn.classList.toggle('open');
  body.classList.toggle('open');
  btn.querySelector('span:first-child').textContent = btn.classList.contains('open') ? 'Ocultar detalhes técnicos' : 'Ver detalhes técnicos';
}

/* ══ REPORT ══ */
function doReport(){
  showToast('✅ Relatório enviado à equipa técnica!');
}
function goHome(){
  showToast('← A redirecionar…');
  setTimeout(()=>{ window.location.href='petropub-biblioteca.html'; }, 900);
}

/* ══ TOAST ══ */
function showToast(msg){
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 3200);
}
</script>
</body>
</html>
