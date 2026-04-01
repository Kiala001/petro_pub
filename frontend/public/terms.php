<?php
require_once 'includes.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Termos de Uso</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= publicCss() ?>
<style>
.page-hero{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 50%,#1A2A50 100%);padding:clamp(40px,7vw,70px) clamp(14px,4vw,40px);text-align:center;position:relative;overflow:hidden}
.page-hero::before{content:'';position:absolute;width:350px;height:350px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-100px;right:-40px;pointer-events:none}
.ph-inner{position:relative;z-index:1;max-width:640px;margin:0 auto}
.ph-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);padding:5px 16px;border-radius:100px;font-size:11px;font-weight:700;color:rgba(255,255,255,.80);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px}
.ph-title{font-family:'Arial',serif;font-size:clamp(24px,5vw,44px);font-weight:900;color:#fff;margin-bottom:10px}
.ph-sub{font-size:clamp(13px,1.5vw,15px);color:rgba(255,255,255,.65);line-height:1.6}
.content-wrap{max-width:800px;margin:0 auto;padding:clamp(36px,6vw,60px) clamp(14px,4vw,40px)}
/* TABLE OF CONTENTS */
.toc{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);padding:clamp(18px,2.5vw,26px);margin-bottom:clamp(28px,4vw,44px);box-shadow:var(--sh0)}
.toc-title{font-size:13px;font-weight:800;color:var(--tx);text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px;display:flex;align-items:center;gap:7px}
.toc-list{list-style:none;display:flex;flex-direction:column;gap:6px}
.toc-list li a{font-size:14px;color:var(--cr);text-decoration:none;display:flex;align-items:center;gap:8px;padding:4px 0;transition:color var(--t)}
.toc-list li a:hover{color:var(--cr-dk)}.toc-num{font-size:11px;font-weight:700;background:var(--cr-xl);color:var(--cr);width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
/* SECTIONS */
.term-section{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);padding:clamp(20px,3vw,32px);margin-bottom:clamp(14px,2vw,18px);box-shadow:var(--sh0);scroll-margin-top:80px;animation:fadeUp .4s ease both}
.ts-num{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:var(--cr);color:#fff;border-radius:50%;font-size:13px;font-weight:800;flex-shrink:0}
.ts-header{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.ts-title{font-family:'Arial',serif;font-size:clamp(16px,2vw,20px);font-weight:700;color:var(--tx)}
.ts-body{font-size:clamp(13px,1.4vw,14px);color:var(--tx-m);line-height:1.75}
.ts-body p{margin-bottom:10px}.ts-body p:last-child{margin-bottom:0}
.ts-body ul{margin:10px 0 10px 20px;display:flex;flex-direction:column;gap:6px}
.ts-body ul li{line-height:1.6}
.highlight-box{background:var(--cr-xl);border:1px solid var(--cr-bdr);border-radius:var(--r2);padding:12px 16px;margin:12px 0;font-size:13px;color:var(--cr-dk);line-height:1.6;font-weight:500}
.last-update{text-align:center;padding:clamp(16px,2.5vw,24px);background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);font-size:13px;color:var(--tx-l);margin-top:clamp(20px,3vw,28px)}
</style>
</head>
<body>
<div class="toast" id="toast"></div>
<?= pubNav() ?>

<section class="page-hero">
  <div class="ph-inner">
    <div class="ph-eyebrow">⚖️ Legal</div>
    <h1 class="ph-title">Termos de Uso</h1>
    <p class="ph-sub">Ao utilizar o PetroPub, concorda com os seguintes termos e condições. Leia atentamente antes de utilizar a plataforma.</p>
  </div>
</section>

<div class="content-wrap">
  <!-- TABLE OF CONTENTS -->
  <div class="toc">
    <div class="toc-title">📋 Índice dos Termos</div>
    <ul class="toc-list">
      <?php $tocItems = ['Aceitação dos Termos','Propriedade Intelectual','Responsabilidades do Utilizador','Validação de Conteúdos','Limitação de Responsabilidade','Privacidade dos Dados','Actualizações dos Termos'];
      foreach ($tocItems as $i => $item): ?>
      <li><a href="#term-<?=$i+1?>"><span class="toc-num"><?=$i+1?></span><?=$item?></a></li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- TERMS SECTIONS -->
  <?php $terms = [
    [1,'Aceitação dos Termos','O acesso e uso do Petropub implicam na aceitação destes termos:','<p>Ao utilizar o portal, você concorda com os termos de uso e políticas da plataforma. O uso continuado da plataforma constitui aceitação de quaisquer alterações efectuadas aos termos.</p><div class="highlight-box">⚠️ Se não concordar com qualquer parte destes termos, deve descontinuar imediatamente o uso da plataforma.</div>'],
    [2,'Propriedade Intelectual','Direitos autorais e protecção de conteúdos','<p>Todos os conteúdos publicados no PetroPub são protegidos por direitos autorais. É estritamente proibida a cópia, reprodução ou distribuição sem autorização expressa dos autores e da plataforma.</p><p>Os autores que submetem conteúdos mantêm os seus direitos autorais, mas concedem ao PetroPub uma licença de uso para fins de disponibilização e divulgação académica.</p>'],
    [3,'Responsabilidades do Utilizador','O que se espera de cada utilizador','<ul><li>Submeter apenas conteúdos originais e em conformidade com normas académicas reconhecidas</li><li>Manter respeito e cordialidade em comentários e todas as formas de interacção</li><li>Não utilizar a plataforma para fins ilícitos, ofensivos ou contrários à ética académica</li><li>Manter as credenciais de acesso em segredo e não partilhá-las com terceiros</li><li>Reportar qualquer conteúdo impróprio ou violação de direitos autorais</li></ul>'],
    [4,'Validação de Conteúdos','Processo editorial e aprovação','<p>Nenhum artigo ou documento será publicado sem avaliação docente e aprovação final do administrador da plataforma.</p><p>O processo de validação inclui revisão de originalidade, qualidade académica e conformidade com as normas da plataforma. O PetroPub reserva-se o direito de recusar ou remover qualquer conteúdo que não cumpra os critérios de qualidade estabelecidos.</p>'],
    [5,'Limitação de Responsabilidade','Âmbito da responsabilidade da plataforma','<p>A Petrochamp e a Webtec Solution não se responsabilizam por decisões ou acções baseadas nos conteúdos da plataforma. Os conteúdos disponíveis são de responsabilidade dos respectivos autores.</p><p>A plataforma não garante a disponibilidade ininterrupta do serviço e pode efectuar manutenções programadas com aviso prévio.</p>'],
    [6,'Privacidade dos Dados','Como tratamos a sua informação pessoal','<p>Os dados dos utilizadores são usados exclusivamente para operação e gestão da plataforma, sem compartilhamento externo sem consentimento do utilizador.</p><p>As informações recolhidas incluem dados de registo, actividade na plataforma e preferências de uso. Todos os dados são tratados com confidencialidade e segurança.</p><div class="highlight-box">🔒 A sua privacidade é uma prioridade. Não vendemos nem partilhamos os seus dados com terceiros.</div>'],
    [7,'Actualizações dos Termos','Alterações a estes termos','<p>Os termos de uso podem ser alterados a qualquer momento para reflectir mudanças na plataforma ou requisitos legais. Os utilizadores serão notificados sobre mudanças importantes através da plataforma.</p><p>A continuação do uso da plataforma após as alterações constitui aceitação dos novos termos.</p>'],
  ]; foreach ($terms as $i => [$num,$title,$subtitle,$body]): ?>
  <div class="term-section" id="term-<?=$num?>" style="animation-delay:<?=$i*.06?>s">
    <div class="ts-header">
      <div class="ts-num"><?=$num?></div>
      <div>
        <div class="ts-title"><?=$title?></div>
        <div style="font-size:12px;color:var(--tx-l);margin-top:2px"><?=$subtitle?></div>
      </div>
    </div>
    <div class="ts-body"><?=$body?></div>
  </div>
  <?php endforeach; ?>

  <div class="last-update">
    <span>Para dúvidas sobre estes termos, contacte-nos em <a href="mailto:suporte@petropub.com" style="color:var(--cr)">suporte@petropub.com</a></span>
  </div>
</div>

<?= pubFooter() ?>
</body>
</html>
