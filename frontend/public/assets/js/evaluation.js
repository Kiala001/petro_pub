

let currentScore = 0;
const scoreLabels = ['','Insuficiente','Fraco','Razoável','Bom','Excelente'];

let currentDecision = '';
function selectDecision(d) {
  currentDecision = d;
  ['approve','reject','revision'].forEach(opt => {
    const el = document.getElementById(`dec-${opt}`);
    el.className = 'decision-opt' + (opt === d ? ` selected-${opt}` : '');
  });
}

function prepareEval(document_id) {
    const suggestion = document.getElementById('eval-suggest').value.trim();
    const comment = document.getElementById('eval-comment').value.trim();
    
    if (currentScore === 0) { showToast('⚠️ Seleccione uma pontuação (1 a 5 estrelas)'); return; }
    if (comment.length < 20) { showToast('⚠️ Escreva um comentário com pelo menos 20 caracteres'); return; }
    if (!currentDecision) { showToast('⚠️ Seleccione uma decisão (Aprovar, Rejeitar ou Pedir Revisão)'); return; }

    if (suggestion) {
        if (suggestion.length < 20) { 
            showToast('⚠️ Forneça uma sugestão com pelo menos 20 caracteres'); 
            return; 
        } else {
            validateValue(suggestion, 'sugestão')
        }
    }
    
    console.log("Document ID: "+document_id)

    validateValue(comment, 'comentário')
    // showToast('🎉 Avaliação enviada com sucesso!');

    const formData = new FormData();
    formData.append('document_id', document_id);
    formData.append('score', currentScore);
    formData.append('decision', currentDecision);
    formData.append('comment', comment);
    formData.append('suggest', suggestion)

    submitReview(formData);
}

async function submitReview(evalData) {
  const response = await fetch(`${API_BASE_URL}/reviews`, {
    method: "POST",
    headers: {
        Authorization: `Bearer ${getToken()}`
    },
    body: evalData
  });

  const result = await response.json();

  if(result.success){
    showToast(result.message || result.error || "A sua avaliação foi enviada com sucesso! A página será recarregada dentro de instantes.");
    
    setTimeout(() => {
        location.reload();
    }, 4000);
  }else{
    showToast(result.message);
  }
}

function validateValue(input, type) {
    const minLength = type === 'sugestão' ? 20 : 20
    const maxLength = type === 'sugestão' ? 300 : 500

    const trimmed = input.trim()
    if (trimmed.length < minLength || trimmed.length > maxLength) {
        showToast(`${type} deve ter entre ${minLength} e ${maxLength} caracteres`)
        return;
    }
    
    const regex = /^[A-Za.z0-9 \-;.]+$/;
    if(regex.test(trimmed)) {
        showToast(`${type} contém caracteres especiais`)
        return ;
    }

    const onlyNumbersOrAllowed = /^[0-9\-; .]+$/.test(trimmed);
    if (onlyNumbersOrAllowed) {
        showToast('Não pode ter apenas números ou caracteres espciais permitidos')
        return ;
    }
}

function resetEvalForm() {
  currentScore = 0; currentDecision = '';
  renderStars(0);
  document.getElementById('star-label').textContent = 'Toque para classificar';
  document.getElementById('eval-comment').value = '';
  document.getElementById('eval-suggest').value = '';
  document.getElementById('cc-comment').textContent = '0';
  document.getElementById('cc-suggest').textContent = '0';
  ['approve','reject','revision'].forEach(opt => {
    document.getElementById(`dec-${opt}`).className = 'decision-opt';
  });
  document.getElementById('eval-form-body').style.display = 'block';
  document.getElementById('eval-confirm').style.display = 'none';
}

function scrollToForm() {
  document.getElementById('eval-form-card').scrollIntoView({ behavior:'smooth', block:'start' });
  const card = document.getElementById('eval-form-card');
  card.style.boxShadow = '0 0 0 3px var(--gold), var(--sh-md)';
  setTimeout(() => card.style.boxShadow = '', 2000);
}
