
function doLogin() {
    const email = document.getElementById('l-email').value;
    const pw = document.getElementById('l-password').value;
    const type = document.getElementById('l-type').value;
    if (!email || !pw || !type) { showToast('⚠️ Preencha todos os campos'); return; }

    loginAuth(email, pw, type)
}

function togglePw(id, icon) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
    input.type = 'text';
    icon.textContent = '👁';
    } else {
    input.type = 'password';
    icon.textContent = '👁';
    }
}