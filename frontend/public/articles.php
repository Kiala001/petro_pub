<?php
require_once 'includes.php';

$uploadDir = '../../uploads/documents';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$documentRepository = new DocumentRepositoryImpl($db);
$categoryRepository = new CategoryRepositoryImpl($db);
$documentService = new DocumentService($documentRepository, $categoryRepository, $uploadDir);
$userId = $_SESSION['user_uuid'];

$allArticles = $documentService->getAllDocuments();

if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php');
    exit;
}

if (isset($_SESSION['jwt_auth']) && $_SESSION['type_auth'] != 'ADMIN') {
    header('Location: library.php');
    exit;
}

$result = $drService->getDocumentsWithReviews();
$document = $result['documents'];

$jwt = $_SESSION['jwt_auth'];
$userName = $_SESSION['user_name'] ?? 'Usuário';
$userEmail = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));
?>
<!doctype html>
<html lang="pt">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PetroPub Admin — Gestão de Avaliações</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap"
      rel="stylesheet"
    />
    <link href="assets/css/admin-review.css" rel="stylesheet">
    <link href="assets/css/modals.css" rel="stylesheet">
    <link href="assets/css/elements.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
  </head>
  <body>
    <div class="toast t-def" id="toast"></div>
    <div class="sb-ov" id="sb-ov" onclick="closeSB()"></div>

    <div class="app">
      <!-- ════════ SIDEBAR ════════ -->
      <aside class="sidebar" id="sidebar">
        <div class="sb-head">
          <div>
            <div class="sb-logo">PETRO<span>PUB</span></div>
            <div class="sb-role-tag">Administração</div>
          </div>
          <button class="sb-tog" id="sb-close" onclick="closeSB()">✕</button>
          <button class="sb-tog" id="sb-col" onclick="toggleCol()">◀</button>
        </div>
        <div class="sb-user">
          <div class="ava ava-dk"><?=$userInitials?></div>
          <div class="sb-ui">
            <div class="sb-un"><?=$userName?></div>
            <div class="sb-ue"><?=$userEmail?></div>
          </div>
        </div>
        <div class="nav-s">
          <div class="nav-l">Visão Geral</div>
          <a href="users.php" class="nav-i" data-tip="Utilizadores">
            <span class="ni"><i class="fa fa-users"></i></span><span class="nt">Utilizadores</span>
          </a>
          <a href="articles.php" class="nav-i act" data-tip="Documentos">
            <span class="ni"><i class="fa fa-book"></i></span><span class="nt">Documentos</span>
          </a>
          <a href="library.php" class="nav-i">
            <span class="ni"><i class="fa fa-book"></i></span><span class="nt">Biblioteca</span>
          </a>
        </div>
        <div class="nav-s">
          <div class="nav-l"></div>
          <a href="pair-review.php" class="nav-i" data-tip="Avaliações">
            <span class="ni"><i class="fa fa-comments-o"></i></span
            ><span class="nt">Revisão por Par</span
            >
          </a>
          <div class="nav-i" data-tip="Publicação">
            <span class="ni"><i class="fa fa-file"></i></span><span class="nt">Meus Documentos</span>
          </div>
        </div>
        <div class="sb-foot">
          <div class="nav-i" data-tip="Sair">
            <span class="ni">🚪</span><span class="nt">Terminar Sessão</span>
          </div>
        </div>
      </aside>


      <!-- ════════ MAIN ════════ -->
      <div class="main">
        <div class="topbar">
          <div class="tb-l">
            <button class="tb-ham" onclick="openSB()">☰</button>
            <div class="tb-info">
              <div class="tb-bc">
                PetroPub <span>/ Admin / Gestão de Avaliações</span>
              </div>
              <div class="tb-title">Gestão de Avaliações</div>
            </div>
          </div>
          <div class="tb-r">
            <!-- <div class="notif-wrap">
              <div class="notif-btn">🔔</div>
              <div class="notif-dot"></div>
            </div> -->
            <div
              class="ava ava-dk"
              style="
                width: 36px;
                height: 36px;
                font-size: 12px;
                cursor: pointer;
                flex-shrink: 0;
              "
            >
              <?=$userInitials?>
            </div>
          </div>
        </div>

        <div class="page-wrap">
          <!-- ADMIN BANNER -->
          <div class="admin-banner">
            <div class="ab-ico">⚖️</div>
            <div class="ab-body">
              <div class="ab-title">
                Decisão Editorial — Avaliação Por Pares
              </div>
              <div class="ab-desc">
                Visualiza todas as avaliações dos pares com identidade completa
                dos avaliadores. Cabe ao administrador tomar a decisão final de
                aprovar ou rejeitar cada documento.
              </div>
              <div class="ab-caps">
                <span class="ab-cap cap-y">✓ Ver todas as avaliações</span>
                <span class="ab-cap cap-y">✓ Identidade dos avaliadores</span>
                <span class="ab-cap cap-y">✓ Histórico completo</span>
                <span class="ab-cap cap-y"
                  >✓ Decisão final (Aprovar / Rejeitar)</span
                >
                <span class="ab-cap cap-n">✗ Não avalia documentos</span>
              </div>
            </div>
          </div>

          <!-- STATS -->
          <div class="stats-row" id="stats-row">
            <div class="sc">
              <div class="sc-top">
                <div class="sc-ico si-wn">⏳</div>
                <span class="sc-pill sp-wn">Urgente</span>
              </div>
              <div class="sc-num" id="st-pending">-</div>
              <div class="sc-lbl">Aguardam decisão</div>
            </div>
            <div class="sc">
              <div class="sc-top">
                <div class="sc-ico si-cr"><i class="fa fa-comments-o" style="color: var(--gd)"></i></div>
                <span class="sc-pill sp-ok">Total</span>
              </div>
              <div class="sc-num">-</div>
              <div class="sc-lbl">Avaliações recebidas</div>
            </div>
            <div class="sc">
              <div class="sc-top">
                <div class="sc-ico si-ok">
                  <i class="fa fa-check" style="color: var(--ok)"></i>
                </div>
                <span class="sc-pill sp-ok" id="st-app-pill">+3 hoje</span>
              </div>
              <div class="sc-num" id="st-approved">-</div>
              <div class="sc-lbl">Aprovados pelo admin</div>
            </div>
            <div class="sc">
              <div class="sc-top">
                <div class="sc-ico si-er">
                  <i class="fa fa-close" style="color: var(--er)"></i>
                </div>
                <span class="sc-pill sp-er">Total</span>
              </div>
              <div class="sc-num" id="st-rejected">-</div>
              <div class="sc-lbl">Rejeitados pelo admin</div>
            </div>
            <div class="sc">
              <div class="sc-top">
                <div class="sc-ico si-gd">
                  <i class="fa fa-star" style="color: var(--gd-lt)"></i>
                </div>
                <span class="sc-pill sp-ok">Global</span>
              </div>
              <div class="sc-num">-</div>
              <div class="sc-lbl">Média das avaliações</div>
            </div>
          </div>

          <!-- TOOLBAR -->
          <!-- <div class="toolbar">
            <div class="tb-search">
              <span class="search-ico"><i class="fa fa-search"></i></span>
              <input
                type="text"
                id="q"
                placeholder="Pesquisar por título, autor, avaliador…"
                oninput="applyFilters()"
              />
            </div>
            <select class="f-sel" id="sort-sel" onchange="applyFilters()">
              <option value="recent">Mais recentes</option>
              <option value="score-asc">Menor pontuação</option>
              <option value="score-desc">Maior pontuação</option>
              <option value="reviews">Mais avaliações</option>
            </select>
            <div class="chips" id="status-chips">
              <button class="chip on" onclick="setChip('all', this)">
                Todos
              </button>
              <button class="chip" onclick="setChip('pendente', this)">
                ⏳ Aguardam
              </button>
              <button class="chip" onclick="setChip('aprovado', this)">
                <i class="fa fa-check" style="color: var(--ok)"></i>
                Aprovados
              </button>
              <button class="chip" onclick="setChip('rejeitado', this)">
                <i class="fa fa-close" style="color: var(--er)"></i>
                Rejeitados
              </button>
            </div>
            </div>
          </div> -->

          <!-- DOCUMENT LIST -->
          <div id="doc-list">
        </div>
      </div>
    </div>

    <?php
    require_once 'assets/modals/document.php';
    ?>

    <script src="assets/js/modal.js"></script>
    <!-- <script src="assets/js/util.js"></script> -->
    <script src="assets/js/api.js"></script>
    <script>
      const S = (n) => "⭐".repeat(n) + "☆".repeat(5 - n);

      let docs = "<?php echo $document; ?>";

      console.log(docs);

      async function loadArticle(docs) {
        const response = await apiRequest("article");
             
        docs = response.data.documents

        render(docs)
      }

      /* ═══ STATE ═══ */
      let statusFilter = "all",
        decideId = null,
        selectedDec = "";
      
      const sCfg = {
        pendente: { cls: "bo", lbl: "Em Avaliação" },
        aguardando_pagamento: { cls: "br", lbl: "Aguardando Pagamento" },
        aprovado: { cls: "bg", lbl: "Aprovado" },
        publicado: { cls: "bg", lbl: "Publicado" },
        rejeitado: { cls: "br", lbl: "Rejeitado" },
      };

      /* ═══ RENDER ═══ */
      function render(data, total) {

        
        const el = document.getElementById("doc-list");
        if (!data.length) {
          el.innerHTML = `<div class="empty"><div class="empty-ico"><i class="fa fa-search"></i></div><div class="empty-title">Nenhum documento encontrado</div><div class="empty-sub">Tente ajustar os filtros aplicados.</div></div>`;
          return updateStats();
        }
        el.innerHTML = data
        .map((d, i) => {
            const sc = sCfg[d.status] || sCfg.aguardando_pagamento;
            const pend = d.status === "pendente";
            const nOk = d.reviews.filter((r) => r.dLbl === "Aprovado").length;
            const nRev = d.reviews.filter(
              (r) => r.dLbl === "Pedido de Revisão",
            ).length;
            const nEr = d.reviews.filter((r) => r.dLbl === "Rejeitado").length;
            const pct = Math.round((d.avg / 5) * 100);
            
            
            return `
            <div class="doc-card" style="animation-delay:${(i * 0.06).toFixed(2)}s">
              <div class="dc-hd">
                <div class="dc-type ${d.bg}"><i class="fa fa-book" class="color: var(--inf)"></i></div>
                <div class="dc-info">
                  <div class="dc-tags">
                    <span class="badge ${sc.cls}">${sc.lbl}</span>
                    <span class="badge bc">${d.type}</span>
                    <span class="badge bw">${d.cat}</span>
                  </div>
                  <div class="dc-title" style="color: var(--inf); font-family: Arial">${d.title}</div>
                  <div class="dc-meta">
                    <span><i class="fa fa-user" style="color: var(--inf)"></i> <strong>${d.author}</strong></span>
                    <span><i class="fa fa-calendar" style="color: var(--inf)"></i> <strong>${d.year}</strong></span>
                    <span><i class="fa fa-file" style="color: var(--inf)"></i> <strong>${d.pages} págs.</strong></span>
                    <span><i class="fa fa-comments-o" style="color: var(--inf)"></i> <strong>${d.reviews.length} avaliações</strong></span>
                  </div>
                </div>
                <div class="dc-side">
                  <div class="dc-score">
                    <div class="dc-score-n">${d.avg.toFixed(1)}</div>
                    <div class="dc-score-s">${S(Math.round(d.avg))}</div>
                    <div class="dc-score-l">${d.reviews.length} aval.</div>
                  </div>
                  <a class="btn btn-cr btn-sm" href="article-detail.php?flex-direction=${d.documentId}"><i class="fa fa-list"></i> Ver Detalhes</a>
                </div>
              </div>

              ${
                pend
                  ? `
              <div class="pending-bar">
                <div class="pb-msg"><i class="fa fa-alert"></i> <span>Aguarda decisão &nbsp;·&nbsp; Média: <strong>${d.avg.toFixed(1)}/5</strong> &nbsp;·&nbsp; ${nOk} <i class="fa fa-check" style="color: var(--ok)"></i> aprovados &nbsp;·&nbsp; ${nRev} <i class="fa fa-refresh" style="color: var(--inf)"></i> revisões &nbsp;·&nbsp; ${nEr} <i class="fa fa-close" style="color: var(--er)"></i> rejeitados</span></div>
                <div class="pb-actions">
                  <button class="btn btn-ok btn-sm" onclick="quickDecide('${d.id}','approve')"><i class="fa fa-check"></i> Aprovar</button>
                  <button class="btn btn-er btn-sm" onclick="quickDecide('${d.id}','reject')"><i class="fa fa-close"></i> Rejeitar</button>
                  <a href="article-detail.php?flex-direction=${d.documentId}" class="btn btn-gh btn-sm"><i class="fa fa-balance"></i> Ver e decidir</a>
                </div>
              </div>`
                  : ""
              }

              <button class="exp-tog" onclick="toggleExp(this)">▼ Ver ${d.reviews.length} avaliações detalhadas com identidade dos avaliadores</button>

              <div class="exp-body">
                <div class="rv-wrap">
                  ${d.reviews
                    .map(
                      (rv, ri) => `
                  <div class="rv-item ${rv.dcls}" style="animation-delay:${(ri * 0.06).toFixed(2)}s">
                    <div class="rv-top">
                      <span class="rv-num">Avaliação #${rv.n}</span>
                      <span class="badge ${rv.dbdg}">${rv.dLbl}</span>
                    </div>
                    <div class="rv-score">
                      <span class="rv-stars">${S(rv.score)}</span>
                      <span class="rv-val">${rv.score}</span>
                      <span class="rv-oof">/ 5</span>
                    </div>
                    <div class="rv-sec"><div class="rv-lbl"><i class="fa fa-comment"></i> Comentário</div><div class="rv-txt">${rv.comment}</div></div>
                    <div class="rv-sec"><div class="rv-lbl">💡 Sugestões</div><div class="rv-txt">${rv.suggestion}</div></div>
                    <div class="rv-reviewer">
                      <div class="rv-ava">${rv.rev.ini}</div>
                      <div><div class="rv-rname">${rv.rev.name}</div><div class="rv-rrole">${rv.rev.role}</div></div>
                      <div class="rv-date">${rv.date}</div>
                    </div>
                  </div>`,
                    )
                    .join("")}
                </div>

                <div class="dc-ft">
                  <div>
                    <div style="font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:5px">Média das Avaliações</div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                      <span class="avg-big">${d.avg.toFixed(1)}</span>
                      <div>
                        <div style="font-size:18px;letter-spacing:2px">${S(Math.round(d.avg))}</div>
                        <div class="avg-meta">${d.reviews.length} avaliações · ${nOk} aprovadas · ${nRev} revisões · ${nEr} rejeitadas</div>
                      </div>
                    </div>
                    <div class="prog"><div class="prog-f" style="width:${pct}%"></div></div>
                  </div>
                  ${
                    pend
                      ? `<div style="display:flex;gap:8px;flex-wrap:wrap">
                        <button class="btn btn-ok btn-sm" onclick="quickDecide('${d.id}','approve', '${d.status}')"><i class="fa fa-check"></i> Aprovar</button>
                        <button class="btn btn-er btn-sm" onclick="quickDecide('${d.id}','reject', '${d.status}')"><i class="fa fa-close"></i> Rejeitar</button>
                      </div>`
                      : `<span class="badge ${sc.cls}" style="font-size:13px;padding:7px 16px">${sc.lbl}</span>`
                  }
                </div>
              </div>
            </div>`;
          })
          .join("");
        updateStats();
      }

      function updateStats() {
        document.getElementById("st-pending").textContent = docs.filter(
          (d) => d.status === "pendente",
        ).length;
        document.getElementById("st-approved").textContent = docs.filter(
          (d) => d.status === "aprovado",
        ).length;
        document.getElementById("st-rejected").textContent = docs.filter(
          (d) => d.status === "rejeitado",
        ).length;
        document.getElementById("st-payment").textContent = docs.filter(
          (d) => d.status === "aguardando pagamento",
        ).length;
      }

      /* ═══ FILTERS ═══ */
      function applyFilters() {
        const q = (document.getElementById("q").value || "").toLowerCase();
        let data = docs.filter((d) => {
          const ms = statusFilter === "all" || d.status === statusFilter;
          const mq =
            !q ||
            d.title.toLowerCase().includes(q) ||
            d.author.toLowerCase().includes(q) ||
            d.cat.toLowerCase().includes(q) ||
            d.reviews.some((r) => r.rev.name.toLowerCase().includes(q));
          return ms && mq;
        });
        const sort = document.getElementById("sort-sel").value;
        if (sort === "score-asc")
          data = [...data].sort((a, b) => a.avg - b.avg);
        if (sort === "score-desc")
          data = [...data].sort((a, b) => b.avg - a.avg);
        if (sort === "reviews")
          data = [...data].sort((a, b) => b.reviews.length - a.reviews.length);
        render(data);
      }

      function setChip(s, btn) {
        statusFilter = s;
        document
          .querySelectorAll("#status-chips .chip")
          .forEach((c) => c.classList.remove("on"));
        btn.classList.add("on");
        applyFilters();
      }

      /* ═══ EXPAND ═══ */
      function toggleExp(btn) {
        const body = btn.nextElementSibling;
        const open = body.classList.toggle("open");
        const n = body.querySelectorAll(".rv-item").length;
        btn.textContent = open
          ? "▲ Ocultar avaliações"
          : `▼ Ver ${n} avaliações detalhadas com identidade dos avaliadores`;
      }

      /* ═══ DECIDE ═══ */
      function openDecide(id) {
        decideId = id;
        selectedDec = "";
        const d = docs.find((x) => x.id === id);
        document.getElementById("dec-title").textContent = d.title;
        document.getElementById("dec-meta").textContent =
          `${d.author} · ${d.inst} · ${d.year} · Média: ${d.avg.toFixed(1)}/5 · ${d.reviews.length} avaliações`;
        document.getElementById("dec-note").value = "";
        document.getElementById("dec-approve").className = "dec-opt";
        document.getElementById("dec-reject").className = "dec-opt";
        openModal("modal-decide");
      }

      function quickDecide(id, dec) {
        decideId = id;
        
        applyDecision(id, dec);
      }

      function selDec(d) {
        selectedDec = d;
        document.getElementById("dec-approve").className =
          "dec-opt" + (d === "approve" ? " sel-ok" : "");
        document.getElementById("dec-reject").className =
          "dec-opt" + (d === "reject" ? " sel-er" : "");
      }

      function confirmDecision() {
        if (!selectedDec) {
          showToast("⚠️ Seleccione uma decisão", "t-wn");
          return;
        }
        applyDecision(selectedDec);
        closeModal("modal-decide");
      }

      async function applyDecision(id, dec) {
        const note = null;
        
        const response = await apiRequest("documents-decision", {
          method: "PUT",
          body: {id, dec, note},
        });
        
        const data = response.data;

        const status = data.status;
        const message = data.message;
        const title = data.title;

        showDecision(status, message, title, dec)
      }

      function showDecision(status, message, title, dec) {
        const ok = dec === "approve";

        document.getElementById("res-mhd").className = "m-hd " + (ok ? "mh-ok" : "mh-er");
        document.getElementById("res-htitle").innerHTML = ok ? "<i class='fa fa-check'></i> Artigo Aprovada" : "<i class='fa fa-close'></i> Artigo Rejeitado";
        document.getElementById("res-hsub").textContent = message;
        document.getElementById("res-ico").textContent = ok ? "🎉" : "🚫";
        document.getElementById("res-title").textContent = ok
          ? "Decisão registada!"
          : "Rejeição registada.";
        document.getElementById("res-desc").textContent = message;
        document.getElementById("res-sum").className = "res-sum " + (ok ? "rs-ok" : "rs-er");
        document.getElementById("rs-doc").textContent = title;
        document.getElementById("rs-dec").innerHTML = ok
          ? "<i class='fa fa-check' style='color: green'></i> Aprovado"
          : "<i class='fa fa-close' style='color: red;'></i> Rejeitado";
        document.getElementById("rs-date").textContent =
          new Date().toLocaleDateString("pt-PT");
        openModal("modal-result");
        // applyFilters();
        setTimeout(() => {
          showToast(
            ok ? " Documento aprovado! A página será recarregada em instantes." : " Documento rejeitado! A página será recarregada em instantes.",
            ok ? "t-ok" : "t-er",
          );
        }, 4000);

        location.reload();
      }


      const sidebar = document.getElementById("sidebar"),
        sbOv = document.getElementById("sb-ov");
      const sbClose = document.getElementById("sb-close"),
        sbCol = document.getElementById("sb-col");
      let collapsed = false;
      function checkBP() {
        const w = window.innerWidth;
        if (w < 768) {
          sbClose.style.display = sidebar.classList.contains("open")
            ? "flex"
            : "none";
          sbCol.style.display = "none";
          sidebar.classList.remove("collapsed");
        } else if (w < 1200) {
          sbClose.style.display = "none";
          sbCol.style.display = "none";
          sidebar.classList.remove("open");
          sbOv.classList.remove("open");
          document.body.style.overflow = "";
        } else {
          sbClose.style.display = "none";
          sbCol.style.display = "flex";
          sbCol.textContent = collapsed ? "▶" : "◀";
        }
      }
      function openSB() {
        sidebar.classList.add("open");
        sbOv.style.display = "block";
        setTimeout(() => sbOv.classList.add("open"), 10);
        sbClose.style.display = "flex";
        document.body.style.overflow = "hidden";
      }
      function closeSB() {
        sidebar.classList.remove("open");
        sbOv.classList.remove("open");
        setTimeout(() => (sbOv.style.display = "none"), 300);
        sbClose.style.display = "none";
        document.body.style.overflow = "";
      }
      function toggleCol() {
        collapsed = !collapsed;
        sidebar.classList.toggle("collapsed", collapsed);
        sbCol.textContent = collapsed ? "▶" : "◀";
      }
      document.querySelectorAll(".nav-i").forEach((i) =>
        i.addEventListener("click", () => {
          if (window.innerWidth < 768) closeSB();
        }),
      );
      window.addEventListener("resize", checkBP);
      checkBP();

      loadArticle(docs)
    </script>
  </body>
</html>
