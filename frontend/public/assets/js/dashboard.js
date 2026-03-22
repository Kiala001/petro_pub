// Dashboard Functions

const token = getToken();
console.log("Token: "+token)

console.log(checkAuth())

requireAuth();

document.addEventListener('DOMContentLoaded', () => {
    loadDashboardData();
});

async function loadDashboardData() {
    const userData = getUserData();

    // Atualizar nome do usuário
    document.getElementById('userName').textContent = userData.name;

    // Carregar documentos do usuário
    const docResponse = await apiRequest(`documents/user/${userData.user_id}`);
    if (docResponse.data.success) {
        document.getElementById('documentCount').textContent = docResponse.data.count;
        
        // Mostrar documentos recentes
        const recentDocs = docResponse.data.documents.slice(0, 5);
        const container = document.getElementById('recentDocs');
        
        if (recentDocs.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <p class="mb-4">Você ainda não carregou documentos</p>
                    <a href="novo-upload.html" class="inline-block bg-red-900 text-white px-6 py-2 rounded hover:bg-red-800">
                        Carregue seu primeiro documento
                    </a>
                </div>
            `;
        } else {
            container.innerHTML = recentDocs.map(doc => `
                <div class="flex justify-between items-start p-4 border border-gray-200 rounded-lg">
                    <div>
                        <h4 class="font-bold text-gray-800">${doc.title}</h4>
                        <p class="text-sm text-gray-600 mt-1">Status: <span class="font-semibold ${getStatusColor(doc.status)}">${doc.status}</span></p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-green-600">${formatKwanza(doc.price_kz)}</p>
                        <p class="text-xs text-gray-500 mt-1">${formatDate(doc.created_at)}</p>
                    </div>
                </div>
            `).join('');
        }
    }

    // Carregar histórico de pagamentos para obter saldo
    const paymentResponse = await apiRequest('payments/history');
    if (paymentResponse.data.success && paymentResponse.data.payments) {
        // Calcular saldo baseado em pagamentos aprovados
        let balance = 0;
        paymentResponse.data.payments.forEach(payment => {
            if (payment.status === 'APPROVED') {
                balance += payment.amount_kz;
            }
        });
        document.getElementById('userBalance').textContent = formatKwanza(balance);
        document.getElementById('downloadCount').textContent = paymentResponse.data.count;
    }
}

function getStatusColor(status) {
    const colors = {
        'PENDING': 'text-yellow-600',
        'APPROVED': 'text-green-600',
        'REJECTED': 'text-red-600',
        'ARCHIVED': 'text-gray-600'
    };
    return colors[status] || 'text-gray-600';
}
