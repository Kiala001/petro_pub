
let deleteTarget = null;
function deleteArticle(id) {
  deleteTarget = id;
  openModal('modal-delete');
}

async function confirmDelete() {
    if (!deleteTarget) { 
        showToast('Id do artigo não fornecido');
        return ;
    }

    try {
        const response =  await apiRequest(`documents/${deleteTarget}`, {
            method: 'DELETE'
        });

        const respData = response.data || {}
        
        if (respData.success) {
            showToast(respData.message || respData.error || '🗑 Artigo excluido com sucesso.')
            
            setTimeout(() =>
                document.location.href = 'my-documents.php?dnjvoivhijovihirjofjrhvjofjuvdifjioruhfi4rfij58ty89u98yt8u49rfihghu95jfo8y58tfirh5u8h8=urhihguut9u858fufhu54h8tf9guhijuih489u9yt8u58uyut905y8tu5904htuy58u905uy8u90tuuhguy8ut9i95uy89y65yg' 
            , 580); 
        } else {
            showToast(respData.message || respData.error || '🗑 Erro ao excluir artigo');
        }
        closeModal('modal-delete');
        
    } catch (error) {
        console.error('Erro ao excluir artigo:', error);
        
        closeModal('modal-delete');
        showToast('Erro ao excluir artigo. Tente mais tarde...');
    }    
    
  
}

function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalOutside(e, id) { if (e.target.id === id) closeModal(id); }