<?php
require_once 'includes.php';

$flash = null;

// ─── PROCESS FORM ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = filter_var(trim($_POST['email']??''), FILTER_VALIDATE_EMAIL);
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $topic   = sanitize($_POST['topic']   ?? 'Geral');

    $errors = [];
    if (!$name || mb_strlen($name) < 2)       $errors[] = 'Nome inválido (mínimo 2 caracteres).';
    if (!$email)                               $errors[] = 'E-mail inválido.';
    if (!$subject || mb_strlen($subject) < 4) $errors[] = 'Assunto obrigatório.';
    if (!$message || mb_strlen($message) < 10)$errors[] = 'Mensagem muito curta (mínimo 10 caracteres).';

    if (empty($errors)) {
        // save to DB
        $db->prepare("INSERT INTO contacts (name,email,subject,message) VALUES (?,?,?,?)")
             ->execute([$name, $email, "[$topic] $subject", $message]);

        // ── SEND EMAIL ──────────────────────────────────────────
        $to      = 'suporte@petropub.com';
        $from    = 'noreply@petropub.com';
        $headers = implode("\r\n", [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "From: PetroPub <$from>",
            "Reply-To: $name <$email>",
            "X-Mailer: PetroPub/2.5",
        ]);
        $emailBody = "
        <html><body style='font-family:Arial,sans-serif;background:#f5f5f5;padding:20px'>
        <div style='max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)'>
          <div style='background:#4A0B16;padding:24px;text-align:center'>
            <h2 style='color:#E5C97E;font-size:22px;margin:0'>PetroPub — Nova Mensagem</h2>
          </div>
          <div style='padding:28px'>
            <table style='width:100%;border-collapse:collapse'>
              <tr><td style='padding:8px 0;color:#8A7060;font-size:13px;width:120px'>Nome</td><td style='padding:8px 0;font-weight:600;color:#1A1208'>" . htmlspecialchars($name) . "</td></tr>
              <tr><td style='padding:8px 0;color:#8A7060;font-size:13px'>E-mail</td><td style='padding:8px 0;font-weight:600;color:#1A1208'><a href='mailto:$email'>$email</a></td></tr>
              <tr><td style='padding:8px 0;color:#8A7060;font-size:13px'>Tópico</td><td style='padding:8px 0;font-weight:600;color:#1A1208'>" . htmlspecialchars($topic) . "</td></tr>
              <tr><td style='padding:8px 0;color:#8A7060;font-size:13px'>Assunto</td><td style='padding:8px 0;font-weight:600;color:#1A1208'>" . htmlspecialchars($subject) . "</td></tr>
            </table>
            <hr style='border:none;border-top:1px solid #f0e8e0;margin:18px 0'>
            <p style='color:#4A3728;font-size:14px;line-height:1.7'>" . nl2br(htmlspecialchars($message)) . "</p>
          </div>
          <div style='background:#FAF7F2;padding:16px 28px;text-align:center;font-size:12px;color:#8A7060'>
            Enviado em " . date('d/m/Y H:i') . " · PetroPub Portal Académico Angola
          </div>
        </div>
        </body></html>";

        @mail($to, "[$topic] " . htmlspecialchars($subject) . " — PetroPub", $emailBody, $headers);

        // confirmation to sender
        $confBody = "
        <html><body style='font-family:Arial,sans-serif;background:#f5f5f5;padding:20px'>
        <div style='max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden'>
          <div style='background:#4A0B16;padding:24px;text-align:center'>
            <h2 style='color:#E5C97E;margin:0'>Mensagem recebida!</h2>
          </div>
          <div style='padding:28px'>
            <p style='font-size:16px;font-weight:600;color:#1A1208;margin-bottom:12px'>Olá, " . htmlspecialchars($name) . "!</p>
            <p style='font-size:14px;color:#4A3728;line-height:1.7;margin-bottom:16px'>Recebemos a sua mensagem e iremos responder em breve (normalmente em 24–48 horas em dias úteis).</p>
            <div style='background:#FAF7F2;border-radius:8px;padding:16px;margin-bottom:18px;font-size:13px;color:#8A7060'>
              <strong style='color:#1A1208'>Assunto:</strong> " . htmlspecialchars($subject) . "<br>
              <strong style='color:#1A1208'>Tópico:</strong> " . htmlspecialchars($topic) . "
            </div>
            <p style='font-size:13px;color:#8A7060'>Se precisar de ajuda imediata, pode contactar-nos directamente por WhatsApp: +244 XXX XXX XXX</p>
          </div>
        </div>
        </body></html>";
        $confHeaders = implode("\r\n",["MIME-Version: 1.0","Content-Type: text/html; charset=UTF-8","From: PetroPub <noreply@petropub.com>"]);
        @mail($email, "Confirmação: recebemos a sua mensagem — PetroPub", $confBody, $confHeaders);

        $flash = ['type'=>'ok','msg'=>'<i class="fa fa-check"></i> Mensagem enviada com sucesso! Responderemos em 24–48 horas.'];
        // Clear POST
        $name=$email=$subject=$message=$topic='';
    } else {
        $flash = ['type'=>'er','msg'=>'<i class="fa fa-close"></i> '.implode(' ',$errors)];
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PetroPub — Contacto</title>
  <link rel="stylesheet" href="assets/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="assets/icons-reference/font-icon-style.css">
<?= publicCss() ?>
<style>
.page-hero{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 50%,#1A2A50 100%);padding:clamp(40px,7vw,72px) clamp(14px,4vw,40px);text-align:center;position:relative;overflow:hidden}
.page-hero::before{content:'';position:absolute;width:350px;height:350px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.12) 0%,transparent 65%);top:-100px;right:-40px;pointer-events:none}
.ph-inner{position:relative;z-index:1;max-width:640px;margin:0 auto}
.ph-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.18);padding:5px 16px;border-radius:100px;font-size:11px;font-weight:700;color:rgba(255,255,255,.80);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:16px}
.ph-title{font-family:'Arial',serif;font-size:clamp(24px,5vw,44px);font-weight:900;color:#fff;margin-bottom:10px}
.ph-sub{font-size:clamp(13px,1.5vw,15px);color:rgba(255,255,255,.65);line-height:1.6}
/* CONTENT */
.content-wrap{max-width:1000px;margin:0 auto;padding:clamp(36px,5vw,56px) clamp(14px,4vw,40px)}
.contact-grid{display:grid;grid-template-columns:1fr 380px;gap:clamp(24px,4vw,44px);align-items:start}
/* FORM CARD */
.form-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);padding:clamp(22px,3vw,36px);box-shadow:var(--sh1);animation:fadeUp .4s ease both}
.form-title{font-family:'Arial',serif;font-size:clamp(18px,2.5vw,22px);font-weight:700;color:var(--cr-dk);margin-bottom:6px;display:flex;align-items:center;gap:8px}
.form-sub{font-size:13px;color:var(--tx-l);margin-bottom:22px;line-height:1.5}
/* TOPIC SELECTOR */
.topic-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:18px}
.topic-opt{border:1.5px solid var(--bdr);border-radius:var(--r2);padding:11px 14px;cursor:pointer;display:flex;align-items:center;gap:8px;background:var(--cream);transition:all var(--t);-webkit-tap-highlight-color:transparent}
.topic-opt:hover{border-color:var(--cr-bdr);background:var(--cr-xl)}
.topic-opt.sel{border-color:var(--cr);background:var(--cr-xl)}
.topic-opt input{display:none}.topic-ico{font-size:17px}.topic-lbl{font-size:13px;font-weight:600;color:var(--tx-m)}
.topic-opt.sel .topic-lbl{color:var(--cr)}
.char-count{font-size:11px;color:var(--tx-l);text-align:right;margin-top:4px}
.submit-btn{width:100%;padding:13px;border-radius:var(--r3);background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt));color:#fff;border:none;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;box-shadow:0 6px 18px rgba(107,16,32,.28);margin-top:4px}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(107,16,32,.38)}
.submit-btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
/* INFO CARD */
.info-card{background:#fff;border-radius:var(--r4);border:1px solid var(--bdr);overflow:hidden;box-shadow:var(--sh0);animation:fadeUp .4s ease .08s both;position:sticky;top:calc(var(--nav-h) + 20px)}
.ic-head{background:linear-gradient(135deg,var(--cr-dk),var(--cr-lt) 70%,#1A3060 100%);padding:clamp(20px,3vw,28px);position:relative;overflow:hidden}
.ic-head::before{content:'';position:absolute;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,.15) 0%,transparent 65%);top:-60px;right:-40px;pointer-events:none}
.ic-head-title{font-family:'Arial',serif;font-size:clamp(18px,2.5vw,22px);font-weight:700;color:#fff;margin-bottom:6px;position:relative;z-index:1}
.ic-head-sub{font-size:13px;color:rgba(255,255,255,.65);line-height:1.55;position:relative;z-index:1}
.ic-body{padding:clamp(18px,2.5vw,24px)}
.contact-item{display:flex;align-items:flex-start;gap:13px;padding:14px 0;border-bottom:1px solid var(--bdr2)}
.contact-item:last-child{border-bottom:none}
.ci-ico{width:42px;height:42px;border-radius:var(--r2);display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;background:var(--cr-xl)}
.ci-body{flex:1;min-width:0}
.ci-label{font-size:11px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px}
.ci-value{font-size:14px;font-weight:600;color:var(--tx)}
.ci-sub{font-size:12px;color:var(--tx-l);margin-top:2px}
.ci-link{color:var(--cr);text-decoration:none}.ci-link:hover{text-decoration:underline}
/* HOURS */
.hours-grid{display:grid;grid-template-columns:1fr 1fr;gap:0;margin-top:14px;background:var(--cream);border-radius:var(--r2);overflow:hidden;border:1px solid var(--bdr)}
.hg-item{padding:10px 14px;border-bottom:1px solid var(--bdr);font-size:13px}
.hg-item:nth-child(odd){border-right:1px solid var(--bdr)}.hg-item:nth-last-child(-n+2){border-bottom:none}
.hg-day{color:var(--tx-l);font-size:12px;margin-bottom:2px}.hg-time{font-weight:600;color:var(--tx)}
/* RESPONSE TIME */
.response-card{margin-top:14px;background:var(--ok-bg);border:1px solid var(--ok-bdr);border-radius:var(--r2);padding:12px 14px}
.rc-title{font-size:12px;font-weight:700;color:var(--ok);margin-bottom:5px}
.rc-text{font-size:12px;color:var(--tx-m);line-height:1.55}
@media(max-width:768px){.contact-grid{grid-template-columns:1fr}.info-card{position:static}.topic-grid{grid-template-columns:1fr 1fr}}
@media(max-width:480px){.topic-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="toast" id="toast"></div>
<?= pubNav('contacto') ?>

<section class="page-hero">
  <div class="ph-inner">
    <div class="ph-eyebrow"><i class="fa fa-envelope"></i> Fale connosco</div>
    <h1 class="ph-title">Contacto</h1>
    <p class="ph-sub">Tem dúvidas, sugestões ou precisa de ajuda? Estamos aqui para si. Resposta garantida em 24–48 horas.</p>
  </div>
</section>

<div class="content-wrap">
  <?php if ($flash): ?>
  <div class="flash flash-<?=$flash['type']?>" style="margin-bottom:22px"><?=$flash['msg']?></div>
  <?php endif; ?>

  <div class="contact-grid">
    <!-- FORM -->
    <div class="form-card">
      <div class="form-title"><i class="fa fa-envelope" style="color: var(--cr-dk)"></i> Enviar mensagem</div>
      <div class="form-sub">Preencha o formulário abaixo. Todos os campos marcados com * são obrigatórios.</div>

      <form method="POST" action="" id="contact-form" onsubmit="return handleSubmit()">
        <!-- TOPIC -->
        <div class="f-group">
          <label class="f-lbl">Tópico <span style="color:var(--cr)">*</span></label>
          <div class="topic-grid" id="topic-grid">
            <?php $topics=[['<i class="fa fa-warning" style="color: orange"></i>','Suporte técnico'],['📤','Submissão / Conteúdo'],['💰','Pagamentos'],['🤝','Parceria'],['💡','Sugestão'],['❓','Outro']];
            foreach($topics as $i=>[$ico,$lbl]): $selKey=($topic??'')===$lbl||($i===0&&!($topic??'')); ?>
            <label class="topic-opt <?=$selKey?'sel':''?>">
              <input type="radio" name="topic" value="<?=$lbl?>" <?=$selKey?'checked':''?> onchange="selectTopic(this)">
              <!-- <span class="topic-ico"><?=$ico?></span> -->
              <span class="topic-lbl"><?=$lbl?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- NAME + EMAIL -->
        <div class="f-row">
          <div class="f-group">
            <label class="f-lbl">Nome completo <span style="color:var(--cr)">*</span></label>
            <input class="f-input" type="text" name="name" id="f-name"
                   value="<?=h($name?? $_SESSION['user_name'] ?? '')?>" placeholder="O seu nome" required minlength="2">
          </div>
          <div class="f-group">
            <label class="f-lbl">E-mail <span style="color:var(--cr)">*</span></label>
            <input class="f-input" type="email" name="email" id="f-email"
                   value="<?=h($email ?? '')?>" placeholder="o.seu@email.com" required>
          </div>
        </div>

        <!-- SUBJECT -->
        <div class="f-group">
          <label class="f-lbl">Assunto <span style="color:var(--cr)">*</span></label>
          <input class="f-input" type="text" name="subject" id="f-subject"
                 value="<?=h($subject??'')?>" placeholder="Descreva brevemente o assunto" required minlength="4">
        </div>

        <!-- MESSAGE -->
        <div class="f-group">
          <label class="f-lbl">Mensagem <span style="color:var(--cr)">*</span></label>
          <textarea class="f-ta" name="message" id="f-message"
                    placeholder="Descreva a sua questão com o máximo de detalhe possível…"
                    rows="6" required minlength="10" oninput="updateCharCount()"
                    style="min-height:130px"><?=h($message??'')?></textarea>
          <div class="char-count"><span id="char-count">0</span>/1000 caracteres</div>
        </div>

        <button type="submit" class="submit-btn" id="submit-btn">
          <span id="submit-ico"><i class="fa fa-send"></i></span> <span id="submit-text">Enviar mensagem</span>
        </button>
      </form>
    </div>

    <!-- INFO CARD -->
    <div>
      <div class="info-card">
        <div class="ic-head">
          <div class="ic-head-title">Informações de contacto</div>
          <div class="ic-head-sub">Disponíveis para ajudar em todos os assuntos relacionados com a plataforma.</div>
        </div>
        <div class="ic-body">
          <div class="contact-item">
            <div class="ci-ico">✉️</div>
            <div class="ci-body">
              <div class="ci-label">E-mail</div>
              <div class="ci-value"><a href="mailto:petrochamp.ao@outlook.com" class="ci-link">petrochamp.ao@outlook.com</a></div>
              <div class="ci-sub">Resposta em 24 - 48 horas</div>
            </div>
          </div>
          <div class="contact-item">
            <div class="ci-ico">📱</div>
            <div class="ci-body">
              <div class="ci-label">WhatsApp / Telefone</div>
              <div class="ci-value"><a href="https://wa.me/244972339776" class="ci-link" target="_blank">+244 972 339 776</a></div>
              <div class="ci-sub">Segunda–Sexta, 08h–17h</div>
            </div>
          </div>
          <div class="contact-item">
            <div class="ci-ico">🌐</div>
            <div class="ci-body">
              <div class="ci-label">Formulário online</div>
              <div class="ci-value">Disponível nesta página</div>
              <div class="ci-sub">24/7 — respondemos nos dias úteis</div>
            </div>
          </div>
          <div class="contact-item">
            <div class="ci-ico">📍</div>
            <div class="ci-body">
              <div class="ci-label">Localização</div>
              <div class="ci-value">Luanda, Angola</div>
              <div class="ci-sub">Petrochamp</div>
            </div>
          </div>

          <div style="font-size:12px;font-weight:700;color:var(--tx-l);text-transform:uppercase;letter-spacing:.8px;margin:16px 0 8px">Horário de atendimento</div>
          <div class="hours-grid">
            <div class="hg-item"><div class="hg-day">Segunda – Sexta</div><div class="hg-time">08:00 – 17:00</div></div>
            <div class="hg-item"><div class="hg-day">Sábado</div><div class="hg-time">09:00 – 13:00</div></div>
            <div class="hg-item"><div class="hg-day">Domingo</div><div class="hg-time">Encerrado</div></div>
            <div class="hg-item"><div class="hg-day">Feriados</div><div class="hg-time">Encerrado</div></div>
          </div>

          <div class="response-card">
            <div class="rc-title">⚡ Tempo médio de resposta</div>
            <div class="rc-text">E-mail: 24–48h · WhatsApp: 2–4h · Urgente: contacte directamente pelo WhatsApp</div>
          </div>
        </div>
      </div>

      <!-- FAQ LINK -->
      <div style="background:#fff;border-radius:var(--r3);border:1px solid var(--bdr);padding:16px 18px;margin-top:14px;box-shadow:var(--sh0);text-align:center">
        <div style="font-size:24px;margin-bottom:8px">❓</div>
        <div style="font-size:14px;font-weight:700;color:var(--tx);margin-bottom:6px">Consulte o FAQ</div>
        <div style="font-size:12px;color:var(--tx-l);margin-bottom:12px">Muitas dúvidas têm resposta imediata nas nossas Perguntas Frequentes.</div>
        <a href="faq.php" class="btn btn-cr btn-sm" style="display:inline-flex;width:100%;justify-content:center">📋 Ver FAQ</a>
      </div>
    </div>
  </div>
</div>

<?= pubFooter() ?>
<script>
function selectTopic(input) {
    document.querySelectorAll('.topic-opt').forEach(el=>el.classList.remove('sel'));
    input.closest('.topic-opt').classList.add('sel');
}
function updateCharCount() {
    const txt = document.getElementById('f-message').value;
    document.getElementById('char-count').textContent = txt.length;
    if (txt.length > 1000) document.getElementById('f-message').value = txt.substring(0,1000);
}
function handleSubmit() {
    const btn = document.getElementById('submit-btn');
    const ico = document.getElementById('submit-ico');
    const txt = document.getElementById('submit-text');
    btn.disabled = true; ico.textContent = '⌛'; txt.textContent = 'A enviar…';
    return true; // let form submit naturally
}
// init char count
updateCharCount();
</script>
</body>
</html>
