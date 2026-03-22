// assets/js/comments.js
// Módulo para gerenciar comentários em documentos

class CommentManager {
    constructor(documentId, containerId = 'comments-section') {
        this.documentId = documentId;
        this.container = document.getElementById(containerId);
        this.comments = [];
        this.currentUserId = getUserData()?.id;
        this.isLoggedIn = checkAuth();
        
        if (this.container) {
            this.init();
        }
    }
    
    async init() {
        await this.loadComments();
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Botão para adicionar comentário
        const addCommentBtn = this.container.querySelector('.add-comment-btn');
        if (addCommentBtn) {
            addCommentBtn.addEventListener('click', () => this.toggleCommentForm());
        }
        
        // Formulário de envio
        const commentForm = this.container.querySelector('#comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => this.submitComment(e));
        }
    }
    
    async loadComments() {
        try {
            const response = await getComments(this.documentId);
            
            if (response.status === 200) {
                this.comments = response.data.data || [];
                this.renderComments();
            }
        } catch (error) {
            console.error('Erro ao carregar comentários:', error);
        }
    }
    
    renderComments() {
        const commentsContainer = this.container.querySelector('.comments-list');
        if (!commentsContainer) return;
        
        if (this.comments.length === 0) {
            commentsContainer.innerHTML = `
                <div class="empty-state">
                    <p>📝 Sem comentários ainda. Seja o primeiro a comentar!</p>
                </div>
            `;
            return;
        }
        
        commentsContainer.innerHTML = this.comments.map(comment => 
            this.renderCommentCard(comment)
        ).join('');
        
        // Adicionar event listeners para cada comentário
        this.attachCommentEventListeners();
    }
    
    renderCommentCard(comment) {
        const isOwner = this.currentUserId === comment.user_id;
        const userReaction = this.getUserReaction(comment.id);
        
        return `
            <div class="comment-card" data-comment-id="${comment.id}">
                <div class="comment-header">
                    <div class="user-info">
                        <div class="avatar">
                            ${comment.user_name.charAt(0).toUpperCase()}
                        </div>
                        <div class="details">
                            <strong>${comment.user_name}</strong>
                            <span class="timestamp">${this.formatCommentDate(comment.created_at)}</span>
                            ${comment.updated_at !== comment.created_at ? '<span class="edited">(editado)</span>' : ''}
                        </div>
                    </div>
                    
                    ${isOwner ? `
                        <div class="comment-actions">
                            <button class="edit-btn" data-comment-id="${comment.id}" title="Editar">✏️</button>
                            <button class="delete-btn" data-comment-id="${comment.id}" title="Deletar">🗑️</button>
                        </div>
                    ` : ''}
                </div>
                
                <div class="comment-content">
                    <p>${this.escapeHtml(comment.content)}</p>
                </div>
                
                <div class="comment-footer">
                    <div class="reactions">
                        <button class="helpful-btn ${userReaction === 'HELPFUL' ? 'active' : ''}" 
                                data-comment-id="${comment.id}">
                            👍 Útil (${comment.is_helpful_count})
                        </button>
                        <button class="not-helpful-btn ${userReaction === 'NOT_HELPFUL' ? 'active' : ''}" 
                                data-comment-id="${comment.id}">
                            👎 Não útil (${comment.is_not_helpful_count})
                        </button>
                    </div>
                    
                    ${this.isLoggedIn ? `
                        <button class="reply-btn" data-comment-id="${comment.id}">
                            💬 Responder
                        </button>
                    ` : ''}
                    
                    ${comment.helpfulness_score > 0 ? `
                        <span class="helpfulness-score">
                            Utilidade: ${comment.helpfulness_score}%
                        </span>
                    ` : ''}
                </div>
                
                ${comment.replies && comment.replies.length > 0 ? `
                    <div class="replies">
                        ${comment.replies.map(reply => this.renderReplyCard(reply)).join('')}
                    </div>
                ` : ''}
                
                <div class="reply-form hidden" data-comment-id="${comment.id}">
                    <textarea placeholder="Sua resposta..." class="reply-textarea"></textarea>
                    <div class="form-actions">
                        <button class="submit-reply-btn">Responder</button>
                        <button class="cancel-reply-btn" type="button">Cancelar</button>
                    </div>
                </div>
            </div>
        `;
    }
    
    renderReplyCard(reply) {
        const isOwner = this.currentUserId === reply.user_id;
        
        return `
            <div class="reply-card" data-comment-id="${reply.id}">
                <div class="reply-header">
                    <div class="user-info">
                        <div class="avatar small">
                            ${reply.user_name.charAt(0).toUpperCase()}
                        </div>
                        <div class="details">
                            <strong>${reply.user_name}</strong>
                            <span class="timestamp">${this.formatCommentDate(reply.created_at)}</span>
                        </div>
                    </div>
                    ${isOwner ? `
                        <button class="delete-reply-btn" data-comment-id="${reply.id}">🗑️</button>
                    ` : ''}
                </div>
                <div class="reply-content">
                    <p>${this.escapeHtml(reply.content)}</p>
                </div>
            </div>
        `;
    }
    
    attachCommentEventListeners() {
        // Eventos de útil/não útil
        this.container.querySelectorAll('.helpful-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleHelpful(e));
        });
        
        this.container.querySelectorAll('.not-helpful-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleNotHelpful(e));
        });
        
        // Eventos de edição/deleção
        this.container.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.startEdit(e));
        });
        
        this.container.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.deleteCommentConfirm(e));
        });
        
        // Eventos de resposta
        this.container.querySelectorAll('.reply-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleReplyForm(e));
        });
        
        this.container.querySelectorAll('.submit-reply-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.submitReply(e));
        });
        
        this.container.querySelectorAll('.cancel-reply-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleReplyForm(e));
        });
    }
    
    toggleCommentForm() {
        const form = this.container.querySelector('#comment-form');
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.querySelector('textarea').focus();
        }
    }
    
    async submitComment(e) {
        e.preventDefault();
        
        if (!this.isLoggedIn) {
            alert('Você precisa estar logado para comentar');
            window.location.href = 'login.html';
            return;
        }
        
        const textarea = this.container.querySelector('#comment-content');
        const content = textarea.value.trim();
        
        if (!content) {
            alert('Escreva um comentário');
            return;
        }
        
        try {
            const response = await createComment(this.documentId, content);
            
            if (response.status === 201) {
                textarea.value = '';
                this.toggleCommentForm();
                await this.loadComments();
                this.showNotification('Comentário postado com sucesso! ✓');
            } else {
                this.showNotification('Erro ao postar comentário: ' + response.data.error, 'error');
            }
        } catch (error) {
            console.error('Erro ao criar comentário:', error);
            this.showNotification('Erro ao postar comentário', 'error');
        }
    }
    
    toggleReplyForm(e) {
        const btn = e.target.closest('.reply-btn') || e.target.closest('.cancel-reply-btn');
        const commentId = btn.dataset.commentId;
        const form = this.container.querySelector(`[data-comment-id="${commentId}"].reply-form`);
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            form.querySelector('textarea').focus();
        }
    }
    
    async submitReply(e) {
        const form = e.target.closest('.reply-form');
        const commentId = form.dataset.commentId;
        const content = form.querySelector('textarea').value.trim();
        
        if (!content) {
            alert('Escreva uma resposta');
            return;
        }
        
        try {
            const response = await createComment(this.documentId, content, commentId);
            
            if (response.status === 201) {
                await this.loadComments();
                this.showNotification('Resposta postada com sucesso! ✓');
            } else {
                this.showNotification('Erro ao postar resposta', 'error');
            }
        } catch (error) {
            console.error('Erro ao criar resposta:', error);
            this.showNotification('Erro ao postar resposta', 'error');
        }
    }
    
    async toggleHelpful(e) {
        if (!this.isLoggedIn) {
            window.location.href = 'login.html';
            return;
        }
        
        const commentId = e.target.dataset.commentId;
        
        try {
            const response = await markCommentAsHelpful(commentId);
            if (response.status === 200) {
                await this.loadComments();
            }
        } catch (error) {
            console.error('Erro ao marcar como útil:', error);
        }
    }
    
    async toggleNotHelpful(e) {
        if (!this.isLoggedIn) {
            window.location.href = 'login.html';
            return;
        }
        
        const commentId = e.target.dataset.commentId;
        
        try {
            const response = await markCommentAsNotHelpful(commentId);
            if (response.status === 200) {
                await this.loadComments();
            }
        } catch (error) {
            console.error('Erro ao marcar como não útil:', error);
        }
    }
    
    startEdit(e) {
        const commentId = e.target.dataset.commentId;
        const card = this.container.querySelector(`[data-comment-id="${commentId}"].comment-card`);
        const content = card.querySelector('.comment-content p').textContent;
        
        card.querySelector('.comment-content').innerHTML = `
            <div class="edit-form">
                <textarea class="edit-textarea">${this.escapeHtml(content)}</textarea>
                <div class="form-actions">
                    <button class="save-edit-btn" data-comment-id="${commentId}">Salvar</button>
                    <button class="cancel-edit-btn" type="button">Cancelar</button>
                </div>
            </div>
        `;
        
        card.querySelector('.save-edit-btn').addEventListener('click', (e) => this.saveEdit(e));
        card.querySelector('.cancel-edit-btn').addEventListener('click', (e) => this.cancelEdit(e));
    }
    
    async saveEdit(e) {
        const commentId = e.target.dataset.commentId;
        const textarea = e.target.closest('.edit-form').querySelector('textarea');
        const newContent = textarea.value.trim();
        
        if (!newContent) {
            alert('Comentário não pode estar vazio');
            return;
        }
        
        try {
            const response = await updateComment(commentId, newContent);
            
            if (response.status === 200) {
                await this.loadComments();
                this.showNotification('Comentário atualizado com sucesso! ✓');
            } else {
                this.showNotification('Erro ao atualizar comentário', 'error');
            }
        } catch (error) {
            console.error('Erro ao editar comentário:', error);
            this.showNotification('Erro ao atualizar comentário', 'error');
        }
    }
    
    cancelEdit(e) {
        const card = e.target.closest('.comment-card');
        const commentId = card.dataset.commentId;
        const comment = this.comments.find(c => c.id === commentId);
        
        if (comment) {
            card.querySelector('.comment-content').innerHTML = `
                <p>${this.escapeHtml(comment.content)}</p>
            `;
        }
    }
    
    deleteCommentConfirm(e) {
        if (confirm('Tem certeza que deseja deletar este comentário?')) {
            this.deleteComment(e);
        }
    }
    
    async deleteComment(e) {
        const commentId = e.target.dataset.commentId;
        
        try {
            const response = await deleteComment(commentId);
            
            if (response.status === 200) {
                await this.loadComments();
                this.showNotification('Comentário deletado com sucesso! ✓');
            } else {
                this.showNotification('Erro ao deletar comentário', 'error');
            }
        } catch (error) {
            console.error('Erro ao deletar comentário:', error);
            this.showNotification('Erro ao deletar comentário', 'error');
        }
    }
    
    getUserReaction(commentId) {
        // Implementar se necessário carregar reação do usuário
        return null;
    }
    
    formatCommentDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'agora';
        if (minutes < 60) return `${minutes}m atrás`;
        if (hours < 24) return `${hours}h atrás`;
        if (days < 7) return `${days}d atrás`;
        
        return date.toLocaleDateString('pt-AO');
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    const documentId = new URLSearchParams(window.location.search).get('doc_id') || 
                     document.querySelector('[data-document-id]')?.dataset.documentId;
    
    if (documentId && document.getElementById('comments-section')) {
        new CommentManager(documentId);
    }
});
