<?php
require_once 'includes.php';
$users = $db->query("SELECT COUNT(*) as total FROM users WHERE NOT type = 'ADMIN'");
$documents_c = $db->query("SELECT COUNT(*) as total FROM documents WHERE status = 'PUBLICADO'");
$opportunities_c = $db->query("SELECT COUNT(*) as total FROM opportunities WHERE is_approved = 1");
$noticies_c = $db->query("SELECT COUNT(*) as total FROM notices");
$notices = $db->query("SELECT * FROM notices ORDER BY created_at DESC");

$users_count = $users->fetch();
$documents_count = $documents_c->fetch();
$opportunities_count = $opportunities_c->fetch();
$notices_count = $noticies_c->fetch();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Sobre</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= publicCss() ?>
<style>
.page-hero{background:linear-gradient(150deg,var(--cr-dk) 0%,var(--cr-lt) 45%,#1A2A50 75%,#0E1A30 100%);padding:clamp(48px,8vw,84px) clamp(14px,4vw,40px);position:relative;overflow:hidden;text-align:center}
.page-hero::before{content:'';position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.13) 0%,transparent 65%);top:-120px;right:-60px;pointer-events:none}
.page-hero::after{content:'';position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.08) 0%,transparent 65%);bottom:-60px;left:15%;pointer-events:none}
.ph-inner{position:relative;z-index:1;max-width:680px;margin:0 auto}
.ph-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);padding:5px 16px;border-radius:100px;font-size:11px;font-weight:700;color:rgba(255,255,255,.80);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px}
.ph-title{font-family:'Arial',serif;font-size:clamp(26px,5vw,50px);font-weight:900;color:#fff;margin-bottom:12px}
.ph-title em{color:var(--gd-lt);font-style:normal}
.ph-sub{font-size:clamp(14px,1.6vw,17px);color:rgba(255,255,255,.68);line-height:1.65}

.page-content{max-width:900px;margin:0 auto;padding:clamp(40px,6vw,70px) clamp(14px,4vw,40px)}

/* ABOUT SECTION */
.about-lead{font-size:clamp(16px,2vw,19px);font-weight:700;color:var(--tx);line-height:1.6;text-align:center;max-width:700px;margin:0 auto clamp(36px,5vw,56px);padding:clamp(20px,3vw,32px);background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);box-shadow:var(--sh1)}
.sec-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:2px;color:var(--cr);margin-bottom:6px;display:flex;align-items:center;gap:7px}
.sec-label::before{content:'';display:block;width:22px;height:2px;background:var(--cr);border-radius:1px;flex-shrink:0}
.sec-title{font-family:'Arial',serif;font-size:clamp(22px,3.5vw,32px);font-weight:700;color:var(--tx);margin-bottom:clamp(14px,2vw,20px)}
.sec-text{font-size:clamp(13px,1.4vw,15px);color:var(--tx-m);line-height:1.75;margin-bottom:14px}

/* MISSION/VALUES GRID */
.mvg{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(240px,28vw,300px),1fr));gap:clamp(14px,2vw,20px);margin-bottom:clamp(40px,6vw,64px)}
.mv-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);padding:clamp(20px,3vw,28px);box-shadow:var(--sh0);animation:fadeUp .4s ease both;position:relative;overflow:hidden}
.mv-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--cr)}
.mv-ico{font-size:clamp(32px,5vw,44px);margin-bottom:14px}
.mv-title{font-family:'Arial',serif;font-size:clamp(16px,2vw,19px);font-weight:700;color:var(--tx);margin-bottom:10px}
.mv-text{font-size:13px;color:var(--tx-m);line-height:1.65}

/* WHAT YOU FIND */
.wf-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(clamp(160px,20vw,200px),1fr));gap:clamp(12px,2vw,16px);margin-bottom:clamp(40px,6vw,64px)}
.wf-item{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(16px,2vw,22px);text-align:center;box-shadow:var(--sh0);animation:fadeUp .4s ease both;transition:all var(--t)}
.wf-item:hover{box-shadow:var(--sh2);transform:translateY(-3px)}
.wf-ico{font-size:clamp(28px,4vw,36px);margin-bottom:10px}
.wf-name{font-size:clamp(13px,1.5vw,14px);font-weight:700;color:var(--tx);margin-bottom:6px}
.wf-desc{font-size:12px;color:var(--tx-l);line-height:1.5}

/* DIFFERENTIALS */
.diff-list{display:flex;flex-direction:column;gap:12px;margin-bottom:clamp(40px,6vw,64px)}
.diff-item{display:flex;align-items:flex-start;gap:14px;background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:clamp(14px,2vw,20px);box-shadow:var(--sh0);animation:fadeUp .4s ease both;transition:all var(--t)}
.diff-item:hover{box-shadow:var(--sh1);border-color:var(--cr-bdr)}
.diff-ico{width:46px;height:46px;border-radius:var(--r2);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;background:var(--cr-xl)}
.diff-body{flex:1;min-width:0}
.diff-title{font-size:14px;font-weight:700;color:var(--tx);margin-bottom:4px}
.diff-text{font-size:13px;color:var(--tx-m);line-height:1.55}

/* STATS */
.stats-strip{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 55%,#1A3060 100%);border-radius:var(--r5);padding:clamp(28px,4vw,44px) clamp(20px,4vw,40px);margin-bottom:clamp(40px,6vw,64px);display:flex;justify-content:space-around;align-items:center;flex-wrap:wrap;gap:24px;position:relative;overflow:hidden}
.stats-strip::before{content:'';position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-80px;right:-40px;pointer-events:none}
.st-item{text-align:center;position:relative;z-index:1}
.st-num{font-family:'Arial',serif;font-size:clamp(28px,5vw,44px);font-weight:900;color:var(--gd-lt)}
.st-lbl{font-size:12px;color:rgba(255,255,255,.55);margin-top:4px}

/* TEAM / DEV */
.dev-card{background:linear-gradient(135deg,var(--cr-xl),var(--warm));border:1px solid var(--cr-bdr);border-radius:var(--r4);padding:clamp(20px,3vw,32px);text-align:center;margin-bottom:clamp(40px,6vw,64px)}
.dev-title{font-family:'Arial',serif;font-size:clamp(18px,2.5vw,22px);font-weight:700;color:var(--cr-dk);margin-bottom:8px}
.dev-sub{font-size:14px;color:var(--tx-m);line-height:1.65;max-width:560px;margin:0 auto}

@media(max-width:600px){.mvg{grid-template-columns:1fr}.wf-grid{grid-template-columns:repeat(2,1fr)}.stats-strip{flex-direction:column;gap:16px}}
</style>
</head>
<body>
<div class="toast" id="toast"></div>
<?= pubNav('sobre') ?>

<!-- HERO -->
<section class="page-hero">
  <div class="ph-inner">
    <div class="ph-eyebrow"><i class="fa fa-info"></i> Sobre o Portal</div>
    <h1 class="ph-title">O que é o <em>PetroPub</em>?</h1>
    <p class="ph-sub">O portal académico da Petrochamp, criado para centralizar e disponibilizar conteúdos científicos e técnicos do sector de petróleo e gás em Angola.</p>
  </div>
</section>

<div class="page-content">

  <!-- LEAD -->
  <div class="about-lead">
    "Petropub é o portal académico da Petrochamp, criado para centralizar e disponibilizar conteúdos científicos e técnicos do setor de petróleo e gás. A plataforma atende estudantes, professores e profissionais, oferecendo acesso organizado a artigos, relatórios, TCCs, apresentações e datasets, com foco em qualidade, confiabilidade e experiência moderna de uso."
  </div>

  <!-- OBJECTIVE -->
  <div style="margin-bottom:clamp(40px,6vw,64px)">
    <div class="sec-label">Missão</div>
    <div class="sec-title">O nosso objectivo</div>
    <p class="sec-text">Centralizar, organizar e disponibilizar conteúdos académicos e técnicos de forma confiável e acessível a toda a comunidade académica e profissional de Angola e do mundo lusófono.</p>
  </div>

  <!-- STATS -->
  <div class="stats-strip">
    <div class="st-item"><div class="st-num"><?=$documents_count['total']-1?>+</div><div class="st-lbl">Documentos publicados</div></div>
    <div class="st-item"><div class="st-num"><?=$opportunities_count['total']-1?>+</div><div class="st-lbl">Oportunidades registadas</div></div>
    <div class="st-item"><div class="st-num"><?=$users_count['total']-1?>+</div><div class="st-lbl">Utilizadores registados</div></div>
    <div class="st-item"><div class="st-num"><?=$notices_count['total']-1?>+</div><div class="st-lbl">Notícias frescas</div></div>
  </div>

  <!-- WHAT YOU FIND -->
  <div style="margin-bottom:clamp(14px,2vw,18px)">
    <div class="sec-label">Conteúdo</div>
    <div class="sec-title">O que encontra no PetroPub</div>
  </div>
  <div class="wf-grid">
    <?php $contents=[['<i class="fa fa-file" style="color: yellowgreen"></i>','Artigos Científicos','Artigos revistos por pares sobre petróleo, gás e áreas relacionadas'],['🎓','TCCs','Trabalhos de Conclusão de Curso das principais universidades angolanas'],['<i class="fa fa-book" style="color: green"></i>','Dissertações','Dissertações de mestrado e doutoramento'],['📖','Livros','Livros técnicos e académicos digitalizados'],['<i class="fa fa-bars"></i>','Relatórios','Relatórios técnicos e de investigação'],['📑','Apresentações','Slides e apresentações de conferências e seminários']]; foreach($contents as $i=>[$ico,$name,$desc]): ?>
    <div class="wf-item" style="animation-delay:<?=$i*.06?>s">
      <div class="wf-ico"><?=$ico?></div>
      <div class="wf-name"><?=$name?></div>
      <div class="wf-desc"><?=$desc?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- DIFFERENTIALS -->
  <div style="margin-bottom:clamp(14px,2vw,18px)">
    <div class="sec-label">Diferenciais</div>
    <div class="sec-title">Por que escolher o PetroPub</div>
  </div>
  <div class="diff-list">
    <?php $diffs=[
      ['<i class="fa fa-search"></i>','Avaliação académica por professores','Nenhum artigo é publicado sem avaliação docente e aprovação final do administrador. Garantimos qualidade e rigor científico.'],
      ['<i class="fa fa-cog"></i>','Sistema de submissão e aprovação completo','Fluxo transparente desde a submissão até à publicação, com notificações em cada etapa do processo.'],
      ['🎯','Gamificação e reputação','Sistema de pontos que incentiva a participação activa — ganhe reputação por submissões, avaliações e downloads.'],
      ['📱','Interface moderna e responsiva','Experiência de uso fluida em qualquer dispositivo, com pesquisa avançada e filtros intuitivos.'],
      ['🔒','Segurança e privacidade','Dados dos utilizadores protegidos. Conteúdos protegidos por direitos autorais. Plataforma segura e confiável.'],
    ]; foreach($diffs as $i=>[$ico,$title,$text]): ?>
    <div class="diff-item" style="animation-delay:<?=$i*.06?>s">
      <div class="diff-ico"><?=$ico?></div>
      <div class="diff-body"><div class="diff-title"><?=$title?></div><div class="diff-text"><?=$text?></div></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- MISSION / VISION / VALUES -->
  <div style="margin-bottom:clamp(14px,2vw,18px)">
    <div class="sec-label">Identidade</div>
    <div class="sec-title">Missão, Visão e Valores</div>
  </div>
  <div class="mvg">
    <div class="mv-card">
      <div class="mv-ico">🎯</div>
      <div class="mv-title">Missão</div>
      <div class="mv-text">Democratizar o acesso ao conhecimento académico e técnico do sector de petróleo e gás em Angola, conectando estudantes, professores e profissionais numa plataforma digital de qualidade.</div>
    </div>
    <div class="mv-card" style="animation-delay:.06s">
      <div class="mv-ico">🔭</div>
      <div class="mv-title">Visão</div>
      <div class="mv-text">Ser o portal de referência do conhecimento académico e técnico em Angola, reconhecido pela qualidade dos conteúdos, pela facilidade de uso e pelo impacto positivo na formação académica nacional.</div>
    </div>
    <div class="mv-card" style="animation-delay:.12s">
      <div class="mv-ico">💎</div>
      <div class="mv-title">Valores</div>
      <div class="mv-text">Qualidade, transparência, rigor científico, acessibilidade, inovação e compromisso com o desenvolvimento académico de Angola. Acreditamos que o conhecimento partilhado transforma sociedades.</div>
    </div>
  </div>

  <!-- DEVELOPED BY -->
  <div class="dev-card">
    <div style="font-size:42px;margin-bottom:12px">🇦🇴</div>
    <div class="dev-title">Desenvolvido em Angola, para Angola</div>
    <p class="dev-sub">O PetroPub é um projecto da <strong>Petrochamp</strong> desenvolvido pela <strong>Webtec Solution</strong> . Somos uma equipa angolana comprometida com a inovação tecnológica e o desenvolvimento académico nacional.</p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:18px">
      <a href="contact.php" class="btn btn-cr">Contactar-nos</a>
      <a href="library.php" class="btn btn-gh">Explorar Biblioteca</a>
    </div>
  </div>

</div>

<?= pubFooter() ?>
</body>
</html>
