<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'base_academica_petrochamp');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Configuração do banco de dados
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'u633577398_petrochamp');
// define('DB_USER', 'u633577398_petro_champ');
// define('DB_PASSWORD', 'Petrochamp_camp1');

require_once '../../backend/src/Infrastructure/Database/PDOConnection.php';
require_once '../../backend/src/Domain/Id.php';
require_once '../../backend/src/Domain/User/UserId.php';
require_once '../../backend/src/Domain/Document/Document.php';
require_once '../../backend/src/Domain/Category/Category.php';
require_once '../../backend/src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../../backend/src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../../backend/src/Infrastructure/Database/CategoryRepositoryImpl.php';
require_once '../../backend/src/Infrastructure/Database/ReviewRepositoryImpl.php';
require_once '../../backend/src/Application/DocumentService.php';
require_once '../../backend/src/Application/ReviewService.php';
require_once '../../backend/src/Application/UrlEncryptService.php';
require_once '../../backend/src/Infrastructure/Security/JWTService.php';
require_once '../../backend/src/Application/PaymentMethodService.php'; 
require_once '../../backend/src/Domain/PaymentMethod/PaymentMethod.php'; 
require_once '../../backend/src/Infrastructure/Database/PaymentMethodRepositoryImpl.php'; 
require_once '../../backend/src/Infrastructure/Database/ReviewRepositoryImpl.php';
require_once '../../backend/src/Application/DocumentReviewService.php';


$db = PDOConnection::getInstance()->getConnection();

$reviewService = new ReviewService($db);
$documentRepository = new DocumentRepositoryImpl($db);
$userRepository = new UserRepositoryImpl($db);
$categoryRepository = new CategoryRepositoryImpl($db);
$documentService = new DocumentService($documentRepository, $categoryRepository, $uploadDir);
$repositoryPM = new PaymentMethodRepositoryImpl($db); 
$servicePM = new PaymentMethodService($repositoryPM); 
$reviewRepository = new ReviewRepository($db);
$drService = new DocumentReviewService($documentRepository, $reviewRepository, $userRepository);

$role = $_SESSION['type_auth'];
$id_auth = $_SESSION['id_auth'];


// ─── HELPERS ────────────────────────────────────────────────────
function sanitize(string $v): string {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function paginate(int $total, int $page, int $perPage): array {
    $pages = max(1, (int) ceil($total / $perPage));
    $page  = max(1, min($page, $pages));
    return [
        'total'    => $total,
        'page'     => $page,
        'per_page' => $perPage,
        'pages'    => $pages,
        'offset'   => ($page - 1) * $perPage,
        'has_prev' => $page > 1,
        'has_next' => $page < $pages,
    ];
}


function flash(string $msg, string $type = 'ok'): void {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function getFlash(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function csrf(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function renderColorStatus($status) {
    if ($status == "PENDENTE") {
        return '<span class="badge gray">'.$status.'</span>';
    } elseif ($status == "REJEITADO") {
        return '<span class="badge red">'.$status.'</span>';
    } elseif ($status == "APROVADO") {
        return '<span class="badge blue">'.$status.'</span>';
    } elseif ($status == "PUBLICADO") {
        return '<span class="badge green">'.$status.'</span>'; 
    } elseif ($status == "PRGRAMADO") {
        return '<span class="badge crimson">'.$status.'</span>';
    } elseif ($status == "AGUARDANDO PAGAMENTO") {
        return '<span class="badge orange">'.$status.'</span>';
    } elseif ($status == "PAGO") {
        return '<span class="badge gold">'.$status.'</span>';
    }else {
        return '<span class="badge gray">'.$status.'</span>';
    }
}

function calcularMediaAvaliacoes(array $reviews): array
{
    if (count($reviews) === 0) {
        return [
            "media" => 0.0,
            "count" => 0,
            "stars" => '
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
            '
        ];
    }
    
    $count = 0;
    $sum = 0;

    $ratings = array_column($reviews, "rating");
    for ($i=0; $i < count($reviews); $i++) { 
        $count += 1;
        $sum += (int)$reviews[$i]["rating"];
    }

    $media  = $sum / $count;
    $mediaFormated = round($media, 1);

    $starsComplete = floor($media);
    $starMedia = ($media - $starsComplete) >= 0.5 ? 1 : 0;
    $starsEmpty = 5 - ($starsComplete + $starMedia);
    $stars = '';

    for ($i=0; $i < $starsComplete; $i++) { 
        $stars .= '<i class="fa fa-star" style="color: orange;"></i>'; 
    }

    if ($starMedia) {
        $stars .= '<i class="fa fa-star-half" style="color: orange; margin-right: 5px;"></i>'; 
    }

    for ($i=0; $i < $starsEmpty; $i++) { 
        $stars .= '<i class="fa fa-star"></i>'; 
    }

    return [
        "media" => $mediaFormated,
        "count" => $count,
        "stars" => $stars
    ];  
}

function arrayForString($authors_list) {
    $authors = array_map(function($item) {
        return trim($item, '[]"');
    }, $authors_list);

    $result = implode(", ", $authors);
    return $result;
}

function renderStars($rat): string {
    $rating = (int)$rat;
    $maxStars = 5;
    $stars = '';

    for ($i=1; $i <= $maxStars; $i++) { 
        if ($i <= $rating) {
            $stars .= '<i class="fa fa-star" style="color: orange;"></i>'; 
        } else {
            $stars .= '<i class="fa fa-star"></i>'; 
        }
    }

    return $stars;
}

function getInitials(string $name): string {
    $name = trim($name);

    if ($name === '') {
        return '';
    }

    $parts = explode(' ', $name);

    $initials = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= strtoupper($part[0]);
        }
    }

    return $initials;
}


function ensurePublicTables(): void {
  $db->exec("CREATE TABLE IF NOT EXISTS contacts (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(120) NOT NULL, email VARCHAR(150) NOT NULL,
      subject VARCHAR(200) NOT NULL, message TEXT NOT NULL,
      status ENUM('new','read','replied') DEFAULT 'new',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function baseCss(): string { return '
<style>
:root{
  --cr:#6B1020;--cr-dk:#4A0B16;--cr-lt:#8C1A2E;--cr-xl:rgba(107,16,32,.07);--cr-bdr:rgba(107,16,32,.14);
  --gd:#C9A84C;--gd-lt:#E5C97E;--gd-dk:#9A7828;--gd-bg:rgba(201,168,76,.11);
  --cream:#FAF7F2;--warm:#FEF9F3;--white:#fff;
  --bdr:rgba(107,16,32,.10);--bdr2:rgba(107,16,32,.06);
  --tx:#1A1208;--tx-m:#4A3728;--tx-l:#8A7060;
  --ok:#2D7A4F;--ok-bg:rgba(45,122,79,.10);--ok-bdr:rgba(45,122,79,.25);
  --wn:#C47A1A;--wn-bg:rgba(196,122,26,.10);--wn-bdr:rgba(196,122,26,.25);
  --er:#C53030;--er-bg:rgba(197,48,48,.10);--er-bdr:rgba(197,48,48,.22);
  --inf:#1A5C8A;--inf-bg:rgba(26,92,138,.10);--pu:#5A3A8A;--pu-bg:rgba(90,58,138,.10);
  --sh0:0 1px 4px rgba(107,16,32,.07);--sh1:0 3px 14px rgba(107,16,32,.10);
  --sh2:0 8px 32px rgba(107,16,32,.13);--sh3:0 24px 64px rgba(107,16,32,.18);
  --r1:7px;--r2:11px;--r3:15px;--r4:20px;
  --sb:258px;--sb-ico:64px;--sb-mob:248px;--hdr:62px;
  --t:.22s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:\'DM Sans\',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button,textarea,form{font-family:inherit}a{color:inherit;text-decoration:none}
/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--r2);font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-ok{background:var(--ok);color:#fff}.btn-ok:hover{background:#246640;transform:translateY(-1px)}
.btn-er{background:var(--er);color:#fff}.btn-er:hover{background:#a62424;transform:translateY(-1px)}
.btn-wn{background:var(--wn);color:#fff}.btn-wn:hover{filter:brightness(.9)}
.btn-gh{background:var(--cream);color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-sm{padding:5px 13px;font-size:12px;border-radius:var(--r1)}
.btn-xs{padding:3px 9px;font-size:11px;border-radius:6px}
/* BADGES */
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:100px;font-size:11px;font-weight:700}
.bw{background:rgba(0,0,0,.06);color:#666}.bg{background:var(--ok-bg);color:var(--ok);border:1px solid var(--ok-bdr)}
.br{background:var(--er-bg);color:var(--er);border:1px solid var(--er-bdr)}.bo{background:var(--wn-bg);color:var(--wn);border:1px solid var(--wn-bdr)}
.bb{background:var(--inf-bg);color:var(--inf)}.bv{background:var(--pu-bg);color:var(--pu)}.bc{background:var(--cr-xl);color:var(--cr)}.bgd{background:var(--gd-bg);color:var(--gd-dk)}
/* APP */
.app{display:flex;min-height:100vh}
/* SIDEBAR */
.sidebar{width:var(--sb);flex-shrink:0;background:var(--cr-dk);height:100vh;position:sticky;top:0;overflow-y:auto;overflow-x:hidden;display:flex;flex-direction:column;transition:width var(--t),transform var(--t);z-index:200}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12)}
.sb-head{padding:22px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:flex-start;justify-content:space-between;min-height:70px;flex-shrink:0}
.sb-logo{font-family:\'Arial\',serif;font-size:21px;font-weight:900;color:#fff;line-height:1}.sb-logo span{color:var(--gd-lt)}
.sb-role{font-size:10px;font-weight:700;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.6px;margin-top:4px}
.sb-tog{display:none;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);color:#fff;width:30px;height:30px;border-radius:var(--r1);font-size:14px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0;transition:background var(--t);margin-top:2px}.sb-tog:hover{background:rgba(255,255,255,.22)}
.sb-user{padding:13px 20px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.08);flex-shrink:0}
.ava{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;border:2px solid rgba(255,255,255,.20);flex-shrink:0}
.ava-dk{background:#1A3A4A}.ava-cr{background:var(--cr-lt)}
.sb-un{font-size:13px;font-weight:600;color:#fff}.sb-ue{font-size:11px;color:rgba(255,255,255,.38)}
.nav-s{padding:12px 10px 2px}.nav-l{font-size:10px;font-weight:700;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:1.5px;padding:0 9px;margin-bottom:4px}
.nav-i{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:10px;cursor:pointer;color:rgba(255,255,255,.58);font-size:13px;font-weight:500;margin-bottom:2px;white-space:nowrap;overflow:hidden;position:relative;transition:all .16s}
.nav-i:hover{background:rgba(255,255,255,.08);color:#fff}.nav-i.act{background:rgba(255,255,255,.14);color:#fff}
.nav-i.act::before{content:\'\';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--gd);border-radius:0 2px 2px 0}
.ni{font-size:15px;width:18px;text-align:center;flex-shrink:0}
.nt{overflow:hidden;text-overflow:ellipsis;transition:opacity .2s,max-width .2s}
.nb{margin-left:auto;background:#E53E3E;color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px;flex-shrink:0}
.ng{margin-left:auto;background:var(--gd);color:var(--cr-dk);font-size:10px;font-weight:700;padding:2px 7px;border-radius:100px;flex-shrink:0}
.sb-foot{margin-top:auto;padding:10px;border-top:1px solid rgba(255,255,255,.08);flex-shrink:0}
.sidebar.collapsed{width:var(--sb-ico)}
.sidebar.collapsed .sb-logo,.sidebar.collapsed .sb-role,.sidebar.collapsed .sb-un,.sidebar.collapsed .sb-ue,.sidebar.collapsed .nav-l,.sidebar.collapsed .nt,.sidebar.collapsed .nb,.sidebar.collapsed .ng{opacity:0;max-width:0;pointer-events:none;overflow:hidden}
.sidebar.collapsed .nav-i{justify-content:center;padding:9px}.sidebar.collapsed .ni{width:auto}
.sidebar.collapsed .sb-user{justify-content:center;padding:12px}.sidebar.collapsed .sb-head{justify-content:center;padding:16px 10px}
.sb-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:190;backdrop-filter:blur(3px);opacity:0;transition:opacity .28s}.sb-ov.open{opacity:1}
/* MAIN */
.main{flex:1;min-width:0;display:flex;flex-direction:column;overflow:hidden}
.topbar{background:#fff;border-bottom:1px solid var(--bdr);padding:0 clamp(14px,3vw,30px);height:var(--hdr);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:var(--sh0);gap:10px;flex-shrink:0}
.tb-l{display:flex;align-items:center;gap:10px;min-width:0;flex:1}
.tb-ham{display:none;width:36px;height:36px;border-radius:var(--r1);background:var(--cr-xl);border:1px solid var(--bdr);color:var(--cr);font-size:18px;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0}
.tb-bc{font-size:11px;color:var(--tx-l);margin-bottom:2px}.tb-bc a{color:var(--cr);font-weight:600;text-decoration:none}
.tb-title{font-family:\'Arial\',serif;font-size:clamp(15px,2vw,18px);font-weight:700;color:var(--cr-dk)}
.tb-r{display:flex;align-items:center;gap:8px;flex-shrink:0}
.admin-badge{display:flex;align-items:center;gap:6px;background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff;padding:5px 14px;border-radius:100px;font-size:12px;font-weight:700}
.notif-wrap{position:relative;flex-shrink:0}
.notif-btn{width:36px;height:36px;border-radius:var(--r1);background:var(--cream);border:1px solid var(--bdr);display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;text-decoration:none}
.notif-dot{position:absolute;top:6px;right:6px;width:8px;height:8px;background:#E53E3E;border-radius:50%;border:2px solid #fff}
/* PAGE */
.page-wrap{flex:1;overflow-y:auto;padding:clamp(16px,3vw,28px) clamp(14px,3vw,32px)}
/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:clamp(10px,1.5vw,14px);margin-bottom:clamp(18px,2.5vw,24px)}
.sc{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(13px,2vw,17px);box-shadow:var(--sh0);animation:fadeUp .4s ease both}
.sc-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
.sc-ico{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px}
.si-cr{background:var(--cr-xl)}.si-ok{background:var(--ok-bg)}.si-er{background:var(--er-bg)}.si-wn{background:var(--wn-bg)}.si-gd{background:var(--gd-bg)}.si-inf{background:var(--inf-bg)}
.sc-pill{font-size:11px;font-weight:600;padding:2px 8px;border-radius:100px}.sp-ok{background:var(--ok-bg);color:var(--ok)}.sp-er{background:var(--er-bg);color:var(--er)}.sp-wn{background:var(--wn-bg);color:var(--wn)}.sp-gd{background:var(--gd-bg);color:var(--gd-dk)}
.sc-num{font-family:\'Arial\',serif;font-size:clamp(22px,3vw,30px);font-weight:700;color:var(--tx);line-height:1}
.sc-lbl{font-size:12px;color:var(--tx-l);margin-top:4px}
/* TOOLBAR */
.toolbar{background:#fff;border:1px solid var(--bdr);border-radius:var(--r3);padding:clamp(12px,2vw,16px) clamp(14px,2vw,22px);margin-bottom:clamp(14px,2vw,18px);display:flex;align-items:center;gap:10px;flex-wrap:wrap;box-shadow:var(--sh0)}
.search-wrap{flex:1;min-width:180px;position:relative}.search-wrap input{width:100%;padding:9px 14px 9px 36px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}.search-wrap input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.search-wrap input::placeholder{color:var(--tx-l)}
.s-ico{position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none}
.f-sel{padding:9px 28px 9px 11px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx-m);background:#fff url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'9\' height=\'5\'%3E%3Cpath d=\'M1 1l3.5 3 3.5-3\' stroke=\'%238A7060\' stroke-width=\'1.5\' fill=\'none\' stroke-linecap=\'round\'/%3E%3C/svg%3E") no-repeat calc(100% - 8px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}.f-sel:focus{border-color:var(--cr)}
.chips{display:flex;gap:6px;flex-wrap:wrap}
.chip{padding:6px 12px;border-radius:100px;border:1.5px solid var(--bdr);background:#fff;font-size:12px;font-weight:600;color:var(--tx-l);cursor:pointer;transition:all var(--t);white-space:nowrap;text-decoration:none;display:inline-block}
.chip:hover,.chip.on{background:var(--cr);color:#fff;border-color:var(--cr)}
/* CARD */
.card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);overflow:hidden;margin-bottom:clamp(14px,2vw,20px);box-shadow:var(--sh0)}
.card-head{padding:clamp(14px,2vw,18px) clamp(16px,2vw,24px);border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.card-title{font-family:\'Arial\',serif;font-size:clamp(14px,1.8vw,17px);font-weight:700;color:var(--cr-dk);display:flex;align-items:center;gap:8px}
.card-body{padding:clamp(14px,2vw,22px) clamp(16px,2vw,24px)}
.card-foot{padding:clamp(12px,1.5vw,14px) clamp(16px,2vw,24px);border-top:1px solid var(--bdr);background:var(--warm);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
/* FORM */
.f-group{margin-bottom:16px}.f-lbl{display:block;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:7px}
.f-input{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}.f-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.f-input::placeholder{color:var(--tx-l)}
.f-sel-full{width:100%;padding:10px 28px 10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:#fff url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'9\' height=\'5\'%3E%3Cpath d=\'M1 1l3.5 3 3.5-3\' stroke=\'%238A7060\' stroke-width=\'1.5\' fill=\'none\' stroke-linecap=\'round\'/%3E%3C/svg%3E") no-repeat calc(100% - 10px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}.f-sel-full:focus{border-color:var(--cr)}
.f-ta{width:100%;padding:10px 13px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;resize:vertical;min-height:80px;line-height:1.6;transition:all var(--t);font-family:inherit}.f-ta:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.f-ta::placeholder{color:var(--tx-l)}
.f-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}.f-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.f-check-row{display:flex;align-items:center;gap:8px;cursor:pointer}.f-cb{width:15px;height:15px;border-radius:4px;cursor:pointer;accent-color:var(--cr)}.f-cb-lbl{font-size:13px;color:var(--tx-m)}
/* MODAL */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;overflow-y:auto}.overlay.open{display:flex}
.modal{background:#fff;border-radius:var(--r4);width:100%;box-shadow:var(--sh3);animation:modalIn .28s cubic-bezier(.22,1,.36,1);max-height:calc(100vh - 40px);overflow-y:auto}
.modal-sm{max-width:440px}.modal-md{max-width:580px}.modal-lg{max-width:720px}.modal-xl{max-width:900px}
@keyframes modalIn{from{opacity:0;transform:translateY(18px) scale(.97)}to{opacity:1;transform:none}}
.m-hd{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px);position:relative;border-radius:var(--r4) var(--r4) 0 0}
.mh-cr{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt))}.mh-ok{background:linear-gradient(135deg,#1A4A2E,var(--ok))}.mh-er{background:linear-gradient(135deg,#6B0808,var(--er))}.mh-wn{background:linear-gradient(135deg,#5A3A00,var(--wn))}
.m-hd h3{font-family:\'Arial\',serif;font-size:18px;font-weight:700;color:#fff}.m-hd p{font-size:12px;color:rgba(255,255,255,.58);margin-top:4px}
.m-close{position:absolute;top:12px;right:12px;width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center}.m-close:hover{background:rgba(255,255,255,.28)}
.m-body{padding:clamp(18px,2vw,22px) clamp(18px,2vw,24px)}.m-foot{padding:clamp(12px,1.5vw,14px) clamp(18px,2vw,24px);border-top:1px solid var(--bdr);background:var(--warm);border-radius:0 0 var(--r4) var(--r4);display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}
/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:clamp(20px,3vw,28px);flex-wrap:wrap}
.pg-btn{width:36px;height:36px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t);text-decoration:none}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}.pg-btn.disabled{opacity:.3;pointer-events:none}
/* EMPTY */
.empty{text-align:center;padding:60px 20px;background:#fff;border-radius:var(--r4);border:1px solid var(--bdr)}
.empty-ico{font-size:48px;opacity:.18;margin-bottom:12px}.empty-title{font-family:\'Arial\',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}
/* FLASH */
.flash{padding:12px 18px;border-radius:var(--r2);margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;animation:fadeUp .35s ease}
.flash-ok{background:var(--ok-bg);color:var(--ok);border:1px solid rgba(45,122,79,.2)}.flash-er{background:var(--er-bg);color:var(--er);border:1px solid rgba(197,48,48,.2)}.flash-wn{background:var(--wn-bg);color:var(--wn);border:1px solid rgba(196,122,26,.2)}
/* TOGGLE */
.toggle{position:relative;width:36px;height:20px;cursor:pointer;flex-shrink:0}
.toggle input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;background:var(--bdr);border-radius:100px;transition:background var(--t)}
.toggle input:checked+.toggle-track{background:var(--ok)}
.toggle-thumb{position:absolute;top:3px;left:3px;width:14px;height:14px;background:#fff;border-radius:50%;transition:transform var(--t);box-shadow:0 1px 4px rgba(0,0,0,.2)}
.toggle input:checked~.toggle-thumb{transform:translateX(16px)}
/* TOAST */
.toast{position:fixed;bottom:clamp(14px,3vw,22px);right:clamp(14px,3vw,22px);z-index:9999;transform:translateY(30px);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;line-height:1.4;border:1px solid rgba(201,168,76,.2)}
.toast.show{opacity:1;transform:translateY(0)}.t-ok{background:var(--ok)}.t-er{background:var(--er)}.t-def{background:var(--cr-dk)}.t-wn{background:var(--wn)}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
/* RESPONSIVE */
@media(max-width:1199px){
  :root{--sb:var(--sb-ico)}
  .sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nt,.sidebar .nb,.sidebar .ng{opacity:0;max-width:0;pointer-events:none;overflow:hidden}
  .sidebar .nav-i{justify-content:center;padding:9px}.sidebar .ni{width:auto}
  .sidebar .sb-user{justify-content:center;padding:12px}.sidebar .sb-head{justify-content:center;padding:16px 10px}
  .sb-tog{display:none!important}
}
@media(max-width:767px){
  .sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sb-mob)!important;height:100vh;z-index:300;transform:translateX(-100%);box-shadow:var(--sh3)}.sidebar.open{transform:translateX(0)}
  .sidebar .sb-logo,.sidebar .sb-role,.sidebar .sb-un,.sidebar .sb-ue,.sidebar .nav-l,.sidebar .nt,.sidebar .nb,.sidebar .ng{opacity:1!important;max-width:unset!important;pointer-events:auto!important}
  .sidebar .nav-i{justify-content:flex-start;padding:8px 10px;gap:9px}.sidebar .ni{width:18px}
  .sidebar .sb-user{justify-content:flex-start;padding:13px 20px}.sidebar .sb-head{justify-content:space-between;padding:18px 20px}
  .sb-ov{display:block}.sb-tog{display:flex!important}.tb-ham{display:flex}
  .topbar{padding:0 14px;height:56px}.f-row,.f-row-3{grid-template-columns:1fr}
  .m-foot{flex-wrap:wrap}.m-foot .btn{flex:1;justify-content:center}
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
'; }


function sidebar(string $active = '', bool $isAdmin = true): string {
    $role = $isAdmin ? 'Administração' : 'Portal';
    $ava  = $isAdmin ? 'AD' : 'MK';
    $name = $isAdmin ? 'Ana Domingos' : 'Manuel Kiala';
    $email= $isAdmin ? 'admin@petropub.ao' : 'm.kiala@uan.ao';
    return "
<aside class='sidebar' id='sidebar'>
  <div class='sb-head'>
    <div><div class='sb-logo'>PETRO<span>PUB</span></div><div class='sb-role'>$role</div></div>
    <button class='sb-tog' id='sb-close' onclick='closeSB()'>✕</button>
    <button class='sb-tog' id='sb-col'  onclick='toggleCol()'>◀</button>
  </div>
  <div class='sb-user'>
    <div class='ava ava-dk'>$ava</div>
    <div><div class='sb-un'>$name</div><div class='sb-ue'>$email</div></div>
  </div>
  <div class='nav-s'>
    <div class='nav-l'>Visão Geral</div>
    <a class='nav-i' href='admin-dashboard.php'><span class='ni'>📊</span><span class='nt'>Dashboard</span></a>
    <a class='nav-i' href='petropub-admin-utilizadores.html'><span class='ni'>👥</span><span class='nt'>Utilizadores</span></a>
  </div>
  <div class='nav-s'>
    <div class='nav-l'>Editorial</div>
    <a class='nav-i ".($active==='aprovacoes'?'act':'')."' href='admin-aprovacoes.php'><span class='ni'>✅</span><span class='nt'>Aprovações</span><span class='nb' id='sb-pend'>0</span></a>
    <a class='nav-i ".($active==='docs'?'act':'')."' href='admin-documentos.php'><span class='ni'>📚</span><span class='nt'>Documentos</span></a>
    <a class='nav-i ".($active==='avaliacoes'?'act':'')."' href='admin-avaliacoes-lista.html'><span class='ni'>🔍</span><span class='nt'>Avaliações</span></a>
  </div>
  <div class='nav-s'>
    <div class='nav-l'>Portal</div>
    <a class='nav-i ".($active==='seccoes'?'act':'')."' href='admin-seccoes.php'><span class='ni'>🗂️</span><span class='nt'>Secções</span></a>
    <a class='nav-i ".($active==='opp'?'act':'')."' href='admin-oportunidades.php'><span class='ni'>⛽</span><span class='nt'>Oportunidades</span></a>
    <a class='nav-i ".($active==='avisos'?'act':'')."' href='admin-avisos.php'><span class='ni'>📢</span><span class='nt'>Avisos</span></a>
    <a class='nav-i ".($active==='notifs'?'act':'')."' href='admin-notificacoes.php'><span class='ni'>🔔</span><span class='nt'>Notificações</span></a>
  </div>
  <div class='nav-s'>
    <div class='nav-l'>Portal Público</div>
    <a class='nav-i' href='lista-oportunidades.php' target='_blank'><span class='ni'>🌐</span><span class='nt'>Lista Oportunidades</span></a>
    <a class='nav-i' href='lista-avisos.php' target='_blank'><span class='ni'>📋</span><span class='nt'>Lista Avisos</span></a>
    <a class='nav-i' href='biblioteca.php' target='_blank'><span class='ni'>📖</span><span class='nt'>Biblioteca</span></a>
  </div>
  <div class='sb-foot'>
    <a class='nav-i' href='#'><span class='ni'>🚪</span><span class='nt'>Sair da Sessão</span></a>
  </div>
</aside>";
}


if (!function_exists('h')) {
    function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
}



function publicCss(): string { return '
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
  --r1:7px;--r2:11px;--r3:15px;--r4:20px;--r5:28px;--nav-h:62px;
  --t:.22s cubic-bezier(.4,0,.2,1);--max:1280px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:\'DM Sans\',sans-serif;background:var(--cream);color:var(--tx);-webkit-font-smoothing:antialiased;overflow-x:hidden}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--cream)}::-webkit-scrollbar-thumb{background:var(--cr);border-radius:3px}
input,select,button,textarea{font-family:inherit}a{color:inherit;text-decoration:none}
.container{max-width:var(--max);margin:0 auto;padding:0 clamp(14px,4vw,40px)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border-radius:var(--r2);font-size:13px;font-weight:700;cursor:pointer;border:none;transition:all var(--t);white-space:nowrap;line-height:1}
.btn-cr{background:var(--cr);color:#fff;box-shadow:0 3px 12px rgba(107,16,32,.25)}.btn-cr:hover{background:var(--cr-dk);transform:translateY(-1px)}
.btn-gh{background:#fff;color:var(--tx-m);border:1.5px solid var(--bdr)}.btn-gh:hover{background:var(--cr-xl);color:var(--cr);border-color:var(--cr-bdr)}
.btn-gd{background:linear-gradient(135deg,var(--gd-dk),var(--gd));color:var(--cr-dk);font-weight:800}.btn-gd:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(201,168,76,.4)}
.btn-sm{padding:6px 16px;font-size:12px;border-radius:var(--r1)}
.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:100px;font-size:11px;font-weight:700}
/* NAV */
.nav{background:#fff;border-bottom:1px solid var(--bdr);position:sticky;top:0;z-index:300;box-shadow:var(--sh0)}
.nav-inner{display:flex;align-items:center;gap:12px;height:var(--nav-h);max-width:var(--max);margin:0 auto;padding:0 clamp(14px,4vw,40px)}
.nav-logo{font-family:\'Arial\',serif;font-weight:900;font-size:20px;color:var(--cr-dk);flex-shrink:0}
.nav-logo span{color:var(--gd)}
.nav-links{display:flex;align-items:center;gap:0;margin-left:clamp(12px,2vw,24px);flex:1}
.nav-link{padding:0 clamp(10px,1.5vw,16px);height:var(--nav-h);display:flex;align-items:center;font-size:13px;font-weight:600;color:var(--tx-l);border-bottom:2.5px solid transparent;transition:all var(--t);white-space:nowrap;text-decoration:none}
.nav-link:hover,.nav-link.on{color:var(--cr);border-bottom-color:var(--cr)}
.nav-r{display:flex;align-items:center;gap:8px;flex-shrink:0;margin-left:auto}
.ham{display:none;flex-direction:column;align-items:center;justify-content:center;gap:5px;width:38px;height:38px;border-radius:var(--r1);background:none;border:1.5px solid var(--bdr);cursor:pointer;transition:all var(--t)}
.ham:hover{border-color:var(--cr-bdr);background:var(--cr-xl)}
.ham-line{width:16px;height:1.5px;background:var(--tx-m);border-radius:1px}
/* MOBILE DRAWER */
.mob-ov{display:none;position:fixed;inset:0;background:rgba(0,0,0,.52);z-index:500;backdrop-filter:blur(3px);opacity:0;transition:opacity .28s}
.mob-ov.open{opacity:1}
.mob-drawer{position:fixed;right:0;top:0;bottom:0;width:min(300px,85vw);background:#fff;z-index:600;transform:translateX(100%);transition:transform .3s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;box-shadow:var(--sh3)}
.mob-drawer.open{transform:translateX(0)}
.mob-head{padding:18px 20px 14px;border-bottom:1px solid var(--bdr);display:flex;align-items:center;justify-content:space-between}
.mob-logo{font-family:\'Arial\',serif;font-size:18px;font-weight:900;color:var(--cr-dk)}.mob-logo span{color:var(--gd)}
.mob-close{width:28px;height:28px;border-radius:50%;background:var(--cream);border:1px solid var(--bdr);font-size:13px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.mob-body{flex:1;overflow-y:auto;padding:8px 0}
.mob-link{display:flex;align-items:center;gap:10px;padding:13px 20px;font-size:14px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:background var(--t);text-decoration:none}
.mob-link:hover{background:var(--cream);color:var(--cr)}
.mob-link.on{color:var(--cr);background:var(--cr-xl)}
.mob-foot{padding:14px 20px;border-top:1px solid var(--bdr);display:flex;flex-direction:column;gap:8px}
/* PAGINATION */
.pagination{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:clamp(24px,4vw,36px);flex-wrap:wrap}
.pg-btn{width:38px;height:38px;border-radius:var(--r1);border:1.5px solid var(--bdr);background:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:var(--tx-m);cursor:pointer;transition:all var(--t);text-decoration:none}
.pg-btn:hover:not(.on):not(.disabled){border-color:var(--cr-bdr);color:var(--cr)}.pg-btn.on{background:var(--cr);color:#fff;border-color:var(--cr)}.pg-btn.disabled{opacity:.3;pointer-events:none}
/* FOOTER */
.footer{background:var(--cr-dk);padding:clamp(40px,6vw,64px) 0 clamp(20px,3vw,30px)}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:clamp(24px,4vw,48px);margin-bottom:clamp(28px,4vw,44px)}
.ft-logo{font-family:\'Arial\',serif;font-size:22px;font-weight:900;color:#fff;margin-bottom:10px}.ft-logo span{color:var(--gd-lt)}
.ft-desc{font-size:13px;color:rgba(255,255,255,.55);line-height:1.65;margin-bottom:14px}
.ft-col h4{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:1.2px;color:rgba(255,255,255,.35);margin-bottom:12px}
.ft-link{display:block;font-size:13px;color:rgba(255,255,255,.55);margin-bottom:9px;cursor:pointer;transition:color var(--t);text-decoration:none}
.ft-link:hover{color:var(--gd-lt)}
.footer-bottom{border-top:1px solid rgba(255,255,255,.08);padding-top:clamp(14px,2.5vw,20px);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:12px;color:rgba(255,255,255,.30)}
/* FLASH */
.flash{padding:12px 18px;border-radius:var(--r2);margin-bottom:16px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px}
.flash-ok{background:var(--ok-bg);color:var(--ok);border:1px solid var(--ok-bdr)}.flash-er{background:var(--er-bg);color:var(--er)}
/* TOAST */
.toast{position:fixed;bottom:20px;right:20px;z-index:9999;transform:translateY(30px);background:var(--cr-dk);color:#fff;padding:11px 18px;border-radius:var(--r3);font-size:13px;font-weight:500;box-shadow:var(--sh3);opacity:0;transition:all .3s cubic-bezier(.22,1,.36,1);max-width:280px;border:1px solid rgba(201,168,76,.2)}
.toast.show{opacity:1;transform:translateY(0)}.t-ok{background:var(--ok)}.t-er{background:var(--er)}
/* FORM */
.f-group{margin-bottom:16px}.f-lbl{display:block;font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:7px}
.f-input{width:100%;padding:11px 14px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;transition:all var(--t)}.f-input:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.f-input::placeholder{color:var(--tx-l)}
.f-ta{width:100%;padding:11px 14px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:var(--cream);outline:none;resize:vertical;min-height:100px;line-height:1.6;transition:all var(--t);font-family:inherit}.f-ta:focus{border-color:var(--cr);background:#fff;box-shadow:0 0 0 3px var(--cr-xl)}.f-ta::placeholder{color:var(--tx-l)}
.f-sel-f{width:100%;padding:11px 32px 11px 14px;border:1.5px solid var(--bdr);border-radius:var(--r2);font-size:13px;color:var(--tx);background:#fff url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'9\' height=\'5\'%3E%3Cpath d=\'M1 1l3.5 3 3.5-3\' stroke=\'%238A7060\' stroke-width=\'1.5\' fill=\'none\' stroke-linecap=\'round\'/%3E%3C/svg%3E") no-repeat calc(100% - 12px) center;appearance:none;outline:none;cursor:pointer;transition:border-color var(--t)}.f-sel-f:focus{border-color:var(--cr)}
.f-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.empty-state{text-align:center;padding:clamp(48px,8vw,80px) 20px}
.es-ico{font-size:clamp(48px,7vw,64px);margin-bottom:16px}
.es-title{font-family:\'Arial\',serif;font-size:clamp(20px,3vw,26px);color:var(--tx);margin-bottom:8px}
.es-sub{font-size:clamp(13px,1.4vw,15px);color:var(--tx-l);line-height:1.6;max-width:420px;margin:0 auto 24px}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
@media(max-width:960px){.footer-grid{grid-template-columns:1fr 1fr}}
@media(max-width:768px){.nav-links{display:none}.ham{display:flex}.footer-grid{grid-template-columns:1fr}.f-row{grid-template-columns:1fr}}
</style>'; }

/* ── NAV HTML ── */
function pubNav(string $active = ''): string {
    $auth = (isset($_SESSION['jwt_auth'])) ? "<a href='my-documents.php' class='btn btn-cr btn-sm'><i class='fa fa-user'></i> ".getInitials($_SESSION['user_name'])."</a>" : "<a href='auth.php' class='btn btn-cr btn-sm'>Entrar</a>";

    $auth_link = '';
    if (isset($_SESSION['jwt_auth'])) {
      $auth_link .= "
        <a href='my-documents.php' class='btn btn-cr' style='justify-content:center'><i class='fa fa-user'></i> ".$_SESSION['user_name']."</a>
        <a href='upload-document.php' class='btn btn-gh' style='justify-content:center'>Submeter Artigo</a>
      ";
    } else {
      $auth_link .= "
        <a href='auth.php' class='btn btn-cr' style='justify-content:center'>Entrar</a>
        <a href='auth.php' class='btn btn-gh' style='justify-content:center'>Registar</a>
      ";
    }

    $links = [
        ['href'=>'index.php','label'=>'Home','key'=>'home'],
        ['href'=>'library.php','label'=>'Biblioteca','key'=>'biblioteca'],
        ['href'=>'list-opportunities.php','label'=>'Oportunidades','key'=>'oportunidades'],
        ['href'=>'list-noticies.php','label'=>'Notícias','key'=>'noticias'],
        ['href'=>'about.php','label'=>'Sobre','key'=>'sobre'],
        ['href'=>'contact.php','label'=>'Contacto','key'=>'contacto'],
        ['href'=>'faq.php','label'=>'FAQ','key'=>'faq'],
    ];
    $linksHtml = '';
    $mobLinksHtml = '';
    foreach ($links as $l) {
        $on = ($l['key'] === $active) ? ' on' : '';
        $linksHtml     .= "<a href='{$l['href']}' class='nav-link{$on}'>{$l['label']}</a>";
        $mobLinksHtml  .= "<a href='{$l['href']}' class='mob-link{$on}'>{$l['label']}</a>";
    }
    return "
<div class='mob-ov' id='mob-ov' onclick='closeMob()'></div>
<div class='mob-drawer' id='mob-drawer'>
  <div class='mob-head'>
    <img src='../../uploads/logo/logo1.PNG' alt='logotipo petropub' style='width: 100px; height: 100px;'>
    <button class='mob-close' onclick='closeMob()'>✕</button>
  </div>
  <div class='mob-body'>$mobLinksHtml</div>
  <div class='mob-foot'>
    $auth_link
  </div>
</div>
<nav class='nav'>
  <div class='nav-inner'>
    <a href='index.php' class='nav-logo'>
      <img src='../../uploads/logo/logo1.PNG' alt='logotipo petropub' style='width: 100px; height: 100px;'>
    </a>
    <div class='nav-links'>$linksHtml</div>
    <div class='nav-r'>
        $auth
        <button class='ham' onclick='openMob()'>
            <i class='fa fa-bars'></i>
        </button>
    </div>
  </div>
</nav>";
}

/* ── FOOTER HTML ── */
function pubFooter(): string { return "
<footer class='footer'>
  <div class='container'>
    <div class='footer-grid'>
      <div>
        <div class='ft-logo'>PETRO<span>PUB</span></div>
        <p class='ft-desc'>Portal académico digital de Angola. Centralizamos e disponibilizamos conteúdos científicos e técnicos do sector de petróleo e gás.</p>
      </div>
      <div class='ft-col'>
        <h4>Acervo</h4>
        <a href='category.php?cat=Artigo Científico' class='ft-link'>Artigos Científicos</a>
        <a href='category.php?cat=TCC' class='ft-link'>TCCs</a>
        <a href='category.php?cat=Dissertação' class='ft-link'>Dissertações</a>
        <a href='category.php?cat=Livro' class='ft-link'>Livros</a>
        <a href='category.php?cat=Monografia' class='ft-link'>Monografias</a>
        <a href='category.php?cat=Relatório' class='ft-link'>Relátorios</a>
      </div>
      <div class='ft-col'>
        <h4>Portal</h4>
        <a href='list-opportunities.php' class='ft-link'>Oportunidades</a>
        <a href='list-noticies.php' class='ft-link'>Notícias</a>
        <a href='about.php' class='ft-link'>Sobre o PetroPub</a>
        <a href='faq.php' class='ft-link'>FAQ</a>
      </div>
      <div class='ft-col'>
        <h4>Suporte</h4>
        <a href='contact.php' class='ft-link'>Contacto</a>
        <a href='terms.php' class='ft-link'>Termos de Uso</a>
        <a href='faq.php' class='ft-link'>Perguntas Frequentes</a>
      </div>
    </div>
    <div class='footer-bottom'>
      <span>© ".date('Y')." PetroPub — Petrochamp & Webtec Solution. Todos os direitos reservados.</span>
      <span>Luanda, Angola 🇦🇴</span>
    </div>
  </div>
</footer>
<script>
function openMob(){const o=document.getElementById('mob-ov'),d=document.getElementById('mob-drawer');o.style.display='block';setTimeout(()=>o.classList.add('open'),10);d.classList.add('open');document.body.style.overflow='hidden';}
function closeMob(){const o=document.getElementById('mob-ov'),d=document.getElementById('mob-drawer');o.classList.remove('open');d.classList.remove('open');setTimeout(()=>o.style.display='none',300);document.body.style.overflow='';}
function showToast(msg,cls=''){const t=document.getElementById('toast');if(!t)return;t.textContent=msg;t.className='toast '+(cls||'');t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3000);}
</script>"; }

/* ── PAGINATION HTML ── */
function pubPagination(array $pg, string $baseUrl): string {
    if ($pg['pages'] <= 1) return '';
    $sep = strpos($baseUrl,'?') !== false ? '&' : '?';
    $h = "<div class='pagination'>";
    $h .= "<a href='{$baseUrl}{$sep}page=".($pg['page']-1)."' class='pg-btn ".(!$pg['has_prev']?'disabled':'')."'>‹</a>";
    for ($p = 1; $p <= $pg['pages']; $p++) {
        $show = ($p===1||$p===$pg['pages']||abs($p-$pg['page'])<=1);
        if ($show) $h .= "<a href='{$baseUrl}{$sep}page=$p' class='pg-btn ".($p===$pg['page']?'on':'')."'>$p</a>";
        elseif (abs($p-$pg['page'])===2) $h .= "<span style='color:var(--tx-l);padding:0 4px'>…</span>";
    }
    $h .= "<a href='{$baseUrl}{$sep}page=".($pg['page']+1)."' class='pg-btn ".(!$pg['has_next']?'disabled':'')."'>›</a>";
    return $h."</div>";
}

function paginationHtml(array $pg, string $baseUrl): string {
    if ($pg['pages'] <= 1) return '';
    $sep = strpos($baseUrl,'?') !== false ? '&' : '?';
    $h  = "<div class='pagination'>";
    $h .= "<a href='{$baseUrl}{$sep}page=".($pg['page']-1)."' class='pg-btn ".(!$pg['has_prev']?'disabled':'')."'>‹</a>";
    for ($p = 1; $p <= $pg['pages']; $p++) {
        $show = ($p===1||$p===$pg['pages']||abs($p-$pg['page'])<=1);
        $ell  = (!$show&&abs($p-$pg['page'])===2);
        if ($show) $h .= "<a href='{$baseUrl}{$sep}page=$p' class='pg-btn ".($p===$pg['page']?'on':'')."'>$p</a>";
        elseif ($ell) $h .= "<span style='color:var(--tx-l);padding:0 4px'>…</span>";
    }
    $h .= "<a href='{$baseUrl}{$sep}page=".($pg['page']+1)."' class='pg-btn ".(!$pg['has_next']?'disabled':'')."'>›</a>";
    $h .= "</div>";
    return $h;
}
?>