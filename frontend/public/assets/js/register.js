
function doRegister() {
    const fname = document.getElementById('r-fname').value;
    const lname = document.getElementById('r-lname').value;
    const email = document.getElementById('r-email').value;
    const type  = document.getElementById('r-inst').value;
    const pw    = document.getElementById('r-password').value;
    const terms = document.getElementById('r-terms').checked;
    if (!fname || !lname || !email || !type || !pw) { showToast('Preencha todos os campos obrigatórios'); return; }
    
    if (!(/[A-Z]/.test(fname))) { showToast("O primeiro nome  não pode conter caracteres especiais ou números"); return; }
    if (!(/[A-Z]/.test(lname))) { showToast("O ultimo nome  não pode conter caracteres especiais ou números"); return; }
    
    if (pw.length < 8) { showToast('A palavra-passe deve ter pelo menos 8 caracteres'); return; }
    if (!terms) { showToast('Aceite os termos para continuar'); return; }

    registerAuth(fname, lname, email, pw, type)
}