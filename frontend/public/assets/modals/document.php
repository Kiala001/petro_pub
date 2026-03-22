<!-- DOCUMENT MODALS -->
 <!-- DELETE MODAL -->

<div class="modal-overlay" id="modal-delete" onclick="closeModalOutside(event,'modal-delete')">
  <div class="modal" style="max-width:400px">
    <div class="modal-head" style="background:linear-gradient(135deg,#4A0B16,#7A1020)">
      <h3>Confirmar Exclusão</h3>
      <p>Esta acção não pode ser desfeita</p>
      <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
    </div>
    <div class="modal-body" style="text-align:center;padding:32px">
      <div style="font-size:48px;margin-bottom:16px">
        <i class="fa fa-trash" style="color: var(--crimson-light)"></i>
      </div>
      <h3 style="font-family:'Playfair Display',serif;font-size:18px;color:var(--text-dark);margin-bottom:8px">Remover este artigo?</h3>
      <p style="font-size:14px;color:var(--text-light);line-height:1.6">Artigos publicados já não podem ser excluidos, o mesmo acontece com os usuários que pagam para terem os artigos na plataforma.</p>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:12px">
      <button class="btn btn-ghost" onclick="closeModal('modal-delete')">Cancelar</button>
      <button class="btn btn-danger" onclick="confirmDelete()">Sim, Remover</button>
    </div>
  </div>
</div>


<!-- ═══ MODAL: DECISÃO ═══ -->
<div
  class="overlay"
  id="modal-decide"
  onclick="ovClose(event, 'modal-decide')"
>
  <div class="modal modal-md">
    <div class="m-hd mh-cr">
      <h3>⚖️ Decisão Editorial</h3>
      <p>Aprovar ou rejeitar a publicação do documento na plataforma</p>
      <button class="m-close" onclick="closeModal('modal-decide')">
        ✕
      </button>
    </div>
    <div class="m-body">
      <div class="mdp">
        <div class="mdp-lbl">Documento em análise</div>
        <div class="mdp-title" id="dec-title">—</div>
        <div class="mdp-meta" id="dec-meta">—</div>
      </div>
      <label class="f-lbl"
        >Decisão <span style="color: var(--cr)">*</span></label
      >
      <div class="dec-opts">
        <div class="dec-opt" id="dec-approve" onclick="selDec('approve')">
          <div class="dec-radio" id="dr-a"></div>
          <span class="dec-ico">✅</span>
          <div class="dec-tx">
            <div class="dec-nm">Aprovar Publicação</div>
            <div class="dec-ds">
              O documento cumpre os critérios e será publicado e
              disponibilizado para download pelos utilizadores.
            </div>
          </div>
        </div>
        <div class="dec-opt" id="dec-reject" onclick="selDec('reject')">
          <div class="dec-radio" id="dr-r"></div>
          <span class="dec-ico">❌</span>
          <div class="dec-tx">
            <div class="dec-nm">Rejeitar Documento</div>
            <div class="dec-ds">
              O documento não cumpre os critérios mínimos de qualidade e não
              será publicado na plataforma.
            </div>
          </div>
        </div>
      </div>
      <label class="f-lbl"
        >Nota para o Autor
        <span
          style="font-weight: 400; text-transform: none; color: var(--tx-l)"
          >(opcional)</span
        ></label
      >
      <textarea
        class="f-ta"
        id="dec-note"
        placeholder="Explique os motivos, pontos a melhorar ou felicitações…"
      ></textarea>
    </div>
    <div class="m-foot">
      <button class="btn btn-gh" onclick="closeModal('modal-decide')">
        Cancelar
      </button>
      <button class="btn btn-cr" onclick="confirmDecision()">
        ⚖️ Confirmar Decisão
      </button>
    </div>
  </div>
</div>

<!-- ═══ MODAL: RESULTADO ═══ -->
<div
  class="overlay"
  id="modal-result"
  onclick="ovClose(event, 'modal-result')"
>
  <div class="modal modal-sm">
    <div class="m-hd" id="res-mhd">
      <h3 id="res-htitle">—</h3>
      <p id="res-hsub">—</p>
      <button class="m-close" onclick="closeModal('modal-result')">
        ✕
      </button>
    </div>
    <div class="res-center">
      <div class="res-ico" id="res-ico">🎉</div>
      <div class="res-title" id="res-title">—</div>
      <div class="res-desc" id="res-desc">—</div>
      <div class="res-sum" id="res-sum">
        <div class="res-row">
          <span class="rl">Documento</span
          ><span class="rv" id="rs-doc">—</span>
        </div>
        <div class="res-row">
          <span class="rl">Decisão</span
          ><span class="rv" id="rs-dec">—</span>
        </div>
        <div class="res-row">
          <span class="rl">Data</span><span class="rv" id="rs-date">—</span>
        </div>
      </div>
    </div>
    <div class="m-foot" style="justify-content: center">
      <button class="btn btn-gh" onclick="closeModal('modal-result')">
        <i class="fa fa-close"></i>
        Fechar
      </button>
    </div>
  </div>
</div>