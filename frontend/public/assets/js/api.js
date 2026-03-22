// API Configuration
const API_BASE_URL = 'http://localhost/petro_pub/backend/api';

// Utility Functions
function getToken() {
    return localStorage.getItem('authToken');
}

function setToken(token) {
    localStorage.setItem('authToken', token);
}

function removeToken() {
    localStorage.removeItem('authToken');
}

function getUserData() {
    const data = localStorage.getItem('userData');
    return data ? JSON.parse(data) : null;
}

function setUserData(data) {
    localStorage.setItem('userData', JSON.stringify(data));
}

function clearUserData() {
    removeToken();
    localStorage.removeItem('userData');
}

// API Request Helper
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE_URL}/${endpoint}`;
    const config = {
        method: options.method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            ...options.headers
        },
        ...options
    };

    if (getToken()) {
        config.headers['Authorization'] = `Bearer ${getToken()}`;
    }

    if (options.body && typeof options.body === 'object') {
        config.body = JSON.stringify(options.body);
    }

    try {
        const response = await fetch(url, config);
        // Try to parse JSON only when Content-Type indicates JSON
        let data = null;
        const contentType = response.headers.get('content-type') || '';
        if (contentType.indexOf('application/json') !== -1) {
            try {
                data = await response.json();
            } catch (e) {
                // Invalid JSON body
                console.warn('Failed to parse JSON response for', url, e);
                data = null;
            }
        } else {
            // No JSON body; collect text for diagnostics
            try {
                const text = await response.text();
                data = text ? { text } : null;
            } catch (e) {
                data = null;
            }
        }

        if (response.status === 401) {
            clearUserData();
            // window.location.href = 'login.html';
            return null;
        }

        return { status: response.status, data };
    } catch (error) {
        console.error('API Request Error:', error);
        return { status: 500, data: { error: error.message } };
    }
}

// Logout function
function logout() {
    clearUserData();
    window.location.href = 'index.html';
}

// Check authentication
function checkAuth() {
    const token = getToken();
    if (token == null) {
        return true;
    }
    return false;
}

// Redirect to login if not authenticated
function requireAuth() {
    if (checkAuth()) {
        // window.location.href = 'login.html';
        console.log("Sim, deve se autenticar")
        return
    }
    console.log("Não precisa de autenticação")
    return
}

// Format currency
function formatKwanza(value) {
    return new Intl.NumberFormat('pt-AO', {
        style: 'currency',
        currency: 'AOA'
    }).format(value);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-AO');
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// ========== COMMENTS API ==========
async function getComments(documentId, limit = 50, offset = 0) {
    return await apiRequest(`comments?document_id=${documentId}&limit=${limit}&offset=${offset}`, {
        method: 'GET'
    });
}

async function createComment(documentId, content, parentCommentId = null) {
    return await apiRequest('comments', {
        method: 'POST',
        body: {
            document_id: documentId,
            content: content,
            parent_comment_id: parentCommentId
        }
    });
}

async function updateComment(commentId, content) {
    return await apiRequest(`comments/${commentId}`, {
        method: 'PUT',
        body: {
            content: content
        }
    });
}

async function deleteComment(commentId) {
    return await apiRequest(`comments/${commentId}`, {
        method: 'DELETE'
    });
}

async function markCommentAsHelpful(commentId) {
    return await apiRequest(`comments/${commentId}/helpful`, {
        method: 'POST'
    });
}

async function markCommentAsNotHelpful(commentId) {
    return await apiRequest(`comments/${commentId}/not-helpful`, {
        method: 'POST'
    });
}

