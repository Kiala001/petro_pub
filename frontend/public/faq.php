<?php
require_once 'includes.php';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Perguntas Frequentes</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= publicCss() ?>
<style>
.page-hero{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 50%,#1A2A50 100%);padding:clamp(40px,7vw,72px) clamp(14px,4vw,40px);text-align:center;position:relative;overflow:hidden}
.page-hero::before{content:'';position:absolute;width:350px;height:350px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-100px;right:-40px;pointer-events:none}
.ph-inner{position:relative;z-index:1;max-width:640px;margin:0 auto}
.ph-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);padding:5px 16px;border-radius:100px;font-size:11px;font-weight:700;color:rgba(255,255,255,.80);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px}
.ph-title{font-family:'Arial',serif;font-size:clamp(24px,5vw,44px);font-weight:900;color:#fff;margin-bottom:10px}
.ph-sub{font-size:clamp(13px,1.5vw,15px);color:rgba(255,255,255,.65);line-height:1.6;margin-bottom:20px}
/* SEARCH */
.faq-search{position:relative;max-width:480px;margin:0 auto}
.faq-s-input{width:100%;padding:13px 46px 13px 46px;border:none;border-radius:var(--r3);font-size:14px;color:var(--tx);background:rgba(255,255,255,.95);outline:none;box-shadow:0 8px 28px rgba(0,0,0,.25)}
.faq-s-ico{position:absolute;left:16px;top:50%;transform:translateY(-50%);font-size:17px;pointer-events:none}
.faq-s-clear{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:24px;height:24px;border-radius:50%;background:var(--tx-l);color:#fff;border:none;cursor:pointer;font-size:11px;display:none;align-items:center;justify-content:center}
.faq-s-clear.show{display:flex}

.content-wrap{max-width:860px;margin:0 auto;padding:clamp(32px,5vw,56px) clamp(14px,4vw,40px)}

/* CATEGORY TABS */
.cat-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:clamp(24px,3.5vw,36px)}
.cat-tab{padding:8px 18px;border-radius:100px;border:1.5px solid var(--bdr);background:#fff;font-size:13px;font-weight:600;color:var(--tx-l);cursor:pointer;transition:all var(--t)}
.cat-tab.on{background:var(--cr);color:#fff;border-color:var(--cr)}
.cat-tab:hover:not(.on){border-color:var(--cr-bdr);color:var(--cr)}

/* FAQ ITEM */
.faq-group{margin-bottom:clamp(24px,3.5vw,36px)}
.faq-group-title{font-family:'Arial',serif;font-size:clamp(17px,2.2vw,21px);font-weight:700;color:var(--tx);margin-bottom:14px;display:flex;align-items:center;gap:9px;padding-bottom:10px;border-bottom:2px solid var(--bdr)}
.faq-item{background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);margin-bottom:8px;overflow:hidden;transition:box-shadow var(--t);animation:fadeUp .38s ease both}
.faq-item:hover{box-shadow:var(--sh1)}
.faq-item.open{box-shadow:var(--sh1);border-color:var(--cr-bdr)}
.faq-q{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:clamp(14px,2vw,18px) clamp(16px,2.5vw,22px);cursor:pointer;user-select:none;-webkit-tap-highlight-color:transparent}
.faq-q-text{font-size:clamp(13px,1.5vw,15px);font-weight:700;color:var(--tx);line-height:1.4;flex:1}
.faq-item.open .faq-q-text{color:var(--cr)}
.faq-icon{width:28px;height:28px;border-radius:50%;background:var(--cream);border:1.5px solid var(--bdr);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--tx-m);flex-shrink:0;transition:all var(--t)}
.faq-item.open .faq-icon{background:var(--cr);border-color:var(--cr);color:#fff;transform:rotate(45deg)}
.faq-a{display:none;padding:0 clamp(16px,2.5vw,22px) clamp(14px,2vw,18px);font-size:clamp(13px,1.4vw,14px);color:var(--tx-m);line-height:1.75;border-top:1px solid var(--bdr2)}
.faq-a p{margin-bottom:10px}.faq-a p:last-child{margin-bottom:0}
.faq-a a{color:var(--cr);font-weight:600}.faq-a a:hover{text-decoration:underline}
.faq-item.open .faq-a{display:block;animation:fadeUp .2s ease both;padding-top:14px}

/* STILL NEED HELP */
.help-card{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 55%,#1A3060 100%);border-radius:var(--r4);padding:clamp(24px,4vw,40px);text-align:center;position:relative;overflow:hidden;margin-top:clamp(28px,4vw,44px)}
.help-card::before{content:'';position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-80px;right:-40px;pointer-events:none}
.hc-title{font-family:'Arial',serif;font-size:clamp(20px,3vw,26px);font-weight:700;color:#fff;margin-bottom:8px;position:relative;z-index:1}
.hc-sub{font-size:14px;color:rgba(255,255,255,.65);margin-bottom:18px;position:relative;z-index:1;line-height:1.6}
.hc-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1}

.no-results{text-align:center;padding:clamp(40px,6vw,60px) 20px;display:none}
.no-results.show{display:block}
.no-results-ico{font-size:48px;opacity:.2;margin-bottom:12px}
.no-results-title{font-family:'Arial',serif;font-size:18px;color:var(--tx-m);margin-bottom:6px}
</style>
</head>
<body>
<div class="toast" id="toast"></div>
<?= pubNav('faq') ?>

<section class="page-hero">
  <div class="ph-inner">
    <div class="ph-eyebrow">❓ Ajuda</div>
    <h1 class="ph-title">Perguntas Frequentes</h1>
    <p class="ph-sub">Encontre respostas rápidas para as dúvidas mais comuns sobre o PetroPub.</p>
    <div class="faq-search">
      <span class="faq-s-ico"><i class="fa fa-search"></i></span>
      <input class="faq-s-input" id="faq-search" type="text" placeholder="Pesquisar perguntas…" oninput="filterFAQ(this.value)">
      <button class="faq-s-clear" id="faq-clear" onclick="clearSearch()">✕</button>
    </div>
  </div>
</section>

<div class="content-wrap">
  <!-- CATEGORY TABS -->
  <div class="cat-tabs">
    <button class="cat-tab on" onclick="filterCat('all',this)">📦 Todas</button>
    <button class="cat-tab" onclick="filterCat('documentos',this)"><i class="fa fa-book"></i> Documentos</button>
    <button class="cat-tab" onclick="filterCat('submissao',this)"><i class="fa fa-send"></i> Submissão</button>
    <button class="cat-tab" onclick="filterCat('conta',this)"><i class="fa fa-user"></i> Conta & Acesso</button>
    <button class="cat-tab" onclick="filterCat('tecnico',this)"><i class="fa fa-cog"></i> Técnico</button>
    <!-- <button class="cat-tab" onclick="filterCat('pagamentos',this)"><i class="fa fa-money"></i> Pagamentos</button> -->
  </div>

  <?php
  $faqs = [
    'documentos' => ['📚 Documentos', [
      ['Como pesquisar documentos?', '<p>Use a barra de pesquisa no topo de qualquer página ou na Biblioteca. Pode pesquisar por título, autor, palavras-chave ou área temática. Use os filtros da sidebar para refinar os resultados por categoria, ano ou tipo.</p>'],
      ['Posso guardar documentos para ler mais tarde?', '<p>Sim! Clique na estrela ☆ em qualquer documento para adicioná-lo aos favoritos. Aceda aos seus favoritos através do botão "Favoritos" no menu de navegação.</p>'],
      ['Como aceder a documentos pagos?', '<p>Documentos pagos exigem compra ou subscrição de um plano. Faça login, seleccione o documento, clique em "Comprar", escolha o método de pagamento e envie o comprovativo. O acesso é concedido após verificação (normalmente em 2 horas).</p>'],
      ['O que são documentos gratuitos?', '<p>Documentos marcados com "GRÁTIS" são de acesso livre. Basta criar uma conta e fazer login para os ler e baixar sem custo adicional.</p>'],
      ]],
      'submissao' => ['📤 Submissão de Conteúdos', [
        ['Como submeter um documento?', '<p>Faça login e clique em "Submeter Conteúdo". Preencha o formulário com título, autores, resumo, palavras-chave e carregue o ficheiro. O documento passará por avaliação académica antes da publicação.</p>'],
        ['Quanto tempo demora a aprovação?', '<p>O processo de avaliação demora normalmente entre 5 a 15 dias úteis. Receberá notificações sobre cada etapa do processo. Em caso de dúvidas, contacte-nos em <a href="mailto:suporte@petropub.com">suporte@petropub.com</a>.</p>'],
        ['Que formatos de ficheiro são aceites?', '<p>Aceitamos ficheiros nos seguintes formatos: PDF, DOCX, PPTX. O tamanho máximo por ficheiro é 50 MB. Recomendamos PDF para melhor compatibilidade e visualização.</p>'],
        ['Posso actualizar um documento já publicado?', '<p>Sim. Aceda ao painel do utilizador, localize o documento e clique em "Actualizar versão". O sistema criará uma nova versão e o documento passará novamente pelo processo de aprovação.</p>'],
      ]],
      'conta' => ['👤 Conta & Acesso', [
        ['Como criar uma conta no PetroPub?', '<p>Clique em "Registar" no topo da página. Preencha o formulário com o seu nome, e-mail institucional e palavra-passe. Receberá um e-mail de confirmação. Após confirmar, poderá aceder à plataforma.</p>'],
        ['Esqueci a minha palavra-passe. Como recuperar?', '<p>Na página de login, clique em "Esqueci a palavra-passe". Introduza o e-mail associado à sua conta. Receberá instruções para definir uma nova palavra-passe.</p>'],
        ['Posso usar o PetroPub sem conta?', '<p>Sim! Pode explorar e descobrir documentos sem criar conta. Contudo, para ler documentos completos, baixar ficheiros, submeter conteúdos ou guardar favoritos, é necessário ter uma conta registada.</p>'],
        ['Como alterar os meus dados de perfil?', '<p>Após fazer login, aceda às <a href="#">Configurações de perfil</a> no menu do utilizador. Pode alterar o nome, instituição, fotografia e e-mail de contacto.</p>'],
      ]],
    'tecnico' => ['⚙️ Questões Técnicas', [
      ['O portal não está a funcionar correctamente. O que fazer?', '<p>Tente as seguintes soluções: limpe o cache do browser (Ctrl+Shift+Delete), actualize a página (F5), tente num browser diferente. Se o problema persistir, contacte o suporte técnico com uma descrição detalhada do problema.</p>'],
      ['Como reportar um erro ou conteúdo inadequado?', '<p>Use o botão "Reportar" disponível em cada documento ou oportunidade. Para erros técnicos, contacte-nos em <a href="mailto:suporte@petropub.com">suporte@petropub.com</a> com capturas de ecrã se possível.</p>'],
      ['O PetroPub funciona em telemóvel?', '<p>Sim! O PetroPub foi desenvolvido com design responsivo e funciona em qualquer dispositivo — computador, tablet ou smartphone. A experiência é optimizada para todos os tamanhos de ecrã.</p>'],
      ['Os meus dados estão seguros?', '<p>Sim. Utilizamos encriptação SSL para proteger as comunicações. Os seus dados pessoais são tratados com confidencialidade e não são partilhados com terceiros sem o seu consentimento, conforme os nossos <a href="termos.php">Termos de Uso</a>.</p>'],
    ]],
    // 'pagamentos' => ['💰 Pagamentos', [
    //   ['Que métodos de pagamento são aceites?', '<p>Aceitamos Transferência Bancária (IBAN), Depósito Bancário, Multicaixa Express e Kwik. O pagamento é feito directamente e o comprovativo é enviado através da plataforma.</p>'],
    //   ['Como funciona o sistema de pontos?', '<p>Ganha pontos por cada acção na plataforma: submissão de documentos (+200 pts), download (+5 pts), avaliação (+50 pts), comentário (+10 pts). Os pontos acumulados podem ser usados como desconto em compras.</p>'],
    //   ['O que acontece se o meu pagamento não for verificado?', '<p>Se não receber confirmação em 24 horas, contacte o suporte em <a href="mailto:suporte@petropub.com">suporte@petropub.com</a> com o comprovativo de pagamento. Verificamos e resolvemos em menos de 4 horas em dias úteis.</p>'],
    //   ['Posso pedir reembolso?', '<p>Pedidos de reembolso são avaliados caso a caso. Se tiver problemas com um documento adquirido (link inválido, ficheiro corrompido, etc.), contacte o suporte dentro de 48 horas após a compra.</p>'],
    // ]],
  ];
  $allIdx = 0;
  foreach ($faqs as $catKey => [$catTitle, $items]): ?>
  <div class="faq-group" data-cat="<?=$catKey?>">
    <div class="faq-group-title"><?=$catTitle?></div>
    <?php foreach ($items as $i => [$q,$a]): $allIdx++; ?>
    <div class="faq-item" id="faq-<?=$allIdx?>" data-cat="<?=$catKey?>" data-q="<?=strtolower(h($q))?>">
      <div class="faq-q" onclick="toggleFaq(<?=$allIdx?>)">
        <div class="faq-q-text"><?=h($q)?></div>
        <div class="faq-icon">+</div>
      </div>
      <div class="faq-a"><?=$a?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <div class="no-results" id="no-results">
    <div class="no-results-ico"><i class="fa fa-search"></i></div>
    <div class="no-results-title">Nenhuma resposta encontrada</div>
    <p style="font-size:14px;color:var(--tx-l)">Tente outros termos ou contacte o nosso suporte.</p>
  </div>

  <!-- STILL NEED HELP -->
  <div class="help-card">
    <div class="hc-title">Ainda precisa de ajuda?</div>
    <p class="hc-sub">A nossa equipa de suporte está disponível para responder a todas as suas questões.</p>
    <div class="hc-actions">
      <a href="contact.php" class="btn btn-gd"><i class="fa fa-envelope"></i> Contactar suporte</a>
      <a href="mailto:suporte@petropub.com" class="btn" style="background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.25)">✉️ suporte@petropub.com</a>
    </div>
  </div>
</div>

<?= pubFooter() ?>
<script>
function toggleFaq(id) {
    const item = document.getElementById('faq-'+id);
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(el=>el.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
}

let currentCat = 'all';
function filterCat(cat, btn) {
    currentCat = cat;
    document.querySelectorAll('.cat-tab').forEach(b=>b.classList.remove('on'));
    btn.classList.add('on');
    applyFilters();
}

function filterFAQ(val) {
    document.getElementById('faq-clear').classList.toggle('show', val.length > 0);
    applyFilters(val);
}

function applyFilters(searchVal) {
    const q = (searchVal ?? document.getElementById('faq-search').value).toLowerCase().trim();
    let anyVisible = false;

    document.querySelectorAll('.faq-item').forEach(item => {
        const catMatch = currentCat === 'all' || item.dataset.cat === currentCat;
        const qMatch   = !q || item.dataset.q.includes(q) || item.querySelector('.faq-a').textContent.toLowerCase().includes(q);
        const show     = catMatch && qMatch;
        item.style.display = show ? '' : 'none';
        if (show) anyVisible = true;
    });

    // hide/show groups
    document.querySelectorAll('.faq-group').forEach(group => {
        const vis = [...group.querySelectorAll('.faq-item')].some(i=>i.style.display!=='none');
        group.style.display = vis ? '' : 'none';
    });

    document.getElementById('no-results').classList.toggle('show', !anyVisible);
}

function clearSearch() {
    document.getElementById('faq-search').value='';
    document.getElementById('faq-clear').classList.remove('show');
    applyFilters('');
}
</script>
</body>
</html>
