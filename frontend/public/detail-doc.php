<?php
include_once 'includes.php';

if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php');
    exit;
}
$jwt = $_SESSION['jwt_auth'];
$userName = $_SESSION['user_name'] ?? 'Usuário';
$userEmail = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));

$documentId = $_GET['id'];
$readMode = isset($_GET['read']);

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

$stmt = $db->prepare("UPDATE documents SET review_count = ? + review_count WHERE id=?");
$stmt->execute([1, $documentId]);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetroPub – Detalhe do Documento</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/detail-doc.css" rel="stylesheet">
    <link href="assets/css/elements.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
</head>
<body>

<div class="toast" id="toast"></div>

<!-- ════ TOPBAR ════ -->
<div class="topbar">
  <div class="tb-l">
    <img src='../../uploads/logo/logo1.PNG' alt='logotipo petropub' style='width: 100px; height: 90px;'>
    <div class="breadcrumb">
      <a onclick="showToast('← A voltar à Biblioteca')">← Biblioteca</a>
      &nbsp;/&nbsp; Eng. Informática &nbsp;/&nbsp; Detalhes
    </div>
  </div>
  <div class="tb-r">
    <a href="my-documents.php" class="btn btn-icon btn-ok"><i class="fa fa-home"></i></a>
    <a href="library.php" class="btn btn-icon btn-gd"><i class="fa fa-book"></i></a>
    <div class="ava-sm"><?=$userInitials?></div>
  </div>
</div>

<!-- ════ HERO ════ -->
<div class="doc-hero">
  <div class="hero-inner">

    <!-- Cover -->
    <div class="doc-cover">
      <div class="cover-card">
        <?php
        if ($article['file_cover'] != null || !empty($article['file_cover'])) {
          echo '<img src="../../uploads/documents/cover/'.$article['file_cover'].'" style="width: 100%; height: 100%;" alt="'.$article['title'].'">
              <div class="cover-badge">'.$article['category_id'].'</div>
            ';
        }else {
        ?>
        <div class="cover-ico"><i class="fa fa-book" style="color: white"></i></div>
        <div class="cover-lines">
          <div class="cover-line"></div>
          <div class="cover-line"></div>
          <div class="cover-line"></div>
        </div>
        <div class="cover-badge"><?=$article['category_id']?></div>
        <?php
        }
        ?>
      </div>
    </div>

    <!-- Info -->
    <div class="hero-body">
      <div class="hero-tags">
        <span class="h-tag ht-cat"><?=$article['course']?></span>
        <!-- <span class="h-tag ht-type">Dissertação de Mestrado</span> -->
        <span class="h-tag ht-free">🆓 Acesso Livre</span>
      </div>
      <div class="hero-title"><?=$article['title']?></div>
      <div class="hero-authors">
        <span style="color:rgba(255,255,255,.60)"><i class="fa fa-user"></i></span>
        <span class="author-chip">
          <?=arrayForString($authors_list)?>
        </span>
        <span class="author-chip"><?=$articlei['advisor'] || ''?></span>
      </div>
      <div class="hero-rating-big">
        <div class="hrb-num"><?=$review_stat['media']?></div>
        <div class="hrb-right">
          <div class="hrb-stars"><?=$review_stat['stars']?></div>
          <div class="hrb-count"><?=$review_count?> avaliações · <?=$article['review_count']?> Leituras</div>
        </div>
      </div>
      <div class="hero-meta">
        <!-- <div class="hm-item"><div class="hm-val">📄 128</div><div class="hm-lbl">Páginas</div></div> -->
        <div class="hm-item"><div class="hm-val"></div><div class="hm-lbl">Leituras</div></div>
        <div class="hm-item"><div class="hm-val"><?=$article['created_at']?></div><div class="hm-lbl">Publicado em</div></div>
        <div class="hm-item"><div class="hm-val"><?=$article['course']?></div><div class="hm-lbl">Curso</div></div>
      </div>
    </div>

    <!-- Purchase Panel -->
    <div class="hero-purchase" id="purchase-panel">
      <div class="hp-price-lbl">
        Físico
      </div>
      <div class="hp-price-free">
        <?php
          echo number_format(($article['price']),2,',','.');
        ?>
      </div>
      <div class="hp-price-sub">Localização para obtenção do livro</div>
      <div class="hp-price-sub"><?=$article['location']??''?></div>
      <!-- <button class="hp-cta hp-cta-free" onclick="doDownload()">📥 Baixar Documento</button> -->
      <button class="hp-cta" style="background:var(--cream);color:var(--tx-m);border:1.5px solid var(--bdr);margin-top:2px;padding:10px" onclick="showToast('Em implementação!')"><i class="fa fa-heart"></i> Favoritar</button>
      <div class="hp-divider"></div>
      <div class="hp-features">
        <!-- <div class="hp-feat"><span class="hp-feat-ico">✅</span> Download imediato</div> -->
        <div class="hp-feat"><span class="hp-feat-ico"><i class="fa fa-book"></i></span> Formato Físico</div>
        <!-- <div class="hp-feat"><span class="hp-feat-ico">🔒</span> </div> -->
        <!-- <div class="hp-feat"><span class="hp-feat-ico">⭐</span> +5 pontos no download</div> -->
      </div>
    </div>

  </div>
</div>

<!-- ════ PAGE WRAP ════ -->
<div class="page-wrap">
  <div class="content-grid">

    <!-- LEFT MAIN -->
    <div>
      <!-- TABS -->
      <div class="tabs-bar">
        <button class="tab-btn on" onclick="setTab('desc',this)"><i class="fa fa-file"></i> Descrição</button>
        <button class="tab-btn" onclick="setTab('preview',this)"><i class="fa fa-eye"></i> Leitura</button>
        <button class="tab-btn" onclick="setTab('reviews',this)"><i class="fa fa-star"></i> Avaliações <span style="background:var(--cr-xl);color:var(--cr);font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px;margin-left:2px"><?=$review_count?></span></button>
      </div>

      <!-- ── TAB: DESCRIÇÃO ── -->
      <div class="tab-panel on" id="tab-desc">
        <div class="card" style="animation-delay:.04s">
          <div class="card-head"><div class="card-title">📋 Resumo</div></div>
          <div class="card-body">
            <div class="doc-abstract">
              <?=$article['summary']?>
            </div>
            <div style="margin-bottom:12px;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px">Palavras-chave</div>
            <div class="kw-wrap">
              <?php
                foreach ($keywords as $keyword) {
                  echo '<span class="kw-tag">'.$keyword.'</span>';
                }
              ?>
            </div>
          </div>
        </div>

      </div>

      <!-- ── TAB: PREVIEW ── -->
      <div class="tab-panel" id="tab-preview">
        <div class="card">
          <div class="card-head">
            <div class="card-title">👁️ Pré-visualização</div>
            <span class="badge bc">Primeiras páginas</span>
          </div>
          <div class="card-body">
            <div class="preview-wrap">
              <div class="preview-doc-sim">
                <div class="preview-page" id="preview-page">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── TAB: AVALIAÇÕES ── -->
      <div class="tab-panel" id="tab-reviews">
        <div class="card">
          <div class="card-head">
            <div class="card-title">⭐ Avaliações dos Utilizadores</div>
            <span style="font-size:13px;color:var(--tx-l)">
              <?=$count = ($review_count == 0) ? $review_count.' avaliação' : $review_count.' avaliações'?>
            </span>
          </div>
          <div class="card-body">
            <!-- summary -->
            <div class="rev-summary">
              <div class="rs-big">
                <div class="rs-num"><?=$review_stat['media']?></div>
                <div class="rs-stars"><?=$review_stat['stars']?></div>
                <div class="rs-count">
                  <?=$count = ($review_count == 0) ? $review_count.' avaliação' : $review_count.' avaliações'?>
                </div>
              </div>
              <!-- BREVEMENTE -->
              <!-- <div class="rs-bars">
                <div class="rs-bar-row"><span class="rs-bar-lbl">5</span><div class="rs-bar-track"><div class="rs-bar-fill" style="width:62%"></div></div><span class="rs-bar-n">20</span></div>
                <div class="rs-bar-row"><span class="rs-bar-lbl">4</span><div class="rs-bar-track"><div class="rs-bar-fill" style="width:25%"></div></div><span class="rs-bar-n">8</span></div>
                <div class="rs-bar-row"><span class="rs-bar-lbl">3</span><div class="rs-bar-track"><div class="rs-bar-fill" style="width:9%"></div></div><span class="rs-bar-n">3</span></div>
                <div class="rs-bar-row"><span class="rs-bar-lbl">2</span><div class="rs-bar-track"><div class="rs-bar-fill" style="width:3%"></div></div><span class="rs-bar-n">1</span></div>
                <div class="rs-bar-row"><span class="rs-bar-lbl">1</span><div class="rs-bar-track"><div class="rs-bar-fill" style="width:0%"></div></div><span class="rs-bar-n">0</span></div>
              </div> -->
            </div>

            <!-- review items -->
            <div id="reviews-list">
              <?php
              foreach ($reviews as $review) {
                $id = new UserId("US");
                $id->__fromString($review['user_id']);
                $user = $userRepository->findById($id);
              ?>
                <div class="rv-item" style="animation-delay:${(i*.07).toFixed(2)}s">
                  <div class="rv-top">
                    <div class="rv-ava" style="background:#6B1020">
                      <?=getInitials($user['name'])?>
                    </div>
                    <div class="rv-meta">
                      <div class="rv-name"><?=$user['name']?></div>
                      <div class="rv-inst"><?=$user['email']?></div>
                    </div>
                    <div style="text-align:right">
                      <div class="rv-score-row">
                        <span class="rv-stars">
                          <?=renderStars($review['rating'])?>
                        </span>
                        <span class="rv-score-n">
                          <?=$review['rating']?>/5
                        </span>
                      </div>
                      <div class="rv-date"><?=$review['created_at']?></div>
                    </div>
                  </div>
                  <div class="rv-comment"><?=$review['comment']?></div>
                  <div class="rv-suggestion">
                    <div class="rv-suggestion-lbl">💡 Sugestão</div>
                    <?=$review['suggest']?>
                  </div>
                </div>
              <?php
              }
              ?>
            </div>

            <!-- write review -->
            <div class="write-rv">
              <div class="wr-title">✍️ Escrever avaliação</div>
              <div class="star-picker" id="star-picker">
                <span class="sp-star" onclick="pickStar(1)">★</span>
                <span class="sp-star" onclick="pickStar(2)">★</span>
                <span class="sp-star" onclick="pickStar(3)">★</span>
                <span class="sp-star" onclick="pickStar(4)">★</span>
                <span class="sp-star" onclick="pickStar(5)">★</span>
              </div>
              <textarea class="f-ta" id="rv-comment" placeholder="Partilhe a sua opinião sobre este documento…"></textarea>
              <textarea class="f-ta" id="rv-suggest" placeholder="Sugestão para o autor (opcional)…"></textarea>
              <button class="btn btn-cr" onclick="prepareReview()" style="width:100%;justify-content:center;padding:11px">📤 Publicar avaliação</button>
            </div>
          </div>
        </div>
      </div>

    </div><!-- end left col -->

    <!-- RIGHT COLUMN -->
    <div>
      <div class="right-col-sticky">

        <!-- Detalhes técnicos -->
        <div class="card" style="animation-delay:.04s">
          <div class="card-head"><div class="card-title"><i class="fa fa-info"></i> Detalhes</div></div>
          <div class="card-body" style="padding:clamp(14px,2vw,18px)">
            <div class="badge-list">
              <div class="bl-item"><span class="bl-ico"><i class="fa fa-file"></i></span><div><div class="bl-lbl">Tipo de documento</div><div class="bl-val"><?=$article['category_id']?></div></div></div>
              <div class="bl-item"><span class="bl-ico"><i class="fa fa-folder"></i></span><div><div class="bl-lbl">Categoria</div><div class="bl-val"><?=$article['course']?></div></div></div>
              <div class="bl-item"><span class="bl-ico"><i class="fa fa-calendar"></i></span><div><div class="bl-lbl">Publicado em</div><div class="bl-val"><?=$article['created_at']?></div></div></div>
              <div class="bl-item"><span class="bl-ico"><i class="fa fa-map"></i></span><div><div class="bl-lbl">Idioma</div><div class="bl-val">Português</div></div></div>
              <div class="bl-item"><span class="bl-ico"><i class="fa fa-file"></i></span><div><div class="bl-lbl">Formato</div><div class="bl-val">Físico · <?=$article['file_size']?> Páginas</div></div></div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</div>


<script src="assets/js/util.js"></script>
<script src="assets/js/api.js"></script>
<script src="assets/js/evaluation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
/* ═══ DATA ═══ */
const docId = "<?php echo htmlspecialchars($documentId); ?>";
const readMode = <?php echo $readMode ? 'true' : 'false'; ?>;

let docDetail = null;
let docReviews = [];
async function fetchDocDetail() {
  const res = await apiRequest(`read/${docId}`);
  const data = res.data;
  if (!data.success) {
    document.getElementById('hero-inner').innerHTML = '<div style="color:red">Documento não encontrado.</div>';
    return;
  }

  docDetail = data.document;
  const document = data.document;

  return document;
}

async function renderPdfPreview() {
  const res = await apiRequest(`read/${docId}`);
  const data = res.data;
  const doc = data.document;

  if (!doc || !doc.file_path) {
    document.getElementById('preview-page').innerHTML = '<div style="color:red">Arquivo não encontrado.</div>';
    return;
  }

  // Caminho para o PDF
  const url = '../../uploads/documents/' + doc.file_path;

  // Inicializa o pdfjsLib
  if (!window.pdfjsLib && window['pdfjs-dist'] && window['pdfjs-dist'].build && window['pdfjs-dist'].build.pdf) {
    window.pdfjsLib = window['pdfjs-dist'].build.pdf;
  }
  const pdfjsLib = window.pdfjsLib || window['pdfjs-dist/build/pdf'] || window['pdfjs-dist']?.build?.pdf;

  if (!pdfjsLib) {
    document.getElementById('preview-page').innerHTML = '<div style="color:red">Erro ao carregar PDF.js</div>';
    return;
  }

  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

  try {
    const loadingTask = pdfjsLib.getDocument(url);
    const pdf = await loadingTask.promise;

    const previewDiv = document.getElementById('preview-page');
    previewDiv.innerHTML = ''; // limpa preview antigo

    const scale = 1.2;

    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
      const page = await pdf.getPage(pageNumber);
      const viewport = page.getViewport({ scale });

      const canvas = document.createElement('canvas');
      canvas.height = viewport.height;
      canvas.width = viewport.width;

      const context = canvas.getContext('2d');
      await page.render({ canvasContext: context, viewport: viewport }).promise;

      // adiciona canvas ao preview
      previewDiv.appendChild(canvas);
      
      // opcional: separador entre páginas
      const separator = document.createElement('hr');
      previewDiv.appendChild(separator);
    }
  } catch (e) {
    console.error(e);
    document.getElementById('preview-page').innerHTML = '<div style="color:red">Erro ao carregar pré-visualização do PDF.</div>';
  }
}

function openPreviewTab() {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('on'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('on'));
  document.querySelector('.tab-btn[data-tab="preview"]').classList.add('on');
  document.getElementById('tab-preview').classList.add('on');
  renderPdfPreview();
}
/* ═══ STATE ═══ */
let pickedStars=0;

/* ═══ RENDER ═══ */
function stars(r){return'★'.repeat(r)+'☆'.repeat(5-r);}

function renderReviews(){
  document.getElementById('reviews-list').innerHTML=reviews.map((rv,i)=>`
<div class="rv-item" style="animation-delay:${(i*.07).toFixed(2)}s">
  <div class="rv-top">
    <div class="rv-ava" style="background:${rv.color}">${rv.ini}</div>
    <div class="rv-meta">
      <div class="rv-name">${rv.name}</div>
      <div class="rv-inst">${rv.inst}</div>
    </div>
    <div style="text-align:right">
      <div class="rv-score-row"><span class="rv-stars">${stars(rv.score)}</span><span class="rv-score-n">${rv.score}/5</span></div>
      <div class="rv-date">${rv.date}</div>
    </div>
  </div>
  <div class="rv-comment">${rv.comment}</div>
  <div class="rv-suggestion">
    <div class="rv-suggestion-lbl">💡 Sugestão</div>
    ${rv.suggestion}
  </div>
</div>`).join('');
}

function renderRelated(){
  document.getElementById('related-docs').innerHTML=relDocs.map(d=>`
<div class="rel-doc" onclick="showToast('📄 A abrir: ${d.title.substring(0,30)}…')">
  <div class="rel-thumb">${d.ico}</div>
  <div class="rel-body">
    <div class="rel-title">${d.title}</div>
    <div class="rel-author">${d.author}</div>
    <div class="rel-price">${d.price===0?'Grátis':d.price.toLocaleString('pt-PT')+' Kz'}</div>
  </div>
</div>`).join('');
}

/* ═══ TABS ═══ */
function setTab(id,btn){
  document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('on'));
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('on'));
  btn.classList.add('on');
  document.getElementById('tab-'+id).classList.add('on');

  if (id === 'preview') renderPdfPreview();
}

/* ═══ STAR PICKER ═══ */
function pickStar(n){
  pickedStars=n;
  document.querySelectorAll('#star-picker .sp-star').forEach((s,i)=>s.classList.toggle('lit',i<n));
}

/* ═══ ACTIONS ═══ */
function doDownload(){
  showToast('📥 Download iniciado! +5 pts adicionados à sua conta');
}
function prepareReview(){
  const comment=document.getElementById('rv-comment').value.trim();
  const suggest=document.getElementById('rv-suggest').value.trim();

  if(!pickedStars){showToast('⚠️ Seleccione uma classificação de 1–5 estrelas');return;}

  if(!comment){showToast('⚠️ Escreva um comentário antes de publicar');return;}
  if (comment.length < 20) { showToast('⚠️ Escreva um comentário com pelo menos 20 caracteres'); return; }
  
  validateValue(comment, 'comentário')
  
  if (suggest) {
      if (suggest.length < 20) { 
        showToast('⚠️ Forneça uma sugestão com pelo menos 20 caracteres'); 
        return; 
      } else {
        validateValue(suggest, 'sugestão')
      }
  }
  
  const formData = new FormData();
  formData.append('document_id', docId);
  formData.append('score', pickedStars);
  formData.append('decision', "other");
  formData.append('comment', comment);
  formData.append('suggest', suggest)
  
  submitReview(formData)
}

/* ═══ TOAST ═══ */
function showToast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2800);}

// /* ═══ INIT ═══ */
// renderReviews();
// renderRelated();
</script>
</body>
</html>
