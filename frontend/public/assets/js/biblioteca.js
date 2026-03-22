// Biblioteca Functions

requireAuth();

let allDocuments = [];
let filteredDocuments = [];
let categories = [];
let currentDocumentId = null;
let currentPage = 1;
let itemsPerPage = 9;

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadDocuments();
});


async function loadCategories() {
    const response = await apiRequest('categories');
    if (response.data.success) {
        categories = response.data.categories;
        
        const select = document.getElementById('categoryFilter');
        select.innerHTML = '<option value="">Todas as categorias</option>' + 
            categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
    }
}

async function loadDocuments() {
    const response = await apiRequest('documents');

    if (!response.data.success) {
        showError('Erro ao carregar documentos');
        return;
    }

    allDocuments = response.data.documents;
    filteredDocuments = [...allDocuments];
    displayDocuments(filteredDocuments);
}

function displayDocuments(documents) {
    const container = document.getElementById('documentsList');

    if (documents.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-500 col-span-full py-8">Nenhum documento encontrado</p>';
        updatePagination();
        return;
    }

    // Aplicar paginação
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedDocs = documents.slice(startIndex, endIndex);

    container.innerHTML = paginatedDocs.map(doc => `
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition cursor-pointer"
            onclick="showDocumentModal('${doc.id}')">
            <h3 class="font-bold text-lg mb-2 line-clamp-2 text-gray-800">${doc.title}</h3>
            <p class="text-sm text-gray-600 mb-2">Autores: ${doc.authors.join(', ')}</p>
            <p class="text-sm text-gray-500 mb-3 line-clamp-2">${doc.summary}</p>
            
            <!-- Informações adicionais -->
            <div class="mb-3 space-y-1">
                <p class="text-xs text-gray-500">
                    📁 Tamanho: <span class="font-semibold">${formatFileSize(doc.file_size || 0)}</span>
                </p>
                <p class="text-xs text-gray-500">
                    📅 Adicionado: <span class="font-semibold">${formatDate(doc.created_at)}</span>
                </p>
            </div>

            <div class="flex justify-between items-center">
                <span class="font-bold text-green-600 text-lg">${formatKwanza(doc.price_kz)}</span>
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${doc.status}</span>
            </div>
        </div>
    `).join('');

    updateResultsInfo(documents.length);
    updatePagination(documents.length);
}

function updatePagination(total) {
    const totalPages = Math.ceil((total || filteredDocuments.length) / itemsPerPage);
    document.getElementById('page-info').textContent = `Página ${currentPage} de ${totalPages}`;
    
    // Desabilitar botões conforme necessário
    document.querySelectorAll('#pagination button')[0].disabled = currentPage === 1;
    document.querySelectorAll('#pagination button')[1].disabled = currentPage >= totalPages;
}

function nextPage() {
    const totalPages = Math.ceil(filteredDocuments.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        displayDocuments(filteredDocuments);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        displayDocuments(filteredDocuments);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function updateResultsInfo(total) {
    const infoEl = document.getElementById('results-info');
    if (total > 0) {
        infoEl.classList.remove('hidden');
        document.getElementById('results-count').textContent = `📊 Encontrados ${total} documento(s)`;
    } else {
        infoEl.classList.add('hidden');
    }
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('priceFilter').value = '';
    document.getElementById('sizeFilter').value = '';
    currentPage = 1;
    filteredDocuments = [...allDocuments];
    displayDocuments(filteredDocuments);
}

async function searchDocuments() {
    const title = document.getElementById('searchInput').value.toLowerCase();
    const categoryId = document.getElementById('categoryFilter').value;
    const maxPrice = parseFloat(document.getElementById('priceFilter').value) || Infinity;
    const maxSize = parseFloat(document.getElementById('sizeFilter').value) * 1024 * 1024 || Infinity; // Converter MB para bytes

    // Filtrar documentos localmente
    filteredDocuments = allDocuments.filter(doc => {
        const titleMatch = !title || doc.title.toLowerCase().includes(title) || 
                          doc.authors.some(a => a.toLowerCase().includes(title));
        const categoryMatch = !categoryId || doc.category_id === categoryId;
        const priceMatch = doc.price_kz <= maxPrice;
        const sizeMatch = (doc.file_size || 0) <= maxSize;

        return titleMatch && categoryMatch && priceMatch && sizeMatch;
    });

    currentPage = 1;
    displayDocuments(filteredDocuments);
}

async function showDocumentModal(docId) {
    const response = await apiRequest(`documents/${docId}`);

    if (!response.data.success) {
        alert('Erro ao carregar documento');
        return;
    }

    currentDocumentId = docId;
    const doc = response.data.document;
    document.getElementById('modalTitle').textContent = doc.title;

    const content = `
        <p class="text-gray-600"><strong>Autores:</strong> ${doc.authors.join(', ')}</p>
        <p class="text-gray-600"><strong>Instituição:</strong> ${doc.institution}</p>
        <p class="text-gray-600"><strong>Curso:</strong> ${doc.course}</p>
        <p class="text-gray-600"><strong>Resumo:</strong> ${doc.summary}</p>
        <p class="text-gray-600"><strong>Palavras-chave:</strong> ${doc.keywords.join(', ')}</p>
        <p class="text-gray-600"><strong>Preço:</strong> <span class="font-bold text-green-600">${formatKwanza(doc.price_kz)}</span></p>
        <p class="text-gray-600"><strong>Data:</strong> ${formatDate(doc.created_at)}</p>
    `;

    document.getElementById('modalContent').innerHTML = content;

    const downloadBtn = document.getElementById('downloadBtn');
    downloadBtn.setAttribute('data-doc-id', docId);

    document.getElementById('documentModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('documentModal').classList.add('hidden');
}

async function downloadDocument() {
    const userData = getUserData();
    if (!userData) {
        window.location.href = 'login.html';
        return;
    }

    const docId = document.getElementById('downloadBtn').getAttribute('data-doc-id');
    
    // Lógica de pagamento/download
    const response = await apiRequest(`documents/${docId}`);
    const doc = response.data.document;

    if (doc.price_kz > 0) {
        // Redirecionar para pagamento
        window.location.href = `pagamento.html?doc_id=${docId}`;
    } else {
        // Download direto para documentos gratuitos
        // Aqui você implementaria a lógica de download
        alert('Download iniciado!');
    }
}

function showError(message) {
    const container = document.getElementById('documentsList');
    container.innerHTML = `<p class="text-center text-red-600 col-span-full py-8">${message}</p>`;
}

// Fechar modal ao clicar fora
document.addEventListener('click', (e) => {
    const modal = document.getElementById('documentModal');
    if (e.target === modal) {
        closeModal();
    }
});
