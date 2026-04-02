<?php
include_once 'includes.php';

// ─── STABS: recent/popular/recommended via AJAX param ───────────
$stab = isset($_GET['stab']) ? $_GET['stab'] : 'recent';

// Base documents query
$documents_recent = $db->query("SELECT * FROM documents WHERE status = 'PUBLICADO' ORDER BY created_at DESC LIMIT 8");
$documents_popular = $db->query("SELECT * FROM documents WHERE status = 'PUBLICADO' ORDER BY read_count DESC LIMIT 8");
$documents_recommended = $db->query("SELECT * FROM documents WHERE status = 'PUBLICADO' ORDER BY review_count DESC LIMIT 8");

// choose which set based on stab
if ($stab === 'popular') {
    $documents = $documents_popular;
} elseif ($stab === 'recommended') {
    $documents = $documents_recommended;
} else {
    $stab = 'recent';
    $documents = $documents_recent;
}

$opportunities = $db->query("SELECT * FROM opportunities WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 6");
$users = $db->query("SELECT COUNT(*) as total FROM users WHERE NOT type = 'ADMIN'");
$documents_c = $db->query("SELECT COUNT(*) as total FROM documents WHERE status = 'PUBLICADO'");
$opportunities_c = $db->query("SELECT COUNT(*) as total FROM opportunities WHERE is_approved = 1");
$noticies_c = $db->query("SELECT COUNT(*) as total FROM notices");
$notices = $db->query("SELECT * FROM notices WHERE is_active=1 ORDER BY sort_order ASC, created_at DESC LIMIT 6");

$users_count = $users->fetch();
$documents_count = $documents_c->fetch();
$opportunities_count = $opportunities_c->fetch();
$notices_count = $noticies_c->fetch();

// ─── TRENDS: pulled from DB ─────────────────────────────────────
$trends_popular = $db->query("SELECT d.id, d.title, d.file_cover, d.authors, d.read_count, d.category_id, c.name as cat_name, c.icon as cat_icon, d.created_at FROM documents d LEFT JOIN categories c ON c.id=d.category_id WHERE d.status='PUBLICADO' ORDER BY d.read_count DESC LIMIT 6");
$trends_rated   = $db->query("SELECT d.id, d.title, d.file_cover, d.authors, d.review_count, d.category_id, c.name as cat_name, c.icon as cat_icon, d.created_at, COALESCE(AVG(dr.rating),0) as avg_rating FROM documents d LEFT JOIN categories c ON c.id=d.category_id LEFT JOIN document_review dr ON dr.document_id=d.id WHERE d.status='PUBLICADO' GROUP BY d.id ORDER BY avg_rating DESC, d.review_count DESC LIMIT 6");
$trends_popular_rows = $trends_popular->fetchAll();
$trends_rated_rows   = $trends_rated->fetchAll();

$review_repo = new ReviewRepository($db);

// ─── CATEGORIES from DB ─────────────────────────────────────────
$cats_query = $db->query("SELECT c.id, c.name, c.icon, c.upload_count, (SELECT COUNT(*) FROM documents d WHERE d.category_id=c.id AND d.status='PUBLICADO') as doc_count FROM categories c ORDER BY doc_count DESC");
$all_categories = $cats_query->fetchAll();

?>
<!doctype html>
<html lang="pt">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PetroPub — Portal Académico Angola</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
    <link
      href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap"
      rel="stylesheet"
    />
    <style>
      :root {
        --cr: #6b1020;
        --cr-dk: #4a0b16;
        --cr-lt: #8c1a2e;
        --cr-xl: rgba(107, 16, 32, 0.07);
        --cr-bdr: rgba(107, 16, 32, 0.14);
        --gd: #c9a84c;
        --gd-lt: #e5c97e;
        --gd-dk: #9a7828;
        --gd-bg: rgba(201, 168, 76, 0.11);
        --cream: #faf7f2;
        --warm: #fef9f3;
        --white: #fff;
        --bdr: rgba(107, 16, 32, 0.1);
        --bdr2: rgba(107, 16, 32, 0.06);
        --tx: #1a1208;
        --tx-m: #4a3728;
        --tx-l: #8a7060;
        --ok: #2d7a4f;
        --ok-bg: rgba(45, 122, 79, 0.1);
        --wn: #c47a1a;
        --wn-bg: rgba(196, 122, 26, 0.1);
        --er: #c53030;
        --er-bg: rgba(197, 48, 48, 0.1);
        --inf: #1a5c8a;
        --inf-bg: rgba(26, 92, 138, 0.1);
        --sh0: 0 1px 4px rgba(107, 16, 32, 0.07);
        --sh1: 0 3px 16px rgba(107, 16, 32, 0.1);
        --sh2: 0 8px 32px rgba(107, 16, 32, 0.13);
        --sh3: 0 24px 64px rgba(107, 16, 32, 0.18);
        --r1: 7px;
        --r2: 11px;
        --r3: 15px;
        --r4: 20px;
        --r5: 28px;
        --nav-h: 60px;
        --t: 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        --max: 1280px;
      }
      *,
      *::before,
      *::after {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }
      html {
        scroll-behavior: smooth;
        font-size: 16px;
      }
      body {
        font-family: "DM Sans", sans-serif;
        background: var(--cream);
        color: var(--tx);
        -webkit-font-smoothing: antialiased;
        overflow-x: hidden;
      }
      ::-webkit-scrollbar {
        width: 5px;
      }
      ::-webkit-scrollbar-track {
        background: var(--cream);
      }
      ::-webkit-scrollbar-thumb {
        background: var(--cr);
        border-radius: 3px;
      }
      input,
      button {
        font-family: inherit;
      }
      a {
        color: inherit;
        text-decoration: none;
      }
      img {
        display: block;
        max-width: 100%;
      }
      .container {
        max-width: var(--max);
        margin: 0 auto;
        padding: 0 clamp(14px, 4vw, 40px);
      }
      .section {
        padding: clamp(40px, 6vw, 72px) 0;
      }

      /* ════════════════════════
   UTILITY BITS
════════════════════════ */
      .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 22px;
        border-radius: var(--r2);
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        transition: all var(--t);
        white-space: nowrap;
        line-height: 1;
      }
      .btn-cr {
        background: var(--cr);
        color: #fff;
        box-shadow: 0 3px 12px rgba(107, 16, 32, 0.25);
      }
      .btn-cr:hover {
        background: var(--cr-dk);
        transform: translateY(-1px);
      }
      .btn-gd {
        background: linear-gradient(135deg, var(--gd-dk), var(--gd));
        color: var(--cr-dk);
        font-weight: 800;
        box-shadow: 0 4px 14px rgba(201, 168, 76, 0.35);
      }
      .btn-gd:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(201, 168, 76, 0.45);
      }
      .btn-gh {
        background: #fff;
        color: var(--tx-m);
        border: 1.5px solid var(--bdr);
      }
      .btn-gh:hover {
        background: var(--cr-xl);
        color: var(--cr);
        border-color: var(--cr-bdr);
      }
      .btn-outline {
        background: transparent;
        color: var(--cr);
        border: 1.5px solid var(--cr);
      }
      .btn-outline:hover {
        background: var(--cr);
        color: #fff;
      }
      .btn-sm {
        padding: 7px 16px;
        font-size: 12px;
        border-radius: var(--r1);
      }
      .badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 3px 9px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
      }
      .sec-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--cr);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
      }
      .sec-label::before {
        content: "";
        display: block;
        width: 24px;
        height: 2px;
        background: var(--cr);
        border-radius: 1px;
      }
      .sec-title {
        font-family: "Arial", serif;
        font-size: clamp(22px, 3.5vw, 34px);
        font-weight: 900;
        color: var(--tx);
        line-height: 1.25;
        margin-bottom: clamp(4px, 1vw, 8px);
      }
      .sec-sub {
        font-size: clamp(13px, 1.4vw, 15px);
        color: var(--tx-l);
        line-height: 1.6;
      }

      /* ════════════════════════
   TOP ANNOUNCE STRIP
════════════════════════ */
      .announce {
        background: var(--cr-dk);
        color: rgba(255, 255, 255, 0.85);
        font-size: 12px;
        text-align: center;
        padding: 7px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        position: relative;
        z-index: 200;
      }
      .announce strong {
        color: var(--gd-lt);
      }
      .announce-close {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.5);
        font-size: 16px;
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
        transition: color 0.18s;
      }
      .announce-close:hover {
        color: #fff;
      }

      /* ════════════════════════
   NAV
════════════════════════ */
      .nav {
        background: #fff;
        border-bottom: 1px solid var(--bdr);
        position: sticky;
        top: 0;
        z-index: 300;
        box-shadow: var(--sh0);
      }
      .nav-inner {
        display: flex;
        align-items: center;
        gap: 12px;
        height: var(--nav-h);
        max-width: var(--max);
        margin: 0 auto;
        padding: 0 clamp(14px, 4vw, 40px);
      }
      .nav-logo {
        font-family: "Arial", serif;
        font-weight: 900;
        font-size: 21px;
        color: var(--cr-dk);
        white-space: nowrap;
        flex-shrink: 0;
        display: flex;
        align-items: baseline;
        gap: 1px;
      }
      .nav-logo span {
        color: var(--gd);
      }
      .nav-logo-sub {
        font-size: 9px;
        font-weight: 700;
        color: var(--tx-l);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin-left: 6px;
        margin-bottom: 1px;
        align-self: flex-end;
      }
      .nav-links {
        display: flex;
        align-items: center;
        gap: 0;
        margin-left: clamp(14px, 3vw, 32px);
        flex: 1;
      }
      .nav-link {
        padding: 0 clamp(10px, 1.5vw, 16px);
        height: var(--nav-h);
        display: flex;
        align-items: center;
        font-size: 13px;
        font-weight: 600;
        color: var(--tx-l);
        cursor: pointer;
        border-bottom: 2.5px solid transparent;
        transition: all var(--t);
        white-space: nowrap;
      }
      .nav-link:hover,
      .nav-link.on {
        color: var(--cr);
        border-bottom-color: var(--cr);
      }
      .nav-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        margin-left: auto;
      }
      .nav-search-sm {
        width: 34px;
        height: 34px;
        border-radius: var(--r1);
        background: var(--cream);
        border: 1.5px solid var(--bdr);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 15px;
        transition: all var(--t);
        flex-shrink: 0;
      }
      .nav-search-sm:hover {
        border-color: var(--cr-bdr);
        background: var(--cr-xl);
      }
      .ham {
        width: 38px;
        height: 38px;
        border-radius: var(--r1);
        background: none;
        border: 1.5px solid var(--bdr);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        cursor: pointer;
        transition: all var(--t);
        flex-shrink: 0;
        padding: 0;
      }
      .ham:hover {
        border-color: var(--cr-bdr);
        background: var(--cr-xl);
      }
      .ham-line {
        width: 16px;
        height: 1.8px;
        background: var(--tx-m);
        border-radius: 1px;
        transition: all 0.25s;
      }
      .ham:hover .ham-line {
        background: var(--cr);
      }
      .ava-nav {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--cr-lt);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 2px solid rgba(107, 16, 32, 0.15);
        flex-shrink: 0;
      }

      /* ════════════════════════
   HAMBURGER DRAWER
════════════════════════ */
      .drawer-ov {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 500;
        opacity: 0;
        transition: opacity 0.28s;
        backdrop-filter: blur(3px);
      }
      .drawer-ov.open {
        opacity: 1;
      }
      .drawer {
        position: fixed;
        right: 0;
        top: 0;
        bottom: 0;
        width: min(320px, 88vw);
        background: #fff;
        z-index: 600;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        box-shadow: var(--sh3);
        overflow: hidden;
      }
      .drawer.open {
        transform: translateX(0);
      }
      .drawer-head {
        padding: 20px 22px 16px;
        border-bottom: 1px solid var(--bdr);
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
      .drawer-logo {
        font-family: "Arial", serif;
        font-size: 20px;
        font-weight: 900;
        color: var(--cr-dk);
      }
      .drawer-logo span {
        color: var(--gd);
      }
      .drawer-close {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--cream);
        border: 1.5px solid var(--bdr);
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--tx-m);
        transition: all var(--t);
      }
      .drawer-close:hover {
        background: var(--cr-xl);
        color: var(--cr);
        border-color: var(--cr-bdr);
      }
      .drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 8px 0;
      }
      .drawer-section {
        padding: 6px 0;
      }
      .drawer-sec-lbl {
        font-size: 10px;
        font-weight: 800;
        color: var(--tx-l);
        text-transform: uppercase;
        letter-spacing: 1.4px;
        padding: 6px 22px 4px;
        margin-top: 4px;
      }
      .drawer-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 22px;
        font-size: 14px;
        font-weight: 500;
        color: var(--tx-m);
        cursor: pointer;
        transition: background var(--t);
      }
      .drawer-item:hover {
        background: var(--cream);
        color: var(--cr);
      }
      .drawer-item.on {
        color: var(--cr);
        font-weight: 700;
        background: var(--cr-xl);
      }
      .drawer-item .di-ico {
        font-size: 16px;
        width: 20px;
        text-align: center;
        flex-shrink: 0;
      }
      .drawer-item .di-badge {
        margin-left: auto;
        background: var(--cr);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 100px;
      }
      .drawer-sep {
        height: 1px;
        background: var(--bdr);
        margin: 6px 22px;
      }
      .drawer-foot {
        padding: 16px 22px;
        border-top: 1px solid var(--bdr);
        display: flex;
        flex-direction: column;
        gap: 8px;
      }

      /* ════════════════════════
   HERO
════════════════════════ */
      .hero {
        background: linear-gradient(
          150deg,
          var(--cr-dk) 0%,
          var(--cr-lt) 40%,
          #1a2a50 72%,
          #0e1a30 100%
        );
        padding: clamp(48px, 8vw, 96px) clamp(14px, 4vw, 40px);
        position: relative;
        overflow: hidden;
      }
      /* geometric decoration */
      .hero::before {
        content: "";
        position: absolute;
        width: 500px;
        height: 500px;
        border-radius: 50%;
        background: radial-gradient(
          circle,
          rgba(201, 168, 76, 0.13) 0%,
          transparent 65%
        );
        top: -150px;
        right: -80px;
        pointer-events: none;
      }
      .hero::after {
        content: "";
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: radial-gradient(
          circle,
          rgba(26, 42, 80, 0.5) 0%,
          transparent 70%
        );
        bottom: -80px;
        left: 10%;
        pointer-events: none;
      }
      .hero-geo {
        position: absolute;
        right: clamp(20px, 8vw, 120px);
        top: 50%;
        transform: translateY(-50%);
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        opacity: 0.12;
        pointer-events: none;
      }
      .hero-geo-sq {
        width: clamp(48px, 6vw, 72px);
        height: clamp(48px, 6vw, 72px);
        border: 1.5px solid rgba(255, 255, 255, 0.8);
        border-radius: var(--r1);
      }
      .hero-inner {
        position: relative;
        z-index: 1;
        max-width: var(--max);
        margin: 0 auto;
        text-align: center;
      }
      .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 5px 14px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.8);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: clamp(16px, 2.5vw, 22px);
        animation: fadeUp 0.5s ease both;
      }
      .hero-title {
        font-family: "Arial", serif;
        font-size: clamp(28px, 5.5vw, 58px);
        font-weight: 900;
        color: #fff;
        line-height: 1.18;
        margin-bottom: clamp(12px, 2vw, 18px);
        animation: fadeUp 0.5s ease 0.08s both;
      }
      .hero-title em {
        color: var(--gd-lt);
        font-style: normal;
      }
      .hero-sub {
        font-size: clamp(14px, 1.6vw, 17px);
        color: rgba(255, 255, 255, 0.68);
        max-width: 600px;
        margin: 0 auto clamp(28px, 4vw, 44px);
        line-height: 1.65;
        animation: fadeUp 0.5s ease 0.16s both;
      }
      /* search */
      .hero-search {
        max-width: 680px;
        margin: 0 auto clamp(20px, 3vw, 30px);
        animation: fadeUp 0.5s ease 0.24s both;
      }
      .hs-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 0;
        border-radius: var(--r2) var(--r2) 0 0;
        overflow: hidden;
        width: fit-content;
      }
      .hs-tab {
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.55);
        cursor: pointer;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-bottom: none;
        transition: all 0.18s;
        margin-right: 2px;
        border-radius: var(--r1) var(--r1) 0 0;
      }
      .hs-tab.on {
        background: rgba(255, 255, 255, 0.95);
        color: var(--cr-dk);
      }
      .hs-box { position:relative;
        display: flex;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 0 var(--r2) var(--r2) var(--r2);
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
      }
      .hs-input {
        flex: 1;
        padding: clamp(13px, 2vw, 17px) clamp(14px, 2vw, 20px);
        font-size: clamp(14px, 1.5vw, 16px);
        border: none;
        outline: none;
        color: var(--tx);
        background: transparent;
      }
      .hs-input::placeholder {
        color: var(--tx-l);
      }
      .hs-btn {
        padding: 0 clamp(18px, 3vw, 28px);
        background: var(--cr);
        color: #fff;
        border: none;
        font-size: clamp(13px, 1.4vw, 15px);
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: background var(--t);
        white-space: nowrap;
        flex-shrink: 0;
      }
      .hs-btn:hover {
        background: var(--cr-dk);
      }
      .hero-hints {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
        animation: fadeUp 0.5s ease 0.32s both;
      }
      .hero-hint-lbl {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.45);
      }
      .hero-hint {
        padding: 4px 13px;
        border-radius: 100px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.16);
        font-size: 12px;
        color: rgba(255, 255, 255, 0.75);
        cursor: pointer;
        transition: all 0.18s;
      }
      .hero-hint:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
      }
      /* hero stats */
      .hero-stats {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: clamp(20px, 4vw, 48px);
        flex-wrap: wrap;
        margin-top: clamp(32px, 5vw, 52px);
        padding-top: clamp(22px, 3.5vw, 32px);
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        animation: fadeUp 0.5s ease 0.4s both;
      }
      .hs-item {
        text-align: center;
      }
      .hs-num {
        font-family: "Arial", serif;
        font-size: clamp(24px, 3.5vw, 36px);
        font-weight: 900;
        color: var(--gd-lt);
      }
      .hs-lbl {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.5);
        margin-top: 3px;
      }

      /* ════════════════════════
   QUICK CATEGORIES BAR
════════════════════════ */
      .qc-bar {
        background: #fff;
        border-bottom: 1px solid var(--bdr);
        box-shadow: var(--sh0);
      }
      .qc-inner {
        max-width: var(--max);
        margin: 0 auto;
        padding: 0 clamp(14px, 4vw, 40px);
        display: flex;
        align-items: center;
        gap: 0;
        overflow-x: auto;
        scrollbar-width: none;
        height: 50px;
      }
      .qc-inner::-webkit-scrollbar {
        display: none;
      }
      .qc-item {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 0 clamp(12px, 2vw, 20px);
        height: 100%;
        font-size: 13px;
        font-weight: 600;
        color: var(--tx-l);
        cursor: pointer;
        border-bottom: 2.5px solid transparent;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all var(--t);
      }
      .qc-item:hover,
      .qc-item.on {
        color: var(--cr);
        border-bottom-color: var(--cr);
      }
      .qc-ico {
        font-size: 15px;
      }

      /* ════════════════════════
   SECTION HEADERS
════════════════════════ */
      .sec-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: clamp(20px, 3vw, 28px);
        flex-wrap: wrap;
      }
      .see-all {
        font-size: 13px;
        font-weight: 600;
        color: var(--cr);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        transition: gap var(--t);
      }
      .see-all:hover {
        gap: 8px;
      }

      /* ════════════════════════
   TABS ROW (for sections)
════════════════════════ */
      .stabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: clamp(16px, 2vw, 22px);
      }
      .stab {
        padding: 7px 16px;
        border-radius: 100px;
        border: 1.5px solid var(--bdr);
        background: #fff;
        font-size: 12px;
        font-weight: 700;
        color: var(--tx-l);
        cursor: pointer;
        transition: all var(--t);
        white-space: nowrap;
      }
      .stab.on {
        background: var(--cr);
        color: #fff;
        border-color: var(--cr);
      }
      .stab:hover:not(.on) {
        border-color: var(--cr-bdr);
        color: var(--cr);
      }

      /* ════════════════════════
   DOCUMENT CARD
════════════════════════ */
      .docs-grid {
        display: grid;
        grid-template-columns: repeat(
          auto-fill,
          minmax(clamp(200px, 22vw, 240px), 1fr)
        );
        gap: clamp(12px, 2vw, 18px);
      }
      .doc-card {
        background: #fff;
        border-radius: var(--r3);
        border: 1px solid var(--bdr);
        overflow: hidden;
        transition:
          box-shadow var(--t),
          transform var(--t),
          border-color var(--t);
        cursor: pointer;
        animation: fadeUp 0.4s ease both;
      }
      .doc-card:hover {
        box-shadow: var(--sh2);
        transform: translateY(-3px);
        border-color: rgba(107, 16, 32, 0.18);
      }
      .dc-thumb {
        height: clamp(150px, 110vw, 300px);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(32px, 5vw, 44px);
      }

      .dc-thumb-img {
        height: 100%;
        width: 100%;
      }
      .dc-body {
        padding: clamp(12px, 1.8vw, 16px);
      }
      .dc-type {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 100px;
        margin-bottom: 8px;
      }
      .dt-tcc {
        background: var(--inf-bg);
        color: var(--inf);
      }
      .dt-art {
        background: var(--ok-bg);
        color: var(--ok);
      }
      .dt-liv {
        background: var(--gd-bg);
        color: var(--gd-dk);
      }
      .dt-dis {
        background: var(--pu-bg, rgba(90, 58, 138, 0.1));
        color: #5a3a8a;
      }
      .dt-rel {
        background: var(--wn-bg);
        color: var(--wn);
      }
      .dt-apr {
        background: var(--cr-xl);
        color: var(--cr);
      }
      .dc-title {
        font-family: "Arial", serif;
        font-size: clamp(13px, 1.4vw, 15px);
        font-weight: 700;
        color: var(--tx);
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 5px;
      }
      .dc-author {
        font-size: 12px;
        color: var(--tx-l);
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .dc-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        margin-bottom: 8px;
      }
      .dc-rating {
        font-size: 12px;
        color: var(--gd-dk);
        font-weight: 600;
      }
      .dc-price {
        font-family: "Arial", serif;
        font-size: 14px;
        font-weight: 700;
        color: var(--cr);
      }
      .dc-free {
        font-size: 12px;
        font-weight: 700;
        color: var(--ok);
      }
      .dc-btn {
        padding: 5px 13px;
        border-radius: var(--r1);
        background: var(--cr-xl);
        border: 1px solid var(--cr-bdr);
        font-size: 11px;
        font-weight: 700;
        color: var(--cr);
        cursor: pointer;
        transition: all var(--t);
        white-space: nowrap;
      }
      .dc-btn:hover {
        background: var(--cr);
        color: #fff;
      }
      /* hover overlay */
      .doc-card {
        position: relative;
      }
      .dc-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
          to bottom,
          transparent 30%,
          rgba(74, 11, 22, 0.9)
        );
        opacity: 0;
        transition: opacity 0.22s;
        display: flex;
        align-items: flex-end;
        padding: 14px;
        gap: 7px;
        flex-wrap: wrap;
      }
      .doc-card:hover .dc-overlay {
        opacity: 1;
      }
      .dco-btn {
        flex: 1;
        min-width: 100px;
        padding: 8px;
        border-radius: var(--r1);
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        text-align: center;
        transition: background var(--t);
      }
      .dco-v {
        background: #fff;
        color: var(--cr-dk);
      }
      .dco-v:hover {
        background: var(--cream);
      }
      .dco-buy {
        background: var(--gd);
        color: var(--cr-dk);
      }
      .dco-buy:hover {
        background: var(--gd-lt);
      }

      /* ════════════════════════
   CATEGORIES BLOCKS
════════════════════════ */
      .cats-grid {
        display: grid;
        grid-template-columns: repeat(
          auto-fill,
          minmax(clamp(140px, 18vw, 200px), 1fr)
        );
        gap: clamp(10px, 1.5vw, 14px);
      }
      .cat-card {
        background: #fff;
        border-radius: var(--r3);
        border: 1px solid var(--bdr);
        padding: clamp(18px, 2.5vw, 28px) clamp(14px, 2vw, 22px);
        cursor: pointer;
        transition: all var(--t);
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        position: relative;
        overflow: hidden;
      }
      .cat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        opacity: 0;
        transition: opacity var(--t);
      }
      .cat-card:hover {
        box-shadow: var(--sh2);
        transform: translateY(-2px);
        border-color: rgba(107, 16, 32, 0.18);
      }
      .cat-card:hover::before {
        opacity: 1;
      }
      .cc-ico {
        font-size: clamp(28px, 4vw, 36px);
      }
      .cc-name {
        font-family: "Arial", serif;
        font-size: clamp(14px, 1.6vw, 17px);
        font-weight: 700;
        color: var(--tx);
        line-height: 1.25;
      }
      .cc-count {
        font-size: 12px;
        color: var(--tx-l);
        font-weight: 600;
      }
      .cc-arr {
        position: absolute;
        bottom: 14px;
        right: 14px;
        font-size: 18px;
        color: var(--tx-l);
        opacity: 0;
        transition: all var(--t);
      }
      .cat-card:hover .cc-arr {
        opacity: 1;
        right: 10px;
      }

      /* ════════════════════════
   TRENDS — HORIZONTAL CARDS
════════════════════════ */
      .trends-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
      }
      .trend-item {
        background: #fff;
        border-radius: var(--r3);
        border: 1px solid var(--bdr);
        padding: clamp(12px, 1.8vw, 16px) clamp(14px, 2vw, 18px);
        display: flex;
        align-items: center;
        gap: clamp(12px, 2vw, 18px);
        cursor: pointer;
        transition: all var(--t);
      }
      .trend-item:hover {
        box-shadow: var(--sh1);
        border-color: rgba(107, 16, 32, 0.18);
        transform: translateX(4px);
      }
      .ti-rank {
        font-family: "Arial", serif;
        font-size: clamp(20px, 3vw, 26px);
        font-weight: 900;
        color: var(--cr-xl);
        min-width: 32px;
        text-align: center;
        flex-shrink: 0;
      }
      .ti-rank.top {
        color: var(--gd-dk);
      }
      .ti-thumb {
        width: clamp(44px, 6vw, 54px);
        height: clamp(44px, 6vw, 54px);
        border-radius: var(--r2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(20px, 3vw, 26px);
        flex-shrink: 0;
      }
      .ti-body {
        flex: 1;
        min-width: 0;
      }
      .ti-title {
        font-size: clamp(13px, 1.4vw, 14px);
        font-weight: 700;
        color: var(--tx);
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 4px;
      }
      .ti-meta {
        font-size: 12px;
        color: var(--tx-l);
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
      }
      .ti-right {
        flex-shrink: 0;
        text-align: right;
      }
      .ti-stat {
        font-family: "Arial", serif;
        font-size: clamp(15px, 2vw, 18px);
        font-weight: 700;
        color: var(--cr);
      }
      .ti-stat-lbl {
        font-size: 10px;
        color: var(--tx-l);
      }

      .tendency-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: clamp(20px, 3vw, 36px);
      }

      /* ════════════════════════
   OPPORTUNITIES SECTION
════════════════════════ */
      .opp-section {
        background: linear-gradient(to bottom, #fff, var(--cream));
      }
      .opp-grid {
        display: grid;
        grid-template-columns: repeat(
          auto-fill,
          minmax(clamp(240px, 28vw, 300px), 1fr)
        );
        gap: clamp(12px, 2vw, 18px);
      }
      .opp-card {
        background: #fff;
        border-radius: var(--r3);
        border: 1px solid var(--bdr);
        overflow: hidden;
        cursor: pointer;
        transition: all var(--t);
      }
      .opp-card:hover {
        box-shadow: var(--sh2);
        transform: translateY(-3px);
        border-color: rgba(107, 16, 32, 0.18);
      }
      .oc-banner {
        height: clamp(70px, 10vw, 100px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(28px, 4vw, 42px);
        position: relative;
        overflow: hidden;
      }
      .oc-banner::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.08), transparent);
      }
      .oc-body {
        padding: clamp(14px, 2vw, 18px);
      }
      .oc-type {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 100px;
        margin-bottom: 8px;
      }
      .ot-curso {
        background: var(--inf-bg);
        color: var(--inf);
      }
      .ot-equip {
        background: var(--gd-bg);
        color: var(--gd-dk);
      }
      .ot-evento {
        background: var(--ok-bg);
        color: var(--ok);
      }
      .ot-vaga {
        background: var(--er-bg);
        color: var(--er);
      }
      .oc-title {
        font-family: "Arial", serif;
        font-size: clamp(13px, 1.5vw, 15px);
        font-weight: 700;
        color: var(--tx);
        line-height: 1.35;
        margin-bottom: 5px;
      }
      .oc-desc {
        font-size: 12px;
        color: var(--tx-l);
        line-height: 1.5;
        margin-bottom: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      .oc-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
      }
      .oc-src {
        font-size: 11px;
        color: var(--tx-l);
      }

      /* ════════════════════════
   QUICK ACTIONS SECTION
════════════════════════ */
      .qa-section {
        background: var(--cr-dk);
        position: relative;
        overflow: hidden;
      }
      .qa-section::before {
        content: "";
        position: absolute;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: radial-gradient(
          circle,
          rgba(201, 168, 76, 0.1) 0%,
          transparent 70%
        );
        top: -100px;
        right: -60px;
        pointer-events: none;
      }
      .qa-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: clamp(20px, 4vw, 48px);
        align-items: center;
        flex-wrap: wrap;
      }
      .qa-body .sec-label {
        color: var(--gd-lt);
      }
      .qa-body .sec-label::before {
        background: var(--gd);
      }
      .qa-title {
        font-family: "Arial", serif;
        font-size: clamp(22px, 3.5vw, 34px);
        font-weight: 900;
        color: #fff;
        line-height: 1.3;
        margin-bottom: clamp(8px, 1.5vw, 14px);
      }
      .qa-sub {
        font-size: clamp(13px, 1.4vw, 15px);
        color: rgba(255, 255, 255, 0.6);
        line-height: 1.65;
        margin-bottom: clamp(16px, 2.5vw, 24px);
      }
      .qa-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }
      .qa-features {
        display: flex;
        flex-direction: column;
        gap: 12px;
        flex-shrink: 0;
      }
      .qaf-item {
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255, 255, 255, 0.75);
        font-size: 13px;
      }
      .qaf-ico {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
      }

      /* ════════════════════════
   NOTICES SECTION
════════════════════════ */
      .notices-grid {
        display: grid;
        grid-template-columns: repeat(
          auto-fill,
          minmax(clamp(260px, 30vw, 340px), 1fr)
        );
        gap: clamp(12px, 2vw, 16px);
      }
      .notice-card {
        background: #fff;
        border-radius: var(--r3);
        border: 1px solid var(--bdr);
        padding: clamp(14px, 2vw, 20px);
        display: flex;
        gap: 13px;
        transition: box-shadow var(--t);
      }
      .notice-card:hover {
        box-shadow: var(--sh1);
      }
      .nc-ico {
        font-size: 22px;
        flex-shrink: 0;
        margin-top: 2px;
      }
      .nc-body {
        flex: 1;
        min-width: 0;
      }
      .nc-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--tx);
        margin-bottom: 4px;
        line-height: 1.35;
      }
      .nc-desc {
        font-size: 12px;
        color: var(--tx-l);
        line-height: 1.55;
        margin-bottom: 8px;
      }
      .nc-date {
        font-size: 11px;
        color: var(--tx-l);
      }

      /* ════════════════════════
   FOOTER
════════════════════════ */
      .footer {
        background: var(--cr-dk);
        color: rgba(255, 255, 255, 0.7);
        padding: clamp(40px, 6vw, 64px) 0 clamp(20px, 3vw, 32px);
      }
      .footer-grid {
        display: grid;
        grid-template-columns: 2fr repeat(3, 1fr);
        gap: clamp(24px, 4vw, 48px);
        margin-bottom: clamp(28px, 4vw, 44px);
      }
      .ft-brand .fb-logo {
        font-family: "Arial", serif;
        font-size: 22px;
        font-weight: 900;
        color: #fff;
        margin-bottom: 10px;
      }
      .ft-brand .fb-logo span {
        color: var(--gd-lt);
      }
      .ft-brand p {
        font-size: 13px;
        line-height: 1.65;
        color: rgba(255, 255, 255, 0.55);
        margin-bottom: 14px;
      }
      .ft-col h4 {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: rgba(255, 255, 255, 0.35);
        margin-bottom: 14px;
      }
      .ft-link {
        display: block;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.58);
        margin-bottom: 10px;
        cursor: pointer;
        transition: color var(--t);
      }
      .ft-link:hover {
        color: var(--gd-lt);
      }
      .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: clamp(16px, 2.5vw, 22px);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.35);
      }
      .footer-bottom a {
        color: rgba(255, 255, 255, 0.45);
      }

      /* LOGIN PROMPT */
      .login-prompt {
        position: fixed;
        bottom: clamp(16px, 3vw, 24px);
        left: 50%;
        transform: translateX(-50%);
        z-index: 400;
        background: #fff;
        border-radius: var(--r4);
        box-shadow: var(--sh3);
        border: 1px solid var(--bdr);
        padding: clamp(16px, 2vw, 20px) clamp(20px, 3vw, 28px);
        display: none;
        align-items: center;
        gap: clamp(12px, 2vw, 18px);
        max-width: min(520px, 90vw);
        width: 100%;
        animation: slideUp 0.3s cubic-bezier(0.22, 1, 0.36, 1);
      }
      .login-prompt.show {
        display: flex;
      }
      @keyframes slideUp {
        from {
          opacity: 0;
          transform: translateX(-50%) translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateX(-50%) translateY(0);
        }
      }
      .lp-ico {
        font-size: 28px;
        flex-shrink: 0;
      }
      .lp-body {
        flex: 1;
        min-width: 0;
      }
      .lp-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--tx);
        margin-bottom: 2px;
      }
      .lp-sub {
        font-size: 12px;
        color: var(--tx-l);
      }
      .lp-actions {
        display: flex;
        gap: 7px;
        flex-shrink: 0;
        flex-wrap: wrap;
      }
      .lp-close {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--cream);
        border: 1px solid var(--bdr);
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--tx-l);
        flex-shrink: 0;
        transition: all var(--t);
      }
      .lp-close:hover {
        background: var(--cr-xl);
        color: var(--cr);
      }

      /* TOAST */
      .toast {
        position: fixed;
        bottom: clamp(14px, 3vw, 22px);
        right: clamp(14px, 3vw, 22px);
        z-index: 9999;
        transform: translateY(30px);
        background: var(--cr-dk);
        color: #fff;
        padding: 11px 18px;
        border-radius: var(--r3);
        font-size: 13px;
        font-weight: 500;
        box-shadow: var(--sh3);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        max-width: 300px;
        line-height: 1.4;
        border: 1px solid rgba(201, 168, 76, 0.2);
      }
      .toast.show {
        opacity: 1;
        transform: translateY(0);
      }

      @keyframes fadeUp {
        from {
          opacity: 0;
          transform: translateY(16px);
        }
        to {
          opacity: 1;
          transform: none;
        }
      }

      /* ════════════════════════
   RESPONSIVE
════════════════════════ */
      @media (max-width: 960px) {
        .dc-thumb {
          height: clamp(140px, 120vw, 200px);
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: clamp(32px, 5vw, 44px);
        }
        .nav-links {
          display: none;
        }
        .footer-grid {
          grid-template-columns: 1fr 1fr;
        }
        .qa-inner {
          grid-template-columns: 1fr;
        }
        .qa-features {
          flex-direction: row;
          flex-wrap: wrap;
        }
        .tendency-grid {
          grid-template-columns: 1fr;
        }
      }
      @media (max-width: 640px) {
        .dc-thumb {
          height: clamp(120px, 100vw, 190px);
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: clamp(32px, 5vw, 44px);
        }
        .footer-grid {
          grid-template-columns: 1fr;
        }
        .docs-grid {
          grid-template-columns: repeat(2, 1fr);
        }
        .cats-grid {
          grid-template-columns: repeat(3, 1fr);
        }
        .opp-grid {
          grid-template-columns: 1fr;
        }
        .tendency-grid {
          grid-template-columns: 1fr;
        }
      }
      @media (max-width: 480px) {
        .dc-thumb {
          height: clamp(110px, 90vw, 150px);
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: clamp(32px, 5vw, 44px);
        }
        .cats-grid {
          grid-template-columns: repeat(2, 1fr);
        }
        .docs-grid {
          grid-template-columns: repeat(2, 1fr);
        }
        .hero-stats {
          gap: 18px;
        }
        .hs-tabs {
          display: none;
        }
        .tendency-grid {
          grid-template-columns: 1fr;
        }
      }
    
      /* ════════════════════════
   TREND TABS & CAROUSEL
════════════════════════ */
      .trend-tab {
        padding: 11px 22px;
        border: none;
        background: none;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: var(--tx-l);
        cursor: pointer;
        border-bottom: 2.5px solid transparent;
        margin-bottom: -2px;
        transition: all var(--t);
        white-space: nowrap;
      }
      .trend-tab:hover { color: var(--cr); }
      .trend-tab.on { color: var(--cr); border-bottom-color: var(--cr); font-weight: 700; }

      .trend-carousel {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(clamp(220px, 28vw, 300px), 1fr));
        gap: clamp(12px, 2vw, 18px);
        overflow: visible;
      }
      .trend-card {
        background: #fff;
        border-radius: var(--r3);
        border: 1px solid var(--bdr);
        overflow: hidden;
        transition: box-shadow var(--t), transform var(--t);
        cursor: pointer;
        animation: fadeUp 0.4s ease both;
      }
      .trend-card:hover { box-shadow: var(--sh2); transform: translateY(-3px); border-color: rgba(107,16,32,.18); }
      .tc-thumb {
        height: clamp(150px, 110vw, 300px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(28px, 4vw, 38px);
      }
      .tc-thumb-img {
        height: 100%;
        width: 100%;
      }
      .tc-body { padding: clamp(12px, 1.8vw, 16px); }
      .tc-type {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 100px;
        background: var(--inf-bg);
        color: var(--inf);
        display: inline-block;
        margin-bottom: 8px;
      }
      .tc-title {
        font-family: 'Arial', serif;
        font-size: clamp(13px, 1.4vw, 14px);
        font-weight: 700;
        color: var(--tx);
        line-height: 1.35;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      .tc-meta { margin-bottom: 8px; }
      .tc-author { font-size: 12px; color: var(--tx-l); }
      .tc-stat { margin-bottom: 12px; }
      .tc-views { font-size: 12px; color: var(--tx-l); font-weight: 600; }
      .tc-btn {
        width: 100%;
        text-align: center;
        display: inline-block;
        padding: 7px 16px;
        border-radius: var(--r2);
        background: var(--cr);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: background var(--t);
      }
      .tc-btn:hover { background: var(--cr-dk); }

      /* ════ NOTICE CARD: enhanced ════ */
      .notice-card {
        background: #fff;
        border-radius: var(--r3);
        border: 1px solid var(--bdr);
        padding: clamp(14px, 2vw, 20px) clamp(16px, 2.5vw, 22px);
        display: flex;
        align-items: flex-start;
        gap: 14px;
        box-shadow: var(--sh0);
        transition: box-shadow var(--t), transform var(--t);
        animation: fadeUp 0.4s ease both;
      }
      .notice-card:hover { box-shadow: var(--sh1); }
      .nc-ico { font-size: clamp(22px, 3vw, 28px); flex-shrink: 0; margin-top: 2px; }

      /* ════ SEARCH SUGGESTIONS ════ */
      .search-suggestion {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        cursor: pointer;
        transition: background 0.15s;
        border-bottom: 1px solid var(--bdr2);
        font-size: 13px;
        color: var(--tx-m);
      }
      .search-suggestion:last-child { border-bottom: none; }
      .search-suggestion:hover { background: var(--cream); color: var(--cr); }
      .ss-ico { font-size: 16px; flex-shrink: 0; }
      .ss-title { flex: 1; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
      .ss-type { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 100px; background: var(--inf-bg); color: var(--inf); flex-shrink: 0; }

      @media (max-width: 640px) {
        .trend-carousel { grid-template-columns: repeat(2, 1fr); }
      }
      @media (max-width: 400px) {
        .trend-carousel { grid-template-columns: 1fr; }
      }

    </style>
  </head>
  <body>
    <div class="toast" id="toast"></div>

    <!-- LOGIN PROMPT -->
    <div class="login-prompt" id="login-prompt">
      <div class="lp-ico">🔐</div>
      <div class="lp-body">
        <div class="lp-title" id="lp-title">Inicie sessão para continuar</div>
        <div class="lp-sub" id="lp-sub">
          Crie uma conta gratuita para aceder ao conteúdo completo
        </div>
      </div>
      <div class="lp-actions">
        <a href="auth.php" class="btn btn-gh btn-sm">
          Registar
        </a>
        <a href="auth.php" class="btn btn-cr btn-sm">
          Entrar
        </a>
      </div>
      <button class="lp-close" onclick="closePrompt()">✕</button>
    </div>

    <!-- DRAWER OVERLAY -->
    <div class="drawer-ov" id="dr-ov" onclick="closeDrawer()"></div>
    <div class="drawer" id="drawer">
      <div class="drawer-head">
        <div class="drawer-logo">
          <img src="../../uploads/logo/logo1.PNG" alt="logotipo petropub" style="width: 100px; height: 100px;">
        </div>
        <button class="drawer-close" onclick="closeDrawer()">✕</button>
      </div>
      <div class="drawer-body">
        <div class="drawer-section">
          <div class="drawer-sec-lbl">Navegar</div>
          <a href="index.php" class="drawer-item on">
            <span class="di-ico"><i class="fa fa-home"></i></span> Home
          </a>
          <a href="library.php" class="drawer-item">
            <span class="di-ico"><i class="fa fa-book"></i></span> Biblioteca
          </a>
          <a href="list-opportunities.php" class="drawer-item">
            <span class="di-ico"><i class="fa fa-users"></i></span> Oportunidades
          </a>
          <a href="list-noticies.php" class="drawer-item">
            <span class="di-ico"><i class="fa fa-info"></i></span> Notícias
          </a>
          <a href="about.php" class="drawer-item">
            <span class="di-ico"><i class="fa fa-list"></i></span> Sobre
          </a>
          <a href="contact.php" class="drawer-item">
            <span class="di-ico"><i class="fa fa-phone"></i></span> Contacto
          </a>
        </div>
      </div>
      <div class="drawer-foot">
        <?php
        if (isset($_SESSION['jwt_auth'])) {
        ?>
          <a href="my-documents.php"
            class="btn btn-cr"
            style="width: 100%; justify-content: center"
          >
            <?=$_SESSION['user_name']?>
        </a>
          <a href="uplod-document.php"
            class="btn btn-gh"
            style="width: 100%; justify-content: center"
          >
            Submeter Artigo
        </a>
        <?php
        } else {
        ?>
            <a href="auth.php"
              class="btn btn-cr"
              style="width: 100%; justify-content: center"
            >
              🔑 Entrar na conta
            </a>
            <a href="auth.php"
              class="btn btn-gh"
              style="width: 100%; justify-content: center"
              onclick="goAuth('register')"
            >
              ✨ Criar conta gratuita
            </a>
        <?php
        }
        ?>
      </div>
    </div>

    <!-- ANNOUNCE STRIP -->
    <div class="announce" id="announce">
      ✨ <strong>Novo:</strong> Mais de <?=$documents_count['total']-1?>+ documentos académicos disponíveis
      — acesso gratuito a conteúdo digital
      <button
        class="announce-close"
        onclick="document.getElementById('announce').style.display = 'none'"
      >
        ✕
      </button>
    </div>

    <!-- NAV -->
    <nav class="nav">
      <div class="nav-inner">
        <div
          class="nav-logo"
          onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        >
          <img src="../../uploads/logo/logo1.PNG" alt="logotipo petropub" style="width: 100px; height: 100px;">
        </div>
        <div class="nav-links">
          <a href="index.php" class="nav-link on">Home</a>
          <a href="library.php" class="nav-link">Biblioteca</a>
          <a href="list-noticies.php" class="nav-link">
            Notícias
          </a>
          <a href="list-opportunities.php" class="nav-link"> Oil & Gas </a>
          <a href="about.php" class="nav-link">Sobre</a>
          <a href="contact.php" class="nav-link">Contacto</a>
        </div>
        <div class="nav-right">
          <?php
          if (isset($_SESSION['jwt_auth'])) {
           echo '<a href="my-documents.php" class="btn btn-cr btn-sm">
                  <i class="fa fa-user"></i>
                  '.getInitials($_SESSION['user_name']).'
                </a>';
          } else {
            echo '
              <a href="auth.php" class="btn btn-cr btn-sm">
                Entrar
              </a>
            ';
          }
          
          ?>
          <div class="ham" onclick="openDrawer()" title="Menu"> 
            <i class="fa fa-bars"></i>
          </div>
        </div>
      </div>
    </nav>

    <!-- HERO -->
    <section class="hero" id="hero">
      <div class="hero-geo">
        <div class="hero-geo-sq"></div>
        <div class="hero-geo-sq" style="margin-top: 20px"></div>
        <div class="hero-geo-sq" style="margin-top: 10px"></div>
        <div class="hero-geo-sq"></div>
      </div>
      <div class="hero-inner">
        <div class="hero-eyebrow">Portal Académico de Angola</div>
        <h1 class="hero-title" style="text-transform: uppercase;">
          Conhecimento académico para o ramo de <br /><em>Oil & Gas</em>
        </h1>
        <!-- <p class="hero-sub">
          TCCs, dissertações, artigos científicos, livros físicos e relatórios técnicos
          das principais universidades angolanas. Explore gratuitamente.
        </p> -->
        <div class="hero-search">
          <div class="hs-tabs" id="hs-tabs">
            <div class="hs-tab on" data-type="" onclick="setHsTab(this)">Tudo</div>
            <div class="hs-tab" data-type="Artigos Científicos" onclick="setHsTab(this)">Artigos</div>
            <div class="hs-tab" data-type="TCC" onclick="setHsTab(this)">TCCs</div>
            <div class="hs-tab" data-type="Livro" onclick="setHsTab(this)">Livros</div>
            <div class="hs-tab" data-type="Dissertação" onclick="setHsTab(this)">Dissertações</div>
            <div class="hs-tab" data-type="Apresentação" onclick="setHsTab(this)">Apresentações</div>
            <div class="hs-tab" data-type="Monografia" onclick="setHsTab(this)">Monografias</div>
          </div>
          <form class="hs-box" id="hero-search-form" action="search-results.php" method="GET" style="display:flex;gap:0;">
            <input type="hidden" name="tipo" id="search-type-input" value="">
            <input
              class="hs-input"
              id="hero-search-input"
              name="q"
              type="text"
              placeholder="Pesquisar artigos, TCCs, livros…"
              autocomplete="off"
              onkeydown="if(event.key==='Enter'){event.preventDefault();doSearch();}"
              oninput="showSuggestions(this.value)"
            />
            <button type="button" class="hs-btn" onclick="doSearch()"><i class="fa fa-search"></i> Pesquisar</button>
            <!-- AUTOCOMPLETE DROPDOWN -->
            <div id="search-suggestions" style="position:absolute;top:100%;left:0;right:0;background:#fff;border-radius:0 0 var(--r3) var(--r3);box-shadow:var(--sh2);display:none;z-index:100;max-height:280px;overflow-y:auto;border:1px solid var(--bdr);border-top:none"></div>
          </form>
        </div>
        <div class="hero-stats">
          <div class="hs-item">
            <div class="hs-num"><?=$documents_count['total']-1?>+</div>
            <div class="hs-lbl">Documentos</div>
          </div>
          <div class="hs-item">
            <div class="hs-num"><?=$opportunities_count['total']-1?>+</div>
            <div class="hs-lbl">Oportunidades</div>
          </div>
          <div class="hs-item">
            <div class="hs-num"><?=$users_count['total']-1?>+</div>
            <div class="hs-lbl">Utilizadores</div>
          </div>
          <div class="hs-item">
            <div class="hs-num"><?=$notices_count['total']-1?>+</div>
            <div class="hs-lbl">Novidades</div>
          </div>
        </div>
      </div>
    </section>

    <!-- QUICK CATEGORIES BAR -->
    <div class="qc-bar">
      <div class="qc-inner">
        <a href="library.php" class="qc-item on" style="text-decoration:none">
          Todos
        </a>
        <?php
        $qc_cats = [
          ['name'=>'Artigo Científico'],
          ['name'=>'TCC'                 ],
          ['name'=>'Dissertação'],
          ['name'=>'Livro'],
          ['name'=>'Relatório'],
          ['name'=>'Apresentação'],
          ['name'=>'Monografia'],
          ['name'=>'Tese de Doutoramento'],
        ];
        foreach($qc_cats as $qc):
        ?>
        <a href="search-results.php?tipo=<?=urlencode($qc['name'])?>" class="qc-item" style="text-decoration:none">
          </span> <?=$qc['name']?>
        </a>
        <?php endforeach; ?>
        <!-- <a href="list-opportunities.php" class="qc-item" style="text-decoration:none">
          <span class="qc-ico">⛽</span> Oil & Gas
        </a> -->
      </div>
    </div>

    <!-- ══ SECTION 1: CONTEÚDO EM DESTAQUE ══ -->
    <section class="section" style="background: #fff">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label">Conteúdo em Destaque</div>
            <div class="sec-title">O melhor do acervo académico</div>
          </div>
          <a href="library.php" class="see-all">Ver todos →</a>
        </div>
        <div class="stabs">
          <a href="?stab=recent" class="stab <?php echo $stab==='recent'?'on':''; ?>" style="text-decoration:none">
            Adicionados recentemente
          </a>
          <a href="?stab=popular" class="stab <?php echo $stab==='popular'?'on':''; ?>" style="text-decoration:none">
            Mais populares
          </a>
          <a href="?stab=recommended" class="stab <?php echo $stab==='recommended'?'on':''; ?>" style="text-decoration:none">
            Recomendados
          </a>
        </div>
        <!-- <div class="docs-grid" id="featured-grid"> -->
        <div class="docs-grid">
          <?php
          if (!empty($documents)) {
            foreach ($documents as $document) {

              $result = $reviewService->getReviewsByDocument($document['id']);
              $reviews = $result['reviews'];
              $review_count = $result['count'];

              $review_stat = calcularMediaAvaliacoes($reviews);
          ?>
              <div class="doc-card" style="animation-delay:${(i*.05).toFixed(2)}s">
                <div class="dc-thumb" style="background:hsl(200,55%,92%)">
                    <img class="dc-thumb-img" src="../../uploads/documents/cover/<?=$document['file_cover']?>">
                </div>
                <div class="dc-overlay">
                  <?php
                  if (isset($_SESSION['jwt_auth'])) {
                    echo '<a href="detail-doc.php?id='.$document['id'].'" class="dco-btn dco-v"><i class="fa fa-page"></i> Ver Detalhes</a>';
                  } else {
                  ?>
                    <button class="dco-btn dco-v" onclick="event.stopPropagation();goDoc()"><i class="fa fa-page"></i> Ver Detalhes</button>
                    <!-- <button class="dco-btn dco-buy" onclick="event.stopPropagation();goCheckout()"><i class="fa fa-money"></i> Ler</button> -->
                  <?php
                  }
                  ?>
                </div>
                <div class="dc-body">
                  <span class="dc-type ${d.tcls}"><?=$document['category_id']?></span>
                  <div class="dc-title"><?=$document['title']?></div>
                  <div class="dc-author">
                    <?php
                      $authors = json_decode($document['authors']);
                      $authors_list = explode(",", $authors);
                      echo arrayForString($authors_list);
                      ?>
                  </div>
                  <div>
                    <div class="dc-footer">
                      <span class="dc-rating"><?=$review_stat['stars']?> <?=$review_stat['media']?></span>
                      <span class="dc-free">
                        <?=$price = ($document['download_link'] == 'fisico') ? 'Fisico' : 'Digital'?>
                      </span>
                    </div>
                    <?php
                      $price = ($document['download_link'] == 'fisico') ?
                        '<span class="dc-price">
                        '.number_format(($document['price']),2,',','.').' Kz
                      </span>' : '';
                      echo $price;
                    ?>
                  </div>
                </div>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>
    </section>

    <!-- ══ SECTION 2: CATEGORIAS ══ -->
    <section class="section" style="background: var(--cream)">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label">Categorias</div>
            <div class="sec-title">Explore por área do conhecimento</div>
            <div class="sec-sub">Organizado para facilitar a sua pesquisa académica</div>
          </div>
          <a href="library.php" class="see-all">Ver todas →</a>
        </div>
        <div class="cats-grid" id="cats-grid">
          <?php
          $cat_grads = [
            'linear-gradient(135deg,#f0fff4,#c6f6d5)',
            'linear-gradient(135deg,#ebf8ff,#bee3f8)',
            'linear-gradient(135deg,#faf5ff,#e9d8fd)',
            'linear-gradient(135deg,#fffbeb,#fef3c7)',
            'linear-gradient(135deg,var(--cream),#fce4e9)',
            'linear-gradient(135deg,#fffaf0,#feebc8)',
            'linear-gradient(135deg,#f0f9ff,#e0f2fe)',
            'linear-gradient(135deg,#f7fee7,#ecfccb)',
            'linear-gradient(135deg,#fdf4ff,#fae8ff)',
          ];
          $cat_colors = ['#2D7A4F','#1A5C8A','#5A3A8A','#9A7828','#6B1020','#C47A1A','#1A5C8A','#2D7A4F','#5A3A8A'];
          foreach ($all_categories as $ci => $cat):
            $grad  = $cat_grads[$ci % count($cat_grads)];
            $color = $cat_colors[$ci % count($cat_colors)];
            $cnt   = (int)$cat['doc_count'];
            $icon  = !empty($cat['icon']) ? $cat['icon'] : '📂';
            $cats_c = $db->query("SELECT COUNT(*) as doc_counter FROM documents d WHERE d.category_id='".$cat['name']."' AND d.status='PUBLICADO'");
            $cats_cc = $cats_c->fetch();
          ?>
          <a class="cat-card" style="animation-delay:<?=number_format($ci*.06,2)?>s;background:<?=$grad?>;text-decoration:none" href="search-results.php?tipo=<?=urlencode($cat['name'])?>">
            <div style="width:3px;height:100%;position:absolute;left:0;top:0;background:<?=$color?>;border-radius:3px 0 0 3px;opacity:.5"></div>
            <div class="cc-name"><?=htmlspecialchars($cat['name'])?></div>
            <div class="cc-count"><?=$cats_cc['doc_counter']?> documento<?=$cats_cc['doc_counter']!=1?'s':''?></div>
            <div class="cc-arr">→</div>
          </a>
          <?php endforeach; ?>
          <?php if(empty($all_categories)): ?>
          <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--tx-l)">Nenhuma categoria registada.</div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- ══ SECTION 3: OPORTUNIDADES OIL & GAS ══ -->
    <section class="section opp-section">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label">⛽ Oil & Gas Angola</div>
            <div class="sec-title">Oportunidades & Recursos</div>
            <div class="sec-sub">
              Cursos, equipamentos, eventos e vagas do sector petrolífero
              angolano
            </div>
          </div>
          <a href="list-opportunities.php"
            class="see-all"
            >Ver todos →</a
          >
        </div>
        <!-- <div class="opp-grid" id="opp-grid"> -->
        <div class="opp-grid">
          <?php
          if (!empty($opportunities)) {
            foreach ($opportunities as $opportunity) {
          ?>
              <div class="opp-card" style="animation-delay:0.06s">
                <div class="oc-body">
                  <span class="oc-type "><?=$opportunity['type']?></span>
                  <div class="oc-title"><?=$opportunity['title']?></div>
                  <div class="oc-desc"><?=$opportunity['description']?></div>
                  <div class="oc-footer">
                    <span class="oc-src"><i class="fa fa-home"></i> <?=$opportunity['location']?></span>
                    <a href="detail-opportunity.php?id=<?=$opportunity['id']?>" class="btn btn-cr btn-sm">Saiba mais →</a>
                  </div>
                </div>
              </div>
          <?php
            }
          } else {
            echo 'Nenhuma oportunidade registada até ao momento.';
          }
          ?>
        </div>
      </div>
    </section>

    <!-- ══ SECTION 4: EM ALTA NA COMUNIDADE ══ -->
    <section class="section" id="sec-tendencias" style="background: #fff">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label">Em Alta na Comunidade</div>
            <div class="sec-title">O que todos estão a ler e avaliar</div>
          </div>
          <a href="library.php" class="see-all">Ver biblioteca →</a>
        </div>

        <!-- TREND TABS -->
        <div style="display:flex;gap:8px;margin-bottom:clamp(18px,2.5vw,24px);border-bottom:2px solid var(--bdr);padding-bottom:0">
          <button class="trend-tab on" id="ttab-popular" onclick="switchTrendTab('popular',this)">
            Mais Populares
          </button>
          <button class="trend-tab" id="ttab-rated" onclick="switchTrendTab('rated',this)">
            Mais Bem Avaliados
          </button>
        </div>

        <!-- CARROSSEL: MAIS POPULARES -->
        <div class="trend-panel" id="tpanel-popular">
          <div class="trend-carousel" id="carousel-popular">
            <?php
            $bgPalette=['hsl(200,55%,92%)','hsl(140,45%,92%)','hsl(220,55%,92%)','hsl(40,55%,92%)','hsl(0,45%,92%)','hsl(280,45%,92%)'];
            foreach($trends_popular_rows as $i => $t):
              $bg = $bgPalette[$i % count($bgPalette)];
              $ico = !empty($t['cat_icon']) ? $t['cat_icon'] : '📄';
              $catName = !empty($t['category_id']) ? $t['category_id'] : 'Documento';
              $year = $t['created_at'] ? date('Y', strtotime($t['created_at'])) : '—';

              $authors = json_decode($t['authors']);
              $authors_list = explode(",", $authors);

            ?>
            <div class="trend-card" style="animation-delay:<?=$i*.06?>s">
              <div class="tc-thumb" style="background:<?=$bg?>">
                <img class="tc-thumb-img" src="../../uploads/documents/cover/<?=$t['file_cover']?>">
              </div>
              <div class="tc-body">
                <span class="tc-type"><?=htmlspecialchars($catName)?></span>
                <div class="tc-title"><?=htmlspecialchars(mb_substr($t['title'],0,70)).(mb_strlen($t['title'])>70?'…':'')?></div>
                <div class="tc-meta">
                  <span class="tc-author"><?=arrayForString($authors_list)?></span>
                </div>
                <div class="tc-stat">
                  <span class="tc-views"><?=number_format($t['read_count'] ?? 0)?> visualizações</span>
                </div>
                <?php
                  $price = ($document['download_link'] == 'fisico') ?
                    '<span class="dc-price" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                      <span> '.number_format(($document['price']),2,',','.').' Kz </span>
                      <span>Físico</span>
                  </span>' : 'Digital';
                  echo $price;
                ?>
                <a style="margin-top: 10px;" href="detail-doc.php?id=<?=$t['id']?>" class="tc-btn">Ver Detalhes →</a>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($trends_popular_rows)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--tx-l)">
              <div style="font-size:44px;opacity:.18;margin-bottom:12px">📥</div>
              <div style="font-size:15px;font-weight:600">Ainda não há dados suficientes</div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- CARROSSEL: MAIS BEM AVALIADOS -->
        <div class="trend-panel" id="tpanel-rated" style="display:none">
          <div class="trend-carousel" id="carousel-rated">
            <?php foreach($trends_rated_rows as $i => $t):
              $bg = $bgPalette[$i % count($bgPalette)];
              $ico = !empty($t['cat_icon']) ? $t['cat_icon'] : '📄';
              $catName = !empty($t['cat_name']) ? $t['cat_name'] : 'Documento';
              $rating = round((float)($t['avg_rating'] ?? 0), 1);
              $stars = str_repeat('★', min(5,max(0,(int)round($rating)))) . str_repeat('☆', 5-min(5,max(0,(int)round($rating))));
              
              $authors = json_decode($t['authors']);
              $authors_list = explode(",", $authors);
            ?>
            <div class="trend-card" style="animation-delay:<?=$i*.06?>s">
              <div class="tc-thumb" style="background:<?=$bg?>">
                <img class="tc-thumb-img" src="../../uploads/documents/cover/<?=$t['file_cover']?>">
              </div>
              <div class="tc-body">
                <span class="tc-type"><?=htmlspecialchars($catName)?></span>
                <div class="tc-title"><?=htmlspecialchars(mb_substr($t['title'],0,70)).(mb_strlen($t['title'])>70?'…':'')?></div>
                <div class="tc-meta">
                  <span class="tc-author"><?=arrayForString($authors_list)?></span>
                </div>
                <div class="tc-stat">
                  <span class="tc-rating" style="color:var(--gd-dk);font-weight:700"><?=$stars?> <?=$rating > 0 ? number_format($rating,1) : '0'?></span>
                </div>
                <?php
                  $price = ($document['download_link'] == 'fisico') ?
                    '<span class="dc-price" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                      <span> '.number_format(($document['price']),2,',','.').' Kz </span>
                      <span>Físico</span>
                  </span>' : 'Digital';
                  echo $price;
                ?>
                <a href="detail-doc.php?id=<?=$t['id']?>" class="tc-btn">Ver Detalhes →</a>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($trends_rated_rows)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--tx-l)">
              <div style="font-size:44px;opacity:.18;margin-bottom:12px">⭐</div>
              <div style="font-size:15px;font-weight:600">Ainda não há avaliações</div>
            </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </section>

    <!-- ══ SECTION 5: QUICK ACTIONS ══ -->
    <section class="section qa-section">
      <div class="container">
        <div class="qa-inner">
          <div class="qa-body">
            <div class="sec-label">Acção Rápida</div>
            <div class="qa-title">Partilhe o seu conhecimento com Angola</div>
            <div class="qa-sub">
              Submeta o seu TCC, dissertação ou artigo científico e contribua
              para o crescimento do conhecimento académico nacional.
            </div>
            <div class="qa-actions">
              <?php
              if (isset($_SESSION['jwt_auth'])) {
                echo '<a href="upload-document.php" class="btn btn-gd">
                        <i class="fa fa-send"></i> Enviar Artigo
                      </a>';
              } else {
              ?>
                <button class="btn btn-gd" onclick="requireLogin('upload')">
                  <i class="fa fa-send"></i> Enviar Artigo
                </button>
              <?php
              }
              ?>
              <a href="library.php"
                class="btn"
                style="
                  background: rgba(255, 255, 255, 0.12);
                  color: #fff;
                  border: 1.5px solid rgba(255, 255, 255, 0.2);
                "
              >
                <i class="fa fa-book"></i> Explorar Biblioteca
              </a>
            </div>
          </div>
          <div class="qa-features">
            <div class="qaf-item">
              <div class="qaf-ico"><i class="fa fa-money"></i></div>
              Sem custo de publicação
            </div>
            <div class="qaf-item">
              <div class="qaf-ico"><i class="fa fa-eye"></i></div>
              Revisão por pares
            </div>
            <div class="qaf-item">
              <div class="qaf-ico"><i class="fa fa-star"></i></div>
              Ganhe pontos por cada upload
            </div>
            <div class="qaf-item">
              <div class="qaf-ico"><i class="fa fa-map"></i></div>
              Alcance nacional e internacional
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ══ SECTION 6: CENTRO DE ACTUALIZAÇÕES ══ -->
    <section class="section" style="background: var(--cream)">
      <div class="container">
        <div class="sec-head">
          <div>
            <div class="sec-label">Centro de Actualizações</div>
            <div class="sec-title">Acompanhe as últimas novidades</div>
          </div>
          <a href="list-noticies.php" class="see-all">Ver todos →</a>
        </div>
        <div class="notices-grid">
          <?php
          $notice_icons = ['info'=>'ℹ️','success'=>'🆕','warning'=>'📢','update'=>'⚙️'];
          if (!empty($notices)) {
            foreach ($notices as $i => $noticy):
              $ntype = $noticy['type'] ?? 'info';
              // Override icon by type if DB icon is empty/default
              $nicon = (!empty($noticy['icon']) && $noticy['icon'] !== '?') ? $noticy['icon'] : ($notice_icons[$ntype] ?? '📢');
              // Standardize: announcements=📢, new content=🆕, system updates=⚙️
              if ($ntype === 'success') $nicon = '🆕';
              elseif ($ntype === 'update') $nicon = '⚙️';
              elseif ($ntype === 'warning') $nicon = '📢';
              $delay = number_format($i * 0.07, 2);
          ?>
            <div class="notice-card" style="animation-delay:<?=$delay?>s">
              <div class="nc-ico"><?=$nicon?></div>
              <div class="nc-body">
                <div class="nc-title"><?=htmlspecialchars($noticy['title'])?></div>
                <?php if(!empty($noticy['description'])): ?>
                <div class="nc-desc"><?=htmlspecialchars($noticy['description'])?></div>
                <?php endif; ?>
                <div class="nc-meta" style="display:flex;align-items:center;gap:10px;margin-top:7px;flex-wrap:wrap">
                  <span class="nc-date">🕐 <?=date('d/m/Y', strtotime($noticy['created_at']))?></span>
                  <?php if(!empty($noticy['link_url'])): ?>
                  <a href="<?=htmlspecialchars($noticy['link_url'])?>" style="font-size:12px;color:var(--cr);font-weight:600;text-decoration:none">Saber mais →</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php
            endforeach;
          } else {
            echo '<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--tx-l)"><div style="font-size:44px;opacity:.18;margin-bottom:12px">📢</div><div>Nenhuma novidade registada.</div></div>';
          }
          ?>
        </div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
      <div class="container">
        <div class="footer-grid">
          <div class="ft-brand">
            <div class="fb-logo">PETRO<span>PUB</span></div>
            <p>
              Portal académico digital de Angola. Conectamos estudantes,
              docentes e investigadores das principais universidades do país.
            </p>

          </div>
          <div class="ft-col">
            <a href='category.php?cat=Artigos Científicos' class='ft-link'>Artigos Científicos</a>
            <a href='category.php?cat=TCC' class='ft-link'>TCCs</a>
            <a href='category.php?cat=Dissertação' class='ft-link'>Dissertações</a>
            <a href='category.php?cat=Livro' class='ft-link'>Livros</a>
            <a href='category.php?cat=Monografia' class='ft-link'>Monografias</a>
            <a href='category.php?cat=Relatório' class='ft-link'>Relátorios</a>
          </div>
          <div class="ft-col">
            <h4>Ajuda</h4>
            <a href="faq.php" class="ft-link">Perguntas frequentes</a>
            <a href="terms.php" class="ft-link">Termos de uso</a>
            <a href="faq.php" class="ft-link">Como submeter</a>
            <a href="contact.php" class="ft-link">Contacto</a>
            <a href="contact.php" class="ft-link">Suporte</a>
            <a href="about.php" class="ft-link">Sobre</a>
          </div>
        </div>
        <div class="footer-bottom">
          <span
            >© 2025 PetroPub — Portal Académico Digital de Angola. Todos os
            direitos reservados.</span
          >
          <span>Luanda, Angola 🇦🇴</span>
        </div>
      </div>
    </footer>

    <script>
      /* ═══════════════════════════════
         DATA
      ═══════════════════════════════ */
      const featuredDocs={
        popular:[
          {ico:'⚙️',bg:'hsl(200,55%,92%)',title:'Algoritmos de ML para Detecção de Falhas em Pipelines',author:'Kiala Emanuel · UAN',type:'TCC',tcls:'dt-tcc',rating:'4.8',price:0},
          {ico:'📊',bg:'hsl(140,45%,92%)',title:'Gestão Estratégica de Projectos no Sector Petrolífero Angolano',author:'Filomena Luvualu · ISPTEC',type:'Dissertação',tcls:'dt-dis',rating:'4.6',price:1500},
          {ico:'💻',bg:'hsl(220,55%,92%)',title:'Redes Neurais Convolucionais Aplicadas à Diagnose Médica',author:'João Manuel · UAN',type:'Artigo',tcls:'dt-art',rating:'4.5',price:0},
          {ico:'⚡',bg:'hsl(40,55%,92%)',title:'Energias Renováveis no Sistema Eléctrico de Angola: Perspectiva 2030',author:'Sónia Pimentel · Metodista',type:'Livro',tcls:'dt-liv',rating:'4.9',price:2500},
          {ico:'📈',bg:'hsl(0,45%,92%)',title:'Finanças Corporativas e Mercados de Capitais em Angola',author:'Carlos Neto · UCAN',type:'Relatório',tcls:'dt-rel',rating:'4.2',price:800},
          {ico:'🔬',bg:'hsl(280,45%,92%)',title:'Bioinformática e Genómica Computacional: Uma Introdução',author:'Ana Rodrigues · UAN',type:'Artigo',tcls:'dt-art',rating:'4.4',price:0},
        ],
        recent:[
          {ico:'🤖',bg:'hsl(160,45%,92%)',title:'Inteligência Artificial na Medicina Angolana: Desafios e Oportunidades',author:'Pedro Matos · ISPTEC',type:'Dissertação',tcls:'dt-dis',rating:'4.3',price:1200},
          {ico:'🌍',bg:'hsl(60,55%,92%)',title:'Sistemas de Informação Geográfica para Engenharia Civil',author:'Luísa Baptista · Jean Piaget',type:'TCC',tcls:'dt-tcc',rating:'4.1',price:0},
          {ico:'📱',bg:'hsl(200,45%,92%)',title:'Marketing Digital e E-commerce no Mercado Angolano',author:'Rui Ferreira · Lusíada',type:'Artigo',tcls:'dt-art',rating:'4.0',price:600},
          {ico:'⚗️',bg:'hsl(280,55%,92%)',title:'Química Industrial Aplicada à Refinação do Petróleo',author:'Marta Costa · UAN',type:'Livro',tcls:'dt-liv',rating:'4.7',price:3000},
          {ico:'🏗️',bg:'hsl(20,45%,92%)',title:'Estruturas de Betão Armado: Cálculo e Dimensionamento',author:'Prof. Helena Lima · UAN',type:'Apresentação',tcls:'dt-apr',rating:'4.5',price:0},
          {ico:'🔧',bg:'hsl(100,45%,92%)',title:'Manutenção Preditiva em Equipamentos Offshore',author:'Ricardo Dias · ISPTEC',type:'Relatório',tcls:'dt-rel',rating:'4.6',price:2000},
        ],
        recommended:[
          {ico:'📐',bg:'hsl(220,45%,92%)',title:'Fundamentos de Matemática para Engenharia Computacional',author:'Catarina Lopes · UCAN',type:'Livro',tcls:'dt-liv',rating:'4.8',price:0},
          {ico:'🧬',bg:'hsl(140,55%,92%)',title:'Introdução à Biologia Molecular para Estudantes de Medicina',author:'Bernardo Teixeira · Metodista',type:'Artigo',tcls:'dt-art',rating:'4.5',price:700},
          {ico:'⚖️',bg:'hsl(0,55%,92%)',title:'Direito do Trabalho em Angola: Casos e Comentários',author:'Graça Nkosi · Jean Piaget',type:'Livro',tcls:'dt-liv',rating:'4.3',price:1800},
          {ico:'🛢️',bg:'hsl(40,45%,92%)',title:'Geologia de Reservatórios Petrolíferos da Bacia do Congo',author:'Kiala Emanuel · UAN',type:'TCC',tcls:'dt-tcc',rating:'4.7',price:0},
          {ico:'📡',bg:'hsl(180,45%,92%)',title:'Telecomunicações e Redes 5G: Impacto para Angola',author:'Pedro Matos · ISPTEC',type:'Artigo',tcls:'dt-art',rating:'4.4',price:500},
          {ico:'🏥',bg:'hsl(300,45%,92%)',title:'Saúde Pública e Epidemiologia em Angola Contemporânea',author:'João Manuel · UAN',type:'Relatório',tcls:'dt-rel',rating:'4.6',price:0},
        ]
      };

      const categories=[
        {ico:'📄',name:'Artigos Científicos',count:342,color:'#2D7A4F',grad:'linear-gradient(135deg,#f0fff4,#c6f6d5)'},
        {ico:'🎓',name:'TCCs',count:518,color:'#1A5C8A',grad:'linear-gradient(135deg,#ebf8ff,#bee3f8)'},
        {ico:'📘',name:'Dissertações',count:204,color:'#5A3A8A',grad:'linear-gradient(135deg,#faf5ff,#e9d8fd)'},
        {ico:'📄',name:'Monografia',count:342,color:'#2D7A4F',grad:'linear-gradient(135deg,#f0fff4,#c6f6d5)'},
        {ico:'📑',name:'Tese Doutoramento',count:93,color:'#6B1020',grad:'linear-gradient(135deg,var(--cream),#fce4e9)'},
        {ico:'📖',name:'Livros',count:89,color:'#9A7828',grad:'linear-gradient(135deg,#fffbeb,#fef3c7)'},
        {ico:'📊',name:'Relatório',count:167,color:'#C47A1A',grad:'linear-gradient(135deg,#fffaf0,#feebc8)'},
        {ico:'📑',name:'Apresentações',count:93,color:'#6B1020',grad:'linear-gradient(135deg,var(--cream),#fce4e9)'},
        {ico:'📖',name:'Outros',count:89,color:'#9A7828',grad:'linear-gradient(135deg,#fffbeb,#fef3c7)'},
      ];

      const opportunities=[
        {ico:'🎓',bg:'linear-gradient(135deg,#1A3860,#1A5C8A)',type:'Curso',tcls:'ot-curso',title:'Engenharia de Petróleo — Curso Online',desc:'Formação completa em engenharia de reservatórios com certificado reconhecido.',src:'Sonangol EP'},
        {ico:'⚙️',bg:'linear-gradient(135deg,#4A0B16,#8C1A2E)',type:'Equipamento',tcls:'ot-equip',title:'Catálogo de Equipamentos de Perfuração',desc:'Lista actualizada de fornecedores de equipamentos para perfuração offshore em Angola.',src:'Angola LNG'},
        {ico:'🗓️',bg:'linear-gradient(135deg,#1A4A2E,#2D7A4F)',type:'Evento',tcls:'ot-evento',title:'Conferência de Energia — Luanda 2025',desc:'O maior evento do sector energético angolano. Junho 2025, Talatona Convention Center.',src:'ANPG'},
        {ico:'💼',bg:'linear-gradient(135deg,#5A3A00,#C47A1A)',type:'Vaga',tcls:'ot-vaga',title:'Técnico de Manutenção — Offshore',desc:'Oportunidade para técnicos sénior em plataformas offshore. Experiência mínima 3 anos.',src:'TotalEnergies AO'},
        {ico:'📚',bg:'linear-gradient(135deg,#2C1A4A,#5A3A8A)',type:'Curso',tcls:'ot-curso',title:'Segurança Industrial em Instalações Petrolíferas',desc:'Formação certificada NEBOSH. Inscrições abertas para Março e Abril de 2025.',src:'IFP Training'},
        {ico:'🔬',bg:'linear-gradient(135deg,#1A3820,#2D7A4F)',type:'Evento',tcls:'ot-evento',title:'Workshop: Transição Energética em África',desc:'Debate sobre energias renováveis no contexto africano — 15 de Abril, Luanda.',src:'SADC Energy'},
      ];

      const trendsDownloads=[
        {ico:'⚙️',bg:'hsl(200,55%,92%)',title:'Optimização de Pipelines com Algoritmos Evolutivos',meta:'TCC · UAN · 2024',stat:'1.248',lbl:'downloads'},
        {ico:'📊',bg:'hsl(140,45%,92%)',title:'Análise de Dados com Python para Engenharia',meta:'Livro · ISPTEC · 2024',stat:'987',lbl:'downloads'},
        {ico:'💻',bg:'hsl(220,55%,92%)',title:'Sistemas Distribuídos: Teoria e Prática',meta:'Dissertação · UAN · 2023',stat:'832',lbl:'downloads'},
        {ico:'⚡',bg:'hsl(40,55%,92%)',title:'Energias Renováveis em Angola: Estado da Arte',meta:'Artigo · Metodista · 2024',stat:'711',lbl:'downloads'},
        {ico:'📐',bg:'hsl(0,45%,92%)',title:'Cálculo Diferencial e Integral para Engenheiros',meta:'Livro · UCAN · 2022',stat:'654',lbl:'downloads'},
      ];
      const trendsRated=[
        {ico:'🔬',bg:'hsl(280,45%,92%)',title:'Fundamentos de Cibersegurança Industrial',meta:'Artigo · ISPTEC · 2025',stat:'4.9',lbl:'★ avaliação'},
        {ico:'🌍',bg:'hsl(60,55%,92%)',title:'Gestão Ambiental em Zonas Petrolíferas',meta:'Dissertação · UAN · 2024',stat:'4.8',lbl:'★ avaliação'},
        {ico:'📱',bg:'hsl(200,45%,92%)',title:'Arquitectura de Microserviços com Docker',meta:'TCC · ISPTEC · 2024',stat:'4.8',lbl:'★ avaliação'},
        {ico:'🏗️',bg:'hsl(20,45%,92%)',title:'Pontes e Estruturas: Guia Prático para Angola',meta:'Livro · Jean Piaget · 2023',stat:'4.7',lbl:'★ avaliação'},
        {ico:'🧬',bg:'hsl(100,45%,92%)',title:'Epidemiologia das Doenças Tropicais em Angola',meta:'Artigo · UAN · 2025',stat:'4.7',lbl:'★ avaliação'},
      ];

      const notices=[
        {ico:'🆕',title:'Mais de 120 novos documentos esta semana',desc:'TCCs do ISPTEC e dissertações da UAN foram recentemente adicionados ao acervo. Explore as novas adições.',date:'Hoje'},
        {ico:'⚙️',title:'Melhorias na pesquisa avançada',desc:'O motor de pesquisa foi actualizado com filtros por área e ano de publicação mais precisos.',date:'Há 2 dias'},
        {ico:'🎓',title:'Parceria com a Universidade Agostinho Neto',desc:'Novo acordo de colaboração para disponibilização de mais 400 documentos académicos da UAN.',date:'Há 4 dias'},
        {ico:'💎',title:'Programa de Pontos & Recompensas activo',desc:'Ganhe pontos por cada download, avaliação ou publicação e troque por descontos no acervo premium.',date:'Há 1 semana'},
      ];

      /* ═══════════════════════════════
         RENDER
      ═══════════════════════════════ */
      let currentTab='popular';

      function stars(r){return'★'.repeat(Math.min(5,Math.max(0,Math.round(+r))));}

      function renderFeatured(tab='popular'){
        const docs=(featuredDocs[tab]||featuredDocs.popular);
        document.getElementById('featured-grid').innerHTML=docs.map((d,i)=>`
        <div class="doc-card" style="animation-delay:${(i*.05).toFixed(2)}s" onclick="goDoc()">
          <div class="dc-thumb" style="background:${d.bg}">${d.ico}</div>
          <div class="dc-overlay">
            <button class="dco-btn dco-v" onclick="event.stopPropagation();goDoc()">📄 Ver</button>
            ${d.price>0?`<button class="dco-btn dco-buy" onclick="event.stopPropagation();goCheckout()">🛒 ${d.price.toLocaleString('pt-PT')} Kz</button>`:''}
          </div>
          <div class="dc-body">
            <span class="dc-type ${d.tcls}">${d.type}</span>
            <div class="dc-title">${d.title}</div>
            <div class="dc-author">👤 ${d.author}</div>
            <div class="dc-footer">
              <span class="dc-rating">${stars(d.rating)} ${d.rating}</span>
              ${d.price===0?'<span class="dc-free">Grátis</span>':`<span class="dc-price">${d.price.toLocaleString('pt-PT')} Kz</span>`}
            </div>
          </div>
        </div>`).join('');
      }

      function renderCats(){
        // Now rendered via PHP directly
      }

      function renderOpps(){
        document.getElementById('opp-grid').innerHTML=opportunities.map((o,i)=>`
          <div class="opp-card" style="animation-delay:${(i*.06).toFixed(2)}s">
            <div class="oc-banner" style="background:${o.bg}">${o.ico}</div>
            <div class="oc-body">
              <span class="oc-type ${o.tcls}">${o.type}</span>
              <div class="oc-title">${o.title}</div>
              <div class="oc-desc">${o.desc}</div>
              <div class="oc-footer">
                <span class="oc-src">🏢 ${o.src}</span>
                <button class="btn btn-cr btn-sm" onclick="showToast('🔗 Saiba mais: ${o.title.substring(0,25)}…')">Saiba mais →</button>
              </div>
            </div>
          </div>`).join('');
      }

      function renderTrends(){
        document.getElementById('trends-downloads').innerHTML=trendsDownloads.map((t,i)=>`
          <div class="trend-item" onclick="goDoc()">
            <div class="ti-rank ${i<3 ? 'top' : ''}">${i+1}</div>
            <div class="ti-thumb" style="background:${t.bg}">${t.ico}</div>
            <div class="ti-body">
              <div class="ti-title">${t.title}</div>
              <div class="ti-meta">${t.meta}</div>
            </div>
            <div class="ti-right"><div class="ti-stat">${(+t.stat).toLocaleString('pt-PT')}</div><div class="ti-stat-lbl">${t.lbl}</div></div>
          </div>`).join('');
        document.getElementById('trends-rated').innerHTML=trendsRated.map((t,i)=>`
          <div class="trend-item" onclick="goDoc()">
            <div class="ti-rank ${i<3? 'top' : ''}">${i+1}</div>
            <div class="ti-thumb" style="background:${t.bg}">${t.ico}</div>
            <div class="ti-body">
              <div class="ti-title">${t.title}</div>
              <div class="ti-meta">${t.meta}</div>
            </div>
            <div class="ti-right"><div class="ti-stat">${t.stat}</div><div class="ti-stat-lbl">${t.lbl}</div></div>
          </div>`).join('');
      }

      function renderNotices(){
        document.getElementById('notices-grid').innerHTML=notices.map((n,i)=>`
      <div class="notice-card" style="animation-delay:${(i*.07).toFixed(2)}s">
        <div class="nc-ico">${n.ico}</div>
        <div class="nc-body">
          <div class="nc-title">${n.title}</div>
          <div class="nc-desc">${n.desc}</div>
          <div class="nc-date">🕐 ${n.date}</div>
        </div>
      </div>`).join('');
      }

      /* ═══ HERO SEARCH TABS ═══ */
      function setHsTab(el){
        document.querySelectorAll('.hs-tab').forEach(t=>t.classList.remove('on'));
        el.classList.add('on');
        document.getElementById('search-type-input').value = el.dataset.type || '';
      }

      /* ═══ SEARCH ═══ */
      function doSearch(){
        const q   = document.getElementById('hero-search-input').value.trim();
        const tipo = document.getElementById('search-type-input').value;
        if(!q && !tipo){ showToast('⚠️ Escreva algo para pesquisar'); return; }
        const params = new URLSearchParams();
        if(q)    params.set('q', q);
        if(tipo) params.set('tipo', tipo);
        hideSuggestions();
        window.location.href = 'search-results.php?' + params.toString();
      }
      function searchFor(term){
        document.getElementById('hero-search-input').value = term;
        doSearch();
      }
      function focusHeroSearch(){
        window.scrollTo({top:0,behavior:'smooth'});
        setTimeout(()=>document.getElementById('hero-search-input').focus(),400);
      }

      /* ═══ AUTOCOMPLETE SUGGESTIONS ═══ */
      let suggestTimer = null;
      function showSuggestions(val){
        clearTimeout(suggestTimer);
        const box = document.getElementById('search-suggestions');
        if(!val || val.length < 2){ hideSuggestions(); return; }
        suggestTimer = setTimeout(async ()=>{
          try {
            const tipo = document.getElementById('search-type-input').value;
            const res  = await fetch('search-results.php?api=suggest&q='+encodeURIComponent(val)+(tipo?'&tipo='+encodeURIComponent(tipo):''));
            const data = await res.json();
            if(!data.length){ hideSuggestions(); return; }
            box.innerHTML = data.map(d=>`
              <div class="search-suggestion" onclick="window.location.href='detail-doc.php?id=${d.id}'">
                <span class="ss-ico">${d.icon||'📄'}</span>
                <span class="ss-title">${d.title}</span>
                <span class="ss-type">${d.cat}</span>
              </div>`).join('');
            box.style.display='block';
          } catch(e){ hideSuggestions(); }
        }, 280);
      }
      function hideSuggestions(){
        const box=document.getElementById('search-suggestions');
        if(box){ box.style.display='none'; box.innerHTML=''; }
      }
      document.addEventListener('click', e=>{
        if(!e.target.closest('#hero-search-form')) hideSuggestions();
      });

      /* ═══ TREND TABS ═══ */
      function switchTrendTab(tab, btn){
        document.querySelectorAll('.trend-tab').forEach(b=>b.classList.remove('on'));
        btn.classList.add('on');
        document.querySelectorAll('.trend-panel').forEach(p=>p.style.display='none');
        const panel = document.getElementById('tpanel-'+tab);
        if(panel){ panel.style.display='block'; panel.style.animation='fadeUp .3s ease both'; }
      }

      /* ═══ LOGIN GATE ═══ */
      const gateMessages={
        download:{title:'Faça login para baixar',sub:'Acesso gratuito — basta criar uma conta rápida'},
        read:{title:'Login necessário para ler completo',sub:'Crie uma conta gratuita para acesso ao conteúdo completo'},
        upload:{title:'Faça login para submeter conteúdo',sub:'Registe-se e partilhe o seu conhecimento académico'},
        downloads:{title:'Acesso à sua área pessoal',sub:'Faça login para ver os seus downloads e favoritos'},
        favorites:{title:'Guarde os seus favoritos',sub:'Faça login para criar a sua lista de documentos favoritos'},
        points:{title:'Pontos & Ranking',sub:'Faça login para ver os seus pontos e posição no ranking'},
      };
      function requireLogin(type='download'){
        const cfg=gateMessages[type]||gateMessages.download;
        document.getElementById('lp-title').textContent=cfg.title;
        document.getElementById('lp-sub').textContent=cfg.sub;
        document.getElementById('login-prompt').classList.add('show');
      }
      function closePrompt(){document.getElementById('login-prompt').classList.remove('show');}
      function goDoc(){requireLogin('read');}
      function goCheckout(){requireLogin('download');}
      function goAuth(type){showToast(type==='login'?'🔑 A abrir página de login…':'✨ A abrir registo…');setTimeout(()=>{window.location.href='petropub-auth.html';},700);}
      function goBiblioteca(){showToast('📚 A abrir biblioteca…');setTimeout(()=>{window.location.href='petropub-biblioteca.html';},700);}

      /* ═══ DRAWER ═══ */
      function openDrawer(){const o=document.getElementById('dr-ov'),d=document.getElementById('drawer');o.style.display='block';setTimeout(()=>o.classList.add('open'),10);d.classList.add('open');document.body.style.overflow='hidden';}
      function closeDrawer(){const o=document.getElementById('dr-ov'),d=document.getElementById('drawer');o.classList.remove('open');d.classList.remove('open');setTimeout(()=>o.style.display='none',300);document.body.style.overflow='';}

      /* ═══ TOAST ═══ */
      function showToast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),2600);}

      /* ═══ INIT ═══ */
      renderCats(); // no-op, PHP renders now
    </script>
  </body>
</html>
