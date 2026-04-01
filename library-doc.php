<?php

require_once 'includes.php';


/* ═══════════════════════════════════════════════════════════════
SEED — garante dados demo na BD (corre apenas se tabela vazia)
═══════════════════════════════════════════════════════════════ */
function seedDocuments($db): void {
    $count = (int)$db->query("SELECT COUNT(*) FROM documents_p")->fetchColumn();
    if ($count > 0) return;

    $types  = ['TCC','Artigo','Livro','Dissertação','Relatório','Apresentação'];
    $icons  = ['🎓','📄','📖','📘','📊','📑'];
    $cats   = ['Eng. Informática','Eng. Petróleo','Gestão','Medicina','Direito','Electrotécnica','Matemática','Arquitectura'];
    $insts  = ['ISPTEC','UAN','UCAN','Metodista','Jean Piaget','Lusíada'];
    $authors= ['Kiala Emanuel','Filomena Luvualu','João Manuel','Sónia Pimentel','Carlos Neto',
    'Ana Rodrigues','Pedro Matos','Ricardo Dias','Luísa Baptista','Prof. Helena Lima',
    'Rui Ferreira','Marta Costa'];
    $prices = [0,0,500,0,1200,1500,0,2000,800,0,3000,2500,600,0,1800,0,900,1400,0,2800,
    1100,700,0,1600,2200,0,3200,500,1000,0];
    $bgs    = ['hsl(200,55%,92%)','hsl(140,45%,92%)','hsl(220,55%,92%)','hsl(40,55%,92%)',
    'hsl(0,45%,92%)','hsl(280,45%,92%)','hsl(60,55%,92%)','hsl(180,45%,92%)',
               'hsl(300,45%,92%)','hsl(20,45%,92%)'];
               $titles = [
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
      $abstracts = [
        'Este trabalho propõe e avalia algoritmos de aprendizagem profunda para detecção automática de falhas em sistemas petrolíferos distribuídos de Angola.',
        'Análise das melhores práticas de gestão de projectos aplicadas ao sector petrolífero angolano, com foco em metodologias ágeis e tradicionais.',
        'Aplicação de redes neurais convolucionais para classificação e diagnose médica, com resultados aplicados ao contexto da saúde em Angola.',
        'Estudo aprofundado de arquitecturas distribuídas e plataformas cloud para suporte a grandes organizações empresariais angolanas.',
        'Guia prático de direito empresarial angolano, incluindo contratos comerciais, responsabilidade civil e regulamentação do sector.',
        'Manual de análise de dados com Python aplicado à engenharia do petróleo, cobrindo exploração, tratamento e visualização de dados.',
        'Perspectivas e desafios para a integração de energias renováveis na matriz eléctrica angolana até 2035.',
        'Introdução aos conceitos de cibersegurança aplicados à protecção de infraestruturas críticas em Angola.',
        'Guia prático de microserviços, containerização com Docker e práticas DevOps para engenheiros de software.',
        'Análise macroeconómica do papel do petróleo na economia angolana e estratégias de diversificação.',
      ];

    $stmt = $db->prepare("INSERT INTO documents_p
        (title,abstract,author,institution,category,type,icon,bg_color,pages,price,is_free,
         rating,rating_count,downloads,pub_year,is_active)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1)");

    foreach ($titles as $i => $title) {
        $ti   = $i % count($types);
        $price= $prices[$i] ?? 0;
        $rating = round(min(5, max(3, 3.2 + (sin($i) * 1.4 + 1))), 1);
        $stmt->execute([
          $title,
          $abstracts[$i % count($abstracts)],
            $authors[$i % count($authors)],
            $insts[$i % count($insts)],
            $cats[$i % count($cats)],
            $types[$ti],
            $icons[$ti],
            $bgs[$i % count($bgs)],
            12 + $i * 4,
            $price,
            $price === 0 ? 1 : 0,
            $rating,
            12 + $i * 7,
            80 + $i * 67,
            2022 + ($i % 4),
          ]);
        }
}
        

/* ═══════════════════════════════════════════════════════════════
   GARANTIR TABELAS EXISTEM
═══════════════════════════════════════════════════════════════ */
function ensureTables($db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS documents_p (
        id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title        VARCHAR(300) NOT NULL,
        abstract     TEXT,
        author       VARCHAR(150),
        institution  VARCHAR(100),
        category     VARCHAR(100),
        type         ENUM('TCC','Artigo','Livro','Dissertação','Relatório','Apresentação') DEFAULT 'TCC',
        icon         VARCHAR(10)  DEFAULT '📄',
        bg_color     VARCHAR(40)  DEFAULT 'hsl(200,55%,92%)',
        pages        SMALLINT     DEFAULT 1,
        price        INT UNSIGNED DEFAULT 0,
        is_free      TINYINT(1)   DEFAULT 1,
        rating       DECIMAL(3,1) DEFAULT 4.0,
        rating_count SMALLINT     DEFAULT 0,
        downloads    INT UNSIGNED DEFAULT 0,
        pub_year     SMALLINT     DEFAULT 2024,
        is_active    TINYINT(1)   DEFAULT 1,
        created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $db->exec("CREATE TABLE IF NOT EXISTS favorites (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        session_id  VARCHAR(128) NOT NULL,
        user_id     INT UNSIGNED DEFAULT NULL,
        document_id INT UNSIGNED NOT NULL,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_fav (session_id, document_id),
        INDEX idx_session (session_id),
        INDEX idx_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

ensureTables($db);
seedDocuments($db);

$sessionId = session_id();

/* ═══════════════════════════════════════════════════════════════
   API — JSON AJAX ENDPOINTS
═══════════════════════════════════════════════════════════════ */
if (isset($_GET['api'])) {
    $act = $_GET['api'];

    /* toggle favorite */
    if ($act === 'fav' && isset($_POST['doc_id'])) {
        $docId = (int)$_POST['doc_id'];
        $exists = $db->prepare("SELECT id FROM favorites WHERE session_id=? AND document_id=?");
        $exists->execute([$sessionId, $docId]);
        if ($exists->fetch()) {
            $db->prepare("DELETE FROM favorites WHERE session_id=? AND document_id=?")->execute([$sessionId, $docId]);
            $active = false;
        } else {
            $db->prepare("INSERT IGNORE INTO favorites (session_id, document_id) VALUES (?,?)")->execute([$sessionId, $docId]);
            $active = true;
        }
        $total = (int)$db->prepare("SELECT COUNT(*) FROM favorites WHERE session_id=?")->execute([$sessionId]) ? 0 : 0;
        $cntStmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE session_id=?");
        $cntStmt->execute([$sessionId]);
        jsonResponse(['ok'=>true,'active'=>$active,'total'=>(int)$cntStmt->fetchColumn()]);
    }

    /* get user favorite IDs */
    if ($act === 'fav_ids') {
        $stmt = $db->prepare("SELECT document_id FROM favorites WHERE session_id=?");
        $stmt->execute([$sessionId]);
        jsonResponse(['ids' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    }

    /* increment downloads counter */
    if ($act === 'download' && isset($_POST['doc_id'])) {
        $docId = (int)$_POST['doc_id'];
        $db->prepare("UPDATE documents SET downloads = downloads + 1 WHERE id=?")->execute([$docId]);
        jsonResponse(['ok'=>true]);
    }

    jsonResponse(['ok'=>false,'msg'=>'Unknown endpoint'], 404);
}


/* ═══════════════════════════════════════════════════════════════
PARAMS — lidos do GET
═══════════════════════════════════════════════════════════════ */
$q        = sanitize($_GET['q']        ?? '');
$types    = array_filter(array_map('trim', explode(',', $_GET['types'] ?? '')));
$cats     = array_filter(array_map('trim', explode(',', $_GET['cats']  ?? '')));
$author   = sanitize($_GET['author']   ?? '');
$yearFrom = (int)($_GET['year_from']   ?? 0);
$yearTo   = (int)($_GET['year_to']     ?? 0);
$rating   = sanitize($_GET['rating']   ?? '');   // '' | '4' | '5'
$access   = sanitize($_GET['access']   ?? '');   // '' | 'free' | 'paid'
$sort     = sanitize($_GET['sort']     ?? 'recent');
$view     = in_array($_GET['view'] ?? '', ['grid','list']) ? $_GET['view'] : 'grid';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;

/* ═══════════════════════════════════════════════════════════════
BUILD QUERY
═══════════════════════════════════════════════════════════════ */
$where  = ['d.is_active = 1'];
$params = [];

if ($q) {
  $where[]  = '(d.title LIKE :q OR d.author LIKE :q OR d.category LIKE :q OR d.abstract LIKE :q)';
  $params[':q'] = "%{$q}%";
}
if (!empty($types)) {
    $ph = implode(',', array_map(fn($i) => ":type$i", array_keys($types)));
    $where[] = "d.type IN ($ph)";
    foreach ($types as $i => $t) $params[":type$i"] = $t;
  }
if (!empty($cats)) {
  $ph = implode(',', array_map(fn($i) => ":cat$i", array_keys($cats)));
    $where[] = "d.category IN ($ph)";
    foreach ($cats as $i => $c) $params[":cat$i"] = $c;
}
if ($author) {
  $where[] = 'd.author LIKE :author';
    $params[':author'] = "%{$author}%";
  }
if ($yearFrom > 0) { $where[] = 'd.pub_year >= :yf'; $params[':yf'] = $yearFrom; }
if ($yearTo   > 0) { $where[] = 'd.pub_year <= :yt'; $params[':yt'] = $yearTo; }
if ($rating === '4') { $where[] = 'd.rating >= 4.0'; }
if ($rating === '5') { $where[] = 'd.rating >= 4.5'; }
if ($access === 'free') { $where[] = 'd.is_free = 1'; }
if ($access === 'paid') { $where[] = 'd.is_free = 0'; }

$orderMap = [
  'recent'     => 'd.pub_year DESC, d.created_at DESC',
  'popular'    => 'd.downloads DESC',
  'rated'      => 'd.rating DESC',
    'price-asc'  => 'd.price ASC',
    'price-desc' => 'd.price DESC',
    'title'      => 'd.title ASC',
  ];
  $orderSql = $orderMap[$sort] ?? $orderMap['recent'];
$whereSql = implode(' AND ', $where);

/* count */
$cntStmt = $db->prepare("SELECT COUNT(*) FROM documents_p d WHERE {$whereSql}");
$cntStmt->execute($params);
$total = (int)$cntStmt->fetchColumn();
$pg    = paginate($total, $page, $perPage);

/* rows */
$rowStmt = $db->prepare("
SELECT d.*
FROM   documents_p d
WHERE  {$whereSql}
ORDER  BY {$orderSql}
LIMIT  :lim OFFSET :off
");
$rowStmt->bindValue(':lim', $pg['per_page'], PDO::PARAM_INT);
$rowStmt->bindValue(':off', $pg['offset'],   PDO::PARAM_INT);
foreach ($params as $k => $v) $rowStmt->bindValue($k, $v);
$rowStmt->execute();
$docs = $rowStmt->fetchAll();

/* ═══ SIDEBAR AGGREGATES ═══ */
$typeCounts = $db->query("SELECT type, COUNT(*) as c FROM documents_p WHERE status='APROVADO' GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);
$catCounts  = $db->query("SELECT category, COUNT(*) as c FROM documents_p WHERE status='APROVADO' GROUP BY category ORDER BY c DESC")->fetchAll(PDO::FETCH_KEY_PAIR);
$allTypes   = ['TCC','Artigo','Livro','Dissertação','Relatório','Apresentação'];
$allCats    = array_keys($catCounts);

/* ═══ FAV COUNT ═══ */
$favCntStmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id=?");
$favCntStmt->execute([$sessionId]);
$favCount = (int)$favCntStmt->fetchColumn();

/* ═══ HELPERS ═══ */
function stars(float $r): string {
  $f = min(5, max(0, (int)round($r)));
  return str_repeat('★', $f) . str_repeat('☆', 5 - $f);
}

function typeClass(string $type): string {
    return match($type) {
        'TCC'         => 'dt-tcc',
        'Artigo'      => 'dt-art',
        'Livro'       => 'dt-liv',
        'Dissertação' => 'dt-dis',
        'Relatório'   => 'dt-rel',
        'Apresentação'=> 'dt-apr',
        default       => 'dt-tcc',
    };
}

function buildUrl(array $overrides = []): string {
    global $q,$types,$cats,$author,$yearFrom,$yearTo,$rating,$access,$sort,$view,$page;
    $params = compact('q','author','yearFrom','yearTo','rating','access','sort','view','page');
    $params['types'] = implode(',', $types);
    $params['cats']  = implode(',', $cats);
    foreach ($overrides as $k => $v) $params[$k] = $v;
    $clean = array_filter($params, fn($v) => $v !== '' && $v !== '0' && $v !== 0);
    return '?' . http_build_query($clean);
}

$flash = getFlash();
?>
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
  --cream:#FAF7F2;--warm:#FEF9F3;--bdr:rgba(107,16,32,.10);--bdr2:rgba(107,16,32,.06);
  --tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;
  --ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);--ok-bdr:rgba(45,122,79,.25);
  --wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);--er:#C53030;--er-bg:rgba(197,48,48,.10);
  --inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);--pu:#5A3A8A;--pu-bg:rgba(90,58,138,.10);
  --sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);
  --sh2:0 8px 32px rgba(107,16,32,.13);--sh3:0 24px 64px rgba(107,16,32,.18);
  --r1:7px;--r2:11px;--r3:15px;--r4:20px;
  --sb-w:274px;--nav-h:62px;--t:.22s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button,form{font-family:inherit}a{color:inherit;text-decoration:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--r2);font-size:13px;font-weight:700;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-gh{background:#fff;color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}

/* ─── NAV ─── */
.nav{background:#fff;border-bottom:1px solid var(--bdr);position:sticky;top:0;z-index:300;box-shadow:var(--sh0)}
.nav-inner{display:flex;align-items:center;gap:10px;height:var(--nav-h);max-width:1280px;margin:0 auto;padding:0 clamp(14px,4vw,40px)}
.nav-logo{font-family:'Arial',serif;font-weight:900;font-size:20px;color:var(--cr-dk);white-space:nowrap;flex-shrink:0}
.nav-logo span{color:var(--gd)}
.nav-bc{font-size:12px;color:var(--tx-l);display:flex;align-items:center;gap:5px}
.nav-bc a{color:var(--cr);font-weight:600}.nav-bc a:hover{text-decoration:underline}
.nav-search-form{flex:1;position:relative;max-width:500px;margin:0 10px}
.nav-s-input{width:100%;padding:8px 36px 8px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}
.nav-s-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}
.nav-s-input::placeholder{color:var(--tx-l)}
.ns-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
.ns-submit{position:absolute;right:6px;top:50%;transform:translateY(-50%);background:var(--cr);color:#fff;border:none;border-radius:var(--r1);padding:5px 10px;font-size:11px;font-weight:700;cursor:pointer;transition:background var(--t)}
.ns-submit:hover{background:var(--cr-dk)}
.nav-r{display:flex;align-items:center;gap:7px;flex-shrink:0;margin-left:auto}
.fav-btn{display:flex;align-items:center;gap:6px;padding:6px 13px;border-radius:var(--r2);background:var(--cream);border:1.5px solid var(--bdr);font-size:12px;font-weight:700;color:var(--tx-m);cursor:pointer;transition:all var(--t);white-space:nowrap;text-decoration:none}
.fav-btn:hover{border-color:var(--cr-bdr);color:var(--cr);background:var(--cr-xl)}
.fav-count{background:var(--cr);color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:100px;min-width:18px;text-align:center}

/* ─── LAYOUT ─── */
.layout{display:flex;max-width:1280px;margin:0 auto;min-height:calc(100vh - var(--nav-h))}

/* ─── SIDEBAR ─── */
.sidebar{width:var(--sb-w);flex-shrink:0;background:#fff;border-right:1px solid var(--bdr);position:sticky;top:var(--nav-h);height:calc(100vh - var(--nav-h));overflow-y:auto;transition:transform var(--t)}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:var(--bdr)}
.sb-head{padding:16px 20px 13px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;gap:8px}
.sb-title{font-size:13px;font-weight:800;color:var(--tx);text-transform:uppercase;letter-spacing:.8px}
.sb-reset{font-size:11px;font-weight:600;color:var(--cr);text-decoration:none}
.sb-reset:hover{text-decoration:underline}
.sb-block{padding:14px 20px;border-bottom:1px solid var(--bdr2)}
.sb-block:last-child{border-bottom:none}
.sb-block-label{font-size:11px;font-weight:800;color:var(--tx-l);text-transform:uppercase;letter-spacing:1px;margin-bottom:11px;display:flex;align-items:center;justify-content:space-between}
.sb-clear-btn{font-size:10px;font-weight:600;color:var(--cr);background:none;border:none;cursor:pointer}
/* cb */
.cb-item{display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer;-webkit-tap-highlight-color:transparent}
.cb-item:hover .cb-lbl{color:var(--cr)}
.cb-box{width:15px;height:15px;border-radius:4px;border:1.5px solid var(--bdr);background:#fff;flex-shrink:0;appearance:none;-webkit-appearance:none;cursor:pointer;transition:all .15s;position:relative;accent-color:var(--cr)}
.cb-box:checked{background:var(--cr);border-color:var(--cr)}
.cb-box:checked::after{content:'✓';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;font-size:9px;font-weight:800}
.cb-lbl{font-size:13px;color:var(--tx-m);flex:1;transition:color var(--t)}
.cb-cnt{font-size:11px;color:var(--tx-l)}
/* radio */
.rb-item{display:flex;align-items:center;gap:8px;padding:5px 0;cursor:pointer;-webkit-tap-highlight-color:transparent}
.rb-item:hover .rb-lbl{color:var(--cr)}
.rb-dot{width:15px;height:15px;border-radius:50%;border:1.5px solid var(--bdr);background:#fff;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:all .15s}
.rb-item.sel .rb-dot{border-color:var(--cr);background:var(--cr)}
.rb-item.sel .rb-dot::after{content:'';width:5px;height:5px;border-radius:50%;background:#fff}
.rb-lbl{font-size:13px;color:var(--tx-m);transition:color var(--t)}
/* year */
.yr-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.yr-input{padding:7px 10px;border:1.5px solid var(--bdr);border-radius:var(--r1);font-size:13px;color:var(--tx);background:var(--cream);outline:none;width:100%;transition:all var(--t)}
.yr-input:focus{border-color:var(--cr);background:#fff}
/* star */
.star-row{display:flex;flex-direction:column;gap:5px}
.star-item{display:flex;align-items:center;gap:8px;cursor:pointer;padding:5px 8px;border-radius:var(--r1);transition:background var(--t);text-decoration:none}
.star-item:hover{background:var(--cr-xl)}
.star-item.sel{background:var(--gd-bg)}
.star-lbl{font-size:12px;color:var(--tx-m);font-weight:500}
/* apply */
.sb-apply{display:block;margin:14px 20px;width:calc(100% - 40px);padding:10px;border-radius:var(--r2);background:var(--cr);color:#fff;border:none;font-size:13px;font-weight:700;cursor:pointer;text-align:center;transition:background var(--t);box-shadow:0 3px 10px rgba(107,16,32,.22)}
.sb-apply:hover{background:var(--cr-dk)}

/* ─── MAIN ─── */
.main{flex:1;min-width:0;padding:clamp(16px,2.5vw,24px) clamp(14px,3vw,28px)}

/* top bar */
.top-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap}
.result-info{font-size:14px;color:var(--tx-l)}
.result-info strong{color:var(--tx);font-weight:700}
.bar-r{display:flex;align-items:center;gap:8px}
.sort-form select{padding:7px 26px 7px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='9' height='5'%3E%3Cpath d='M1 1l3.5 3 3.5-3' stroke='%238A7060' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}
.sort-form select:focus{border-color:var(--cr)}
.vt{display:flex;gap:3px}
.vt-btn{width:32px;height:32px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;transition:all var(--t);color:var(--tx-l);text-decoration:none}
.vt-btn.on,.vt-btn:hover{background:var(--cr);color:#fff;border-color:var(--cr)}
.mob-filter-btn{display:none;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--r2);border:1.5px solid var(--bdr);background:#fff;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t)}
.mob-filter-btn:hover{border-color:var(--cr-bdr);color:var(--cr)}
.f-badge{background:var(--cr);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:100px}

/* active tags */
.active-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.a-tag{display:flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;background:var(--cr-xl);border:1px solid var(--cr-bdr);font-size:12px;font-weight:600;color:var(--cr)}
.a-tag a{color:var(--cr);font-size:12px;font-weight:700;text-decoration:none;margin-left:2px}

/* flash */
.flash{padding:12px 18px;border-radius:var(--r2);margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
.flash-ok{background:var(--ok-bg);color:var(--ok);border:1px solid rgba(45,122,79,.2)}

/* ─── CARDS ─── */
.docs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(200px,22vw,226px),1fr));gap:clamp(12px,2vw,16px)}
.docs-grid.list-view{grid-template-columns:1fr;gap:10px}

.doc-card{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);overflow:hidden;cursor:pointer;transition:all var(--t);animation:fadeUp .38s ease both;position:relative}
.doc-card:hover{box-shadow:var(--sh2);transform:translateY(-3px);border-color:rgba(107,16,32,.20)}

.dc-thumb{height:clamp(88px,11vw,118px);display:flex;align-items:center;justify-content:center;font-size:clamp(30px,5vw,40px);position:relative;overflow:hidden}
.fav-star{position:absolute;top:8px;right:8px;width:28px;height:28px;background:rgba(255,255,255,.85);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;border:none;cursor:pointer;transition:all .2s;backdrop-filter:blur(4px)}
.fav-star:hover{background:#fff;transform:scale(1.15)}
.fav-star.active{background:var(--gd);box-shadow:0 2px 8px rgba(201,168,76,.4)}
.dc-body{padding:clamp(11px,1.6vw,14px)}
.dc-type-row{display:flex;align-items:center;gap:5px;margin-bottom:7px;flex-wrap:wrap}
.dc-tag{font-size:10px;font-weight:700;padding:2px 8px;border-radius:100px}
.dt-tcc{background:var(--inf-bg);color:var(--inf)}.dt-art{background:var(--ok-bg);color:var(--ok)}
.dt-liv{background:var(--gd-bg);color:var(--gd-dk)}.dt-dis{background:var(--pu-bg);color:var(--pu)}
.dt-rel{background:var(--wn-bg);color:var(--wn)}.dt-apr{background:var(--cr-xl);color:var(--cr)}
.free-tag{background:var(--ok-bg);color:var(--ok);font-size:9px;font-weight:800;padding:2px 7px;border-radius:100px;border:1px solid rgba(45,122,79,.2)}
.dc-title{font-family:'Arial',serif;font-size:clamp(13px,1.4vw,14px);font-weight:700;color:var(--tx);line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:5px}
.dc-author{font-size:12px;color:var(--tx-l);margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dc-meta-row{display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:10px}
.dc-rating{font-size:12px;color:var(--gd-dk);font-weight:600;white-space:nowrap}
.dc-pages{font-size:11px;color:var(--tx-l);white-space:nowrap}
.dc-actions{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.dc-btn-see{padding:7px;border-radius:var(--r1);background:var(--cream);border:1px solid var(--bdr);font-size:11px;font-weight:700;color:var(--tx-m);cursor:pointer;text-align:center;transition:all var(--t)}
.dc-btn-see:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.dc-btn-read{padding:7px;border-radius:var(--r1);background:var(--cr);color:#fff;border:none;font-size:11px;font-weight:700;cursor:pointer;text-align:center;transition:all var(--t)}
.dc-btn-read:hover{background:var(--cr-dk)}
.dc-btn-buy{padding:7px;border-radius:var(--r1);background:linear-gradient(135deg,var(--gd-dk),var(--gd));color:var(--cr-dk);border:none;font-size:11px;font-weight:800;cursor:pointer;text-align:center;transition:all var(--t);grid-column:1/-1}
.dc-btn-buy:hover{filter:brightness(1.06)}

/* list view */
.docs-grid.list-view .doc-card{display:flex;gap:0}
.docs-grid.list-view .dc-thumb{width:80px;height:auto;flex-shrink:0;min-height:115px;border-radius:var(--r3) 0 0 var(--r3)}
.docs-grid.list-view .dc-body{flex:1;display:flex;flex-direction:column}
.docs-grid.list-view .dc-actions{grid-template-columns:auto auto;width:fit-content;margin-top:auto}
.docs-grid.list-view .dc-btn-buy{grid-column:unset}

/* pagination */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:clamp(22px,4vw,34px);flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t);text-decoration:none}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}
.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}
.pg-btn.disabled{opacity:.3;pointer-events:none}
.pg-ellipsis{color:var(--tx-l);padding:0 4px;font-size:13px;display:flex;align-items:center}

/* empty */
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);grid-column:1/-1}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:12px}
.empty-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}

/* mobile sidebar modal */
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:500;backdrop-filter:blur(3px);opacity:0;transition:opacity .28s}
.sb-ov.open{opacity:1}
.mob-sidebar{position:fixed;left:0;top:0;bottom:0;width:min(310px,88vw);background:#fff;z-index:600;transform:translateX(-100%);transition:transform .3s cubic-bezier(.4,0,.2,1);overflow-y:auto}
.mob-sidebar.open{transform:translateX(0)}
.mob-sb-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px 12px;border-bottom:1px solid var(--bdr)}
.mob-sb-title{font-size:15px;font-weight:800;color:var(--tx)}
.mob-sb-close{width:28px;height:28px;border-radius:50%;background:var(--cream);border:1px solid var(--bdr);font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--tx-m)}

/* login prompt */
.login-prompt{position:fixed;bottom:clamp(16px,3vw,22px);left:50%;transform:translateX(-50%);z-index:400;background:#fff;border-radius:var(--r4);box-shadow:var(--sh3);border:1px solid var(--bdr);padding:clamp(14px,2vw,18px) clamp(18px,3vw,24px);display:none;align-items:center;gap:clamp(10px,2vw,16px);max-width:min(500px,90vw);width:100%}
.login-prompt.show{display:flex;animation:slideUp .3s cubic-bezier(.22,1,.36,1)}
@keyframes slideUp{from{opacity:0;transform:translateX(-50%) translateY(16px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}
.lp-body{flex:1;min-width:0}
.lp-title{font-size:13px;font-weight:700;color:var(--tx);margin-bottom:2px}
.lp-sub{font-size:12px;color:var(--tx-l)}
.lp-close{width:26px;height:26px;border-radius:50%;background:var(--cream);border:1px solid var(--bdr);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--tx-l);flex-shrink:0}

/* toast */
.toast{position:fixed;bottom:20px;right:20px;z-index:9999;transform:translateY(30px);background:var(--cr-dk);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;line-height:1.4;border:1px solid rgba(201,168,76,.2)}
.toast.show{opacity:1;transform:translateY(0)}
.t-ok{background:var(--ok)}.t-gd{background:var(--gd-dk)}.t-er{background:var(--er)}

@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}

/* responsive */
@media(max-width:860px){.sidebar{display:none}.mob-filter-btn{display:flex}}
@media(max-width:600px){.docs-grid{grid-template-columns:repeat(2,1fr)}.nav-bc{display:none}}
@media(max-width:440px){.nav-search-form{display:none}.docs-grid{grid-template-columns:repeat(2,1fr)}}
</style>
</head>
<body>

<div class="toast" id="toast"></div>
<div class="sb-ov" id="sb-ov" onclick="closeMobSB()"></div>
<div class="mob-sidebar" id="mob-sidebar">
  <div class="mob-sb-head">
    <div class="mob-sb-title">🔧 Filtros</div>
    <button class="mob-sb-close" onclick="closeMobSB()">✕</button>
  </div>
  <!-- filled by JS -->
  <div id="mob-sb-body"></div>
</div>

<!-- LOGIN PROMPT -->
<div class="login-prompt" id="login-prompt">
  <span style="font-size:26px;flex-shrink:0">🔐</span>
  <div class="lp-body">
    <div class="lp-title" id="lp-title">Faça login para continuar</div>
    <div class="lp-sub">Crie uma conta gratuita para acesso completo</div>
  </div>
  <a href="petropub-auth.html" class="btn btn-gh btn-sm">Registar</a>
  <a href="petropub-auth.html" class="btn btn-cr btn-sm">Entrar</a>
  <button class="lp-close" onclick="document.getElementById('login-prompt').classList.remove('show')">✕</button>
</div>

<!-- ═══ NAV ═══ -->
<nav class="nav">
  <div class="nav-inner">
    <a href="petropub-dashboard.php" class="nav-logo">PETRO<span>PUB</span></a>
    <div class="nav-bc">
      <a href="petropub-dashboard.php">Home</a>
      <span>›</span>
      <span style="font-weight:600;color:var(--tx-m)">Biblioteca</span>
    </div>
    <form class="nav-search-form" method="GET" action="">
      <span class="ns-ico">🔍</span>
      <input class="nav-s-input" name="q" type="text"
             value="<?= htmlspecialchars($q) ?>"
             placeholder="Pesquisar documentos, autores…">
      <button type="submit" class="ns-submit">Ir</button>
    </form>
    <div class="nav-r">
      <a href="favoritos.php" class="fav-btn" id="fav-nav-btn">
        🔖 Favoritos <span class="fav-count" id="fav-nav-count"><?= $favCount ?></span>
      </a>
      <a href="petropub-auth.html" class="btn btn-cr btn-sm">Entrar</a>
    </div>
  </div>
</nav>

<!-- ═══ LAYOUT ═══ -->
<div class="layout">

  <!-- ═══ SIDEBAR DESKTOP ═══ -->
  <aside class="sidebar" id="sidebar-desktop">
    <?php include_once __FILE__; /* sidebar inline below */ ?>
    <div class="sb-head">
      <div class="sb-title">🔧 Filtros</div>
      <a href="biblioteca.php" class="sb-reset">Limpar tudo</a>
    </div>

    <!-- SEARCH -->
    <form method="GET" action="" class="sb-block">
      <div class="sb-block-label">Pesquisa</div>
      <div style="position:relative">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
               placeholder="Título, autor, área…"
               style="width:100%;padding:8px 12px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;outline:none;background:var(--cream);color:var(--tx)"
               onfocus="this.style.borderColor='var(--cr)'" onblur="this.style.borderColor=''">
        <?php
          // keep all other filters in hidden inputs
          foreach ($types as $t): ?><input type="hidden" name="types" value="<?= htmlspecialchars($t) ?>"><?php endforeach;
          foreach ($cats  as $c): ?><input type="hidden" name="cats"  value="<?= htmlspecialchars($c) ?>"><?php endforeach;
          if ($yearFrom): ?><input type="hidden" name="year_from" value="<?= $yearFrom ?>"><?php endif;
          if ($yearTo)  : ?><input type="hidden" name="year_to"   value="<?= $yearTo ?>"><?php endif;
          if ($rating)  : ?><input type="hidden" name="rating"    value="<?= htmlspecialchars($rating) ?>"><?php endif;
          if ($access)  : ?><input type="hidden" name="access"    value="<?= htmlspecialchars($access) ?>"><?php endif;
          if ($sort)    : ?><input type="hidden" name="sort"       value="<?= htmlspecialchars($sort) ?>"><?php endif;
          if ($view)    : ?><input type="hidden" name="view"       value="<?= htmlspecialchars($view) ?>"><?php endif;
        ?>
      </div>
    </form>

    <!-- TYPE -->
    <div class="sb-block">
      <div class="sb-block-label">
        Tipo de documento
        <?php if (!empty($types)): ?>
        <a href="<?= buildUrl(['types'=>'','page'=>1]) ?>" class="sb-clear-btn">Limpar</a>
        <?php endif; ?>
      </div>
      <?php foreach ($allTypes as $t): ?>
      <label class="cb-item">
        <input type="checkbox" class="cb-box"
               <?= in_array($t,$types)?'checked':'' ?>
               onchange="toggleFilter('types','<?= addslashes($t) ?>',this.checked)">
        <span class="cb-lbl"><?= htmlspecialchars($t) ?></span>
        <span class="cb-cnt"><?= $typeCounts[$t] ?? 0 ?></span>
      </label>
      <?php endforeach; ?>
    </div>

    <!-- CATEGORY -->
    <div class="sb-block">
      <div class="sb-block-label">
        Categoria
        <?php if (!empty($cats)): ?>
        <a href="<?= buildUrl(['cats'=>'','page'=>1]) ?>" class="sb-clear-btn">Limpar</a>
        <?php endif; ?>
      </div>
      <?php foreach ($allCats as $c): ?>
      <label class="cb-item">
        <input type="checkbox" class="cb-box"
               <?= in_array($c,$cats)?'checked':'' ?>
               onchange="toggleFilter('cats','<?= addslashes($c) ?>',this.checked)">
        <span class="cb-lbl"><?= htmlspecialchars($c) ?></span>
        <span class="cb-cnt"><?= $catCounts[$c] ?? 0 ?></span>
      </label>
      <?php endforeach; ?>
    </div>

    <!-- YEAR -->
    <div class="sb-block">
      <div class="sb-block-label">Ano de publicação</div>
      <form method="GET" action="" class="yr-row">
        <input class="yr-input" type="number" name="year_from"
               value="<?= $yearFrom ?: '' ?>" placeholder="De…"
               min="2000" max="<?= date('Y') ?>"
               onchange="this.form.submit()">
        <input class="yr-input" type="number" name="year_to"
               value="<?= $yearTo ?: '' ?>" placeholder="Até…"
               min="2000" max="<?= date('Y') ?>"
               onchange="this.form.submit()">
        <?php foreach($types as $t): ?><input type="hidden" name="types" value="<?= h($t) ?>"><?php endforeach; ?>
        <?php foreach($cats  as $c): ?><input type="hidden" name="cats"  value="<?= h($c) ?>"><?php endforeach; ?>
        <?php if($q):?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
        <?php if($rating):?><input type="hidden" name="rating" value="<?= h($rating) ?>"><?php endif; ?>
        <?php if($access):?><input type="hidden" name="access" value="<?= h($access) ?>"><?php endif; ?>
        <?php if($sort):?><input type="hidden" name="sort" value="<?= h($sort) ?>"><?php endif; ?>
        <?php if($view):?><input type="hidden" name="view" value="<?= h($view) ?>"><?php endif; ?>
      </form>
    </div>

    <!-- RATING -->
    <div class="sb-block">
      <div class="sb-block-label">Avaliação mínima</div>
      <div class="star-row">
        <?php foreach ([['','Qualquer'],['4','4+ ★★★★☆'],['5','5 ★★★★★']] as [$v,$l]): ?>
        <a href="<?= buildUrl(['rating'=>$v,'page'=>1]) ?>"
           class="star-item <?= $rating===$v?'sel':'' ?>">
          <span class="star-lbl"><?= $l ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ACCESS -->
    <div class="sb-block">
      <div class="sb-block-label">Tipo de acesso</div>
      <a href="<?= buildUrl(['access'=>'','page'=>1]) ?>"
         class="rb-item <?= $access===''?'sel':'' ?>">
        <div class="rb-dot"></div><span class="rb-lbl">Todos</span>
      </a>
      <a href="<?= buildUrl(['access'=>'free','page'=>1]) ?>"
         class="rb-item <?= $access==='free'?'sel':'' ?>">
        <div class="rb-dot"></div><span class="rb-lbl">🆓 Gratuito</span>
      </a>
      <a href="<?= buildUrl(['access'=>'paid','page'=>1]) ?>"
         class="rb-item <?= $access==='paid'?'sel':'' ?>">
        <div class="rb-dot"></div><span class="rb-lbl">💳 Pago</span>
      </a>
    </div>

    <a href="<?= buildUrl(['page'=>1]) ?>" class="sb-apply">✓ Ver <?= $total ?> resultado<?= $total!=1?'s':'' ?></a>
  </aside>

  <!-- ═══ MAIN CONTENT ═══ -->
  <main class="main">

    <?php 
    if ($flash): ?>
    <div class="flash flash-ok">✅ <?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>

    <!-- TOP BAR -->
    <div class="top-bar">
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <button class="mob-filter-btn" onclick="openMobSB()">
          <i class="fa fa-cog"></i> Filtros
          <?php
          $activeFilterCount = count($types)+count($cats)+($rating?1:0)+($access?1:0)+($yearFrom?1:0)+($q?1:0);
          if ($activeFilterCount > 0): ?>
          <span class="f-badge"><?= $activeFilterCount ?></span>
          <?php endif; ?>
        </button>
        <div class="result-info">
          A mostrar <strong><?= $pg['offset']+1 ?>–<?= min($pg['offset']+$perPage,$total) ?></strong>
          de <strong><?= $total ?></strong> documentos
        </div>
      </div>
      <div class="bar-r">
        <form class="sort-form" method="GET" action="" id="sort-form">
          <?php foreach($types as $t): ?><input type="hidden" name="types" value="<?= h($t) ?>"><?php endforeach; ?>
          <?php foreach($cats  as $c): ?><input type="hidden" name="cats"  value="<?= h($c) ?>"><?php endforeach; ?>
          <?php if($q):?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
          <?php if($rating):?><input type="hidden" name="rating" value="<?= h($rating) ?>"><?php endif; ?>
          <?php if($access):?><input type="hidden" name="access" value="<?= h($access) ?>"><?php endif; ?>
          <?php if($yearFrom):?><input type="hidden" name="year_from" value="<?= $yearFrom ?>"><?php endif; ?>
          <?php if($yearTo):?><input type="hidden" name="year_to" value="<?= $yearTo ?>"><?php endif; ?>
          <input type="hidden" name="view" value="<?= h($view) ?>">
          <select name="sort" onchange="this.form.submit()">
            <option value="recent"     <?= $sort==='recent'    ?'selected':'' ?>>🕐 Mais recentes</option>
            <option value="popular"    <?= $sort==='popular'   ?'selected':'' ?>>📥 Mais populares</option>
            <option value="rated"      <?= $sort==='rated'     ?'selected':'' ?>>⭐ Melhor avaliados</option>
            <option value="price-asc"  <?= $sort==='price-asc' ?'selected':'' ?>>💰 Preço ↑</option>
            <option value="price-desc" <?= $sort==='price-desc'?'selected':'' ?>>💸 Preço ↓</option>
            <option value="title"      <?= $sort==='title'     ?'selected':'' ?>>🔤 A→Z</option>
          </select>
        </form>
        <div class="vt">
          <a href="<?= buildUrl(['view'=>'grid']) ?>" class="vt-btn <?= $view==='grid'?'on':'' ?>" title="Grelha">⊞</a>
          <a href="<?= buildUrl(['view'=>'list']) ?>" class="vt-btn <?= $view==='list'?'on':'' ?>" title="Lista">☰</a>
        </div>
      </div>
    </div>

    <!-- ACTIVE FILTER TAGS -->
    <div class="active-tags">
      <?php if ($q): ?>
      <span class="a-tag">"<?= htmlspecialchars($q) ?>"<a href="<?= buildUrl(['q'=>'','page'=>1]) ?>">✕</a></span>
      <?php endif; ?>
      <?php foreach ($types as $t): ?>
      <span class="a-tag"><?= htmlspecialchars($t) ?><a href="<?= buildUrl(['types'=>implode(',',array_filter($types,fn($x)=>$x!==$t)),'page'=>1]) ?>">✕</a></span>
      <?php endforeach; ?>
      <?php foreach ($cats as $c): ?>
      <span class="a-tag"><?= htmlspecialchars($c) ?><a href="<?= buildUrl(['cats'=>implode(',',array_filter($cats,fn($x)=>$x!==$c)),'page'=>1]) ?>">✕</a></span>
      <?php endforeach; ?>
      <?php if ($access==='free'): ?><span class="a-tag">🆓 Gratuito<a href="<?= buildUrl(['access'=>'','page'=>1]) ?>">✕</a></span><?php endif; ?>
      <?php if ($access==='paid'): ?><span class="a-tag">💳 Pago<a href="<?= buildUrl(['access'=>'','page'=>1]) ?>">✕</a></span><?php endif; ?>
      <?php if ($rating): ?><span class="a-tag">⭐ <?= $rating ?>+<a href="<?= buildUrl(['rating'=>'','page'=>1]) ?>">✕</a></span><?php endif; ?>
      <?php if ($yearFrom||$yearTo): ?><span class="a-tag">📅 <?= $yearFrom ?: '...' ?>–<?= $yearTo ?: '...' ?><a href="<?= buildUrl(['year_from'=>0,'year_to'=>0,'page'=>1]) ?>">✕</a></span><?php endif; ?>
    </div>

    <!-- DOCS GRID -->
    <?php if (empty($docs)): ?>
    <div class="empty">
      <div class="empty-ico">🔍</div>
      <div class="empty-title">Nenhum documento encontrado</div>
      <p style="font-size:14px;color:var(--tx-l);margin-top:6px;margin-bottom:18px">
        Tente ajustar os filtros ou use termos diferentes.
      </p>
      <a href="biblioteca.php" class="btn btn-cr">Limpar todos os filtros</a>
    </div>
    <?php else: ?>
    <div class="docs-grid<?= $view==='list'?' list-view':'' ?>" id="docs-grid">
      <?php foreach ($docs as $i => $d):
        $tcls  = typeClass($d['type']);
        $delay = number_format($i * 0.045, 3);
      ?>
      <div class="doc-card" id="doc-<?= $d['id'] ?>" style="animation-delay:<?= $delay ?>s">
        <div class="dc-thumb" style="background:<?= htmlspecialchars($d['bg_color']) ?>">
          <?= htmlspecialchars($d['icon']) ?>
          <button class="fav-star <?= $d['is_fav']?'active':'' ?>"
                  id="fav-<?= $d['id'] ?>"
                  onclick="toggleFav(<?= $d['id'] ?>,this)"
                  title="<?= $d['is_fav']?'Remover dos favoritos':'Adicionar aos favoritos' ?>">
            <?= $d['is_fav'] ? '★' : '☆' ?>
          </button>
        </div>
        <div class="dc-body">
          <div class="dc-type-row">
            <span class="dc-tag <?= $tcls ?>"><?= htmlspecialchars($d['type']) ?></span>
            <?= $d['is_free'] ? '<span class="free-tag">GRÁTIS</span>' : '' ?>
          </div>
          <div class="dc-title"><?= htmlspecialchars($d['title']) ?></div>
          <div class="dc-author">👤 <?= htmlspecialchars($d['author']) ?> · <?= htmlspecialchars($d['institution']) ?></div>
          <div class="dc-meta-row">
            <span class="dc-rating"><?= stars((float)$d['rating']) ?> <?= number_format($d['rating'],1) ?></span>
            <span class="dc-pages">📄 <?= $d['pages'] ?> págs · <?= $d['pub_year'] ?></span>
          </div>
          <div class="dc-actions">
            <button class="dc-btn-see"
                    onclick="showSummary(<?= htmlspecialchars(json_encode($d),ENT_QUOTES) ?>)">
              Ver resumo
            </button>
            <?php if ($d['is_free']): ?>
            <button class="dc-btn-read"
                    onclick="doDownload(<?= $d['id'] ?>)">
              📖 Ler 🔒
            </button>
            <?php else: ?>
            <button class="dc-btn-buy"
                    onclick="requireLogin('download')">
              🛒 <?= number_format($d['price'],0,'.','.') ?> Kz
            </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($pg['pages'] > 1): ?>
    <div class="pagination">
      <a href="<?= buildUrl(['page'=>$pg['page']-1]) ?>"
         class="pg-btn <?= !$pg['has_prev']?'disabled':'' ?>">‹</a>
      <?php for ($p = 1; $p <= $pg['pages']; $p++):
        $show = ($p===1 || $p===$pg['pages'] || abs($p-$pg['page'])<=1);
        $ellipsis = (!$show && abs($p-$pg['page'])===2);
        if ($show): ?>
        <a href="<?= buildUrl(['page'=>$p]) ?>"
           class="pg-btn <?= $p===$pg['page']?'on':'' ?>"><?= $p ?></a>
        <?php elseif ($ellipsis): ?>
        <span class="pg-ellipsis">…</span>
        <?php endif; ?>
      <?php endfor; ?>
      <a href="<?= buildUrl(['page'=>$pg['page']+1]) ?>"
         class="pg-btn <?= !$pg['has_next']?'disabled':'' ?>">›</a>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </main>
</div>

<!-- SUMMARY MODAL -->
<div id="sum-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:800;align-items:center;justify-content:center;padding:20px">
  <div style="background:#fff;border-radius:20px;max-width:540px;width:100%;max-height:calc(100vh - 40px);overflow-y:auto;box-shadow:0 24px 64px rgba(0,0,0,.22);animation:modalIn .28s cubic-bezier(.22,1,.36,1)">
    <div id="sum-head" style="padding:20px 24px;border-radius:20px 20px 0 0;position:relative">
      <div id="sum-type" style="font-size:10px;font-weight:700;margin-bottom:6px"></div>
      <div id="sum-title" style="font-family:'Arial',serif;font-size:20px;font-weight:700;color:#fff;line-height:1.3"></div>
      <div id="sum-author" style="font-size:13px;margin-top:6px;opacity:.7;color:#fff"></div>
      <button onclick="closeSum()" style="position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center">✕</button>
    </div>
    <div style="padding:20px 24px">
      <div id="sum-abstract" style="font-size:14px;color:var(--tx-m);line-height:1.7;margin-bottom:18px"></div>
      <div id="sum-meta" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px"></div>
      <div id="sum-actions" style="display:flex;gap:8px;flex-wrap:wrap"></div>
    </div>
  </div>
</div>

<script>
/* ═══ FAV IDS (loaded from server, kept in JS state) ═══ */
const favIds = new Set(<?= json_encode(array_column(
    $db->prepare("SELECT document_id FROM favorites WHERE session_id=?")
       ->execute([$sessionId]) ? [] : [],
    'document_id'
)) ?>);
/* recompute properly */
(async () => {
    const r = await fetch('biblioteca.php?api=fav_ids');
    const d = await r.json();
    d.ids.forEach(id => favIds.add(+id));
})();

/* ═══ TOGGLE FAV ═══ */
async function toggleFav(docId, btn) {
    btn.style.transform = 'scale(.8)';
    const res  = await fetch('biblioteca.php?api=fav', {
        method:'POST',
        body: new URLSearchParams({doc_id: docId})
    });
    const data = await res.json();
    btn.style.transform = '';

    if (data.active) {
        btn.textContent = '★';
        btn.classList.add('active');
        btn.title = 'Remover dos favoritos';
        favIds.add(docId);
        showToast('★ Adicionado aos favoritos!','t-gd');
    } else {
        btn.textContent = '☆';
        btn.classList.remove('active');
        btn.title = 'Adicionar aos favoritos';
        favIds.delete(docId);
        showToast('Removido dos favoritos','t-def');
    }

    // update nav badge
    const badge = document.getElementById('fav-nav-count');
    if (badge) badge.textContent = data.total;
}

/* ═══ MOBILE SIDEBAR ═══ */
function openMobSB() {
    const o = document.getElementById('sb-ov');
    const s = document.getElementById('mob-sidebar');
    // clone sidebar content
    document.getElementById('mob-sb-body').innerHTML =
        document.getElementById('sidebar-desktop').innerHTML;
    o.style.display='block';
    setTimeout(()=>o.classList.add('open'),10);
    s.classList.add('open');
    document.body.style.overflow='hidden';
}
function closeMobSB() {
    const o=document.getElementById('sb-ov'), s=document.getElementById('mob-sidebar');
    o.classList.remove('open'); s.classList.remove('open');
    setTimeout(()=>o.style.display='none',300);
    document.body.style.overflow='';
}

/* ═══ CHECKBOX FILTERS ═══ */
function toggleFilter(key, value, checked) {
    const url = new URL(window.location.href);
    const existing = url.searchParams.getAll(key);
    url.searchParams.delete(key);

    if (checked) {
        [...existing, value].forEach(v => url.searchParams.append(key, v));
    } else {
        existing.filter(v => v !== value).forEach(v => url.searchParams.append(key, v));
    }
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

/* ═══ SUMMARY MODAL ═══ */
function showSummary(doc) {
    const ov = document.getElementById('sum-overlay');
    document.getElementById('sum-head').style.background =
        `linear-gradient(135deg,var(--cr-dk),var(--cr-lt))`;
    document.getElementById('sum-type').innerHTML =
        `<span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:100px;color:#fff;font-size:10px">${doc.type}</span>`;
    document.getElementById('sum-title').textContent = doc.title;
    document.getElementById('sum-author').textContent = `👤 ${doc.author} · ${doc.institution}`;
    document.getElementById('sum-abstract').textContent =
        doc.abstract || 'Resumo não disponível para este documento.';

    const metaItems = [
        ['📂 Categoria', doc.category],
        ['📅 Ano', doc.pub_year],
        ['📄 Páginas', doc.pages + ' páginas'],
        ['⭐ Avaliação', doc.rating + '/5 (' + doc.rating_count + ' aval.)'],
        ['📥 Downloads', Number(doc.downloads).toLocaleString('pt-PT')],
        ['💰 Preço', doc.is_free=='1'||doc.is_free===true ? 'Grátis' : Number(doc.price).toLocaleString('pt-PT') + ' Kz'],
    ];
    document.getElementById('sum-meta').innerHTML = metaItems.map(([l,v])=>
        `<div style="background:var(--cream);border:1px solid var(--bdr);border-radius:10px;padding:10px 13px">
          <div style="font-size:10px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:3px">${l}</div>
          <div style="font-size:13px;font-weight:600;color:var(--tx)">${v}</div>
         </div>`
    ).join('');

    const isFree = doc.is_free=='1'||doc.is_free===true;
    const favActive = favIds.has(+doc.id);
    document.getElementById('sum-actions').innerHTML = `
      ${isFree
        ? `<button class="btn btn-cr" onclick="doDownload(${doc.id});closeSum()">📖 Ler completo 🔒</button>`
        : `<button class="btn btn-cr" onclick="requireLogin('download');closeSum()">🛒 Comprar — ${Number(doc.price).toLocaleString('pt-PT')} Kz</button>`}
      <button class="btn btn-gh" onclick="toggleFavFromModal(${doc.id});closeSum()" id="sum-fav-btn">
        ${favActive ? '★ Nos favoritos' : '☆ Adicionar favorito'}
      </button>
    `;

    ov.style.display='flex';
    document.body.style.overflow='hidden';
}
function closeSum() {
    document.getElementById('sum-overlay').style.display='none';
    document.body.style.overflow='';
}
document.getElementById('sum-overlay').addEventListener('click', function(e){
    if(e.target===this) closeSum();
});
function toggleFavFromModal(docId) {
    const btn = document.getElementById('fav-'+docId);
    if (btn) toggleFav(docId, btn);
}

/* ═══ DOWNLOAD ═══ */
function doDownload(docId) {
    showToast('🔐 Inicie sessão para descarregar','t-def');
    document.getElementById('lp-title').textContent = 'Login necessário para baixar';
    document.getElementById('login-prompt').classList.add('show');
    // track click
    fetch('biblioteca.php?api=download', {method:'POST',body:new URLSearchParams({doc_id:docId})});
}

/* ═══ LOGIN GATE ═══ */
function requireLogin(type) {
    const msgs = {
        download: 'Login para baixar este documento',
        read:     'Login para ler o documento completo',
    };
    document.getElementById('lp-title').textContent = msgs[type] || msgs.read;
    document.getElementById('login-prompt').classList.add('show');
}

/* ═══ TOAST ═══ */
function showToast(msg, cls='t-def') {
    const t = document.getElementById('toast');
    t.textContent=msg; t.className='toast '+cls; t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'), 2800);
}

/* ═══ MODAL ANIMATION ═══ */
const style = document.createElement('style');
style.textContent = '@keyframes modalIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}';
document.head.appendChild(style);
</script>
</body>
</html>
<?php
/* helper — avoids repeating htmlspecialchars */
function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
