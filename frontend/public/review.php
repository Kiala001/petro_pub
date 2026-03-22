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
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetroPub – Detalhe e Avaliação do Documento</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/review.css" rel="stylesheet">
    <link href="assets/css/elements.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>

<div class="toast" id="toast"></div>

<!-- Mobile sidebar overlay -->
<div class="sb-overlay" id="sb-overlay" onclick="closeSidebar()"></div>

<div class="app">


  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1">
        <!-- Hamburger (mobile) -->
        <button class="tb-hamburger" onclick="openSidebar()">☰</button>
        <div class="topbar-left">
          <div class="breadcrumb">PetroPub <span>/ Avaliações / Detalhe</span></div>
          <div class="tb-title">Detalhe e Avaliação</div>
        </div>
      </div>
      <div class="tb-right">
        <button class="btn btn-ghost btn-sm" onclick="showToast('← A voltar à lista…')">
          <span>←</span><span class="btn-back-text">Voltar</span>
        </button>
        <div class="notif-btn" style="width:36px;height:36px;border-radius:8px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;position:relative;flex-shrink:0">
          🔔<span style="position:absolute;top:5px;right:5px;width:7px;height:7px;background:#E53E3E;border-radius:50%;border:2px solid white"></span>
        </div>
        <div class="ava ava-gold" style="width:36px;height:36px;font-size:13px;cursor:pointer;flex-shrink:0">JP</div>
      </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="page-wrap">
      <div class="doc-layout">

        <!-- ══ LEFT COLUMN ══ -->
        <div>

          <!-- DOC HEADER CARD -->
          <div class="doc-header-card">
            <div class="dhc-banner">
              <div class="dhc-status-row">
                <span class="badge b-orange">⏳ <?=$article['status']?></span>
                <!-- <span class="badge" style="color: white;"><?=$article['category_id']?></span> -->
                <span class="badge b-crimson"><?=$article['course']?></span>
                <span class="badge" style="background:rgba(255,255,255,.15);color:rgba(255,255,255,.80)"><?=$article['category_id']?></span>
              </div>
              <div class="dhc-title"><?=$article['title']?></div>
              <div class="meta-item">
                <span class="meta-ico">
                  <i class="fa fa-users" style="color: white;"></i>
                </span>
                <div><div class="meta-lbl">Autor(es)</div><div class="meta-val">
                  <?=arrayForString($authors_list)?>
                </div></div>
              </div><br>
              <div class="dhc-meta-grid">
                <div class="meta-item">
                  <span class="meta-ico">
                    <i class="fa fa-calendar" style="color: white;"></i>
                  </span>
                  <div><div class="meta-lbl">Data</div><div class="meta-val"><?=$article['created_at']?></div></div>
                </div>
                <div class="meta-item">
                  <span class="meta-ico">
                    <i class="fa fa-home" style="color: white;"></i>
                  </span>
                  <div><div class="meta-lbl">Curso</div><div class="meta-val"><?=$article['course']?></div></div>
                </div>
                <div class="meta-item">
                  <span class="meta-ico">
                    <i class="fa fa-file" style="color: white;"></i>
                  </span>
                  <div><div class="meta-lbl">Páginas</div><div class="meta-val">- págs.</div></div>
                </div>
              </div>
            </div>
            <div class="dhc-body">
              <div class="doc-action-btns">
                <button class="btn-doc-action bda-evaluate" onclick="scrollToForm()">
                    <i class="fa fa-star" style="color: orange;"></i> 
                    Avaliar Artigo
                </button>
              </div>
              <div class="doc-quick-stats">
                <div class="qs-item"><div class="qs-num"><?=$review_stat['media']?></div><div class="qs-lbl">Média</div></div>
                <div class="qs-item"><div class="qs-num"><?=$review_count?></div><div class="qs-lbl">Avaliações</div></div>
                <div class="qs-item"><div class="qs-num">128</div><div class="qs-lbl">Páginas</div></div>
              </div>
            </div>
          </div>

          <!-- RESUMO -->
          <div class="section-card anim-d1">
            <div class="sc-head">
              <div>
                <div class="sc-title"><i class="fa fa-book"></i> Resumo</div>
                <div class="sc-sub">Abstract do artigo académico</div>
              </div>
            </div>
            <div class="sc-body">
                <p class="abstract-text">
                    <?=$article['summary']?>
                </p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px">
                    <?php
                      $keywords_list = arrayForString($keywords);

                      echo '<span class="badge b-blue">'.$keywords_list.'</span>';
                    ?>
                </div>
            </div>
          </div>

          <!-- AVALIAÇÕES REALIZADAS -->
          <div class="section-card anim-d2">
            <div class="sc-head">
              <div>
                <div class="sc-title"><i class="fa fa-search"></i> Avaliações</div>
                <div class="sc-sub"><?=$review_count?> avaliações realizadas</div>
              </div>
              <span class="badge b-orange">⏳ Em Avaliação</span>
            </div>
            <div class="sc-body" style="padding-top:16px">
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
                    <div class="review-card decision-revision">
                        <div class="rc-header">
                        <div class="rc-num-badge">
                            <span class="rc-num">Avaliação #<?=$i?></span>
                            <?php
                            if ($review['decision'] == "APROVADO") {
                                echo '<span class="badge b-green"><i class="fa fa-check"></i> Aprovado</span>';
                            } elseif ($review['decision'] == "REJEITADO") {
                                echo '<span class="badge b-red"><i class="fa fa-close"></i> Rejeitado</span>';
                            }else{
                                echo '<span class="badge b-purple"><i class="fa fa-refresh"></i> Revisão</span>';
                            }
                            ?>
                        </div>
                        <span style="font-size:12px;color:var(--text-light)"><?=$review['created_at']?></span>
                        </div>
                        <div class="rc-body">
                        <div class="rc-score-line">
                            <div style="font-size:20px">
                              <?=renderStars($review['rating'])?>
                            </div>
                            <span class="score-label"><?=$review['rating']?></span><span class="score-outof">/ 5</span>
                        </div>
                        <div class="rc-section">
                            <div class="rc-section-lbl"><i class="fa fa-comment"></i> Comentário</div>
                            <div class="rc-section-text">
                              <?=$review['comment']?>
                            </div>
                        </div>
                        <div class="rc-section">
                            <div class="rc-section-lbl"><i class="fa fa-idea"></i> Sugestão de Melhoria</div>
                            <div class="rc-section-text"><?=$review['suggest']?></div>
                        </div>
                        <div class="rc-reviewer-row">
                            <div class="rc-reviewer-ava">
                              <?=getInitials($user['name'])?>
                            </div>
                            <div>
                            <div class="rc-reviewer-name">
                              <?php
                              $username = ($user['type'] == "COMMON_USER") ? $user['name'] : 'Docente '.$user['name'] ;
                              echo $username;
                              ?>
                              <?=$isYou = ($haveAlreadyEvaluated) ? '<span class="badge b-green">VOCÊ</span>' : ''?>
                            </div>
                            <div class="rc-reviewer-role">
                              <?=$user['email']?> — <?=$user['points']?> Pontos
                            </div>
                            </div>
                            <div class="rc-date"><?=$user['created_at']?></div>
                        </div>
                        </div>
                    </div>
                <?php
                $i++;
                }
            }
            ?>
            </div>

            <!-- AVERAGE FOOTER -->
            <div class="sc-footer">
              <div>
                <div style="font-size:11px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Média das Avaliações</div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                  <span style="font-family:'Playfair Display',serif;font-size:clamp(22px,3vw,28px);font-weight:700;color:var(--crimson)">
                    <?=$review_stat['media']?>
                  </span>
                  <div>
                    <div style="font-size:clamp(14px,2vw,18px);letter-spacing:2px">
                      <?=$review_stat['stars']?>
                    </div>
                    <!-- <div style="font-size:12px;color:var(--text-light);margin-top:1px">2 avaliações · 0 aprovados · 2 revisões</div> -->
                  </div>
                </div>
                <div class="prog" style="max-width:220px;margin-top:8px"><div class="prog-f" style="width:70%"></div></div>
              </div>
              <button class="btn btn-primary" onclick="scrollToForm()">⭐ Fazer Avaliação</button>
            </div>
          </div>

        </div>
        <!-- ══ END LEFT ══ -->

        <!-- ══ RIGHT COLUMN ══ -->
        <div class="right-panel">

          <!-- AVERAGE VISUAL -->
          <div class="avg-block">
            <div class="avg-title">Média das Avaliações</div>
            <div>
              <span class="avg-score">
                <?=$review_stat['media']?>
              </span>
              <span class="avg-outof"> / 5</span>
            </div>
            <div class="avg-stars-row">
                <?=$review_stat['stars']?> 
            </div>
            <div class="avg-count"><?=$review_count?> avaliações · Aguarda mais avaliadores</div>
            <div class="avg-prog-wrap">
              <div class="avg-prog-label"><span>Progresso até publicação</span><span>70%</span></div>
              <div class="avg-prog-bar"><div class="avg-prog-fill" id="avg-prog" style="width:70%"></div></div>
            </div>
          </div>

          <!-- EVALUATE FORM -->
          <div class="eval-form-card" id="eval-form-card">
            <?php
            if (!$haveAlreadyEvaluated) {
              ?>
              <div class="efc-head">
                <h3>
                  <i class="fa fa-star" style="color: orange;"></i> 
                  Avaliar Documento
              </h3>
                <p>Preencha o formulário de avaliação abaixo</p>
              </div>
              <!-- Form body -->
              <div id="eval-form-body">
                <div class="efc-body">

                  <!-- SCORE -->
                  <div class="form-field">
                    <label class="form-label">Pontuação <span class="req">*</span></label>
                    <div class="star-input-wrap">
                      <div class="stars-input-row" id="star-input">
                        <span class="star-inp" onclick="setScore(1)" onmouseover="hoverScore(1)" onmouseout="resetHover()">⭐</span>
                        <span class="star-inp" onclick="setScore(2)" onmouseover="hoverScore(2)" onmouseout="resetHover()">⭐</span>
                        <span class="star-inp" onclick="setScore(3)" onmouseover="hoverScore(3)" onmouseout="resetHover()">⭐</span>
                        <span class="star-inp" onclick="setScore(4)" onmouseover="hoverScore(4)" onmouseout="resetHover()">⭐</span>
                        <span class="star-inp" onclick="setScore(5)" onmouseover="hoverScore(5)" onmouseout="resetHover()">⭐</span>
                      </div>
                      <div class="star-score-display" id="star-label">Toque para classificar</div>
                    </div>
                  </div>

                  <!-- COMMENT -->
                  <div class="form-field">
                    <label class="form-label">Comentário <span class="req">*</span></label>
                    <textarea id="eval-comment" placeholder="Descreva os pontos fortes e fracos, qualidade da escrita, rigor científico…" oninput="updateCharCount(this,'cc-comment',500)"></textarea>
                    <div class="char-count"><span id="cc-comment">0</span> / 500</div>
                  </div>

                  <!-- SUGGESTIONS -->
                  <div class="form-field">
                    <label class="form-label">Sugestões de Melhoria</label>
                    <textarea id="eval-suggest" placeholder="Indique sugestões concretas para o autor melhorar o trabalho…" style="min-height:80px" oninput="updateCharCount(this,'cc-suggest',300)"></textarea>
                    <div class="char-count"><span id="cc-suggest">0</span> / 300</div>
                  </div>

                  <!-- DECISION -->
                  <div class="form-field" style="margin-bottom:0">
                    <label class="form-label">Decisão <span class="req">*</span></label>
                    <div class="decision-grid">
                      <div class="decision-opt" id="dec-approve" onclick="selectDecision('approve')">
                        <input type="radio" name="decision">
                        <div class="decision-radio-circle" id="drc-approve"></div>
                        <span class="decision-icon">
                          <i class="fa fa-check" style="color: green;"></i> 
                        </span>
                        <div class="decision-text">
                          <div class="decision-name">Aprovar</div>
                          <div class="decision-desc">Pronto para publicação</div>
                        </div>
                      </div>
                      <div class="decision-opt" id="dec-reject" onclick="selectDecision('reject')">
                        <input type="radio" name="decision">
                        <div class="decision-radio-circle" id="drc-reject"></div>
                        <span class="decision-icon">
                          <i class="fa fa-close" style="color: red;"></i> 
                        </span>
                        <div class="decision-text">
                          <div class="decision-name">Rejeitar</div>
                          <div class="decision-desc">Não cumpre os critérios mínimos</div>
                        </div>
                      </div>
                      <div class="decision-opt" id="dec-revision" onclick="selectDecision('revision')">
                        <input type="radio" name="decision">
                        <div class="decision-radio-circle" id="drc-revision"></div>
                        <span class="decision-icon">
                          <i class="fa fa-refresh" style="color: blue;"></i> 
                        </span>
                        <div class="decision-text">
                          <div class="decision-name">Pedir Revisão</div>
                          <div class="decision-desc">Solicitar melhorias antes da publicação</div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>

                <div class="efc-footer">
                  <button class="btn btn-primary btn-lg btn-full" onclick="prepareEval('<?=$article['id']?>')">
                      <i class="fa fa-send" style="color: white;"></i> 
                      Enviar Avaliação
                  </button>
                  <div style="text-align:center;margin-top:10px;font-size:12px;color:var(--text-light)">
                    A avaliação é anónima para o estudante
                  </div>
                </div>
              </div>
            
            <?php
            } else {
            ?>

              <!-- CONFIRM STATE -->
              <div class="eval-confirm" id="eval-confirm">
                <div class="conf-icon">
                      <i class="fa fa-check" style="color: green;"></i> 
                </div>
                <h3>Avaliação Enviada!</h3>
                <p>A sua avaliação foi registada com sucesso e contribui para a decisão final sobre este documento.</p>
                <div class="conf-details" id="conf-details"></div>
                <div class="conf-btns">
                  <a class="btn btn-primary" href="pair-review.php">← Voltar à lista</a>
                </div>
              </div>
            <?php
            }
            ?>

          </div>
          <!-- /eval-form-card -->

        </div>
        <!-- ══ END RIGHT ══ -->

      </div>
    </div>
    <!-- /page-wrap -->

  </div>
  <!-- /main -->

</div>
<!-- /app -->

<!-- MODAL — PDF VIEWER -->
<div class="modal-overlay" id="modal-pdf" onclick="closeModalOut(event,'modal-pdf')">
  <div class="modal">
    <div class="modal-head">
      <h3>📄 Pré-visualização</h3>
      <p>Sistema de Gestão Hospitalar Distribuído</p>
      <button class="modal-close" onclick="closeModal('modal-pdf')">✕</button>
    </div>
    <div class="modal-body" style="text-align:center;padding:36px 24px">
      <div style="font-size:48px;margin-bottom:14px">📄</div>
      <h3 style="font-family:'Playfair Display',serif;font-size:17px;color:var(--text-dark);margin-bottom:8px">Pré-visualização PDF</h3>
      <p style="font-size:14px;color:var(--text-light);margin-bottom:18px">Em produção, o PDF seria renderizado aqui.</p>
      <div style="background:var(--cream);border:2px dashed var(--border);border-radius:var(--r-lg);padding:28px;margin-bottom:18px">
        <div style="font-size:13px;color:var(--text-light)">[ Visualizador de PDF ]</div>
      </div>
      <button class="btn btn-primary" onclick="closeModal('modal-pdf')">Fechar</button>
    </div>
  </div>
</div>

<script src="assets/js/sidebar.js"></script>
<script src="assets/js/util.js"></script>
<script src="assets/js/evaluation.js"></script>
<script src="assets/js/api.js"></script>
<script>

function setScore(n) {
  currentScore = n;
  renderStars(n);
  const lbl = document.getElementById('star-label');
  lbl.textContent = `${n}/5 — ${scoreLabels[n]}`;
  lbl.style.color = n >= 4 ? 'var(--success)' : n >= 3 ? 'var(--warn)' : 'var(--danger)';
}

function hoverScore(n)  { renderStars(n, true); }
function resetHover()   { renderStars(currentScore); }

function renderStars(n, isHover = false) {
  document.querySelectorAll('.star-inp').forEach((s, i) => {
    s.classList.toggle('active', i < n);
    s.style.transform = (isHover && i === n - 1) ? 'scale(1.2)' : '';
  });
}

// Touch-friendly star tap
document.querySelectorAll('.star-inp').forEach((s, i) => {
  s.addEventListener('touchend', (e) => { e.preventDefault(); setScore(i + 1); });
});


// ═══════════════════════════════
//  CHAR COUNT
// ═══════════════════════════════
function updateCharCount(el, targetId, max) {
  const len = el.value.length;
  const span = document.getElementById(targetId);
  span.textContent = len;
  span.style.color = len > max * 0.9 ? 'var(--danger)' : 'var(--text-light)';
}


// ═══════════════════════════════
//  DECISION SELECTOR
// ═══════════════════════════════


// ═══════════════════════════════
//  SUBMIT EVALUATION
// ═══════════════════════════════

// ═══════════════════════════════
//  MODAL
// ═══════════════════════════════
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalOut(e, id) { if (e.target.id === id) closeModal(id); }


// ═══════════════════════════════
//  TOAST
// ═══════════════════════════════
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3400);
}

// Init
renderStars(0);
</script>
</body>
</html>
