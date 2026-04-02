<?php
include_once 'includes.php';
if (!isset($_SESSION['jwt_auth'])) { header('Location: auth.php'); exit; }

$docId        = trim($_GET['id'] ?? '');
$docId = decrypt($docId);

if (!$docId)  { header('Location: my-documents.php'); exit; }

$jwt          = $_SESSION['jwt_auth'];
$userId       = $_SESSION['user_uuid'] ?? '';
$userName     = $_SESSION['user_name']  ?? 'Usuário';
$userEmail    = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));


/* ── FETCH DOCUMENT ── */
$stmt = $db->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
$stmt->execute([$docId, $userId]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) { header('Location: my-documents.php?err=notfound'); exit; }

/* ── DECODE JSON FIELDS ── */
$authorsArr  = json_decode($doc['authors']  ?? '[]', true) ?: [];
$keywordsArr = json_decode($doc['keywords'] ?? '[]', true) ?: [];
$authorsStr  = explode(', ', $authorsArr);
$keywordsStr = explode(', ', $keywordsArr);
$authorsStr = arrayForString($authorsStr);
$keywordsStr = arrayForString($keywordsStr);

$bookMode  = $doc['pub_mode']  ?? '';   // 'fisico' | 'digital'
$price     = $doc['price']     ?? '';
$location  = $doc['location']  ?? '';


/* parse contactos extras — guardados no campo location como JSON ou em campos separados */
$stmt = $db->prepare("SELECT * FROM info_contact WHERE document_id = ?");
$stmt->execute([$docId]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

$phone         = $info['phone']         ?? '';
$whatsapp      = $info['whatsapp']      ?? '';
$emailContact  = $info['email_contact'] ?? '';

/* categories for doc type grid */
$docTypes = ['Dissertação','Monografia','Artigo Científico','Tese Doutoramento','Relatório','Apresentação','TCC','Livro','Outro'];
$currentType = $doc['category_id'] ?? '';

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub – Editar Documento</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<style>
/* Reutiliza exatamente os mesmos tokens e estilos de upload-document.php */
:root{
  --crimson:#6b1020;--crimson-dark:#3d0912;--crimson-light:#8c1a2e;
  --crimson-xlight:rgba(107,16,32,.06);--crimson-border:rgba(107,16,32,.18);
  --gold:#c9a84c;--gold-light:#e5c97e;--gold-bg:rgba(201,168,76,.10);
  --cream:#f9f5ee;--warm:#fef9f3;--border:rgba(107,16,32,.09);
  --text-dark:#1a0a0e;--text-mid:#4a2f35;--text-light:#9a7a82;
  --success:#2d6a4f;--success-bg:rgba(45,106,79,.10);
  --warn:#b07a1a;--warn-bg:rgba(176,122,26,.10);
  --danger:#b53030;--danger-bg:rgba(181,48,48,.10);
  --info:#1a5080;--info-bg:rgba(26,80,128,.10);
  --topbar-h:44px;--content-topbar:66px;
  --shadow-xs:0 1px 4px rgba(61,9,18,.06);--shadow-sm:0 2px 12px rgba(61,9,18,.09);
  --shadow-md:0 8px 32px rgba(61,9,18,.12);--shadow-lg:0 20px 60px rgba(61,9,18,.18);
  --radius-sm:8px;--radius-md:12px;--radius-lg:16px;--radius-xl:22px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--cream);color:var(--text-dark);overflow-x:hidden;-webkit-font-smoothing:antialiased}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--crimson-light);border-radius:3px}
input,select,button,textarea{font-family:inherit}a{text-decoration:none;color:inherit}
/* topnav */
.topnav{position:fixed;top:0;left:0;right:0;z-index:9999;height:var(--topbar-h);background:var(--crimson-dark);display:flex;align-items:center;justify-content:space-between;padding:0 20px;border-bottom:1px solid rgba(201,168,76,.15)}
.topnav-brand{font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:#fff}.topnav-brand span{color:var(--gold-light)}
.topnav-center{display:flex;gap:4px}
.topnav-btn{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);color:rgba(255,255,255,.65);padding:5px 18px;border-radius:100px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:all .2s}
.topnav-btn.active{background:var(--gold);color:var(--crimson-dark);border-color:var(--gold)}
.topnav-btn:hover:not(.active){background:rgba(255,255,255,.16);color:#fff}
.topnav-right{display:flex;align-items:center;gap:8px}
.topnav-avatar{width:30px;height:30px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:var(--crimson-dark);border:2px solid rgba(255,255,255,.2)}
/* layout */
.layout{display:flex;min-height:100vh;padding-top:var(--topbar-h)}
.main{flex:1;overflow-y:auto;background:var(--cream);min-width:0}
.content-topbar{background:#fff;border-bottom:1px solid var(--border);padding:0 28px;height:var(--content-topbar);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;box-shadow:var(--shadow-xs)}
.content-topbar-left{display:flex;align-items:center;gap:12px}
.topbar-titles{display:flex;flex-direction:column}
.breadcrumb-top{font-size:11px;color:var(--text-light)}.breadcrumb-top a{color:var(--crimson);font-weight:600;text-decoration:none}.breadcrumb-top strong{color:var(--crimson);font-weight:600}
.page-title{font-family:'Cormorant Garamond',serif;font-size:19px;font-weight:700;color:var(--crimson-dark)}
.topbar-actions{display:flex;align-items:center;gap:8px}
/* buttons */
.btn{display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:var(--radius-md);font-size:13.5px;font-weight:700;cursor:pointer;border:none;font-family:inherit;transition:all .2s;text-decoration:none;white-space:nowrap}
.btn-primary{background:linear-gradient(135deg,var(--crimson-light),var(--crimson-dark));color:#fff;box-shadow:0 4px 16px rgba(107,16,32,.28)}.btn-primary:hover{transform:translateY(-1px)}
.btn-ghost{background:#fff;color:var(--text-mid);border:1.5px solid var(--border)}.btn-ghost:hover{background:var(--cream);color:var(--crimson);border-color:var(--crimson-border)}
.btn-sm{padding:7px 14px;font-size:12px;border-radius:var(--radius-sm)}.btn-lg{padding:14px 28px;font-size:15px}
/* page */
.page-content{padding:28px;max-width:1180px}
.upload-layout{display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start}
/* edit banner */
.edit-banner{background:linear-gradient(135deg,var(--info-bg),rgba(26,80,128,.05));border:1.5px solid rgba(26,80,128,.22);border-radius:var(--radius-lg);padding:14px 20px;margin-bottom:22px;display:flex;align-items:center;gap:14px}
.eb-ico{font-size:28px;flex-shrink:0}
.eb-title{font-size:14px;font-weight:700;color:var(--info);margin-bottom:3px}
.eb-sub{font-size:12px;color:var(--text-light)}
.eb-status{margin-left:auto;flex-shrink:0}
.status-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:100px;font-size:11px;font-weight:700}
.sp-pen{background:var(--warn-bg);color:var(--warn);border:1px solid rgba(176,122,26,.22)}
.sp-pub{background:var(--success-bg);color:var(--success);border:1px solid rgba(45,106,79,.22)}
.sp-rej{background:var(--danger-bg);color:var(--danger);border:1px solid rgba(181,48,48,.22)}
/* cards */
.card{background:#fff;border-radius:var(--radius-xl);border:1px solid var(--border);box-shadow:var(--shadow-xs);overflow:hidden;margin-bottom:20px}
.card:last-child{margin-bottom:0}
.card-header{padding:18px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(to right,rgba(107,16,32,.02),transparent)}
.card-step{width:26px;height:26px;border-radius:50%;background:var(--crimson);color:#fff;font-size:12px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;margin-right:10px;flex-shrink:0}
.card-title-wrap{display:flex;align-items:center}
.card-title{font-family:'Cormorant Garamond',serif;font-size:16px;font-weight:700;color:var(--crimson-dark)}
.card-sub{font-size:11.5px;color:var(--text-light);margin-top:2px;padding-left:36px}
.card-body{padding:24px}
/* badge */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:.3px}
.badge-crimson{background:var(--crimson-xlight);color:var(--crimson);border:1px solid var(--crimson-border)}
.badge-orange{background:var(--warn-bg);color:var(--warn)}
.badge-info{background:var(--info-bg);color:var(--info)}
/* book mode */
.bm-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.bm-option{border:2px solid var(--border);border-radius:var(--radius-lg);padding:20px 18px;cursor:pointer;transition:all .22s;background:#fff;display:flex;align-items:flex-start;gap:14px;position:relative}
.bm-option:hover{border-color:var(--crimson-border)}
.bm-option.selected{border-color:var(--crimson);background:var(--crimson-xlight);box-shadow:0 0 0 3px rgba(107,16,32,.07)}
.bm-icon{width:52px;height:52px;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:26px;flex-shrink:0;background:var(--cream)}
.bm-option.selected .bm-icon{background:#fff}
.bm-title{font-size:15px;font-weight:700;color:var(--text-dark);margin-bottom:4px}.bm-option.selected .bm-title{color:var(--crimson)}
.bm-desc{font-size:12px;color:var(--text-light);line-height:1.5}
.bm-check{position:absolute;top:12px;right:14px;width:22px;height:22px;border-radius:50%;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:11px;transition:all .2s}
.bm-option.selected .bm-check{background:var(--crimson);border-color:var(--crimson);color:#fff}
/* form */
.form-field{margin-bottom:18px}.form-field:last-child{margin-bottom:0}
.form-field label{display:block;font-size:11.5px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.9px;margin-bottom:7px}
.form-field label .req{color:var(--crimson);margin-left:2px}
input[type=text],input[type=number],input[type=email],input[type=tel],input[type=date],textarea,select{width:100%;padding:11px 15px;border:1.5px solid var(--border);border-radius:var(--radius-md);font-size:13.5px;font-family:inherit;color:var(--text-dark);background:#fff;outline:none;transition:all .2s}
input::placeholder,textarea::placeholder{color:var(--text-light)}
input:focus,textarea:focus{border-color:var(--crimson);box-shadow:0 0 0 3px rgba(107,16,32,.07)}
textarea{resize:vertical;min-height:110px;line-height:1.65}
.input-suffix{display:flex}.input-suffix input{border-radius:var(--radius-md) 0 0 var(--radius-md);border-right:none}
.input-suffix .suffix-tag{background:var(--cream);border:1.5px solid var(--border);border-left:none;border-radius:0 var(--radius-md) var(--radius-md) 0;padding:0 16px;display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-mid);white-space:nowrap}
.field-hint{font-size:11.5px;color:var(--text-light);margin-top:7px;display:flex;align-items:flex-start;gap:5px;line-height:1.5}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.contact-section-title{font-size:12px;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.9px;margin-bottom:14px;display:flex;align-items:center;gap:7px}
.contact-section-title::before{content:'';display:block;width:18px;height:2px;background:var(--crimson);border-radius:1px}
/* cover */
.cover-upload-layout{display:flex;gap:20px;align-items:flex-start}
.cover-drop{flex-shrink:0;width:140px;height:196px;border:2px dashed rgba(107,16,32,.22);border-radius:var(--radius-lg);background:var(--cream);cursor:pointer;transition:all .25s;position:relative;overflow:hidden;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:6px}
.cover-drop:hover,.cover-drop.drag-over{border-color:var(--crimson);background:rgba(107,16,32,.04)}
.cover-drop input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.cover-drop-icon{font-size:32px;opacity:.4}
.cover-drop-text{font-size:11px;font-weight:600;color:var(--text-light);line-height:1.4;padding:0 10px}
.cover-preview-wrap{flex-shrink:0;width:140px;height:196px;border-radius:var(--radius-lg);overflow:hidden;border:2px solid var(--crimson-border);position:relative;display:none;box-shadow:var(--shadow-sm)}
.cover-preview-wrap.visible{display:block}
.cover-preview-wrap img{width:100%;height:100%;object-fit:cover;display:block}
.cover-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(61,9,18,.6),transparent 50%);display:flex;align-items:flex-end;padding:10px;opacity:0;transition:opacity .2s}
.cover-preview-wrap:hover .cover-overlay{opacity:1}
.cover-remove-btn{background:rgba(255,255,255,.9);border:none;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:700;color:var(--danger);cursor:pointer;width:100%;font-family:inherit}
.cover-info{flex:1}.cover-info-title{font-size:13px;font-weight:700;color:var(--text-dark);margin-bottom:6px}
.cover-info p{font-size:12px;color:var(--text-light);line-height:1.6;margin-bottom:10px}
.cover-tips{display:flex;flex-direction:column;gap:5px}.cover-tip{display:flex;align-items:center;gap:7px;font-size:11.5px;color:var(--text-mid)}
.cover-tip::before{content:'';width:5px;height:5px;border-radius:50%;background:var(--gold);flex-shrink:0}
/* file drop */
.drop-zone{border:2px dashed rgba(107,16,32,.22);border-radius:var(--radius-lg);padding:36px 24px;text-align:center;cursor:pointer;transition:all .25s;background:var(--cream);position:relative;overflow:hidden}
.drop-zone:hover,.drop-zone.drag-over{border-color:var(--crimson);background:rgba(107,16,32,.03)}
.drop-zone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.dz-icon{font-size:48px;margin-bottom:12px;display:block;line-height:1}
.dz-title{font-size:15px;font-weight:700;color:var(--text-dark);margin-bottom:5px}
.dz-subtitle{font-size:13px;color:var(--text-light)}.dz-subtitle strong{color:var(--crimson);font-weight:700}
.dz-formats{display:flex;justify-content:center;gap:8px;margin-top:14px;flex-wrap:wrap}
.dz-format{background:#fff;border:1px solid var(--border);color:var(--text-mid);padding:4px 12px;border-radius:100px;font-size:11px;font-weight:700}
.file-preview{display:none;align-items:center;gap:14px;background:var(--success-bg);border:1.5px solid rgba(45,106,79,.22);border-radius:var(--radius-md);padding:14px 18px;margin-top:12px}
.file-preview.visible{display:flex;animation:fadeUp .3s ease}
.file-icon-big{font-size:30px;flex-shrink:0}.file-info .file-name{font-size:13.5px;font-weight:700;color:var(--success)}.file-info .file-size{font-size:11.5px;color:var(--text-light);margin-top:2px}
.file-remove{margin-left:auto;background:transparent;border:none;color:var(--danger);font-size:20px;cursor:pointer;padding:4px 6px;border-radius:6px;line-height:1}
.file-remove:hover{background:var(--danger-bg)}
.upload-progress{display:none;margin-top:12px}.upload-progress.visible{display:block}
.prog-bar-wrap{height:6px;background:var(--border);border-radius:6px;overflow:hidden}
.prog-fill{height:100%;width:0%;background:linear-gradient(90deg,var(--crimson),var(--crimson-light));border-radius:6px;transition:width .4s}
.prog-text{display:flex;justify-content:space-between;font-size:11px;color:var(--text-light);margin-top:5px}
/* doc type */
.doc-type-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.doc-type-option{border:2px solid var(--border);border-radius:var(--radius-md);padding:12px 8px;cursor:pointer;text-align:center;transition:all .2s;background:#fff}
.doc-type-option:hover{border-color:var(--crimson-border);background:var(--crimson-xlight)}
.doc-type-option.selected{border-color:var(--crimson);background:var(--crimson-xlight)}
.doc-type-label{font-size:11px;font-weight:700;color:var(--text-mid);line-height:1.3}.doc-type-option.selected .doc-type-label{color:var(--crimson)}
/* info box */
.info-box{background:var(--info-bg);border:1px solid rgba(26,80,128,.18);border-radius:var(--radius-md);padding:14px 16px;margin-bottom:16px}
.info-box-title{font-size:12px;font-weight:700;color:var(--info);margin-bottom:5px;display:flex;align-items:center;gap:6px}
.info-box p{font-size:12px;color:var(--text-mid);line-height:1.6}
/* current file box */
.current-file-box{background:rgba(45,106,79,.07);border:1.5px solid rgba(45,106,79,.18);border-radius:var(--radius-md);padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px}
.cf-ico{font-size:24px;flex-shrink:0}
.cf-info{flex:1;min-width:0}.cf-name{font-size:13px;font-weight:700;color:var(--success)}.cf-sub{font-size:11px;color:var(--text-light);margin-top:2px}
/* summary */
.summary-card{background:#fff;border-radius:var(--radius-xl);border:1px solid var(--border);box-shadow:var(--shadow-sm);overflow:hidden;position:sticky;top:calc(var(--content-topbar) + 14px)}
.summary-head{background:linear-gradient(135deg,var(--crimson-dark),var(--crimson-light));padding:20px 22px;position:relative;overflow:hidden}
.summary-head::after{content:'';position:absolute;right:-20px;top:-20px;width:80px;height:80px;border-radius:50%;background:rgba(201,168,76,.12)}
.summary-head h3{font-family:'Cormorant Garamond',serif;font-size:17px;font-weight:700;color:#fff;position:relative}
.summary-head p{font-size:11.5px;color:rgba(255,255,255,.55);margin-top:3px;position:relative}
.summary-body{padding:16px 20px}
.summary-row{display:flex;justify-content:space-between;align-items:flex-start;padding:9px 0;border-bottom:1px solid rgba(107,16,32,.05);font-size:12.5px}
.summary-row:last-child{border-bottom:none}
.s-label{color:var(--text-light);flex-shrink:0;margin-right:12px}
.s-val{font-weight:600;color:var(--text-dark);text-align:right}.s-val.empty{color:var(--text-light);font-weight:400;font-style:italic}.s-val.crimson{color:var(--crimson)}
.summary-actions{padding:16px 20px;display:flex;flex-direction:column;gap:8px}
.summary-file-preview{display:none;padding:10px 20px;border-top:1px solid var(--border);align-items:center;gap:10px}
.summary-file-preview.visible{display:flex}
.sfp-img{width:36px;height:50px;border-radius:5px;object-fit:cover;border:1px solid var(--border)}
.sfp-no-img{width:36px;height:50px;border-radius:5px;background:var(--cream);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.sfp-info .sfp-name{font-size:12px;font-weight:700;color:var(--success)}.sfp-info .sfp-size{font-size:11px;color:var(--text-light);margin-top:1px}
/* toast */
.toast{position:fixed;bottom:24px;left:50%;z-index:99999;transform:translateX(-50%) translateY(80px);background:var(--crimson-dark);color:#fff;padding:13px 22px;border-radius:var(--radius-lg);font-size:13.5px;font-weight:500;box-shadow:var(--shadow-lg);opacity:0;transition:all .35s cubic-bezier(.22,1,.36,1);display:flex;align-items:center;gap:10px;border:1px solid rgba(201,168,76,.28);white-space:nowrap;pointer-events:none}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.anim{animation:fadeUp .4s ease both}
.anim-d1{animation-delay:.05s}.anim-d2{animation-delay:.10s}.anim-d3{animation-delay:.16s}.anim-d4{animation-delay:.22s}.anim-d5{animation-delay:.28s}
@media(max-width:1100px){.upload-layout{grid-template-columns:1fr 320px}}
@media(max-width:960px){.upload-layout{grid-template-columns:1fr}.summary-card{position:relative;top:0}.doc-type-grid{grid-template-columns:repeat(4,1fr)}}
@media(max-width:768px){.page-content{padding:16px}.content-topbar{padding:0 16px}.two-col,.contact-grid,.bm-grid{grid-template-columns:1fr}.topnav-center,.topnav-name{display:none}.cover-upload-layout{flex-direction:column}.cover-drop,.cover-preview-wrap{width:100%;height:180px}}
@media(max-width:480px){.doc-type-grid{grid-template-columns:repeat(2,1fr)}.card-body{padding:16px}.card-header{padding:14px 16px}}
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- TOPNAV -->
<nav class="topnav">
  <div class="topnav-brand">PETRO<span>PUB</span></div>
  <div class="topnav-center">
    <a href="my-documents.php" class="topnav-btn">Meus Documentos</a>
    <a href="upload-document.php" class="topnav-btn">Novo</a>
    <a href="library.php" class="topnav-btn">Biblioteca</a>
  </div>
  <div class="topnav-right">
    <div class="topnav-avatar"><?= h($userInitials) ?></div>
    <span style="font-size:12.5px;font-weight:600;color:rgba(255,255,255,.8)"><?= h($userName) ?></span>
  </div>
</nav>

<div class="layout">
  <div id="overlay" onclick="closeSidebar()" style="position:fixed;inset:0;background:rgba(0,0,0,.52);display:none;z-index:998;backdrop-filter:blur(2px)"></div>

  <div class="main">
    <!-- CONTENT TOPBAR -->
    <div class="content-topbar">
      <div class="content-topbar-left">
        <div class="topbar-titles">
          <div class="breadcrumb-top"><a href="my-documents.php">← Meus Documentos</a> <strong>/ Editar</strong></div>
          <div class="page-title">Editar Documento</div>
        </div>
      </div>
      <div class="topbar-actions">
        <a href="my-documents.php" class="btn btn-ghost btn-sm">✕ Cancelar</a>
        <button class="btn btn-primary btn-sm" id="save-btn" onclick="updateDocument('<?= h($docId) ?>')">💾 Guardar alterações</button>
      </div>
    </div>

    <div class="page-content">

      <!-- EDIT BANNER -->
      <div class="edit-banner anim">
        <div class="eb-ico"><i class="fa fa-pencil"></i></div>
        <div>
          <div class="eb-title">Edição de documento</div>
          <div class="eb-sub">Documento: <strong><?= h(mb_substr($doc['title'], 0, 60)) ?><?= mb_strlen($doc['title']) > 60 ? '…' : '' ?></strong></div>
        </div>
        <div class="eb-status">
          <?php
          $statusMap = [
            'PENDENTE'   => ['sp-pen','Pendente'],
            'APROVADO'   => ['sp-pub','Aprovado'],
            'PUBLICADO'  => ['sp-pub','Publicado'],
            'REJEITADO'  => ['sp-rej','Rejeitado'],
          ];
          [$scls,$slbl] = $statusMap[$doc['status'] ?? ''] ?? ['sp-pen', $doc['status'] ?? '—'];
          ?>
          <span class="status-pill <?= $scls ?>"><?= $slbl ?></span>
        </div>
      </div>

      <div class="upload-layout">
        <!-- ══ LEFT ══ -->
        <div>

          <!-- ① TIPO DE DOCUMENTO -->
          <div class="card anim anim-d1">
            <div class="card-header">
              <div class="card-title-wrap">
                <span class="card-step">①</span>
                <div>
                  <div class="card-title">Tipo de Documento</div>
                  <div class="card-sub">Físico (venda presencial) ou Digital (leitura no portal)</div>
                </div>
              </div>
              <span class="badge badge-crimson">Obrigatório</span>
            </div>
            <div class="card-body">
              <div class="bm-grid">
                <div class="bm-option <?= $bookMode==='fisico'?'selected':'' ?>" id="bm-fisico" onclick="selectBookMode('fisico')">
                  <div class="bm-icon"></div>
                  <div><div class="bm-title">Livro Físico</div><div class="bm-desc">Venda presencial. Define preço, localização e contactos.</div></div>
                  <div class="bm-check" id="bm-fisico-check"><?= $bookMode==='fisico'?'✓':'' ?></div>
                </div>
                <div class="bm-option <?= $bookMode==='digital'?'selected':'' ?>" id="bm-digital" onclick="selectBookMode('digital')">
                  <div class="bm-icon"></div>
                  <div><div class="bm-title">Livro Digital</div><div class="bm-desc">Leitura directa no portal. PDF obrigatório. Sem preço.</div></div>
                  <div class="bm-check" id="bm-digital-check"><?= $bookMode==='digital'?'✓':'' ?></div>
                </div>
              </div>
            </div>
          </div>

          <!-- ② CAPA -->
          <div class="card anim anim-d2">
            <div class="card-header">
              <div class="card-title-wrap">
                <span class="card-step">②</span>
                <div>
                  <div class="card-title">Capa do Documento</div>
                  <div class="card-sub">Substituir a capa actual (opcional)</div>
                </div>
              </div>
              <span class="badge badge-orange">Opcional</span>
            </div>
            <div class="card-body">
              <?php if (!empty($doc['file_cover'])): ?>
              <div class="current-file-box" style="margin-bottom:16px">
                <img src="../../uploads/documents/cover/<?= h($doc['file_cover']) ?>"
                     style="width:48px;height:64px;object-fit:cover;border-radius:6px;border:1px solid var(--border)">
                <div class="cf-info">
                  <div class="cf-name">Capa actual</div>
                  <div class="cf-sub">Seleccione uma nova imagem para substituir</div>
                </div>
              </div>
              <?php endif; ?>
              <div class="cover-upload-layout">
                <div class="cover-drop" id="cover-drop" ondragover="coverDragOver(event)" ondragleave="coverDragLeave(event)" ondrop="coverDrop(event)">
                  <input type="file" id="cover-input" accept="image/jpeg,image/png,image/webp" onchange="coverFileSelect(event)">
                  <span class="cover-drop-icon"><i class="fa fa-image"></i></span>
                  <span class="cover-drop-text">Nova capa (opcional)</span>
                  <span style="font-size:10px;font-weight:700;background:#fff;border:1px solid var(--border);color:var(--text-mid);padding:2px 8px;border-radius:100px">JPG · PNG · WEBP</span>
                </div>
                <div class="cover-preview-wrap" id="cover-preview-wrap">
                  <img id="cover-preview-img" src="" alt="">
                  <div class="cover-overlay"><button class="cover-remove-btn" onclick="removeCover(event)">✕ Remover</button></div>
                </div>
                <div class="cover-info">
                  <div class="cover-info-title">Substituir Capa</div>
                  <p>Seleccione uma nova imagem para substituir a capa actual. Deixe em branco para manter a actual.</p>
                  <div class="cover-tips">
                    <div class="cover-tip">Proporção 2:3 (retrato)</div>
                    <div class="cover-tip">Máximo 5 MB</div>
                    <div class="cover-tip">JPG, PNG, WEBP</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ③ FICHEIRO PDF — só para digital -->
          <div class="card anim anim-d3" id="sec-file" style="display:<?= $bookMode==='digital'?'block':'none' ?>">
            <div class="card-header">
              <div class="card-title-wrap">
                <span class="card-step">③</span>
                <div>
                  <div class="card-title">Ficheiro PDF</div>
                  <div class="card-sub">Substituir o ficheiro actual (opcional)</div>
                </div>
              </div>
              <span class="badge badge-info">Digital</span>
            </div>
            <div class="card-body">
              <?php if (!empty($doc['file_path'])): ?>
              <div class="current-file-box">
                <div class="cf-ico"><i class="fa fa-file"></i></div>
                <div class="cf-info">
                  <div class="cf-name">Ficheiro actual: <?= h(basename($doc['file_path'])) ?></div>
                  <div class="cf-sub"><?= h($doc['file_size'] ?? '—') ?> · Seleccione um novo para substituir</div>
                </div>
              </div>
              <?php endif; ?>
              <div class="drop-zone" id="drop-zone" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
                <input type="file" id="file-input" accept=".pdf,.docx,.doc" onchange="handleFileSelectEvt(event)">
                <span class="dz-icon"><i class="fa fa-file"></i></span>
                <div class="dz-title">Novo ficheiro PDF (opcional)</div>
                <div class="dz-subtitle">Substitui o ficheiro actual · máximo <strong>50 MB</strong></div>
                <div class="dz-formats"><span class="dz-format">PDF</span><span class="dz-format">DOC</span><span class="dz-format">DOCX</span></div>
              </div>
              <div class="file-preview" id="file-preview">
                <span class="file-icon-big" id="file-icon-big"><i class="fa fa-file"></i></span>
                <div class="file-info"><div class="file-name" id="file-name">—</div><div class="file-size" id="file-size">—</div></div>
                <button class="file-remove" onclick="removeFile()">✕</button>
              </div>
              <div class="upload-progress" id="upload-progress">
                <div class="prog-bar-wrap"><div class="prog-fill" id="prog-fill"></div></div>
                <div class="prog-text"><span id="prog-label">A processar…</span><span id="prog-pct">0%</span></div>
              </div>
            </div>
          </div>

          <!-- ④ INFORMAÇÕES -->
          <div class="card anim anim-d4">
            <div class="card-header">
              <div class="card-title-wrap">
                <span class="card-step">④</span>
                <div>
                  <div class="card-title">Informações do Documento</div>
                  <div class="card-sub">Edite os metadados do documento</div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="form-field">
                <label>Título <span class="req">*</span></label>
                <input type="text" id="doc-title" value="<?= h($doc['title']) ?>" oninput="updateSummary()">
              </div>
              <div class="form-field">
                <label>Autores <span class="req">*</span> <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:11px">(separar por vírgula)</span></label>
                <input type="text" id="doc-authors" value="<?= h($authorsStr) ?>">
              </div>
              <div class="two-col">
                <div class="form-field">
                  <label>Data de criação</label>
                  <input type="date" id="doc-date" value="<?= h($doc['created_at'] ?? '') ?>" oninput="updateSummary()">
                </div>
                <div class="form-field">
                  <label>Curso / Área <span class="req">*</span></label>
                  <input type="text" id="doc-inst" value="<?= h($doc['course'] ?? '') ?>" oninput="updateSummary()">
                </div>
              </div>
              <div class="form-field">
                <label>Orientador(a)</label>
                <input type="text" id="doc-advisor" value="<?= h($doc['advisor'] ?? '') ?>">
              </div>
              <div class="form-field">
                <label>Resumo / Abstract <span class="req">*</span></label>
                <textarea id="doc-abstract"><?= h($doc['summary'] ?? '') ?></textarea>
              </div>
              <div class="form-field">
                <label>Palavras-chave <span class="req">*</span> <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:11px">(separar por vírgula)</span></label>
                <input type="text" id="doc-tags" value="<?= h($keywordsStr) ?>">
              </div>
              <div class="form-field">
                <label>Tipo de Documento</label>
                <div class="doc-type-grid">
                  <?php foreach($docTypes as $t): ?>
                  <div class="doc-type-option <?= $currentType===$t?'selected':'' ?>"
                       onclick="selectDocType(this,'<?= h($t) ?>')">
                    <div class="doc-type-label"><?= h($t) ?></div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- ⑤ PREÇO & LOCALIZAÇÃO — físico -->
          <div class="card anim anim-d5" id="sec-price" style="display:<?= $bookMode==='fisico'?'block':'none' ?>">
            <div class="card-header">
              <div class="card-title-wrap">
                <span class="card-step">⑤</span>
                <div>
                  <div class="card-title">Preço e Localização</div>
                  <div class="card-sub">Valor de venda e local de retirada do livro físico</div>
                </div>
              </div>
              <span class="badge badge-orange">Físico</span>
            </div>
            <div class="card-body">
              <div class="two-col">
                <div class="form-field">
                  <label>Preço de venda <span class="req">*</span></label>
                  <div class="input-suffix">
                    <input type="number" id="doc-price" value="<?= h($price) ?>" min="0" step="50" oninput="updateSummary()">
                    <div class="suffix-tag">Kz</div>
                  </div>
                </div>
                <div class="form-field">
                  <label>Localização <span class="req">*</span></label>
                  <div class="input-suffix">
                    <input type="text" id="doc-localization" value="<?= h($location) ?>">
                    <div class="suffix-tag"><i class="fa fa-map"></i></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ⑥ CONTACTOS — físico -->
          <div class="card anim" style="animation-delay:.34s;display:<?= $bookMode==='fisico'?'block':'none' ?>" id="sec-contact">
            <div class="card-header">
              <div class="card-title-wrap">
                <span class="card-step">⑥</span>
                <div>
                  <div class="card-title">Informações de Contacto</div>
                  <div class="card-sub">Contactos para compra do livro físico</div>
                </div>
              </div>
              <span class="badge badge-orange">Físico</span>
            </div>
            <div class="card-body">
              <div class="contact-section-title">Meios de contacto</div>
              <div class="contact-grid">
                <div class="form-field">
                  <label>Telefone</label>
                  <div class="input-suffix">
                    <input type="tel" id="doc-phone" value="<?= h($phone) ?>" placeholder="+244 900 000 000">
                    <div class="suffix-tag"><i class="fa fa-phone"></i></div>
                  </div>
                </div>
                <div class="form-field">
                  <label>WhatsApp</label>
                  <div class="input-suffix">
                    <input type="tel" id="doc-whatsapp" value="<?= h($whatsapp) ?>" placeholder="+244 900 000 000">
                    <div class="suffix-tag"><i class="fa fa-whatsapp" style="color:#25d366"></i></div>
                  </div>
                </div>
                <div class="form-field" style="grid-column:1/-1">
                  <label>E-mail de contacto</label>
                  <div class="input-suffix">
                    <input type="email" id="doc-email-contact" value="<?= h($emailContact) ?>" placeholder="contacto@exemplo.com">
                    <div class="suffix-tag"><i class="fa fa-envelope"></i></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <!-- /left -->

        <!-- ══ RIGHT — SUMMARY ══ -->
        <div>
          <div class="summary-card">
            <div class="summary-head">
              <h3>Resumo das Alterações</h3>
              <p>Verifique antes de guardar</p>
            </div>
            <div class="summary-file-preview" id="sfp-wrap">
              <div class="sfp-no-img" id="sfp-no-img"><i class="fa fa-file"></i></div>
              <img class="sfp-img" id="sfp-img" src="" alt="" style="display:none">
              <div class="sfp-info"><div class="sfp-name" id="sfp-name">—</div><div class="sfp-size" id="sfp-size">—</div></div>
            </div>
            <div class="summary-body">
              <div class="summary-row"><span class="s-label">Modo</span><span class="s-val <?= $bookMode?'':'empty' ?>" id="sum-mode"><?= $bookMode==='fisico'?'📦 Físico':($bookMode==='digital'?'💻 Digital':'Não definido') ?></span></div>
              <div class="summary-row"><span class="s-label">Título</span><span class="s-val <?= $doc['title']?'':'empty' ?>" id="sum-title"><?= h(mb_substr($doc['title'],0,40)) ?><?= mb_strlen($doc['title'])>40?'…':'' ?></span></div>
              <div class="summary-row"><span class="s-label">Tipo</span><span class="s-val <?= $currentType?'':'empty' ?>" id="sum-type"><?= h($currentType ?: 'Não definido') ?></span></div>
              <div class="summary-row"><span class="s-label">Curso</span><span class="s-val <?= $doc['course']?'':'empty' ?>" id="sum-inst"><?= h($doc['course'] ?? '—') ?></span></div>
              <?php if ($bookMode === 'fisico'): ?>
              <div class="summary-row"><span class="s-label">Preço</span><span class="s-val crimson" id="sum-price"><?= $price ? number_format((float)$price,0,'.','.') . ' Kz' : '—' ?></span></div>
              <?php else: ?>
              <div class="summary-row" style="display:none"><span class="s-label">Preço</span><span class="s-val crimson" id="sum-price">—</span></div>
              <?php endif; ?>
              <div class="summary-row"><span class="s-label">Estado</span><span class="s-val"><?= h($doc['status'] ?? '—') ?></span></div>
            </div>
            <div class="summary-actions">
              <button class="btn btn-primary btn-lg" id="save-btn2" onclick="updateDocument('<?= h($docId) ?>')" style="width:100%;justify-content:center">
                💾 Guardar alterações
              </button>
              <a href="my-documents.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center">
                ✕ Cancelar
              </a>
              <a href="detail-doc.php?id=<?= h($docId) ?>" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center">
                👁 Ver documento
              </a>
            </div>
          </div>
        </div>
        <!-- /right -->

      </div>
    </div>
  </div>
</div>

<script src="assets/js/api.js"></script>
<script src="assets/js/upload.js"></script>
<script>
// ── PRÉ-INICIALIZAR bookMode e selectedDocType a partir do PHP ──
bookMode        = '<?= h($bookMode) ?>';
selectedDocType = '<?= h($currentType) ?>';

// Aplicar visualmente o estado actual
if (bookMode) {
  document.getElementById('bm-' + bookMode)?.classList.add('selected');
  const chk = document.getElementById('bm-' + bookMode + '-check');
  if (chk) chk.textContent = '✓';
}

function toggleSidebar() {
  document.getElementById('sidebar')?.classList.toggle('open');
  document.getElementById('overlay')?.classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar')?.classList.remove('open');
  document.getElementById('overlay')?.classList.remove('open');
}

let toastTimer;
function showToast(msg, duration=3500) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), duration);
}

// Override selectBookMode to also sync save-btn2 and sum-price visibility in edit mode
const _origSBM = selectBookMode;
window.selectBookMode = function(mode) {
  _origSBM(mode);
  // sync price row
  const prRow = document.querySelector('.summary-row:has(#sum-price)');
  if (prRow) prRow.style.display = (mode === 'fisico') ? 'flex' : 'none';
};

// Update summary init
updateSummary();
</script>
</body>
</html>
