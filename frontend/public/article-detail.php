<?php
include_once 'includes.php';

$documentId = $_GET['flex-direction'];
$documentId = decrypt($documentId);


$result = $documentService->getDocumentDetails($documentId);
$article = $result['document'];
$authors = json_decode($article['authors']);
$authors_list = explode(",", $authors);

$key = json_decode($article['keywords']);
$keywords = explode(",", $key);
$user_id = $_SESSION['user_uuid'];

$result = $reviewService->getReviewsByDocument($documentId);
$reviews = $result['reviews'];
$review_count = $result['count'];

$review_stat = calcularMediaAvaliacoes($reviews);
?>
<!doctype html>
<html lang="pt">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PetroPub — Detalhe de Avaliação</title>
    <link href="assets/css/article_detail.css" rel="stylesheet">
    <link href="assets/css/modals.css" rel="stylesheet">
    <link href="assets/css/elements.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>
    <div class="toast t-def" id="toast"></div>
    <div class="sb-ov" id="sb-ov" onclick="closeSB()"></div>

    <div class="app">
      <!-- ══════ SIDEBAR ══════ -->
      

      <!-- ══════ MAIN ══════ -->
      <div class="main">
        <div class="topbar">
          <div class="tb-l">
            <button class="tb-ham" onclick="openSB()">☰</button>
            <div class="tb-info">
              <div class="tb-bc">
                PetroPub
                <span
                  >/ Admin / Avaliações /
                  <span id="bc-doc">Sistema de Gestão Hospitalar…</span></span
                >
              </div>
              <div class="tb-title">Detalhe da Avaliação</div>
            </div>
          </div>
          <div class="tb-r">
            <button
              class="btn-back"
              onclick="showToast('← A voltar à lista…', 't-def')"
            >
              ← <span class="btn-back-txt">Voltar à lista</span>
            </button>
            <div class="admin-pill">
              <i class="fa fa-cog"></i> <span class="ap-txt">Administrador</span>
            </div>
            <div class="notif-btn">🔔</div>
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
              AD
            </div>
          </div>
        </div>

        <div class="page-wrap">
          <div class="doc-layout">
            <!-- ════ LEFT COLUMN ════ -->
            <div>
              <!-- DOC HEADER -->
              <div class="doc-header">
                <div class="dhc-banner">
                  <div class="dhc-tags">
                    <span class="badge bo"><?=$article['status']?></span>
                    <span class="badge bc"><?=$article['category_id']?></span>
                  </div>
                  <div class="dhc-title">
                    <?=$article['title']?>
                  </div>
                  <div class="dhc-meta">
                    <div class="meta-item">
                      <span class="meta-ico"><i class="fa fa-user"></i></span>
                      <div>
                        <div class="meta-lbl">Autor</div>
                        <div class="meta-val">
                        <?php
                            $id = new UserId("US");
                            $id->__fromString($article['user_id']);
                            $user = $userRepository->findById($id);

                            echo $user['name'];
                        ?>
                        </div>
                      </div>
                    </div>
                    <div class="meta-item">
                      <span class="meta-ico"><i class="fa fa-calendar"></i></span>
                      <div>
                        <div class="meta-lbl">Ano</div>
                        <div class="meta-val"><?=$article['created_at']?></div>
                      </div>
                    </div>
                    <div class="meta-item">
                      <span class="meta-ico"><i class="fa fa-page"></i></span>
                      <div>
                        <div class="meta-lbl">Páginas</div>
                        <div class="meta-val"><?=$article['file_size']?> págs.</div>
                      </div>
                    </div>
                    <div class="meta-item">
                      <span class="meta-ico"><i class="fa fa-search"></i></span>
                      <div>
                        <div class="meta-lbl">Avaliações</div>
                        <div class="meta-val"><?=$result['count']?> recebida(s)</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="dhc-body">
                  <div class="doc-actions">
                    <button
                      class="btn-da bda-view"
                      onclick="
                        showToast('👁 A abrir pré-visualização…', 't-def')
                      "
                    >
                      👁 Ver Documento
                    </button>
                    <button
                      class="btn-da bda-dl"
                      onclick="showToast('📥 A iniciar download…', 't-def')"
                    >
                      📥 Download
                    </button>
                  </div>
                  <div class="doc-qs">
                    <div class="qs-item">
                      <div class="qs-n"><?=$review_stat['media']?></div>
                      <div class="qs-l">Média</div>
                    </div>
                    <div class="qs-item">
                      <div class="qs-n"><?=$result['count']?></div>
                      <div class="qs-l">Avaliações</div>
                    </div>
                    <div class="qs-item">
                      <div class="qs-n"><?=$article['file_size']?></div>
                      <div class="qs-l">Páginas</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- RESUMO -->
              <div class="sc" style="animation-delay: 0.05s">
                <div class="sc-head">
                  <div>
                    <div class="sc-title"><i class="fa fa-edit" style="color: --var(--crimson)"></i> Resumo do Trabalho</div>
                    <div class="sc-sub">Abstract académico</div>
                  </div>
                </div>
                <div class="sc-body">
                  <p class="abstract-text">
                    <?=$article['summary']?>
                  </p>
                  <div
                    style="
                      display: flex;
                      gap: 8px;
                      flex-wrap: wrap;
                      margin-top: 16px;
                    "
                  >
                    <?php
                      $keywords_list = arrayForString($keywords);

                      echo '<span class="badge b-blue">'.$keywords_list.'</span>';
                    ?>
                  </div>
                </div>
              </div>

              <!-- AVALIAÇÕES DOS PARES -->
              <div class="sc" style="animation-delay: 0.1s">
                <div class="sc-head">
                  <div>
                    <div class="sc-title"><i class="fa fa-search" style="color: var(--crimson)"></i> Avaliações dos Pares</div>
                    <div class="sc-sub">
                      <?=$result['count']?> avaliações realizadas por professores avaliadores —
                      identidade sempre visível para o admin
                    </div>
                  </div>
                  <?php
                    $status = ($article['status'] == 'PENDENTE') ? '<span class="badge bo">⏳ Aguardando Decisão DO Administrador</span>' : '';
                  ?>
                </div>
                <div class="sc-body" style="padding-top: 16px">
            <?php
                if (!empty($reviews)) {
                    $i = 1;
                    $haveAlreadyEvaluated = false;
                    foreach ($reviews as $review) {
                    $haveAlreadyEvaluated = ($review['user_id'] == $user_id) ? true : false ;
                    $id = new UserId("US");
                    $id->__fromString($review['user_id']);
                    $user = $userRepository->findById($id);
                    ?>
                        <!-- Review #1 -->
                    <div class="review-card rv-wn">
                        <div class="rc-head">
                            <div class="rc-num-row">
                                <span class="rc-num">Avaliação #1</span>
                                <?php
                                if ($review['decision'] == "APROVADO") {
                                    echo '<span class="badge bg"><i class="fa fa-check" style="color: var(--ok)"></i> Aprovado</span>';
                                } elseif ($review['decision'] == "REJEITADO") {
                                    echo '<span class="badge bc"><i class="fa fa-close" style="color: var(--cr)"></i> Rejeitado</span>';
                                }else{
                                    echo '<span class="badge bb" style="color: var(--inf)"><i class="fa fa-refresh"></i> Revisão</span>';
                                }
                                ?>
                                <span style="font-size: 12px; color: var(--tx-l)"
                                ><?=$review['created_at']?></span
                                >
                            </div>
                        </div>
                        <div class="rc-body">
                        <div class="rc-score">
                            <span class="rc-stars">
                              <?=renderStars($review['rating'])?>
                            </span>
                            <span class="rc-val"><?=$review['rating']?></span
                            ><span class="rc-outof">/ 5</span>
                        </div>
                        <div class="rc-sec">
                            <div class="rc-lbl"><i class="fa fa-comments-o" style="color: var(--inf)"></i> Comentário</div>
                            <div class="rc-txt">
                            <?=$review['comment']?>
                            </div>
                        </div>
                        <div class="rc-sec">
                            <div class="rc-lbl">💡 Sugestão de Melhoria</div>
                            <div class="rc-txt">
                            <?=$review['suggest']?>
                            </div>
                        </div>
                        <div class="rc-reviewer">
                            <div class="rc-rev-ava">
                              <?=getInitials($user['name'])?>
                            </div>
                            <div>
                            <div class="rc-rev-name">
                              <?php
                              $username = ($user['type'] == "COMMON_USER") ? $user['name'] : 'Docente '.$user['name'] ;
                              echo $username;
                              ?>
                            </div>
                            <div class="rc-rev-role">
                              <?=$user['email']?> — <?=$user['points']?> Pontos
                            </div>
                            </div>
                            <div class="rc-date"><?=$review['created_at']?></div>
                        </div>
                        </div>
                    </div>

                <?php
                    }
                }
                ?>

                </div>
                <!-- <div class="sc-footer">
                  <div>
                    <div
                      style="
                        font-size: 11px;
                        font-weight: 700;
                        color: var(--tx-l);
                        text-transform: uppercase;
                        letter-spacing: 0.8px;
                        margin-bottom: 5px;
                      "
                    >
                      Média das Avaliações
                    </div>
                    <div
                      style="
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        flex-wrap: wrap;
                      "
                    >
                      <span
                        style="
                          font-family: &quot;Playfair Display&quot;, serif;
                          font-size: clamp(22px, 3vw, 28px);
                          font-weight: 700;
                          color: var(--cr);
                        "
                        >3.5</span
                      >
                      <div>
                        <div
                          style="
                            font-size: clamp(14px, 2vw, 18px);
                            letter-spacing: 2px;
                          "
                        >
                          ⭐⭐⭐⭐☆
                        </div>
                        <div
                          style="
                            font-size: 12px;
                            color: var(--tx-l);
                            margin-top: 1px;
                          "
                        >
                          2 avaliações · 0 aprovados · 2 pedidos de revisão
                        </div>
                      </div>
                    </div>
                    <div class="prog" style="max-width: 220px; margin-top: 8px">
                      <div class="prog-f" style="width: 70%"></div>
                    </div>
                  </div>
                  <button class="btn btn-cr" onclick="scrollToDecision()">
                    ⚖️ Tomar Decisão
                  </button>
                </div> -->
              </div>

            </div>
            <!-- ════ END LEFT ════ -->

            <!-- ════ RIGHT PANEL ════ -->
            <div class="right-panel">
              <!-- AVG VISUAL BLOCK -->
              <div class="avg-block">
                <div class="avg-t">Média das Avaliações</div>
                <div>
                  <span class="avg-score">
                    <?=$review_stat['media']?>
                  </span>
                  <span class="avg-outof"> / 5</span>
                </div>
                <div class="avg-stars">
                    <?=$review_stat['stars']?> 
                </div>
                <div class="avg-count"><?=$review_count?> avaliações de pares recebidas</div>
                <!-- <div class="avg-prog-wrap">
                  <div class="avg-prog-lbl">
                    <span>Progresso de avaliação</span><span>70%</span>
                  </div>
                  <div class="avg-prog-bar">
                    <div class="avg-prog-fill" style="width: 70%"></div>
                  </div>
                </div> -->
                <div class="avg-chips">
                  <span class="avg-chip avg-chip-ok">- <i class="fa fa-check"></i> Aprovados</span>
                  <span class="avg-chip avg-chip-wn">- <i class="fa fa-refresh"></i> Revisões</span>
                  <span class="avg-chip avg-chip-er">- <i class="fa fa-close"></i> Rejeitados</span>
                </div>
              </div>

              <!-- DECISION CARD -->
              <div class="decision-card" id="decision-card">
                <div class="dc-head">
                  <h3>Decisão Editorial</h3>
                  <p>
                    Apenas o administrador pode aprovar ou rejeitar a publicação
                  </p>
                </div>

                <!-- Form state -->
                <div id="decision-form">
                  <div class="dc-body">
                    <div
                      style="
                        background: var(--wn-bg);
                        border: 1px solid rgba(196, 122, 26, 0.2);
                        border-radius: var(--r2);
                        padding: 12px 14px;
                        margin-bottom: 18px;
                        font-size: 13px;
                        color: var(--tx-m);
                        line-height: 1.5;
                      "
                    >
                      ⚠️ <strong>Atenção:</strong> Esta decisão notificará o
                      autor e tornará o documento público (se aprovado) ou
                      arquivado (se rejeitado). Não pode ser revertida sem
                      contactar o suporte.
                    </div>

                    <input type="hidden" name="id" id="document-id" value="<?=$documentId?>">

                    <div style="margin-bottom: 16px">
                      <label class="f-label"
                        >Decisão Final
                        <span style="color: var(--cr)">*</span></label
                      >
                      <div
                        class="dec-opt"
                        id="dec-approve"
                        onclick="selDec('approve')"
                      >
                        <div class="dec-radio" id="dr-a"></div>
                        <span class="dec-ico">
                            <i class="fa fa-check" style="color: var(--ok);"></i>
                        </span>
                        <div class="dec-txt">
                          <div class="dec-name">Aprovar Publicação</div>
                          <div class="dec-desc">
                            O documento cumpre os critérios. Será publicado e
                            disponibilizado para download.
                          </div>
                        </div>
                      </div>
                      <div
                        class="dec-opt"
                        id="dec-reject"
                        onclick="selDec('reject')"
                      >
                        <div class="dec-radio" id="dr-r"></div>
                        <span class="dec-ico">
                            <i class="fa fa-close" style="color: var(--cr)"></i>
                        </span>
                        <div class="dec-txt">
                          <div class="dec-name">Rejeitar Documento</div>
                          <div class="dec-desc">
                            O documento não cumpre os critérios mínimos. Será
                            arquivado e o autor notificado.
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="note-wrap">
                      <label class="f-label"
                        >Nota para o Autor
                        <span
                          style="
                            font-weight: 400;
                            text-transform: none;
                            color: var(--tx-l);
                          "
                          >(opcional)</span
                        ></label
                      >
                      <textarea
                        class="f-textarea"
                        id="dec-note"
                        placeholder="Explique os motivos da decisão, pontos a melhorar ou felicitações pelo trabalho académico…"
                        rows="4"
                      ></textarea>
                    </div>
                  </div>

                  <div class="dc-footer">
                    <div class="dec-btns">
                      <button
                        class="btn btn-ok"
                        onclick="confirmDecision('approve')"
                      >
                        <i class="fa fa-check"></i> Aprovar
                      </button>
                      <button
                        class="btn btn-er"
                        onclick="confirmDecision('reject')"
                      >
                        <i class="fa fa-close"></i> Rejeitar
                      </button>
                    </div>
                    <button
                      class="btn btn-cr btn-full"
                      onclick="confirmDecision(null)"
                    >
                      ⚖️ Confirmar Decisão Seleccionada
                    </button>
                  </div>
                </div>

                <!-- Confirmed state (hidden initially) -->
                <div class="decision-confirmed" id="decision-confirmed">
                  <div class="conf-ico" id="cf-ico">🎉</div>
                  <div class="conf-title" id="cf-title">
                    Publicação Aprovada!
                  </div>
                  <div class="conf-desc" id="cf-desc">
                    A decisão foi registada e o autor será notificado
                    automaticamente.
                  </div>
                  <div class="conf-sum" id="cf-sum">
                    <div class="conf-row">
                      <span class="cl">Documento</span
                      ><span class="cv">Sistema de Gestão Hospitalar…</span>
                    </div>
                    <div class="conf-row">
                      <span class="cl">Decisão</span
                      ><span class="cv" id="cf-dec">—</span>
                    </div>
                    <div class="conf-row">
                      <span class="cl">Admin</span
                      ><span class="cv">Ana Domingos</span>
                    </div>
                    <div class="conf-row">
                      <span class="cl">Data</span
                      ><span class="cv" id="cf-date">—</span>
                    </div>
                  </div>
                  <div
                    style="
                      margin-top: 16px;
                      display: flex;
                      gap: 8px;
                      justify-content: center;
                      flex-wrap: wrap;
                    "
                  >
                    <button
                      class="btn btn-cr btn-sm"
                      onclick="showToast('← A voltar à lista…', 't-def')"
                    >
                      ← Ver Lista
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <!-- ════ END RIGHT ════ -->
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL CONFIRMAÇÃO -->
    <div
      class="overlay"
      id="modal-confirm"
      onclick="ovClose(event, 'modal-confirm')"
    >
      <div class="modal">
        <div class="m-head" id="mc-head">
          <h3 id="mc-title">—</h3>
          <p id="mc-sub">—</p>
          <button class="m-close" onclick="closeModal('modal-confirm')">
            ✕
          </button>
        </div>
        <div class="m-body">
          <div class="m-ico" id="mc-ico">🎉</div>
          <div class="m-title" id="mc-mt">—</div>
          <div class="m-desc" id="mc-desc">—</div>
          <div class="m-sum conf-sum" id="mc-sum">
            <div class="conf-row">
              <span class="cl">Decisão</span
              ><span class="cv" id="mc-dec">—</span>
            </div>
            <div class="conf-row">
              <span class="cl">Data/Hora</span
              ><span class="cv" id="mc-date">—</span>
            </div>
          </div>
        </div>
        <div class="m-foot">
          <button class="btn btn-gh" onclick="closeModal('modal-confirm')">
            Fechar
          </button>
          <button
            class="btn btn-cr"
            onclick="
              closeModal('modal-confirm');
              showToast('← A voltar à lista…', 't-def');
            "
          >
            ← Ver Lista
          </button>
        </div>
      </div>
    </div>

    <script src="assets/js/api.js"></script>
    <script>
      /* ── STATE ── */
      let selectedDec = "";
      let decisionMade = false;

      /* ── DECISION SELECTION ── */
      function selDec(d) {
        selectedDec = d;
        document.getElementById("dec-approve").className =
          "dec-opt" + (d === "approve" ? " sel-ok" : "");
        document.getElementById("dec-reject").className =
          "dec-opt" + (d === "reject" ? " sel-er" : "");
      }

      /* ── CONFIRM DECISION ── */
      async function confirmDecision(forcedDec) {
        const dec = forcedDec || selectedDec;
        if (!dec) {
          showToast("⚠️ Seleccione uma decisão antes de confirmar", "t-wn");
          return;
        }

        const isOk = dec === "approve";
        const note = document.getElementById("dec-note").value.trim();
        const id = document.getElementById("document-id").value;
        const now = new Date();
        const dateStr =
          now.toLocaleDateString("pt-PT") +
          " " +
          now.toLocaleTimeString("pt-PT", {
            hour: "2-digit",
            minute: "2-digit",
          });

        decisionMade = true;


        const response = await apiRequest("documents-decision", {
          method: "PUT",
          body: {id, dec, note},
        });

        const data = response.data;
        const status = data.status;
        const message = data.message;
        const title = data.title;


        /* Update right panel → confirmed state */
        document.getElementById("decision-form").style.display = "none";
        const cfEl = document.getElementById("decision-confirmed");
        cfEl.style.display = "block";
        cfEl.classList.add("cs-" + (isOk ? "ok" : "er"));
        document.getElementById("cf-ico").textContent = isOk ? "🎉" : "🚫";
        document.getElementById("cf-title").textContent = isOk
          ? "Publicação Aprovada!"
          : "Documento Rejeitado.";
        document.getElementById("cf-desc").textContent = isOk
          ? "O documento foi aprovado. O autor será notificado e o documento ficará disponível para download."
          : "O documento foi rejeitado. O autor será notificado com os motivos indicados.";
        document.getElementById("cf-sum").className =
          "conf-sum " + (isOk ? "cs-ok" : "cs-er");
        document.getElementById("cf-dec").textContent = isOk
          ? "✅ Aprovado"
          : "❌ Rejeitado";
        document.getElementById("cf-date").textContent = dateStr;

        /* Update header badge */
        const tagsEl = document.querySelector(".dhc-tags");
        const firstBadge = tagsEl.firstElementChild;
        if (firstBadge) {
          firstBadge.className = isOk ? "badge bg" : "badge br";
          firstBadge.textContent = isOk ? "✅ Aprovado" : "❌ Rejeitado";
        }

        /* Show confirmation modal */
        document.getElementById("mc-head").className =
          "m-head " + (isOk ? "m-head-ok" : "m-head-er");
        document.getElementById("mc-title").textContent = isOk
          ? "✅ Publicação Autorizada"
          : "❌ Documento Rejeitado";
        document.getElementById("mc-sub").textContent = isOk
          ? "O documento será publicado na plataforma"
          : "O autor será notificado";
        document.getElementById("mc-ico").textContent = isOk ? "🎉" : "🚫";
        document.getElementById("mc-mt").textContent = isOk
          ? "Decisão registada com sucesso!"
          : "Rejeição registada.";
        document.getElementById("mc-desc").textContent = isOk
          ? "O documento foi aprovado pelo administrador e ficará disponível para download após processamento."
          : "O documento foi rejeitado. O autor receberá uma notificação com os motivos desta decisão.";
        document.getElementById("mc-sum").className =
          "m-sum conf-sum " + (isOk ? "cs-ok" : "cs-er");
        document.getElementById("mc-dec").textContent = isOk
          ? "✅ Aprovado"
          : "❌ Rejeitado";
        document.getElementById("mc-date").textContent = dateStr;
        openModal("modal-confirm");

        showToast(
          isOk
            ? "✅ Documento aprovado para publicação!"
            : "❌ Documento rejeitado.",
          isOk ? "t-ok" : "t-er",
        );
      }

      /* ── UNDO / REVERTER ── */
      function undoDecision() {
        decisionMade = false;
        selectedDec = "";
        document.getElementById("decision-form").style.display = "block";
        document.getElementById("decision-confirmed").style.display = "none";
        document.getElementById("decision-confirmed").className =
          "decision-confirmed";
        document.getElementById("dec-approve").className = "dec-opt";
        document.getElementById("dec-reject").className = "dec-opt";
        document.getElementById("dec-note").value = "";
        /* Restore badge */
        const firstBadge =
          document.querySelector(".dhc-tags").firstElementChild;
        if (firstBadge) {
          firstBadge.className = "badge bo";
          firstBadge.textContent = "⏳ Em Avaliação";
        }
        showToast("↩ Decisão revertida — aguardando nova decisão", "t-wn");
      }

      /* ── SCROLL TO DECISION ── */
      function scrollToDecision() {
        const el = document.getElementById("decision-card");
        if (!el) return;
        el.scrollIntoView({ behavior: "smooth", block: "start" });
        el.style.boxShadow =
          "0 0 0 3px rgba(201,168,76,.5), " + el.style.boxShadow;
        setTimeout(() => (el.style.boxShadow = ""), 1800);
      }

      /* ── MODAL ── */
      function openModal(id) {
        document.getElementById(id).classList.add("open");
        document.body.style.overflow = "hidden";
      }
      function closeModal(id) {
        document.getElementById(id).classList.remove("open");
        document.body.style.overflow = "";
      }
      function ovClose(e, id) {
        if (e.target.id === id) closeModal(id);
      }

      /* ── TOAST ── */
      function showToast(msg, cls = "t-def") {
        const t = document.getElementById("toast");
        t.textContent = msg;
        t.className = "toast " + cls;
        t.classList.add("show");
        setTimeout(() => t.classList.remove("show"), 3500);
      }

      /* ── SIDEBAR ── */
      const sidebar = document.getElementById("sidebar");
      const sbOv = document.getElementById("sb-ov");
      const sbClose = document.getElementById("sb-close");
      const sbCol = document.getElementById("sb-col");
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
    </script>
  </body>
</html>
