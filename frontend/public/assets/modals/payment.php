
  <div class="overlay" id="modal-add" onclick="ovClose(event,'modal-add')">
    <div class="modal modal-lg">
      <div class="modal-head mh-cr">
        <h3 id="modal-add-title"><i class="fa fa-bank"></i> Registar Meio de Pagamento</h3>
        <p id="modal-add-sub">Adicione uma nova forma de receber pelos seus documentos</p>
        <button class="modal-close" onclick="closeModal('modal-add')">✕</button>
      </div>
      <div class="modal-body">

        <!-- Type selector -->
        <div class="form-field">
          <label class="f-label">Tipo de Meio <span class="req">*</span></label>
          <div class="mtype-grid">
            <div class="mtype-card sel" id="mt-iban" onclick="selectMType('iban')">
              <div class="mtype-icon"><i class="fa fa-bank"></i></div>
              <div class="mtype-name">Transferência Bancária</div>
              <div class="mtype-sub">Via IBAN</div>
            </div>
            <div class="mtype-card" id="mt-express" onclick="selectMType('express')">
              <div class="mtype-icon"><i class="fa fa-phone"></i></div>
              <div class="mtype-name">Multicaixa Express</div>
              <div class="mtype-sub">App móvel</div>
            </div>
            <div class="mtype-card" id="mt-kwik" onclick="selectMType('kwik')">
              <div class="mtype-icon"><i class="fa fa-money"></i></div>
              <div class="mtype-name">Kwik</div>
              <div class="mtype-sub">Pagamento digital</div>
            </div>
          </div>
        </div>

        <!-- IBAN fields -->
        <div class="dyn-fields visible" id="fields-iban">
          <div class="fields-box">
            <div class="fb-header">
              <div class="fb-ico ico-iban"><i class="fa fa-bank"></i></div>
              <div><div class="fb-title">Transferência Bancária</div><div class="fb-sub">Dados da conta bancária para transferências</div></div>
            </div>
            <div class="form-field">
              <label class="f-label">Nome do Titular <span class="req">*</span></label>
              <input class="f-input" id="iban-titular" type="text" placeholder="Nome completo conforme nos registos do banco">
            </div>
            <div class="form-field" style="margin-bottom:0">
              <label class="f-label">IBAN <span class="req">*</span></label>
              <input class="f-input" id="iban-iban" type="text" placeholder="AO06 0044 0000 0000 0000 1 01" maxlength="34" oninput="formatIBAN(this)">
              <div class="f-hint">🇦🇴 Formato angolano: AO06 seguido de 21 dígitos.</div>
            </div>
          </div>
        </div>

        <!-- EXPRESS fields -->
        <div class="dyn-fields" id="fields-express">
          <div class="fields-box">
            <div class="fb-header">
              <div class="fb-ico ico-express"><i class="fa fa-phone"></i></div>
              <div><div class="fb-title">Multicaixa Express</div><div class="fb-sub">Número registado no Multicaixa Express</div></div>
            </div>
            <div class="form-field" style="margin-bottom:0">
              <label class="f-label">Número de Telefone <span class="req">*</span></label>
              <input class="f-input" id="express-tel" type="tel" placeholder="+244 9XX XXX XXX">
              <div class="f-hint">Deve estar associado a uma conta Multicaixa Express activa</div>
            </div>
          </div>
        </div>

        <!-- KWIK fields -->
        <div class="dyn-fields" id="fields-kwik">
          <div class="fields-box">
            <div class="fb-header">
              <div class="fb-ico ico-kwik"><i class="fa fa-up"></i></div>
              <div><div class="fb-title">Kwik</div><div class="fb-sub">Seleccione o tipo de identificação Kwik</div></div>
            </div>
            <div class="form-field">
              <label class="f-label">Tipo de Identificação <span class="req">*</span></label>
              <div class="kwik-chips">
                <div class="kwik-chip sel" id="kc-alcunha" onclick="selKwik('alcunha')"><i class="fa fa-user"></i> Alcunha</div>
                <div class="kwik-chip" id="kc-iban"    onclick="selKwik('iban')"><i class="fa fa-bank"></i> IBAN</div>
                <div class="kwik-chip" id="kc-numero"  onclick="selKwik('numero')"><i class="fa fa-hash"></i> Número</div>
                <div class="kwik-chip" id="kc-email"   onclick="selKwik('email')"><i class="fa fa-envelope"></i> E-mail</div>
              </div>
            </div>
            <div id="kf-alcunha" class="form-field" style="margin-bottom:0">
              <label class="f-label">Alcunha (Username) <span class="req">*</span></label>
              <input class="f-input" id="kwik-alcunha" type="text" placeholder="seuusername (sem @)">
              <div class="f-hint">@ Será apresentado como @seuusername na plataforma Kwik</div>
            </div>
            <div id="kf-iban" class="form-field" style="margin-bottom:0;display:none">
              <label class="f-label">IBAN Kwik <span class="req">*</span></label>
              <input class="f-input" id="kwik-iban" type="text" placeholder="AO06 0044 0000 0000 0000 1 01" oninput="formatIBAN(this)">
            </div>
            <div id="kf-numero" class="form-field" style="margin-bottom:0;display:none">
              <label class="f-label">Número de Telemóvel <span class="req">*</span></label>
              <input class="f-input" id="kwik-numero" type="tel" placeholder="+244 9XX XXX XXX">
            </div>
            <div id="kf-email" class="form-field" style="margin-bottom:0;display:none">
              <label class="f-label">E-mail Kwik <span class="req">*</span></label>
              <input class="f-input" id="kwik-email" type="email" placeholder="email@exemplo.ao">
            </div>
          </div>
        </div>

        <!-- Active toggle -->
        <div class="toggle-field" style="margin-top:16px">
          <div class="toggle-field-info">
            <div class="tfi-title">Activar imediatamente</div>
            <div class="tfi-sub">O meio ficará disponível para receber pagamentos após o registo</div>
          </div>
          <div class="toggle-wrap" onclick="toggleSwitch('modal-toggle')">
            <div class="toggle-track on" id="modal-toggle"><div class="toggle-knob"></div></div>
            <span class="toggle-lbl" id="modal-toggle-lbl">Activo</span>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-gh" onclick="closeModal('modal-add')">Cancelar</button>
        <button class="btn btn-cr" id="modal-save-btn" onclick="savePM()">Guardar Meio de Pagamento</button>
        <button class="btn btn-cr" id="modal-edit-btn" onclick="editPM()" style="display: none;">Guardar Alterações</button>
      </div>
    </div>
  </div>


  <!-- ══════════════════════════════════════════════════════
      MODAL — DELETE CONFIRM
  ══════════════════════════════════════════════════════ -->
  <div class="overlay" id="modal-delete" onclick="ovClose(event,'modal-delete')">
    <div class="modal modal-sm">
      <div class="modal-head mh-er">
        <h3><i class="fa fa-trash"></i> Remover Meio de Pagamento</h3>
        <p>Esta acção é permanente e não pode ser desfeita</p>
        <button class="modal-close" onclick="closeModal('modal-delete')">✕</button>
      </div>
      <div class="delete-body">
        <div class="del-ico"><i class="fa fa-warning"></i></div>
        <div class="del-title">Tem a certeza?</div>
        <div class="del-body-text">Ao remover este meio de pagamento, os documentos que o utilizam deixarão de o aceitar. Precisará de actualizar as configurações de pagamento dos documentos afectados.</div>
        <!-- <div class="del-card-preview" id="del-preview">
          <div class="del-card-ico" id="del-preview-ico"><i class="fa fa-bank"></i></div>
          <div>
            <div class="del-card-name" id="del-preview-name">—</div>
            <div class="del-card-detail" id="del-preview-detail">—</div>
          </div>
        </div> -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-gh" onclick="closeModal('modal-delete')">Cancelar</button>
        <button class="btn btn-er" onclick="confirmDelete()">🗑 Sim, Remover</button>
      </div>
    </div>
  </div>