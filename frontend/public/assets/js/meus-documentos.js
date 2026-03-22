// Meus Documentos Functions

requireAuth();

let userDocuments = [];

document.addEventListener('DOMContentLoaded', () => {
    loadUserDocuments();
});

async function loadUserDocuments() {
    const userData = getUserData();
    const response = await apiRequest(`documents/user/${userData.user_id}`);

    if (!response.data.success) {
        showError('Erro ao carregar documentos');
        return;
    }

    userDocuments = response.data.documents;
    displayDocuments(userDocuments);
    updateStatistics();
}

function displayDocuments(documents) {
    const tbody = document.getElementById('documentsList');

    if (documents.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td class="px-6 py-8 text-center text-gray-500" colspan="6">
                    <p class="mb-4">Você ainda não carregou documentos</p>
                    <a href="novo-upload.html" class="inline-block bg-red-900 text-white px-6 py-2 rounded hover:bg-red-800">
                        Carregar primeiro documento
                    </a>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = documents.map(doc => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <p class="font-semibold text-gray-800 line-clamp-1">${doc.title}</p>
            </td>
            <td class="px-6 py-4 text-gray-600">${doc.category_id}</td>
            <td class="px-6 py-4 text-center">
                <span class="px-3 py-1 rounded-full text-sm font-bold ${getStatusBadgeClass(doc.status)}">
                    ${doc.status}
                </span>
            </td>
            <td class="px-6 py-4 text-right font-bold text-green-600">
                ${formatKwanza(doc.price_kz)}
            </td>
            <td class="px-6 py-4 text-center text-gray-600 text-sm">
                ${formatDate(doc.created_at)}
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex justify-center space-x-2">
                    <button onclick="openEditModal('${doc.id}')" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm" title="Editar">
                        ✏️
                    </button>
                    <button onclick="viewDetails('${doc.id}')" 
                        class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm" title="Ver">
                        👁️
                    </button>
                    <button onclick="deleteDocument('${doc.id}')" 
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm" title="Excluir">
                        🗑️
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function getStatusBadgeClass(status) {
    const classes = {
        'PENDING': 'bg-yellow-100 text-yellow-800',
        'APPROVED': 'bg-green-100 text-green-800',
        'REJECTED': 'bg-red-100 text-red-800',
        'ARCHIVED': 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function updateStatistics() {
    const total = userDocuments.length;
    const pending = userDocuments.filter(d => d.status === 'PENDING').length;
    const approved = userDocuments.filter(d => d.status === 'APPROVED').length;

    document.getElementById('totalDocs').textContent = total;
    document.getElementById('pendingDocs').textContent = pending;
    document.getElementById('approvedDocs').textContent = approved;
    document.getElementById('totalDownloads').textContent = '0'; // Implementar depois
}

function filterDocuments() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value.toLowerCase();

    let filtered = userDocuments;

    if (status) {
        filtered = filtered.filter(doc => doc.status === status);
    }

    if (search) {
        filtered = filtered.filter(doc => 
            doc.title.toLowerCase().includes(search)
        );
    }

    displayDocuments(filtered);
}

function openEditModal(docId) {
    const doc = userDocuments.find(d => d.id === docId);
    if (!doc) return;

    document.getElementById('editDocId').value = docId;
    document.getElementById('editTitle').value = doc.title;
    document.getElementById('editPrice').value = doc.price_kz;

    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function viewDetails(docId) {
    const doc = userDocuments.find(d => d.id === docId);
    if (!doc) return;

    alert(`Título: ${doc.title}\nStatus: ${doc.status}\nPreço: ${formatKwanza(doc.price_kz)}\nData: ${formatDate(doc.created_at)}`);
}

async function deleteDocument(docId) {
    if (!confirm('Tem certeza que deseja excluir este documento?')) {
        return;
    }

    // Implementar API de exclusão
    alert('Funcionalidade de exclusão em desenvolvimento');
}

document.getElementById('editForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    // Implementar atualização via API
    alert('Funcionalidade de edição em desenvolvimento');
    closeEditModal();
});

function showError(message) {
    alert('Erro: ' + message);
}
