<?php
// Página de gamificação: pontos, ranking, conquistas
session_start();

if (!isset($_SESSION['jwt_auth'])) {
    header('Location: index.php');
    exit;
}
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
    <title>PetroPub – Submeter Artigo</title>
    <link href="assets/css/dashboard-style.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/gamificacao.css">
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
    <link
      href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <style>
      :root {
        --crimson: #6b1020;
        --crimson-dark: #3d0912;
        --crimson-mid: #561019;
        --crimson-light: #8c1a2e;
        --crimson-xlight: rgba(107, 16, 32, 0.06);
        --crimson-border: rgba(107, 16, 32, 0.18);
        --gold: #c9a84c;
        --gold-light: #e5c97e;
        --gold-bg: rgba(201, 168, 76, 0.1);
        --cream: #f9f5ee;
        --warm: #fef9f3;
        --white: #ffffff;
        --border: rgba(107, 16, 32, 0.09);
        --border-focus: rgba(107, 16, 32, 0.35);
        --text-dark: #1a0a0e;
        --text-mid: #4a2f35;
        --text-light: #9a7a82;
        --success: #2d6a4f;
        --success-bg: rgba(45, 106, 79, 0.1);
        --warn: #b07a1a;
        --warn-bg: rgba(176, 122, 26, 0.1);
        --danger: #b53030;
        --danger-bg: rgba(181, 48, 48, 0.1);
        --info: #1a5080;
        --info-bg: rgba(26, 80, 128, 0.1);
        --sidebar-w: 260px;
        --topbar-h: 44px;
        --content-topbar: 66px;
        --shadow-xs: 0 1px 4px rgba(61, 9, 18, 0.06);
        --shadow-sm: 0 2px 12px rgba(61, 9, 18, 0.09);
        --shadow-md: 0 8px 32px rgba(61, 9, 18, 0.12);
        --shadow-lg: 0 20px 60px rgba(61, 9, 18, 0.18);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 22px;
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
      }
      body {
        font-family: "Plus Jakarta Sans", sans-serif;
        background: var(--cream);
        color: var(--text-dark);
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
      }

      /* SCROLLBAR */
      ::-webkit-scrollbar {
        width: 5px;
        height: 5px;
      }
      ::-webkit-scrollbar-track {
        background: var(--cream);
      }
      ::-webkit-scrollbar-thumb {
        background: var(--crimson-light);
        border-radius: 3px;
      }

      /* ══════════════ TOPNAV ══════════════ */
      .topnav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        height: var(--topbar-h);
        background: var(--crimson-dark);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        border-bottom: 1px solid rgba(201, 168, 76, 0.15);
      }
      .topnav-brand {
        font-family: "Cormorant Garamond", serif;
        font-size: 22px;
        font-weight: 700;
        color: white;
        letter-spacing: 0.5px;
      }
      .topnav-brand span {
        color: var(--gold-light);
      }
      .topnav-center {
        display: flex;
        gap: 4px;
      }
      .topnav-btn {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        color: rgba(255, 255, 255, 0.65);
        padding: 5px 18px;
        border-radius: 100px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s;
      }
      .topnav-btn.active {
        background: var(--gold);
        color: var(--crimson-dark);
        border-color: var(--gold);
      }
      .topnav-btn:hover:not(.active) {
        background: rgba(255, 255, 255, 0.16);
        color: white;
      }
      .topnav-right {
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .topnav-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--gold);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        color: var(--crimson-dark);
        border: 2px solid rgba(255, 255, 255, 0.2);
      }
      .topnav-name {
        font-size: 12.5px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
      }

      /* ══════════════ LAYOUT ══════════════ */
      .layout {
        display: flex;
        min-height: 100vh;
        padding-top: var(--topbar-h);
      }

      /* ══════════════ SIDEBAR ══════════════ */

      /* ══════════════ MAIN ══════════════ */
      .main {
        flex: 1;
        overflow-y: auto;
        background: var(--cream);
        min-width: 0;
      }
      .content-topbar {
        background: white;
        border-bottom: 1px solid var(--border);
        padding: 0 28px;
        height: var(--content-topbar);
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 50;
        box-shadow: var(--shadow-xs);
      }
      .content-topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
      }
      .menu-toggle {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: transparent;
        border: none;
        color: var(--text-mid);
        font-size: 18px;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
      }
      .menu-toggle:hover {
        background: var(--crimson-xlight);
      }
      .topbar-titles {
        display: flex;
        flex-direction: column;
      }
      .breadcrumb {
        font-size: 11px;
        color: var(--text-light);
        margin-bottom: 1px;
      }
      .breadcrumb strong {
        color: var(--crimson);
        font-weight: 600;
      }
      .page-title {
        font-family: "Cormorant Garamond", serif;
        font-size: 19px;
        font-weight: 700;
        color: var(--crimson-dark);
      }
      .topbar-actions {
        display: flex;
        align-items: center;
        gap: 8px;
      }

      /* ══════════════ BUTTONS ══════════════ */
      .btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 20px;
        border-radius: var(--radius-md);
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        border: none;
        font-family: inherit;
        transition: all 0.2s;
        text-decoration: none;
        white-space: nowrap;
      }
      .btn-primary {
        background: linear-gradient(
          135deg,
          var(--crimson-light),
          var(--crimson-dark)
        );
        color: white;
        box-shadow: 0 4px 16px rgba(107, 16, 32, 0.28);
      }
      .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(107, 16, 32, 0.35);
      }
      .btn-outline {
        background: transparent;
        color: var(--crimson);
        border: 1.5px solid var(--crimson-border);
      }
      .btn-outline:hover {
        background: var(--crimson-xlight);
        border-color: var(--crimson);
      }
      .btn-ghost {
        background: white;
        color: var(--text-mid);
        border: 1.5px solid var(--border);
      }
      .btn-ghost:hover {
        background: var(--cream);
        color: var(--crimson);
        border-color: var(--crimson-border);
      }
      .btn-success {
        background: var(--success);
        color: white;
      }
      .btn-sm {
        padding: 7px 14px;
        font-size: 12px;
        border-radius: var(--radius-sm);
      }
      .btn-lg {
        padding: 14px 28px;
        font-size: 15px;
      }

      /* ══════════════ PAGE CONTENT ══════════════ */
      .page-content {
        padding: 28px;
        max-width: 1180px;
      }

      /* ══════════════ UPLOAD LAYOUT ══════════════ */
      .upload-layout {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 24px;
        align-items: start;
      }

      /* ══════════════ CARDS ══════════════ */
      .card {
        background: white;
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-xs);
        overflow: hidden;
        margin-bottom: 20px;
      }
      .card:last-child {
        margin-bottom: 0;
      }
      .card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(
          to right,
          rgba(107, 16, 32, 0.02),
          transparent
        );
      }
      .card-step {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--crimson);
        color: white;
        font-size: 12px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        flex-shrink: 0;
      }
      .card-title-wrap {
        display: flex;
        align-items: center;
      }
      .card-title {
        font-family: "Cormorant Garamond", serif;
        font-size: 16px;
        font-weight: 700;
        color: var(--crimson-dark);
      }
      .card-sub {
        font-size: 11.5px;
        color: var(--text-light);
        margin-top: 2px;
        padding-left: 36px;
      }
      .card-body {
        padding: 24px;
      }

      /* ══════════════ BADGE ══════════════ */
      .badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
      }
      .badge-crimson {
        background: var(--crimson-xlight);
        color: var(--crimson);
        border: 1px solid var(--crimson-border);
      }
      .badge-green {
        background: var(--success-bg);
        color: var(--success);
      }
      .badge-orange {
        background: var(--warn-bg);
        color: var(--warn);
      }
      .badge-gray {
        background: rgba(0, 0, 0, 0.05);
        color: #777;
      }

      /* ══════════════ FORM ══════════════ */
      .form-field {
        margin-bottom: 18px;
      }
      .form-field:last-child {
        margin-bottom: 0;
      }
      .form-field label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.9px;
        margin-bottom: 7px;
      }
      .form-field label .req {
        color: var(--crimson);
        margin-left: 2px;
      }
      input[type="text"],
      input[type="number"],
      input[type="email"],
      input[type="tel"],
      input[type="date"],
      input[type="time"],
      textarea,
      select {
        width: 100%;
        padding: 11px 15px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 13.5px;
        font-family: inherit;
        color: var(--text-dark);
        background: white;
        outline: none;
        transition: all 0.2s;
        appearance: none;
        -webkit-appearance: none;
      }
      input::placeholder,
      textarea::placeholder {
        color: var(--text-light);
      }
      input:focus,
      textarea:focus,
      select:focus {
        border-color: var(--crimson);
        box-shadow: 0 0 0 3px rgba(107, 16, 32, 0.07);
      }
      textarea {
        resize: vertical;
        min-height: 110px;
        line-height: 1.65;
      }
      .input-suffix {
        display: flex;
      }
      .input-suffix input {
        border-radius: var(--radius-md) 0 0 var(--radius-md);
        border-right: none;
      }
      .input-suffix .suffix-tag {
        background: var(--cream);
        border: 1.5px solid var(--border);
        border-left: none;
        border-radius: 0 var(--radius-md) var(--radius-md) 0;
        padding: 0 16px;
        display: flex;
        align-items: center;
        font-size: 13px;
        font-weight: 700;
        color: var(--text-mid);
        white-space: nowrap;
      }
      .field-hint {
        font-size: 11.5px;
        color: var(--text-light);
        margin-top: 7px;
        display: flex;
        align-items: flex-start;
        gap: 5px;
        line-height: 1.5;
      }
      .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
      }
      .three-col {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
      }

      /* ══════════════ COVER UPLOAD ══════════════ */
      .cover-upload-layout {
        display: flex;
        gap: 20px;
        align-items: flex-start;
      }
      .cover-drop {
        flex-shrink: 0;
        width: 140px;
        height: 196px;
        border: 2px dashed rgba(107, 16, 32, 0.22);
        border-radius: var(--radius-lg);
        background: var(--cream);
        cursor: pointer;
        transition: all 0.25s;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 6px;
      }
      .cover-drop:hover,
      .cover-drop.drag-over {
        border-color: var(--crimson);
        background: rgba(107, 16, 32, 0.04);
      }
      .cover-drop input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
      }
      .cover-drop-icon {
        font-size: 32px;
        opacity: 0.4;
      }
      .cover-drop-text {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-light);
        line-height: 1.4;
        padding: 0 10px;
      }
      .cover-drop .format-tag {
        font-size: 10px;
        font-weight: 700;
        background: white;
        border: 1px solid var(--border);
        color: var(--text-mid);
        padding: 2px 8px;
        border-radius: 100px;
      }
      .cover-preview-wrap {
        flex-shrink: 0;
        width: 140px;
        height: 196px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        border: 2px solid var(--crimson-border);
        position: relative;
        display: none;
        box-shadow: var(--shadow-sm);
      }
      .cover-preview-wrap.visible {
        display: block;
      }
      .cover-preview-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
      }
      .cover-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
          to top,
          rgba(61, 9, 18, 0.6) 0%,
          transparent 50%
        );
        display: flex;
        align-items: flex-end;
        padding: 10px;
        opacity: 0;
        transition: opacity 0.2s;
      }
      .cover-preview-wrap:hover .cover-overlay {
        opacity: 1;
      }
      .cover-remove-btn {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
        color: var(--danger);
        cursor: pointer;
        width: 100%;
        font-family: inherit;
        transition: all 0.15s;
      }
      .cover-remove-btn:hover {
        background: white;
      }
      .cover-info {
        flex: 1;
      }
      .cover-info-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
      }
      .cover-info p {
        font-size: 12px;
        color: var(--text-light);
        line-height: 1.6;
        margin-bottom: 10px;
      }
      .cover-tips {
        display: flex;
        flex-direction: column;
        gap: 5px;
      }
      .cover-tip {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 11.5px;
        color: var(--text-mid);
      }
      .cover-tip::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--gold);
        flex-shrink: 0;
      }
      .cover-badge-optional {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--gold-bg);
        color: var(--warn);
        padding: 3px 10px;
        border-radius: 100px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 10px;
      }

      /* ══════════════ FILE DROP ══════════════ */
      .drop-zone {
        border: 2px dashed rgba(107, 16, 32, 0.22);
        border-radius: var(--radius-lg);
        padding: 36px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s;
        background: var(--cream);
        position: relative;
        overflow: hidden;
      }
      .drop-zone:hover,
      .drop-zone.drag-over {
        border-color: var(--crimson);
        background: rgba(107, 16, 32, 0.03);
      }
      .drop-zone input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
      }
      .dz-icon {
        font-size: 48px;
        margin-bottom: 12px;
        display: block;
        line-height: 1;
      }
      .dz-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 5px;
      }
      .dz-subtitle {
        font-size: 13px;
        color: var(--text-light);
      }
      .dz-subtitle strong {
        color: var(--crimson);
        font-weight: 700;
      }
      .dz-formats {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 14px;
        flex-wrap: wrap;
      }
      .dz-format {
        background: white;
        border: 1px solid var(--border);
        color: var(--text-mid);
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 11px;
        font-weight: 700;
      }
      .file-preview {
        display: none;
        align-items: center;
        gap: 14px;
        background: var(--success-bg);
        border: 1.5px solid rgba(45, 106, 79, 0.22);
        border-radius: var(--radius-md);
        padding: 14px 18px;
        margin-top: 12px;
      }
      .file-preview.visible {
        display: flex;
        animation: fadeUp 0.3s ease;
      }
      .file-icon-big {
        font-size: 30px;
        flex-shrink: 0;
      }
      .file-info .file-name {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--success);
      }
      .file-info .file-size {
        font-size: 11.5px;
        color: var(--text-light);
        margin-top: 2px;
      }
      .file-remove {
        margin-left: auto;
        background: transparent;
        border: none;
        color: var(--danger);
        font-size: 20px;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 6px;
        line-height: 1;
      }
      .file-remove:hover {
        background: var(--danger-bg);
      }

      /* Progress */
      .upload-progress {
        display: none;
        margin-top: 12px;
      }
      .upload-progress.visible {
        display: block;
        animation: fadeUp 0.3s ease;
      }
      .prog-bar-wrap {
        height: 6px;
        background: var(--border);
        border-radius: 6px;
        overflow: hidden;
      }
      .prog-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(
          90deg,
          var(--crimson),
          var(--crimson-light)
        );
        border-radius: 6px;
        transition: width 0.4s;
      }
      .prog-text {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: var(--text-light);
        margin-top: 5px;
      }

      /* ══════════════ DOC TYPE GRID ══════════════ */
      .doc-type-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
      }
      .doc-type-option {
        border: 2px solid var(--border);
        border-radius: var(--radius-md);
        padding: 12px 8px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
        background: white;
      }
      .doc-type-option:hover {
        border-color: var(--crimson-border);
        background: var(--crimson-xlight);
      }
      .doc-type-option.selected {
        border-color: var(--crimson);
        background: var(--crimson-xlight);
      }
      .doc-type-option input {
        display: none;
      }
      .doc-type-icon {
        font-size: 22px;
        margin-bottom: 5px;
        line-height: 1;
      }
      .doc-type-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-mid);
        line-height: 1.3;
      }
      .doc-type-option.selected .doc-type-label {
        color: var(--crimson);
      }

      /* ══════════════ PUB TOGGLE ══════════════ */
      .pub-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 20px;
      }
      .pub-option {
        border: 2px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: white;
      }
      .pub-option:hover {
        border-color: var(--crimson-border);
      }
      .pub-option.selected {
        border-color: var(--crimson);
        background: var(--crimson-xlight);
      }
      .pub-option input {
        display: none;
      }
      .pub-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        background: var(--cream);
      }
      .pub-option.selected .pub-icon {
        background: white;
      }
      .pub-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 3px;
      }
      .pub-desc {
        font-size: 11.5px;
        color: var(--text-light);
        line-height: 1.4;
      }
      .pub-option.selected .pub-title {
        color: var(--crimson);
      }

      .scheduler-box {
        background: var(--warn-bg);
        border: 1.5px solid rgba(176, 122, 26, 0.22);
        border-radius: var(--radius-md);
        padding: 16px;
        margin-top: 14px;
        display: none;
      }
      .scheduler-box.visible {
        display: block;
        animation: fadeUp 0.3s ease;
      }

      /* ══════════════ PAYMENT METHODS ══════════════ */
      .payment-methods-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
      }
      .pm-option {
        border: 2px solid var(--border);
        border-radius: var(--radius-md);
        padding: 14px;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        display: flex;
        align-items: center;
        gap: 10px;
      }
      .pm-option:hover {
        border-color: var(--crimson-border);
      }
      .pm-option.selected {
        border-color: var(--crimson);
        background: var(--crimson-xlight);
      }
      .pm-option input {
        display: none;
      }
      .pm-logo {
        width: 38px;
        height: 38px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        background: var(--cream);
      }
      .pm-option.selected .pm-logo {
        background: white;
      }
      .pm-name {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--text-dark);
      }
      .pm-type {
        font-size: 11px;
        color: var(--text-light);
        margin-top: 1px;
      }
      .pm-option.selected .pm-name {
        color: var(--crimson);
      }
      .pm-check {
        margin-left: auto;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
        font-size: 10px;
      }
      .pm-option.selected .pm-check {
        background: var(--crimson);
        border-color: var(--crimson);
        color: white;
      }

      /* ══════════════ SUMMARY CARD ══════════════ */
      .summary-card {
        background: white;
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        position: sticky;
        top: calc(var(--content-topbar) + 14px);
      }
      .summary-head {
        background: linear-gradient(
          135deg,
          var(--crimson-dark) 0%,
          var(--crimson-light) 100%
        );
        padding: 20px 22px;
        position: relative;
        overflow: hidden;
      }
      .summary-head::after {
        content: "";
        position: absolute;
        right: -20px;
        top: -20px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(201, 168, 76, 0.12);
      }
      .summary-head h3 {
        font-family: "Cormorant Garamond", serif;
        font-size: 17px;
        font-weight: 700;
        color: white;
        position: relative;
      }
      .summary-head p {
        font-size: 11.5px;
        color: rgba(255, 255, 255, 0.55);
        margin-top: 3px;
        position: relative;
      }
      .summary-body {
        padding: 16px 20px;
      }
      .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 9px 0;
        border-bottom: 1px solid rgba(107, 16, 32, 0.05);
        font-size: 12.5px;
      }
      .summary-row:last-child {
        border-bottom: none;
      }
      .s-label {
        color: var(--text-light);
        flex-shrink: 0;
        margin-right: 12px;
      }
      .s-val {
        font-weight: 600;
        color: var(--text-dark);
        text-align: right;
      }
      .s-val.empty {
        color: var(--text-light);
        font-weight: 400;
        font-style: italic;
      }
      .s-val.crimson {
        color: var(--crimson);
      }
      .summary-price {
        padding: 14px 20px;
        border-top: 2px solid var(--crimson-xlight);
        background: var(--warm);
        display: flex;
        justify-content: space-between;
        align-items: center;
      }
      .summary-price-label {
        font-size: 12px;
        color: var(--text-light);
        font-weight: 600;
      }
      .summary-price-val {
        font-family: "Cormorant Garamond", serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--crimson);
      }
      .summary-actions {
        padding: 16px 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
      }
      .summary-file-preview {
        display: none;
        padding: 10px 20px;
        border-top: 1px solid var(--border);
        align-items: center;
        gap: 10px;
      }
      .summary-file-preview.visible {
        display: flex;
      }
      .sfp-img {
        width: 36px;
        height: 50px;
        border-radius: 5px;
        object-fit: cover;
        border: 1px solid var(--border);
      }
      .sfp-no-img {
        width: 36px;
        height: 50px;
        border-radius: 5px;
        background: var(--cream);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
      }
      .sfp-info .sfp-name {
        font-size: 12px;
        font-weight: 700;
        color: var(--success);
      }
      .sfp-info .sfp-size {
        font-size: 11px;
        color: var(--text-light);
        margin-top: 1px;
      }

      /* ══════════════ TIP CARD ══════════════ */
      .tip-card {
        background: var(--gold-bg);
        border: 1px solid rgba(201, 168, 76, 0.28);
        border-radius: var(--radius-md);
        padding: 14px 16px;
        margin: 0 20px 20px;
      }
      .tip-title {
        font-size: 10.5px;
        font-weight: 800;
        color: var(--warn);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 5px;
      }
      .tip-card li {
        font-size: 11.5px;
        color: var(--text-mid);
        line-height: 1.65;
        padding-left: 14px;
        list-style: none;
        position: relative;
      }
      .tip-card li::before {
        content: "→";
        position: absolute;
        left: 0;
        color: var(--gold);
        font-weight: 700;
        font-size: 10px;
        top: 1px;
      }

      /* ══════════════ ACCESS TOGGLE ══════════════ */
      .access-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
      }

      /* ══════════════ TOAST ══════════════ */
      .toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        z-index: 99999;
        transform: translateX(-50%) translateY(80px);
        background: var(--crimson-dark);
        color: white;
        padding: 13px 22px;
        border-radius: var(--radius-lg);
        font-size: 13.5px;
        font-weight: 500;
        box-shadow: var(--shadow-lg);
        opacity: 0;
        transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(201, 168, 76, 0.28);
        white-space: nowrap;
        pointer-events: none;
      }
      .toast.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }

      /* ══════════════ SECTION DIVIDER ══════════════ */
      .section-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        margin-top: 22px;
      }
      .section-divider-line {
        flex: 1;
        height: 1px;
        background: var(--border);
      }
      .section-divider-label {
        font-size: 10.5px;
        font-weight: 800;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        white-space: nowrap;
      }

      /* ══════════════ OVERLAY ══════════════ */
      .sb-ov {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.52);
        display: none;
        z-index: 998;
        backdrop-filter: blur(2px);
      }
      .sb-ov.open {
        display: block;
      }

      /* ══════════════ ANIMATIONS ══════════════ */
      @keyframes fadeUp {
        from {
          opacity: 0;
          transform: translateY(14px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      .anim {
        animation: fadeUp 0.4s ease both;
      }
      .anim-d1 {
        animation-delay: 0.05s;
      }
      .anim-d2 {
        animation-delay: 0.1s;
      }
      .anim-d3 {
        animation-delay: 0.16s;
      }
      .anim-d4 {
        animation-delay: 0.22s;
      }
      .anim-d5 {
        animation-delay: 0.28s;
      }

      /* ══════════════ RESPONSIVE ══════════════ */
      @media (max-width: 1100px) {
        .upload-layout {
          grid-template-columns: 1fr 320px;
        }
      }

      @media (max-width: 960px) {
        .upload-layout {
          grid-template-columns: 1fr;
        }
        .summary-card {
          position: relative;
          top: 0;
        }
        .doc-type-grid {
          grid-template-columns: repeat(4, 1fr);
        }
      }

      @media (max-width: 768px) {
        :root {
          --content-topbar: 60px;
        }

        .sidebar {
          position: fixed;
          top: var(--topbar-h);
          height: calc(100vh - var(--topbar-h));
          transform: translateX(-100%);
          z-index: 999;
          transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .sidebar.open {
          transform: translateX(0);
          box-shadow: var(--shadow-lg);
        }
        .menu-toggle {
          display: flex;
        }

        .page-content {
          padding: 16px;
        }
        .content-topbar {
          padding: 0 16px;
        }

        .two-col {
          grid-template-columns: 1fr;
        }
        .doc-type-grid {
          grid-template-columns: repeat(3, 1fr);
        }
        .payment-methods-grid {
          grid-template-columns: 1fr;
        }
        .pub-toggle {
          grid-template-columns: 1fr;
        }
        .access-toggle {
          grid-template-columns: 1fr;
        }

        .topbar-actions .btn-ghost {
          display: none;
        }
        .topnav-center {
          display: none;
        }
        .topnav-name {
          display: none;
        }

        .cover-upload-layout {
          flex-direction: column;
        }
        .cover-drop,
        .cover-preview-wrap {
          width: 100%;
          height: 180px;
        }
      }

      @media (max-width: 480px) {
        .doc-type-grid {
          grid-template-columns: repeat(2, 1fr);
        }
        .drop-zone {
          padding: 24px 16px;
        }
        .card-body {
          padding: 16px;
        }
        .card-header {
          padding: 14px 16px;
        }
        .summary-body {
          padding: 12px 16px;
        }
        .summary-actions {
          padding: 12px 16px;
        }
        .tip-card {
          margin: 0 14px 16px;
        }
      }
    </style>
  </head>
  <body>
    <!-- TOAST -->
    <div class="toast" id="toast"></div>

    <!-- TOPNAV -->
    <nav class="topnav">
      <div class="topnav-brand">PETRO<span>PUB</span></div>
      <div class="topnav-center">
        <a href="uploa-document.php" class="topnav-btn active">📤 Submeter Artigo</a>
        <a href="my-documents.php" class="topnav-btn">📚 Meus Artigos</a>
      </div>
      <div class="topnav-right">
        <div class="topnav-avatar"><?=$userInitials?></div>
        <span class="topnav-name"><?=$userName?></span>
      </div>
    </nav>

    <div class="layout">
      <!-- SIDEBAR -->
      <?php
      include_once 'header-role.php';
      // $tp = $_SESSION['type_auth'];
      // if ($tp == "ADMIN") {
      //   echo '<aside class="sidebar" id="sidebar">
      //           <div class="sidebar-user">
      //             <div class="s-avatar">'.$userInitials.'</div>
      //             <div>
      //               <div class="s-name">'.$userName.'</div>
      //               <div class="s-role">'.$userEmail.'</div>
      //             </div>
      //           </div>

      //           <div class="nav-group">
      //             <div class="nav-label">Principal</div>
      //             <a href="upload-document.php" class="nav-item active">
      //               <span class="nav-icon"><i class="fa fa-send"></i></span> Submeter Artigo
      //             </div>
      //             <a href="my-documents.php" class="nav-item">
      //               <span class="nav-icon"><i class="fa fa-book"></i></span> Meus Artigos
      //               <span class="nav-badge">4</span>
      //             </a>
      //             <a href="users.php" class="nav-item"><span class="nav-icon"><i class="fa fa-users"></i></span> Utilizadores</a>
      //             <a href="library.php" class="nav-item"><span class="nav-icon"><i class="fa fa-book"></i></span> Biblioteca</a>
      //             <a href="articles.php" class="nav-item">
      //               <span class="nav-icon"><i class="fa fa-comments-o"></i></span> Todos Artigos
      //             </a>
      //           </div>

      //           <div class="sidebar-footer">
      //             <div class="nav-item" style="color: rgba(255, 255, 255, 0.45)">
      //               <span class="nav-icon">🚪</span> Sair
      //             </div>
      //           </div>
      //         </aside>';
      // } else {
      //   echo '<aside class="sidebar" id="sidebar">
      //           <div class="sidebar-user">
      //             <div class="s-avatar">'.$userInitials.'</div>
      //             <div>
      //               <div class="s-name">'.$userName.'</div>
      //               <div class="s-role">'.$userEmail.'</div>
      //             </div>
      //           </div>

      //           <div class="nav-group">
      //             <div class="nav-label">Principal</div>
      //             <a href="upload-document.php" class="nav-item active">
      //               <span class="nav-icon"><i class="fa fa-send"></i></span> Submeter Artigo
      //             </a>
      //             <a href="my-documents.php" class="nav-item">
      //               <span class="nav-icon"><i class="fa fa-book"></i></span> Meus Artigos
      //             </a>
      //             <a href="library.php" class="nav-item"><span class="nav-icon"><i class="fa fa-book"></i></span> Biblioteca</a>
      //             <div class="nav-item">
      //               <span class="nav-icon"><i class="fa fa-comments-o"></i></span> Revisão por pares
      //             </div>
      //           </div>

      //           <div class="nav-group">
      //             <div class="nav-label">Conta</div>
      //             <a href="logout.php" class="nav-item"><span class="nav-icon"><i class="fa fa-logout"></i></span> Terminar sessão</a>
      //           </div>
      //           </aside>';
          
      // }
      ?>

      <!-- OVERLAY -->
      <div clayyss="sb-oyyv" id="overlay" onclick="closeSidebar()"></div>

      <!-- MAIN -->
      <div class="main">
        <div class="content-topbar">
          <div class="content-topbar-left">
            <button
              class="menu-toggle"
              onclick="toggleSidebar()"
              aria-label="Menu"
            >
              ☰
            </button>
            <div class="topbar-titles">
              <div class="breadcrumb">
                PetroPub <strong>/ Submeter Artigo</strong>
              </div>
              <div class="page-title">Submeter Artigo Académico</div>
            </div>
          </div>
          <div class="topbar-actions">
            <button class="btn btn-ghost btn-sm" onclick="resetUploadForm()">
              ↺ Limpar
            </button>
            <button class="btn btn-primary btn-sm" onclick="submitDocument()">
              📤 Submeter
            </button>
          </div>
        </div>

        <div class="page-content">
          <div class="upload-layout">
            <!-- ══════ LEFT — FORM ══════ -->
            <div>
              <!-- ① CAPA (opcional) -->
              <div class="card anim anim-d1">
                <div class="card-header">
                  <div class="card-title-wrap">
                    <span class="card-step">①</span>
                    <div>
                      <div class="card-title">Capa do Documento</div>
                      <div
                        class="card-sub"
                        style="padding-left: 0; margin-top: 3px"
                      >
                        Imagem de destaque para o artigo
                      </div>
                    </div>
                  </div>
                  <span class="badge badge-orange">Opcional</span>
                </div>
                <div class="card-body">
                  <div class="cover-upload-layout">
                    <!-- Drop zone -->
                    <div
                      class="cover-drop"
                      id="cover-drop"
                      ondragover="coverDragOver(event)"
                      ondragleave="coverDragLeave(event)"
                      ondrop="coverDrop(event)"
                    >
                      <input
                        type="file"
                        id="cover-input"
                        accept="image/jpeg,image/png,image/webp"
                        onchange="coverFileSelect(event)"
                      />
                      <span class="cover-drop-icon">🖼️</span>
                      <span class="cover-drop-text"
                        >Arraste ou clique para seleccionar</span
                      >
                      <span class="format-tag">JPG · PNG · WEBP</span>
                    </div>
                    <!-- Preview -->
                    <div class="cover-preview-wrap" id="cover-preview-wrap">
                      <img id="cover-preview-img" src="" alt="Capa" />
                      <div class="cover-overlay">
                        <button
                          class="cover-remove-btn"
                          onclick="removeCover(event)"
                        >
                          ✕ Remover capa
                        </button>
                      </div>
                    </div>
                    <!-- Info -->
                    <div class="cover-info">
                      <span class="cover-badge-optional">Opcional</span>
                      <div class="cover-info-title">Imagem de Capa</div>
                      <p>
                        Uma boa capa aumenta significativamente a visibilidade e
                        os downloads do artigo na plataforma.
                      </p>
                      <div class="cover-tips">
                        <div class="cover-tip">
                          Proporção recomendada: 2:3 (retrato)
                        </div>
                        <div class="cover-tip">Tamanho máximo: 5 MB</div>
                        <div class="cover-tip">Formatos: JPG, PNG, WEBP</div>
                        <div class="cover-tip">
                          Mínimo 600×900 px para boa qualidade
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ② FICHEIRO -->
              <div class="card anim anim-d2">
                <div class="card-header">
                  <div class="card-title-wrap">
                    <span class="card-step">②</span>
                    <div>
                      <div class="card-title">Ficheiro do Artigo</div>
                      <div
                        class="card-sub"
                        style="padding-left: 0; margin-top: 3px"
                      >
                        Carregue o documento académico
                      </div>
                    </div>
                  </div>
                  <span class="badge badge-crimson">Obrigatório</span>
                </div>
                <div class="card-body">
                  <div
                    class="drop-zone"
                    id="drop-zone"
                    ondragover="handleDragOver(event)"
                    ondragleave="handleDragLeave(event)"
                    ondrop="handleDrop(event)"
                  >
                    <input
                      type="file"
                      id="file-input"
                      accept=".pdf,.docx,.doc"
                      onchange="handleFileSelectEvt(event)"
                    />
                    <span class="dz-icon">📄</span>
                    <div class="dz-title">
                      Arraste o ficheiro ou clique para seleccionar
                    </div>
                    <div class="dz-subtitle">
                      Suporte a <strong>PDF</strong> — máximo
                      <strong>50 MB</strong>
                    </div>
                    <div class="dz-formats">
                      <span class="dz-format">PDF</span>
                      <span class="dz-format">DOC</span>
                      <span class="dz-format">DOCX</span>
                    </div>
                  </div>
                  <div class="file-preview" id="file-preview">
                    <span class="file-icon-big" id="file-icon-big">📄</span>
                    <div class="file-info">
                      <div class="file-name" id="file-name">Artigo.pdf</div>
                      <div class="file-size" id="file-size">2.4 MB · PDF</div>
                    </div>
                    <button
                      class="file-remove"
                      onclick="removeFile()"
                      title="Remover ficheiro"
                    >
                      ✕
                    </button>
                  </div>
                  <div class="upload-progress" id="upload-progress">
                    <div class="prog-bar-wrap">
                      <div class="prog-fill" id="prog-fill"></div>
                    </div>
                    <div class="prog-text">
                      <span id="prog-label">A carregar…</span>
                      <span id="prog-pct">0%</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ③ INFORMAÇÕES -->
              <div class="card anim anim-d3">
                <div class="card-header">
                  <div class="card-title-wrap">
                    <span class="card-step">③</span>
                    <div>
                      <div class="card-title">Informações do Artigo</div>
                      <div
                        class="card-sub"
                        style="padding-left: 0; margin-top: 3px"
                      >
                        Metadados para indexação no acervo
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="form-field">
                    <label>Título do Trabalho <span class="req">*</span></label>
                    <input
                      type="text"
                      id="doc-title"
                      placeholder="Ex: Análise da Produção de Petróleo no Bloco 0 de Cabinda"
                      oninput="updateSummary()"
                    />
                  </div>
                  <div class="form-field">
                    <label
                      >Autores <span class="req">*</span>
                      <span
                        style="
                          font-weight: 400;
                          text-transform: none;
                          letter-spacing: 0;
                          font-size: 11px;
                        "
                        >(separar por vírgula)</span
                      ></label
                    >
                    <input
                      type="text"
                      id="doc-authors"
                      placeholder="Ex: Nsumbo Kitekua, Kiala Emanuel, Nádio Mavinga"
                      oninput="updateSummary()"
                    />
                  </div>
                  <div class="two-col">
                    <div class="form-field">
                      <label>Data de Criação <span class="req">*</span></label>
                      <input
                        type="date"
                        id="doc-date"
                        oninput="updateSummary()"
                      />
                    </div>
                    <div class="form-field">
                      <label>Curso / Área <span class="req">*</span></label>
                      <input
                        type="text"
                        id="doc-inst"
                        oninput="updateSummary()"
                        placeholder="Ex: Engenharia de Petróleo"
                      />
                    </div>
                  </div>
                  <div class="form-field">
                    <label>Orientador(a)</label>
                    <input
                      type="text"
                      id="doc-advisor"
                      placeholder="Nome completo do professor orientador"
                    />
                  </div>
                  <div class="form-field">
                    <label>Resumo / Abstract <span class="req">*</span></label>
                    <textarea
                      id="doc-abstract"
                      placeholder="Escreva um resumo claro do trabalho (mínimo 100 caracteres)…"
                    ></textarea>
                    <div class="field-hint">
                      💡 Um bom resumo aumenta a visibilidade e os downloads do
                      artigo.
                    </div>
                  </div>
                  <div class="form-field">
                    <label
                      >Palavras-Chave <span class="req">*</span>
                      <span
                        style="
                          font-weight: 400;
                          text-transform: none;
                          letter-spacing: 0;
                          font-size: 11px;
                        "
                        >(separar por vírgula)</span
                      ></label
                    >
                    <input
                      type="text"
                      id="doc-tags"
                      placeholder="Ex: petróleo, angola, cabinda, exploração, geologia"
                    />
                  </div>

                  <!-- Tipo de artigo -->
                  <div class="form-field">
                    <label>Tipo de Artigo <span class="req">*</span></label>
                    <div class="doc-type-grid">
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Dissertação')"
                      >
                        <!-- <div class="doc-type-icon">🎓</div> -->
                        <div class="doc-type-label">Dissertação</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Monografia')"
                      >
                        <!-- <div class="doc-type-icon">📖</div> -->
                        <div class="doc-type-label">Monografia</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Artigo Científico')"
                      >
                        <!-- <div class="doc-type-icon">🔬</div> -->
                        <div class="doc-type-label">Artigo Científico</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Tese de Doutoramento')"
                      >
                        <!-- <div class="doc-type-icon">🏛️</div> -->
                        <div class="doc-type-label">Tese Doutoramento</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Relatório')"
                      >
                        <!-- <div class="doc-type-icon">📊</div> -->
                        <div class="doc-type-label">Relatório</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Apresentação')"
                      >
                        <!-- <div class="doc-type-icon">📊</div> -->
                        <div class="doc-type-label">Apresentação</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'TCC')"
                      >
                        <!-- <div class="doc-type-icon">📋</div> -->
                        <div class="doc-type-label">TCC</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Livro')"
                      >
                        <!-- <div class="doc-type-icon">📋</div> -->
                        <div class="doc-type-label">Livro</div>
                      </div>
                      <div
                        class="doc-type-option"
                        onclick="selectDocType(this, 'Outro')"
                      >
                        <!-- <div class="doc-type-icon">📁</div> -->
                        <div class="doc-type-label">Outro</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ④ PUBLICAÇÃO -->
              <div class="card anim anim-d4">
                <div class="card-header">
                  <div class="card-title-wrap">
                    <span class="card-step">④</span>
                    <div>
                      <div class="card-title">Modo de Publicação</div>
                      <div
                        class="card-sub"
                        style="padding-left: 0; margin-top: 3px"
                      >
                        Quando o artigo será disponibilizado
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="pub-toggle">
                    <div
                      class="pub-option selected"
                      id="pub-immediate"
                      onclick="selectPublication('immediate')"
                    >
                      <div class="pub-icon">🚀</div>
                      <div>
                        <div class="pub-title">Publicar Imediatamente</div>
                        <div class="pub-desc">
                          Disponível logo após aprovação editorial.
                        </div>
                      </div>
                    </div>
                    <div
                      class="pub-option"
                      id="pub-scheduled"
                      onclick="selectPublication('scheduled')"
                    >
                      <div class="pub-icon">⏰</div>
                      <div>
                        <div class="pub-title">Publicação Programada</div>
                        <div class="pub-desc">
                          Escolha data e hora para publicação automática.
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="scheduler-box" id="scheduler-box">
                    <div
                      style="
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        margin-bottom: 14px;
                      "
                    >
                      <span>⏰</span>
                      <span
                        style="
                          font-size: 13px;
                          font-weight: 700;
                          color: var(--warn);
                        "
                        >Defina a data e hora de publicação</span
                      >
                    </div>
                    <div class="two-col">
                      <div class="form-field" style="margin-bottom: 0">
                        <label>Data <span class="req">*</span></label>
                        <input
                          type="date"
                          id="sched-date"
                          oninput="updateSummary()"
                          style="background: white"
                        />
                      </div>
                      <div class="form-field" style="margin-bottom: 0">
                        <label>Hora <span class="req">*</span></label>
                        <input
                          type="time"
                          id="sched-time"
                          oninput="updateSummary()"
                          style="background: white"
                        />
                      </div>
                    </div>
                    <div class="field-hint" style="margin-top: 10px">
                      ⚠️ Publicação automática após aprovação editorial na data
                      indicada.
                    </div>
                  </div>

                  <!-- <div class="section-divider">
                    <div class="section-divider-line"></div>
                    <div class="section-divider-label">Tipo de Acesso</div>
                    <div class="section-divider-line"></div>
                  </div>

                  <div class="access-toggle">
                    <div
                      class="pub-option selected"
                      id="access-paid"
                      onclick="selectAccess('paid')"
                      style="padding: 14px"
                    >
                      <div
                        class="pub-icon"
                        style="width: 36px; height: 36px; font-size: 16px"
                      >
                        💰
                      </div>
                      <div>
                        <div class="pub-title" style="font-size: 13px">
                          Acesso Pago
                        </div>
                        <div class="pub-desc">
                          Utilizadores pagam para aceder ao documento
                        </div>
                      </div>
                    </div>
                    <div
                      class="pub-option"
                      id="access-free"
                      onclick="selectAccess('free')"
                      style="padding: 14px"
                    >
                      <div
                        class="pub-icon"
                        style="width: 36px; height: 36px; font-size: 16px"
                      >
                        🆓
                      </div>
                      <div>
                        <div class="pub-title" style="font-size: 13px">
                          Acesso Livre
                        </div>
                        <div class="pub-desc">
                          Disponível gratuitamente a todos os utilizadores
                        </div>
                      </div>
                    </div>
                  </div> -->
                </div>
              </div>

              <!-- ⑤ PREÇO & PAGAMENTOS -->
              <div class="card anim anim-d5" id="pricing-card">
                <div class="card-header">
                  <div class="card-title-wrap">
                    <span class="card-step">⑤</span>
                    <div>
                      <div class="card-title">Preço e Localização</div>
                      <div
                        class="card-sub"
                        style="padding-left: 0; margin-top: 3px"
                      >
                        Valor e o ponto de referència para entrega do livro.
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="form-field">
                    <label>Valor de Acesso <span class="req">*</span></label>
                    <div class="input-suffix">
                      <input
                        type="number"
                        id="doc-price"
                        placeholder="0"
                        min="0"
                        step="50"
                        oninput="updateSummary()"
                      />
                      <div class="suffix-tag">Kz</div>
                    </div>
                  <div class="form-field">
                    <label>Localização <span class="req">*</span></label>
                    <div class="input-suffix">
                      <input
                        type="text"
                        id="doc-localization"
                        placeholder="Sala 02, ANPG, Mutamba, Luanda"
                        oninput="updateSummary()"
                      />
                      <div class="suffix-tag"><i class="fa fa-map"></i></div>
                    </div>
                    <!-- <div class="field-hint">
                      💡 Artigos gratuitos têm mais downloads e aumentam a sua
                      visibilidade.
                    </div> -->
                  </div>

                  <!-- <div class="form-field">
                    <label
                      >Meios de Pagamento <span class="req">*</span>
                      <span
                        style="
                          font-weight: 400;
                          text-transform: none;
                          letter-spacing: 0;
                          font-size: 11px;
                        "
                        >(seleccione um ou mais)</span
                      ></label
                    >
                    <div class="payment-methods-grid">
                      <div
                        class="pm-option"
                        onclick="togglePaymentMethod(this, 'PAG-001')"
                      >
                        <input type="checkbox" />
                        <div class="pm-logo">🏦</div>
                        <div>
                          <div class="pm-name">Transferência Bancária</div>
                          <div class="pm-type">Via IBAN · Banco</div>
                        </div>
                        <div class="pm-check"></div>
                      </div>
                      <div
                        class="pm-option"
                        onclick="togglePaymentMethod(this, 'PAG-002')"
                      >
                        <input type="checkbox" />
                        <div class="pm-logo">🏧</div>
                        <div>
                          <div class="pm-name">Depósito Bancário</div>
                          <div class="pm-type">Depósito em conta</div>
                        </div>
                        <div class="pm-check"></div>
                      </div>
                      <div
                        class="pm-option"
                        onclick="togglePaymentMethod(this, 'PAG-003')"
                      >
                        <input type="checkbox" />
                        <div class="pm-logo">📱</div>
                        <div>
                          <div class="pm-name">Multicaixa Express</div>
                          <div class="pm-type">App móvel</div>
                        </div>
                        <div class="pm-check"></div>
                      </div>
                      <div
                        class="pm-option"
                        onclick="togglePaymentMethod(this, 'PAG-004')"
                      >
                        <input type="checkbox" />
                        <div class="pm-logo">⚡</div>
                        <div>
                          <div class="pm-name">Kwik</div>
                          <div class="pm-type">Pagamento digital</div>
                        </div>
                        <div class="pm-check"></div>
                      </div>
                    </div>
                    <div class="field-hint">
                      💡 Oferecer múltiplos meios aumenta as vendas até 40%.
                    </div>
                  </div> -->
                </div>
              </div>
            </div>
            <!-- /left -->

            <!-- ══════ RIGHT — SUMMARY ══════ -->
            <div>
              <div class="summary-card">
                <div class="summary-head">
                  <h3>Resumo da Submissão</h3>
                  <p>Verifique os dados antes de enviar</p>
                </div>

                <!-- File/Cover preview strip -->
                <div class="summary-file-preview" id="sfp-wrap">
                  <div class="sfp-no-img" id="sfp-no-img">📄</div>
                  <img
                    class="sfp-img"
                    id="sfp-img"
                    src=""
                    alt=""
                    style="display: none"
                  />
                  <div class="sfp-info">
                    <div class="sfp-name" id="sfp-name">—</div>
                    <div class="sfp-size" id="sfp-size">—</div>
                  </div>
                </div>

                <div class="summary-body">
                  <div class="summary-row">
                    <span class="s-label">Título</span>
                    <span class="s-val empty" id="sum-title"
                      >Não preenchido</span
                    >
                  </div>
                  <div class="summary-row">
                    <span class="s-label">Tipo</span>
                    <span class="s-val empty" id="sum-type"
                      >Não seleccionado</span
                    >
                  </div>
                  <div class="summary-row">
                    <span class="s-label">Data</span>
                    <span class="s-val empty" id="sum-date">—</span>
                  </div>
                  <div class="summary-row">
                    <span class="s-label">Curso</span>
                    <span class="s-val empty" id="sum-inst">—</span>
                  </div>
                  <div class="summary-row">
                    <span class="s-label">Publicação</span>
                    <span class="s-val" id="sum-pub">🚀 Imediata</span>
                  </div>
                  <div
                    class="summary-row"
                    id="sum-sched-row"
                    style="display: none"
                  >
                    <span class="s-label">Programado</span>
                    <span class="s-val crimson" id="sum-sched">—</span>
                  </div>
                  <div class="summary-row">
                    <span class="s-label">Capa</span>
                    <span class="s-val empty" id="sum-cover">Sem capa</span>
                  </div>

                <div class="summary-actions">
                  <button
                    class="btn btn-primary btn-lg"
                    style="width: 100%; justify-content: center"
                    onclick="submitDocument()"
                  >
                    📤 Submeter para Revisão
                  </button>
                </div>

                <div class="tip-card">
                  <div class="tip-title">💡 Dicas para Publicar Melhor</div>
                  <ul>
                    <li>Títulos descritivos aumentam as pesquisas orgânicas</li>
                    <li>Resumo entre 150–300 palavras é o ideal</li>
                    <li>Use palavras-chave relevantes à área de estudo</li>
                    <li>Artigos com capa têm +60% de cliques</li>
                    <li>Vários meios de pagamento aumentam as vendas</li>
                  </ul>
                </div>
              </div>
            </div>
            <!-- /right -->
          </div>
          <!-- /upload-layout -->
        </div>
        <!-- /page-content -->
      </div>
      <!-- /main -->
    </div>
    <!-- /layout -->

    <script src="assets/js/upload.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/api.js"></script>
    <script>

      // ══════════════════════════════════
      // SIDEBAR
      // ══════════════════════════════════
      function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("open");
        document.getElementById("overlay").classList.toggle("open");
      }
      function closeSidebar() {
        document.getElementById("sidebar").classList.remove("open");
        document.getElementById("overlay").classList.remove("open");
      }

      // ══════════════════════════════════
      // TOAST
      // ══════════════════════════════════
      let toastTimer;
      function showToast(msg, duration = 3500) {
        const t = document.getElementById("toast");
        t.textContent = msg;
        t.classList.add("show");
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.classList.remove("show"), duration);
      }

      // ══════════════════════════════════
      // COVER UPLOAD
      // ══════════════════════════════════
      function coverDragOver(e) {
        e.preventDefault();
        document.getElementById("cover-drop").classList.add("drag-over");
      }
      function coverDragLeave() {
        document.getElementById("cover-drop").classList.remove("drag-over");
      }
      function coverDrop(e) {
        e.preventDefault();
        document.getElementById("cover-drop").classList.remove("drag-over");
        const file = e.dataTransfer.files[0];
        if (file) processCoverFile(file);
      }
      function coverFileSelect(e) {
        const file = e.target.files[0];
        if (file) processCoverFile(file);
      }
      function processCoverFile(file) {
        const maxSize = 5 * 1024 * 1024;
        const allowed = ["image/jpeg", "image/png", "image/webp"];
        if (!allowed.includes(file.type)) {
          showToast("⚠️ Formato não suportado. Use JPG, PNG ou WEBP");
          return;
        }
        if (file.size > maxSize) {
          showToast("⚠️ Imagem muito grande (máximo 5 MB)");
          return;
        }
        selectedCover = file;
        const reader = new FileReader();
        reader.onload = function (ev) {
          const img = document.getElementById("cover-preview-img");
          img.src = ev.target.result;
          document.getElementById("cover-drop").style.display = "none";
          document
            .getElementById("cover-preview-wrap")
            .classList.add("visible");
          // summary cover
          document.getElementById("sum-cover").textContent = "🖼️ " + file.name;
          document.getElementById("sum-cover").className = "s-val";
          // sfp preview
          const sfpImg = document.getElementById("sfp-img");
          sfpImg.src = ev.target.result;
          sfpImg.style.display = "block";
          document.getElementById("sfp-no-img").style.display = "none";
          document.getElementById("sfp-wrap").classList.add("visible");
          updateSfp();
        };
        reader.readAsDataURL(file);
      }
      function removeCover(e) {
        e.stopPropagation();
        selectedCover = null;
        document
          .getElementById("cover-preview-wrap")
          .classList.remove("visible");
        document.getElementById("cover-drop").style.display = "";
        document.getElementById("cover-input").value = "";
        document.getElementById("sum-cover").textContent = "Sem capa";
        document.getElementById("sum-cover").className = "s-val empty";
        document.getElementById("sfp-img").style.display = "none";
        document.getElementById("sfp-no-img").style.display = "flex";
        updateSfp();
      }

      // ══════════════════════════════════
      // FILE UPLOAD
      // ══════════════════════════════════
      function handleDragOver(e) {
        e.preventDefault();
        document.getElementById("drop-zone").classList.add("drag-over");
      }
      function handleDragLeave() {
        document.getElementById("drop-zone").classList.remove("drag-over");
      }
      function handleDrop(e) {
        e.preventDefault();
        document.getElementById("drop-zone").classList.remove("drag-over");
        const file = e.dataTransfer.files[0];
        if (file) processFile(file);
      }
      function handleFileSelectEvt(e) {
        const file = e.target.files[0];
        if (file) processFile(file);
      }
      function processFile(file) {
        const maxSize = 50 * 1024 * 1024;
        const allowed = [
          "application/pdf",
          "application/msword",
          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        ];
        if (file.size > maxSize) {
          showToast("⚠️ Ficheiro muito grande (máximo 50 MB)");
          return;
        }
        if (!allowed.includes(file.type)) {
          showToast("⚠️ Tipo de ficheiro não permitido");
          return;
        }
        selectedFile = file;
        showFilePreview(file);
      }
      function showFilePreview(file) {
        const ext = file.name.split(".").pop().toUpperCase();
        const size = (file.size / 1024 / 1024).toFixed(1);
        file_size = size;
        document.getElementById("file-name").textContent = file.name;
        document.getElementById("file-size").textContent =
          size + " MB · " + ext;
        document.getElementById("file-preview").classList.add("visible");
        updateSfp();
        simulateUpload();
        updateSummary();
      }
      function removeFile() {
        selectedFile = null;
        document.getElementById("file-preview").classList.remove("visible");
        document.getElementById("upload-progress").classList.remove("visible");
        document.getElementById("file-input").value = "";
        if (!selectedCover)
          document.getElementById("sfp-wrap").classList.remove("visible");
        updateSummary();
      }
      function simulateUpload() {
        const prog = document.getElementById("upload-progress");
        const fill = document.getElementById("prog-fill");
        const pct = document.getElementById("prog-pct");
        const label = document.getElementById("prog-label");
        prog.classList.add("visible");
        let p = 0;
        const iv = setInterval(() => {
          p += Math.random() * 20;
          if (p >= 100) {
            p = 100;
            clearInterval(iv);
            label.textContent = "✓ Ficheiro pronto";
          }
          fill.style.width = p + "%";
          pct.textContent = Math.round(p) + "%";
        }, 160);
      }
      function updateSfp() {
        const wrap = document.getElementById("sfp-wrap");
        const nameEl = document.getElementById("sfp-name");
        const sizeEl = document.getElementById("sfp-size");
        if (selectedFile || selectedCover) {
          wrap.classList.add("visible");
          if (selectedFile) {
            nameEl.textContent = selectedFile.name;
            sizeEl.textContent =
              file_size +
              " MB · " +
              selectedFile.name.split(".").pop().toUpperCase();
          } else {
            nameEl.textContent = selectedCover ? selectedCover.name : "—";
            sizeEl.textContent = "";
          }
        } else {
          wrap.classList.remove("visible");
        }
      }

      // ══════════════════════════════════
      // DOC TYPE
      // ══════════════════════════════════
      function selectDocType(el, type) {
        document
          .querySelectorAll(".doc-type-option")
          .forEach((o) => o.classList.remove("selected"));
        el.classList.add("selected");
        selectedDocType = type;
        updateSummary();
      }

      // ══════════════════════════════════
      // PAYMENT METHODS
      // ══════════════════════════════════
      function togglePaymentMethod(el, name) {
        el.classList.toggle("selected");
        const check = el.querySelector(".pm-check");
        if (el.classList.contains("selected")) {
          check.textContent = "✓";
          if (!selectedPaymentMethods.includes(name))
            selectedPaymentMethods.push(name);
        } else {
          check.textContent = "";
          selectedPaymentMethods = selectedPaymentMethods.filter(
            (m) => m !== name,
          );
        }
        updateSummary();
      }

      // ══════════════════════════════════
      // PUBLICATION MODE
      // ══════════════════════════════════
      function selectPublication(mode) {
        pubMode = mode;
        document
          .getElementById("pub-immediate")
          .classList.toggle("selected", mode === "immediate");
        document
          .getElementById("pub-scheduled")
          .classList.toggle("selected", mode === "scheduled");
        document
          .getElementById("scheduler-box")
          .classList.toggle("visible", mode === "scheduled");
        document.getElementById("sum-sched-row").style.display =
          mode === "scheduled" ? "flex" : "none";
        document.getElementById("sum-pub").textContent =
          mode === "immediate" ? "🚀 Imediata" : "⏰ Programada";
        updateSummary();
      }

      // ══════════════════════════════════
      // ACCESS MODE
      // ══════════════════════════════════
      function selectAccess(mode) {
        accessMode = mode;
        document
          .getElementById("access-paid")
          .classList.toggle("selected", mode === "paid");
        document
          .getElementById("access-free")
          .classList.toggle("selected", mode === "free");
        const pc = document.getElementById("pricing-card");
        pc.style.opacity = mode === "free" ? "0.55" : "1";
        pc.style.pointerEvents = mode === "free" ? "none" : "all";
        document.getElementById("sum-access").textContent =
          mode === "paid" ? "💰 Pago" : "🆓 Gratuito";
        if (mode === "free") {
          document.getElementById("doc-price").value = "0";
          document.querySelectorAll(".pm-option").forEach((o) => {
            o.classList.remove("selected");
            o.querySelector(".pm-check").textContent = "";
          });
          selectedPaymentMethods = [];
        }
        updateSummary();
      }

      // ══════════════════════════════════
      // SUMMARY
      // ══════════════════════════════════
      function updateSummary() {
        const title = document.getElementById("doc-title").value;
        const date = document.getElementById("doc-date").value;
        const inst = document.getElementById("doc-inst").value;
        const schedDate = document.getElementById("sched-date").value;
        const schedTime = document.getElementById("sched-time").value;

        setS("sum-title", title || null, "Não preenchido");
        setS("sum-type", selectedDocType || null, "Não seleccionado");
        setS("sum-date", date ? formatDate(date) : null, "—");
        setS("sum-inst", inst || null, "—");

        if (pubMode === "scheduled" && schedDate) {
          document.getElementById("sum-sched").textContent =
            formatDate(schedDate) + (schedTime ? " às " + schedTime : "");
        }

      }

      function setS(id, val, empty) {
        const el = document.getElementById(id);
        if (val) {
          el.textContent = val;
          el.className = "s-val";
        } else {
          el.textContent = empty;
          el.className = "s-val empty";
        }
      }

      function formatDate(d) {
        const [y, m, day] = d.split("-");
        const months = [
          "Jan",
          "Fev",
          "Mar",
          "Abr",
          "Mai",
          "Jun",
          "Jul",
          "Ago",
          "Set",
          "Out",
          "Nov",
          "Dez",
        ];
        return `${day} ${months[parseInt(m) - 1]} ${y}`;
      }


      // ══════════════════════════════════
      // RESET
      // ══════════════════════════════════
      function resetUploadForm() {
        [
          "doc-title",
          "doc-date",
          "doc-inst",
          "doc-abstract",
          "doc-tags",
          "doc-price",
          "doc-authors",
          "doc-advisor",
        ].forEach((id) => {
          document.getElementById(id).value = "";
        });
        document
          .querySelectorAll(".doc-type-option")
          .forEach((o) => o.classList.remove("selected"));
        document.querySelectorAll(".pm-option").forEach((o) => {
          o.classList.remove("selected");
          o.querySelector(".pm-check").textContent = "";
        });
        selectedDocType = "";
        selectedPaymentMethods = [];
        selectPublication("immediate");
        selectAccess("paid");
        removeFile();
        removeCoverSilent();
        updateSummary();
        showToast("🔄 Formulário limpo com sucesso");
      }
      function removeCoverSilent() {
        selectedCover = null;
        document
          .getElementById("cover-preview-wrap")
          .classList.remove("visible");
        document.getElementById("cover-drop").style.display = "";
        document.getElementById("cover-input").value = "";
        document.getElementById("sum-cover").textContent = "Sem capa";
        document.getElementById("sum-cover").className = "s-val empty";
        document.getElementById("sfp-img").style.display = "none";
        document.getElementById("sfp-no-img").style.display = "flex";
      }

      // ══════════════════════════════════
      // INIT
      // ══════════════════════════════════
      document.getElementById("doc-date").valueAsDate = new Date();
      updateSummary();
    </script>
  </body>
</html>
