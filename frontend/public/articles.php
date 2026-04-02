<?php
require_once 'includes.php';

/* ── API: quickDecide endpoint ── */
if (isset($_GET['api']) && $_GET['api'] === 'decide') {
    header('Content-Type: application/json; charset=utf-8');
    $id  = trim($_POST['id']  ?? '');
    $dec = trim($_POST['dec'] ?? '');
    if (!$id || !in_array($dec, ['approve','reject'])) {
        echo json_encode(['ok'=>false,'msg'=>'Parâmetros inválidos']); exit;
    }
    if ($dec === 'approve') {
        $newStatus = 'APROVADO';
        $db->prepare("UPDATE documents SET status=? WHERE id=?")->execute([$newStatus, $id]);
        // notify author
        $doc = $db->prepare("SELECT title,user_id FROM documents WHERE id=?");
        $doc->execute([$id]); $docRow = $doc->fetch(PDO::FETCH_ASSOC);
        if ($docRow && $docRow['user_id']) {
            try {
                $message = "O seu documento ".$docRow['title']." foi aprovado pelo administrador";
                $db->prepare("INSERT INTO notifications (user_id,role,type,title,message,icon) VALUES (?,?,?,?,?,?)")
                   ->execute([$docRow['user_id'],'user','success','Documento Aprovado!', $message,'✅']);
            } catch(Exception $e) {}
        }
        echo json_encode(['ok'=>true,'msg'=>'✅ Documento aprovado e autor notificado!','status'=>$newStatus]);
    } else {
        $newStatus = 'REJEITADO';
        $db->prepare("UPDATE documents SET status=? WHERE id=?")->execute([$newStatus, $id]);
        $doc = $db->prepare("SELECT title,user_id FROM documents WHERE id=?");
        $doc->execute([$id]); $docRow = $doc->fetch(PDO::FETCH_ASSOC);
        if ($docRow && $docRow['user_id']) {
            try {
                $message = "O seu documento ".$docRow['title']." foi rejeitado.";
                $db->prepare("INSERT INTO notifications (user_id,role,type,title,message,icon) VALUES (?,?,?,?,?,?)")
                   ->execute([$docRow['user_id'],'user','warning','Documento rejeitado', $message,'⚠️']);
            } catch(Exception $e) {}
        }
        echo json_encode(['ok'=>true,'msg'=>'Documento rejeitado e autor notificado.','status'=>$newStatus]);
    }
    exit;
}

if (!isset($_SESSION['jwt_auth'])) {
    header('Location: auth.php');
    exit;
}
if ($_SESSION['type_auth'] !== 'ADMIN') {
    header('Location: library.php');
    exit;
}

/* ════ PARAMS ════ */
$q          = trim($_GET['q']       ?? '');
$statusF    = trim($_GET['status']  ?? 'all');   // all | pendente | aprovado | rejeitado | publicado
$sortF      = trim($_GET['sort']    ?? 'recent'); // recent | score-asc | score-desc | reviews
$catF       = trim($_GET['cat']     ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;

/* ════ WHERE ════ */
$where  = ['1=1'];
$params = [];

if ($statusF !== 'all') {
    $statusMap = [
        'pendente'   => ['PENDENTE', 'AGUARDA_REVISAO'],
        'aprovado'   => ['APROVADO'],
        'publicado'  => ['PUBLICADO'],
        'programado'  => ['PROGRAMADO'],
        'rejeitado'  => ['REJEITADO'],
        'aguardando' => ['AGUARDANDO PAGAMENTO'],
    ];
    if (isset($statusMap[$statusF])) {
        $ph = implode(',', array_fill(0, count($statusMap[$statusF]), '?'));
        $where[]  = "d.status IN ($ph)";
        $params   = array_merge($params, $statusMap[$statusF]);
    }
}
if ($q) {
    $where[]  = '(d.title LIKE ? OR d.authors LIKE ? OR d.summary LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
}
if ($catF) {
    $where[]  = 'd.category_id = ?';
    $params[] = $catF;
}

/* ════ ORDER ════ */
$orderMap = [
    'recent'     => 'd.created_at DESC',
    'score-desc' => 'avg_rating DESC',
    'score-asc'  => 'avg_rating ASC',
    'reviews'    => 'review_count DESC',
    'title'      => 'd.title ASC',
];
$oSql = $orderMap[$sortF] ?? $orderMap['recent'];
$wSql = implode(' AND ', $where);

/* ════ COUNT ════ */
$cStmt = $db->prepare("
    SELECT COUNT(DISTINCT d.id)
    FROM documents d
    LEFT JOIN document_review dr ON dr.document_id = d.id
    WHERE $wSql
");
$cStmt->execute($params);
$total  = (int)$cStmt->fetchColumn();
$pages  = max(1, (int)ceil($total / $perPage));
$page   = min($page, $pages);
$offset = ($page - 1) * $perPage;

/* ════ ROWS ════ */
$dStmt = $db->prepare("
    SELECT d.*,
           c.name  AS cat_name,
           c.icon  AS cat_icon,
           COALESCE(AVG(CAST(dr.rating AS DECIMAL(3,1))), 0) AS avg_rating,
           COUNT(dr.id) AS review_count
    FROM documents d
    LEFT JOIN categories c       ON c.id = d.category_id
    LEFT JOIN document_review dr ON dr.document_id = d.id
    WHERE $wSql
    GROUP BY d.id
    ORDER BY $oSql
    LIMIT ? OFFSET ?
");
$allParams = array_merge($params, [$perPage, $offset]);
$dStmt->execute($allParams);
$documents = $dStmt->fetchAll(PDO::FETCH_ASSOC);

/* ════ REVIEWS per document ════ */
// Load reviews for all documents on this page in one query
$docIds = array_column($documents, 'id');
$reviewsByDoc = [];
if (!empty($docIds)) {
    $ph = implode(',', array_fill(0, count($docIds), '?'));
    $rStmt = $db->prepare("
        SELECT dr.*, u.name as reviewer_name, u.type as reviewer_type
        FROM document_review dr
        LEFT JOIN users u ON u.id = dr.user_id
        WHERE dr.document_id IN ($ph)
        ORDER BY dr.created_at DESC
    ");
    $rStmt->execute($docIds);
    foreach ($rStmt->fetchAll(PDO::FETCH_ASSOC) as $rv) {
        $reviewsByDoc[$rv['document_id']][] = $rv;
    }
}

/* ════ CATEGORIES for filter ════ */
$categories = $db->query("SELECT id, name, icon FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

/* ════ STATS ════ */
$stats = $db->query("
    SELECT
        SUM(status IN ('PENDENTE','AGUARDA_REVISAO')) AS pending,
        SUM(status = 'APROVADO')   AS approved,
        SUM(status = 'REJEITADO')  AS rejected,
        SUM(status = 'PUBLICADO')  AS published,
        COUNT(*)                   AS total_docs
    FROM documents
")->fetch(PDO::FETCH_ASSOC);

$avgGlobal = $db->query("SELECT COALESCE(AVG(CAST(rating AS DECIMAL(3,1))),0) FROM document_review")->fetchColumn();

$jwt          = $_SESSION['jwt_auth'];
$userName     = $_SESSION['user_name']  ?? 'Usuário';
$userEmail    = $_SESSION['user_email'] ?? '';
$userInitials = strtoupper(substr($userName, 0, 2));

/* helpers */
function starsStr(float $r): string {
    $f = min(5, max(0, (int)round($r)));
    return str_repeat('★',$f) . str_repeat('☆', 5-$f);
}
function decodeAuthors($raw): string {
    $arr = json_decode($raw ?? '', true);
    if (is_array($arr)) return implode(', ', array_slice($arr, 0, 3));
    return (string)($raw ?? '');
}
function buildAUrl(array $ov=[]): string {
    global $q,$statusF,$sortF,$catF,$page;
    $p = ['q'=>$q,'status'=>$statusF,'sort'=>$sortF,'cat'=>$catF,'page'=>$page];
    foreach($ov as $k=>$v) $p[$k]=$v;
    $clean = array_filter($p, fn($v)=>$v!==null&&$v!==''&&$v!=='all'&&$v!=='recent'&&$v!==0);
    return '?' . http_build_query($clean);
}
function he(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
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
          <a href="opportunities.php" class="nav-i" data-tip="Oportunidades">
            <span class="ni"><i class="fa fa-list"></i></span><span class="nt">Oportunidades</span>
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
          <a href="opportunity-approve.php" class="nav-i" data-tip="Avaliações">
            <span class="ni"><i class="fa fa-comment-o"></i></span
            ><span class="nt">Revisão das Oportunidades</span
            >
          </a>
          <a href="admin-contacts.php" class="nav-i" data-tip="Avaliações">
            <span class="ni"><i class="fa fa-phone"></i></span
            ><span class="nt">Mensagens de Contacto</span
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
              </div>
            </div>
          </div>

          <!-- STATS -->
          <div class="stats-row" id="stats-row">
            <a href="<?= buildAUrl(['status'=>'pendente','page'=>1]) ?>" style="text-decoration:none">
            <div class="sc <?= $statusF==='pendente'?'sc-on':'' ?>">
              <div class="sc-top">
                <div class="sc-ico si-wn">⏳</div>
                <span class="sc-pill sp-wn">Urgente</span>
              </div>
              <div class="sc-num"><?= (int)($stats['pending']??0) ?></div>
              <div class="sc-lbl">Aguardam decisão</div>
            </div>
            </a>
            <a href="<?= buildAUrl(['status'=>'all','page'=>1]) ?>" style="text-decoration:none">
            <div class="sc <?= $statusF==='all'?'sc-on':'' ?>">
              <div class="sc-top">
                <div class="sc-ico si-cr"><i class="fa fa-book" style="color: var(--gd)"></i></div>
                <span class="sc-pill sp-ok">Total</span>
              </div>
              <div class="sc-num"><?= (int)($stats['total_docs']??0) ?></div>
              <div class="sc-lbl">Total de documentos</div>
            </div>
            </a>
            <a href="<?= buildAUrl(['status'=>'publicado','page'=>1]) ?>" style="text-decoration:none">
            <div class="sc <?= $statusF==='aprovado'?'sc-on':'' ?>">
              <div class="sc-top">
                <div class="sc-ico si-ok"><i class="fa fa-check" style="color: var(--ok)"></i></div>
                <span class="sc-pill sp-ok">Pblicados</span>
              </div>
              <div class="sc-num"><?= (int)($stats['approved']??0) ?></div>
              <div class="sc-lbl">Aprovados pelo admin</div>
            </div>
            </a>
            <a href="<?= buildAUrl(['status'=>'rejeitado','page'=>1]) ?>" style="text-decoration:none">
            <div class="sc <?= $statusF==='rejeitado'?'sc-on':'' ?>">
              <div class="sc-top">
                <div class="sc-ico si-er"><i class="fa fa-close" style="color: var(--er)"></i></div>
                <span class="sc-pill sp-er">Rejeitados</span>
              </div>
              <div class="sc-num"><?= (int)($stats['rejected']??0) ?></div>
              <div class="sc-lbl">Rejeitados pelo admin</div>
            </div>
            </a>
            <a href="<?= buildAUrl(['status'=>'publicado','page'=>1]) ?>" style="text-decoration:none">
            <div class="sc <?= $statusF==='publicado'?'sc-on':'' ?>">
              <div class="sc-top">
                <div class="sc-ico si-gd"><i class="fa fa-star" style="color: var(--gd-lt)"></i></div>
                <span class="sc-pill sp-ok"><?= number_format((float)$avgGlobal, 1) ?>/5</span>
              </div>
              <div class="sc-num"><?= (int)($stats['published']??0) ?></div>
              <div class="sc-lbl">Publicados · Média global</div>
            </div>
            </a>
          </div>

          <!-- TOOLBAR -->
          <form method="GET" action="" id="filter-form">
          <div class="toolbar" style="background:#fff;border:1px solid var(--bdr);border-radius:15px;padding:14px 20px;margin-bottom:18px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--sh0)">
            <div style="flex:1;min-width:200px;position:relative">
              <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none"><i class="fa fa-search"></i></span>
              <input type="text" name="q" id="q" value="<?= he($q) ?>"
                placeholder="Pesquisar por título, autor, categoria…"
                style="width:100%;padding:9px 14px 9px 34px;border:1.5px solid var(--bdr);border-radius:11px;font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all .22s"
                onfocus="this.style.borderColor='var(--cr)';this.style.boxShadow='0 0 0 3px rgba(107,16,32,.07)'"
                onblur="this.style.borderColor='';this.style.boxShadow=''">
            </div>
            <select name="sort" id="sort-sel" class="f-sel" onchange="this.form.submit()">
              <option value="recent"     <?= $sortF==='recent'    ?'selected':'' ?>>Mais recentes</option>
              <option value="score-desc" <?= $sortF==='score-desc'?'selected':'' ?>>Maior pontuação</option>
              <option value="score-asc"  <?= $sortF==='score-asc' ?'selected':'' ?>>Menor pontuação</option>
              <option value="reviews"    <?= $sortF==='reviews'   ?'selected':'' ?>>Mais avaliações</option>
              <option value="title"      <?= $sortF==='title'     ?'selected':'' ?>>Título A→Z</option>
            </select>
            <select name="cat" class="f-sel" onchange="this.form.submit()">
              <option value="">Todas as categorias</option>
              <?php foreach($categories as $c): ?>
              <option value="<?= he($c['id']) ?>" <?= $catF===$c['id']?'selected':'' ?>><?= he($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="chips" id="status-chips" style="display:flex;gap:6px;flex-wrap:wrap">
              <?php
              $chips = [
                ['all',       'Todos'],
                ['pendente',  'Pendentes'],
                ['aprovado',  'Aprovados'],
                ['rejeitado', 'Rejeitados'],
                ['publicado', 'Publicados'],
                ['programado',  'Programados'],
              ];
              foreach($chips as [$sv,$sl]):
              ?>
              <a href="<?= buildAUrl(['status'=>$sv,'page'=>1]) ?>"
                 class="chip <?= $statusF===$sv?'on':'' ?>"
                 style="text-decoration:none;padding:6px 14px;border-radius:100px;border:1.5px solid var(--bdr);background:<?= $statusF===$sv?'var(--cr)':'#fff' ?>;color:<?= $statusF===$sv?'#fff':'var(--tx-l)' ?>;font-size:12px;font-weight:700;white-space:nowrap;transition:all .22s">
                <?= $sl ?>
              </a>
              <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-cr btn-sm"><i class="fa fa-search"></i> Filtrar</button>
            <?php if($q||$statusF!=='all'||$catF||$sortF!=='recent'): ?>
            <a href="articles.php" class="btn btn-gh btn-sm">✕ Limpar</a>
            <?php endif; ?>
            <input type="hidden" name="status" value="<?= he($statusF) ?>">
            <span style="font-size:13px;color:var(--tx-l);margin-left:auto;white-space:nowrap"><?= $total ?> documento<?= $total!=1?'s':'' ?></span>
          </div>
          </form>

          <!-- DOCUMENT LIST — PHP rendered -->
          <div id="doc-list">
          <?php if (empty($documents)): ?>
            <div class="empty" style="text-align:center;padding:60px 20px;background:#fff;border-radius:20px;border:1px solid var(--bdr)">
              <div class="empty-ico" style="font-size:48px;opacity:.18;margin-bottom:12px"><i class="fa fa-search"></i></div>
              <div class="empty-title" style="font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px">Nenhum documento encontrado</div>
              <div class="empty-sub" style="font-size:14px;color:var(--tx-l);margin-bottom:18px">Tente ajustar os filtros aplicados.</div>
              <a href="articles.php" class="btn btn-cr">Limpar filtros</a>
            </div>
          <?php else: ?>
          <?php foreach($documents as $i => $d):
            $reviews    = $reviewsByDoc[$d['id']] ?? [];
            $avgRating  = (float)$d['avg_rating'];
            $revCount   = (int)$d['review_count'];
            $catName    = he($d['cat_name'] ?? 'Sem categoria');
            $catIcon    = $d['cat_icon'] ?? '📂';
            $year       = $d['created_at'] ? date('Y', strtotime($d['created_at'])) : '—';
            $stars      = starsStr($avgRating);
            $isPending  = in_array($d['status'], ['PENDENTE', 'AGUARDA_REVISAO']);
            $pct        = min(100, (int)round(($avgRating / 5) * 100));
            $delay      = number_format($i * 0.06, 2);
            $docId      = he($d['id']);
            $nAprov     = count(array_filter($reviews, fn($r) => strtolower($r['decision']??'') === 'aprovado'));
            $nRevis     = count(array_filter($reviews, fn($r) => strtolower($r['decision']??'') === 'revisão'));
            $nRejei     = count(array_filter($reviews, fn($r) => strtolower($r['decision']??'') === 'rejeitado'));

            $authors = json_decode($d['authors']);
            $authors_list = explode(",", $authors);

            $sCfg = [
              'PENDENTE'              => ['cls'=>'bo', 'lbl'=>'EM AVALIAÇÃO'],
              'AGUARDA_REVISAO'       => ['cls'=>'bo', 'lbl'=>'AGUARDANDO REVISÃO'],
              'AGUARDANDO PAGAMENTO'  => ['cls'=>'br', 'lbl'=>'Aguardando Pagamento'],
              'APROVADO'              => ['cls'=>'bg', 'lbl'=>'APROVADO'],
              'PUBLICADO'             => ['cls'=>'bg', 'lbl'=>'PUBLICADO'],
              'REJEITADO'             => ['cls'=>'br', 'lbl'=>'REJEITADO'],
              'PAGO'                  => ['cls'=>'bg', 'lbl'=>'PAGO'],
              'PRONTO'                => ['cls'=>'bg', 'lbl'=>'PRONTO'],
            ];
            $sc  = $sCfg[$d['status']] ?? ['cls'=>'bw','lbl'=>$d['status']];
          ?>
            <div class="doc-card" style="animation-delay:<?= $delay ?>s">
              <div class="dc-hd">
                <div class="dc-type bb" style="width:100px;height:100px;border-radius:12px;flex-shrink:0">
                  <img src="../../uploads/documents/cover/<?=$d['file_cover']?>" alt="<?=$d['title']?>" style="width: 100%;height: 100%; border-radius: 12px;">
                </div>
                <div class="dc-info">
                  <div class="dc-tags">
                    <span class="badge <?= $sc['cls'] ?>"><?= $sc['lbl'] ?></span>
                    <span class="badge bc"><?= $d['category_id'] ?></span>
                    <span class="badge bc"><?= $d['download_link'] ?></span>
                  </div>
                  <div class="dc-title" style="color:var(--inf);font-family:Arial"><?= he($d['title']) ?></div>
                  <div class="dc-meta">
                    <span><i class="fa fa-user" style="color:var(--inf)"></i> <strong><?= arrayForString($authors_list) ?></strong></span>
                    <span><i class="fa fa-calendar" style="color:var(--inf)"></i> <strong><?= $year ?></strong></span>
                    <span><i class="fa fa-file" style="color:var(--inf)"></i> <strong><?= he($d['file_size']??'—') ?></strong></span>
                    <span><i class="fa fa-comments-o" style="color:var(--inf)"></i> <strong><?= $revCount ?> avaliações</strong></span>
                  </div>
                </div>
                <div class="dc-side">
                  <div class="dc-score">
                    <div class="dc-score-n"><?= $revCount > 0 ? number_format($avgRating,1) : '—' ?></div>
                    <div class="dc-score-s"><?= $stars ?></div>
                    <div class="dc-score-l"><?= $revCount ?> aval.</div>
                  </div>
                  <a class="btn btn-cr btn-sm" href="article-detail.php?flex-direction=<?= encrypt($docId) ?>">
                    <i class="fa fa-list"></i> Ver Detalhes
                  </a>
                </div>
              </div>

              <?php if ($isPending): ?>
              <div class="pending-bar">
                <div class="pb-msg">
                  <i class="fa fa-warning"></i>
                  <span>Aguarda decisão &nbsp;·&nbsp; Média: <strong><?= number_format($avgRating,1) ?>/5</strong>
                  &nbsp;·&nbsp; <?= $nAprov ?> <i class="fa fa-check" style="color:var(--ok)"></i> aprovados
                  &nbsp;·&nbsp; <?= $nRevis ?> <i class="fa fa-refresh" style="color:var(--inf)"></i> revisões
                  &nbsp;·&nbsp; <?= $nRejei ?> <i class="fa fa-close" style="color:var(--er)"></i> rejeitados</span>
                </div>
                <div class="pb-actions">
                  <button class="btn btn-ok btn-sm" onclick="quickDecide('<?= $docId ?>','approve')">
                    <i class="fa fa-check"></i> Aprovar
                  </button>
                  <button class="btn btn-er btn-sm" onclick="quickDecide('<?= $docId ?>','reject')">
                    <i class="fa fa-close"></i> Rejeitar
                  </button>
                  <a href="article-detail.php?id=<?= $docId ?>" class="btn btn-gh btn-sm">
                    <i class="fa fa-balance-scale"></i> Ver e decidir
                  </a>
                </div>
              </div>
              <?php endif; ?>

              <button class="exp-tog" onclick="toggleExp(this)">
                ▼ Ver <?= $revCount ?> avaliações detalhadas com identidade dos avaliadores
              </button>

              <div class="exp-body">
                <div class="rv-wrap">
                  <?php foreach($reviews as $ri => $rv):
                    $rvScore   = (int)($rv['rating'] ?? 0);
                    $rvDecision = trim($rv['decision'] ?? '');
                    $dcls = match(strtolower($rvDecision)) {
                        'aprovado'  => 'rv-ok',
                        'rejeitado' => 'rv-er',
                        'revisão' => 'rv-er',
                        default     => 'rv-rev',
                    };
                    $dbdg = match(strtolower($rvDecision)) {
                        'aprovado'  => 'bg',
                        'rejeitado' => 'br',
                        'revisão' => 'br',
                        default     => 'bb',
                    };
                    $rvLabel   = $rvDecision ?: 'Em avaliação';
                    $rvName    = $rv['reviewer_name'] ?? 'Avaliador';
                    $rvRole    = $rv['reviewer_type'] ?? 'TEACHER';
                    $rvInitials= strtoupper(substr($rvName,0,2));
                    $rvDate    = $rv['created_at'] ? date('d/m/Y', strtotime($rv['created_at'])) : '—';
                    $rvStars   = starsStr($rvScore);
                  ?>
                  <div class="rv-item <?= $dcls ?>" style="animation-delay:<?= number_format($ri*.06,2) ?>s">
                    <div class="rv-top">
                      <span class="rv-num">Avaliação #<?= $ri+1 ?></span>
                      <span class="badge <?= $dbdg ?>"><?= he($rvLabel) ?></span>
                    </div>
                    <div class="rv-score">
                      <span class="rv-stars"><?= $rvStars ?></span>
                      <span class="rv-val"><?= $rvScore ?></span>
                      <span class="rv-oof">/ 5</span>
                    </div>
                    <div class="rv-sec">
                      <div class="rv-lbl"><i class="fa fa-comment"></i> Comentário</div>
                      <div class="rv-txt"><?= he($rv['comment'] ?? '—') ?></div>
                    </div>
                    <div class="rv-sec">
                      <div class="rv-lbl">💡 Sugestões</div>
                      <div class="rv-txt"><?= he($rv['suggest'] ?? '—') ?></div>
                    </div>
                    <div class="rv-reviewer">
                      <div class="rv-ava"><?= $rvInitials ?></div>
                      <div>
                        <div class="rv-rname"><?= he($rvName) ?></div>
                        <div class="rv-rrole"><?= he($rvRole) ?></div>
                      </div>
                      <div class="rv-date"><?= $rvDate ?></div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                  <?php if(empty($reviews)): ?>
                  <div style="text-align:center;padding:24px;color:var(--tx-l);font-size:13px">
                    <i class="fa fa-comments-o" style="font-size:28px;opacity:.2;margin-bottom:8px;display:block"></i>
                    Nenhuma avaliação por pares recebida ainda.
                  </div>
                  <?php endif; ?>
                </div>

                <div class="dc-ft">
                  <div>
                    <div style="font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:5px">Média das Avaliações</div>
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                      <span class="avg-big"><?= $revCount > 0 ? number_format($avgRating,1) : '—' ?></span>
                      <div>
                        <div style="font-size:18px;letter-spacing:2px"><?= $stars ?></div>
                        <div class="avg-meta"><?= $revCount ?> avaliações · <?= $nAprov ?> aprovadas · <?= $nRevis ?> revisões · <?= $nRejei ?> rejeitadas</div>
                      </div>
                    </div>
                    <div class="prog"><div class="prog-f" style="width:<?= $pct ?>%"></div></div>
                  </div>
                  <?php if ($isPending): ?>
                  <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button class="btn btn-ok btn-sm" onclick="quickDecide('<?= $docId ?>','approve')">
                      <i class="fa fa-check"></i> Aprovar
                    </button>
                    <button class="btn btn-er btn-sm" onclick="quickDecide('<?= $docId ?>','reject')">
                      <i class="fa fa-close"></i> Rejeitar
                    </button>
                  </div>
                  <?php else: ?>
                  <span class="badge <?= $sc['cls'] ?>" style="font-size:13px;padding:7px 16px"><?= $sc['lbl'] ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- PAGINATION -->
          <?php if($pages > 1): ?>
          <div style="display:flex;align-items:center;justify-content:center;gap:5px;margin-top:28px;flex-wrap:wrap">
            <a href="<?= buildAUrl(['page'=>$page-1]) ?>"
               style="width:36px;height:36px;border-radius:7px;border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;text-decoration:none;<?= $page<=1?'opacity:.3;pointer-events:none':'' ?>">‹</a>
            <?php for($p=1;$p<=$pages;$p++):
              $show=($p===1||$p===$pages||abs($p-$page)<=1);
              $ell=(!$show&&abs($p-$page)===2);
              if($show): ?>
            <a href="<?= buildAUrl(['page'=>$p]) ?>"
               style="width:36px;height:36px;border-radius:7px;border:1.5px solid var(--bdr);background:<?= $p===$page?'var(--cr)':'#fff' ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:<?= $p===$page?'#fff':'var(--tx-m)' ?>;cursor:pointer;text-decoration:none"><?= $p ?></a>
            <?php elseif($ell): ?>
            <span style="color:var(--tx-l);padding:0 4px">…</span>
            <?php endif; endfor; ?>
            <a href="<?= buildAUrl(['page'=>$page+1]) ?>"
               style="width:36px;height:36px;border-radius:7px;border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;text-decoration:none;<?= $page>=$pages?'opacity:.3;pointer-events:none':'' ?>">›</a>
          </div>
          <?php endif; ?>
          <?php endif; ?>
          </div>
      </div>
    </div>

    <?php
    require_once 'assets/modals/document.php';
    ?>

    <script src="assets/js/modal.js"></script>
    <script src="assets/js/api.js"></script>
    <script>
      /* ═══ EXPAND TOGGLE ═══ */
      function toggleExp(btn) {
        const body = btn.nextElementSibling;
        const open = body.classList.toggle("open");
        const n    = body.querySelectorAll(".rv-item").length;
        btn.textContent = open
          ? "▲ Ocultar avaliações"
          : `▼ Ver ${n} avaliações detalhadas com identidade dos avaliadores`;
      }

      /* ═══ QUICK DECIDE ═══ */
      async function quickDecide(id, dec) {
        const ok = dec === "approve";
        if (!confirm(ok ? "Aprovar este documento?" : "Rejeitar este documento?")) return;
        const t = document.getElementById("toast");
        t.textContent = "⌛ A processar…"; t.className = "toast t-def show";
        try {
          const res  = await fetch("articles.php?api=decide", {
            method: "POST",
            body: new URLSearchParams({ id, dec })
          });
          const data = await res.json();
          t.className = "toast " + (data.ok ? "t-ok" : "t-er") + " show";
          t.textContent = data.msg || (ok ? "Aprovado!" : "Rejeitado!");
          if (data.ok) setTimeout(() => location.reload(), 800);
          else setTimeout(() => t.classList.remove("show"), 3500);
        } catch(e) {
          t.textContent = "Erro de rede"; t.className = "toast t-er show";
          setTimeout(() => t.classList.remove("show"), 3500);
        }
      }

      /* ═══ LIVE SEARCH DEBOUNCE ═══ */
      let _dbT = null;
      const qEl = document.getElementById("q");
      if (qEl) {
        qEl.addEventListener("input", () => {
          clearTimeout(_dbT);
          _dbT = setTimeout(() => document.getElementById("filter-form").submit(), 380);
        });
      }

      /* ═══ SIDEBAR ═══ */
      const sidebar = document.getElementById("sidebar"), sbOv = document.getElementById("sb-ov");
      const sbClose = document.getElementById("sb-close"), sbCol = document.getElementById("sb-col");
      let collapsed = false;
      function checkBP() {
        const w = window.innerWidth;
        if (w < 768) { sbClose.style.display = sidebar.classList.contains("open") ? "flex" : "none"; sbCol.style.display = "none"; sidebar.classList.remove("collapsed"); }
        else if (w < 1200) { sbClose.style.display = "none"; sbCol.style.display = "none"; sidebar.classList.remove("open"); sbOv && sbOv.classList.remove("open"); document.body.style.overflow = ""; }
        else { sbClose.style.display = "none"; sbCol.style.display = "flex"; sbCol.textContent = collapsed ? "▶" : "◀"; }
      }
      function openSB() { sidebar.classList.add("open"); sbOv.style.display = "block"; setTimeout(() => sbOv.classList.add("open"), 10); sbClose.style.display = "flex"; document.body.style.overflow = "hidden"; }
      function closeSB() { sidebar.classList.remove("open"); sbOv && sbOv.classList.remove("open"); setTimeout(() => { if (sbOv) sbOv.style.display = "none"; }, 300); sbClose.style.display = "none"; document.body.style.overflow = ""; }
      function toggleCol() { collapsed = !collapsed; sidebar.classList.toggle("collapsed", collapsed); sbCol.textContent = collapsed ? "▶" : "◀"; }
      document.querySelectorAll(".nav-i").forEach(i => i.addEventListener("click", () => { if (window.innerWidth < 768) closeSB(); }));
      window.addEventListener("resize", checkBP); checkBP();

      /* ═══ TOAST ═══ */
      function showToast(msg, cls = "t-def") {
        const t = document.getElementById("toast"); if (!t) return;
        t.textContent = msg; t.className = "toast " + cls + " show";
        setTimeout(() => t.classList.remove("show"), 3200);
      }

      /* ═══ MODAL HELPERS (fallback if modal.js missing) ═══ */
      if (typeof openModal === "undefined") {
        window.openModal  = id => { const el = document.getElementById(id); if(el) { el.classList.add("open"); document.body.style.overflow="hidden"; } };
        window.closeModal = id => { const el = document.getElementById(id); if(el) { el.classList.remove("open"); document.body.style.overflow=""; } };
        window.ovClose    = (e, id) => { if (e.target.id === id) closeModal(id); };
      }
    </script>
  </body>
</html>
