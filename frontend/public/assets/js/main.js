// Main Page Functions

document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadRecentDocuments();
    updateNavigation();
});

function updateNavigation() {
    const userData = getUserData();
    const navButtons = document.getElementById('navButtons');
    const navLogged = document.getElementById('navLogged');

    if (userData) {
        navButtons?.classList.add('hidden');
        navLogged?.classList.remove('hidden');
        document.getElementById('userNameNav').textContent = userData.name;
    } else {
        navButtons?.classList.remove('hidden');
        navLogged?.classList.add('hidden');
    }
}

async function loadCategories() {
    const response = await apiRequest('categories');

    if (!response.data.success || !response.data.categories) {
        return;
    }

    const grid = document.getElementById('categoriesGrid');
    if (!grid) return;

    grid.innerHTML = response.data.categories.map(category => `
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition border-t-4 border-red-900">
            <p class="text-4xl mb-3">
                <i class="fa fa-file"></i>
            </p>
            <h3 class="font-bold text-lg mb-2 text-gray-800">${category.name}</h3>
            <p class="text-gray-600 text-sm mb-3 line-clamp-2">${category.description}</p>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">
                    <i class="fa fa-file"></i>
                    ${category.upload_count} 
                    Carregados
                </span>
                <span class="text-gray-500"> 
                    <i class="fa fa-file"></i>
                    ${category.download_count} 
                    Baixados
                </span>
            </div>
        </div>
    `).join('');
}

async function loadRecentDocuments() {
    const response = await apiRequest('documents');

    if (!response.data.success || !response.data.documents) {
        return;
    }

    const container = document.getElementById('recentDocuments');
    if (!container) return;

    const recent = response.data.documents.slice(0, 6);

    container.innerHTML = recent.map(doc => `
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition">
            <h4 class="font-bold text-lg mb-2 line-clamp-2 text-gray-800">${doc.title}</h4>
            <p class="text-sm text-gray-600 mb-3">Autores: ${doc.authors.join(', ')}</p>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">${doc.summary}</p>
            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-bold text-green-600">${formatKwanza(doc.price_kz)}</span>
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${doc.status}</span>
            </div>
            <button onclick="goToDocument('${doc.id}')" 
                class="w-full bg-red-900 hover:bg-red-800 text-white font-bold py-2 rounded">
                Ver Detalhes
            </button>
        </div>
    `).join('');
}

function goToDocument(docId) {
    window.location.href = `documento.html?id=${docId}`;
}
