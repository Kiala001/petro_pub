// Authentication Functions

let is2FARequired = false;
let currentUser = null;

async function loginAuth(l_email, l_password, l_userType) {

    const email = l_email;
    const password = l_password;
    const type = l_userType;

    const response = await apiRequest('auth/login', {
        method: 'POST',
        body: { email, password, type }
    });

    if (!response || response.status >= 500) {
        showToast('Erro no servidor. Tente novamente mais tarde.');
        return;
    }

    const respData = response.data || {};

    if (!respData.success) {
        const message = respData.error || 'Erro ao fazer login';
        showToast(message)
        return;
    }

    if (response.data.requires_2fa) {
        is2FARequired = true;
        document.getElementById('twoFactorModal').classList.remove('hidden');
        return;
    }

    // Login bem-sucedido
    setToken(response.data.token);
    setUserData({
        user_id: response.data.user_id,
        name: response.data.name,
        type: response.data.type
    });
    
    showToast('Sessão iniciada! Bem-vindo de volta à PetroPub. <br> Serás redicionado dentro de alguns instantes');
    
    setTimeout(() => 
        window.location.href = 'dashboard.php',
    4000);
        
}

async function verify2FA() {
    const code = document.getElementById('twoFactorCode').value;

    if (code.length !== 6) {
        alert('Insira um código de 6 dígitos');
        return;
    }

    const response = await apiRequest('auth/verify-2fa', {
        method: 'POST',
        body: { code }
    });

    const errorDiv = document.getElementById('error');

    if (!response.data.success) {
        errorDiv.textContent = response.data.error || 'Código inválido';
        errorDiv.classList.remove('hidden');
        return;
    }

    setToken(response.data.token);
    setUserData({
        user_id: response.data.user_id,
        name: response.data.name,
        type: response.data.type
    });

    window.location.href = 'dashboard.html';
}

async function registerAuth(fname, lname, r_email, pw, r_type) {
    const name = fname+" "+lname;
    const email = r_email;
    const password = pw;
    const type = r_type;

    const response = await apiRequest('auth/register', {
        method: 'POST',
        body: { email, password, name, type }
    });

    if (!response || response.status >= 500) {
        showToast('Erro no servidor. Tente novamente mais tarde.');
        return;
    }

    const respData = response.data || {};

    // Observabilidade
    console.log(respData)

    if (!respData.success) {
        var errMessage = ""
        // Ifle server returned text instead of JSON, show it
        if (typeof respData === 'object' && respData.error) {
            errMessage = respData.error;
        } else if (respData && respData.text) {
            errMessage = respData.text;
        } else {
            errMessage = 'Erro ao registrar';
        }
        showToast(errMessage)
        return;
    }

    showToast('Conta criada com sucesso! Redirecionando para login...');

    setTimeout(() => switchTab('login'), 2200);
}
