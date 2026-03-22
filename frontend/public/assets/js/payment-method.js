async function loadPaymentMethods() {
  const response = await apiRequest("payment-methods");
  if (!response.data.success) return;
  pmData = response.data.methods;
 
  renderList(pmData);
  const step1 = JSON.parse(pmData.data)
  pmData = JSON.parse(step1)
}

loadPaymentMethods()

function openAddFresh() {
  editingId = null;
  document.getElementById("modal-add-title").textContent =
    "Registar Meio de Pagamento";
  document.getElementById("modal-add-sub").textContent =
    "Adicione uma nova forma de receber pelos seus documentos";
  document.getElementById("modal-save-btn").textContent =
    "Guardar Meio de Pagamento";

  // Reset
  selectMType("iban");
  selKwik("alcunha");
  [
    "iban-titular",
    "iban-iban",
    "dep-titular",
    "dep-iban",
    "dep-conta",
    "express-tel",
    "kwik-alcunha",
    "kwik-iban",
    "kwik-numero",
    "kwik-email",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });

  modalActive = true;
  const tr = document.getElementById("modal-toggle");
  const lb = document.getElementById("modal-toggle-lbl");
  tr.classList.add("on");
  tr.classList.remove("off");
  lb.textContent = "Activo";

  openModal("modal-add");
}

function validateAndCollect() {
  const type = currentMType;
  let name = "",
    detail = "",
    extra = "",
    raw = {};

  if (type === "iban") {
    const t = document.getElementById("iban-titular").value.trim();
    const i = document.getElementById("iban-iban").value.trim();

    if (!t || !i) {
      showToast("Preencha todos os campos obrigatórios");
      return null;
    }

    name = t;
    detail = i;
    extra = "Transferência Bancária";
    raw = { titular: t, iban: i };
    if (validateIBAN() === null) {
      return null;
    }
  } else if (type === "express") {
    const tel = document.getElementById("express-tel").value.trim();
    if (!tel) {
      showToast("Preencha o número de telefone usado no express");
      return null;
    }
    name = tel;
    detail = "Multicaixa Express";
    extra = "";
    raw = { tel };
    if (validate("tel", tel) === null) {
      return null;
    }
  } else if (type === "kwik") {
    const kwikFields = {
      alcunha: document.getElementById("kwik-alcunha").value.trim(),
      iban: document.getElementById("kwik-iban").value.trim(),
      numero: document.getElementById("kwik-numero").value.trim(),
      email: document.getElementById("kwik-email").value.trim(),
    };

    const val = kwikFields[currentKwik];
    if (!val) {
      showToast("Preencha o valor correspondente ao tipo de Kwik escolhido");
      return null;
    }
    const kwikLabels = {
      alcunha: "Alcunha",
      iban: "IBAN",
      numero: "Número",
      email: "E-mail",
    };

    name = currentKwik === "alcunha" ? "@" + val : val;
    detail = `Kwik — ${kwikLabels[currentKwik]}`;
    extra = "";
    raw = { kwikType: currentKwik, val };

    if (currentKwik == "Número") {
      if (validate("tel", val) === null) {
        return null;
      }
    }

    if (currentKwik == "E-mail") {
      if (validate("email", val)) {
        return null;
      }
    }
  }

  return { type, name, detail, extra, raw, active: modalActive };
}

function validateIBAN() {
  const titular = document.getElementById("iban-titular").value.trim();
  const iban = document.getElementById("iban-iban").value.trim();
  if (titular.length < 5) {
    showToast("Nome do titular inválido, deve ter mais de 5 dígitos");
    return null;
  }
  if (
    !/^AO\d{2}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{4}\s?\d{1,3}\s?\d{1,2}$/.test(iban)
  ) {
    showToast("IBAN inválido. Ex.: AO06 0044 0000 0000 0000 1 01");
    return null;
  }
}

function validate(type, val) {
  if (type === "tel") {
    if (!/^\+244\s?9\d{2}\s?\d{3}\s?\d{3}$/.test(val)) {
      showToast("Número de telefone inválido");
      return null;
    }
  }

  if (type === "email") {
    if (!/^\S+@\S+\.\S+$/.test(val)) {
      showToast("Email inválido");
      return null;
    }
  }
}

async function savePM() {
  const data = validateAndCollect();
  if (!data) {
    return;
  }

  const isActive = data.active ? true : false;
  data.active = isActive;

  const today = new Date();
  const months = [
    "Jan",
    "Fev",
    "Mar",
    "Abr",
    "Mai",
    "Jun",
    "Jul",
    "Ago",
    "Set",
    "Out",
    "Nov",
    "Dez",
  ];
  const added = `${today.getDate()} ${months[today.getMonth()]} ${today.getFullYear()}`;

  data.date = added;
  const request = data;

  const response = await apiRequest("payment-methods", {
    method: "POST",
    body: request,
  });

  if (!response || response.status >= 500) {
    showToast("Erro no servidor. Tente novamente mais tarde.");
    return;
  }

  const respData = response.data || {};

  if (!respData.success) {
    const message = respData.message || "Erro ao registar o meio de pagamento";
    showToast(message);
    return;
  }

  showToast(`${respData.message}!`, "success");

  closeModal("modal-add");
  loadPaymentMethods()
}

async function loadPaymentMethods() {
  const response = await apiRequest("payment-methods");
  if (!response.data.success) return;
  pmData = response.data.methods;
  renderList(pmData);
}

function openEdit(id) {
  const payload = localStorage.getItem(id)
  const pm = JSON.parse(payload)

  editingId = id;

  // Update modal title
  document.getElementById("modal-add-title").textContent = "Editar Meio de Pagamento";
  document.getElementById("modal-add-sub").textContent = "Actualize os dados deste método de pagamento";
  document.getElementById("modal-edit-btn").style.display = "block";
  document.getElementById("modal-save-btn").style.display = "none";
  
  openModal("modal-add");

  // Select type
  selectMType(pm.type);

  // Populate fields
  if (pm.type === "iban") {
    document.getElementById("iban-titular").value = pm.raw.titular || "";
    document.getElementById("iban-iban").value = pm.raw.iban || "";
  } else if (pm.type === "deposito") {
    document.getElementById("dep-titular").value = pm.raw.titular || "";
    document.getElementById("dep-iban").value = pm.raw.iban || "";
    document.getElementById("dep-conta").value = pm.raw.conta || "";
  } else if (pm.type === "express") {
    document.getElementById("express-tel").value = pm.raw.tel || "";
  } else if (pm.type === "kwik") {
    selKwik(pm.raw.kwikType || "alcunha");
    const fId = `kwik-${pm.raw.kwikType || "alcunha"}`;
    if (document.getElementById(fId))
      document.getElementById(fId).value = pm.raw.val || "";
  }

  // Toggle state
  modalActive = pm.active;
  const tr = document.getElementById("modal-toggle");
  const lb = document.getElementById("modal-toggle-lbl");
  tr.classList.toggle("on", pm.active);
  tr.classList.toggle("off", !pm.active);
  lb.textContent = pm.active ? "Activo" : "Inactivo";

}

async function editPM() {
  const data = validateAndCollect();
  if (!data) {
    return;
  }

  data.id = editingId

  const isActive = data.active ? true : false;
  data.active = isActive;

  const today = new Date();
  const months = [
    "Jan",
    "Fev",
    "Mar",
    "Abr",
    "Mai",
    "Jun",
    "Jul",
    "Ago",
    "Set",
    "Out",
    "Nov",
    "Dez",
  ];
  const added = `${today.getDate()} ${months[today.getMonth()]} ${today.getFullYear()}`;

  data.date = added;
  const request = data;

  const response = await apiRequest("payment-methods", {
    method: "PUT",
    body: request,
  });

  if (!response || response.status >= 500) {
    showToast("Erro no servidor. Tente novamente mais tarde.");
    return;
  }

  const respData = response.data || {};

  if (!respData.success) {
    const message = respData.message || "Erro ao guardar as alterações feitas";
    showToast(message);
    return;
  }

  showToast(`${respData.message}!`, "success");

  closeModal("modal-add");
  loadPaymentMethods()
}

function openDelete(id) {
  deleteId = id;
  
  openModal("modal-delete");
}

async function confirmDelete() {
  if (!deleteId) return;

  const response = await apiRequest(`payment-methods/${deleteId}`, {
    method: "DELETE",
    body: deleteId,
  });

  if (!response || response.status >= 500) {
    showToast("Erro no servidor. Tente novamente mais tarde.");
    return;
  }

  const respData = response.data || {};

  if (!respData.success) {
    const message = respData.message || "Erro ao exluir o meio de pagamento";
    showToast(message);
    return;
  }

  showToast(`${respData.message}!`, "success");

  closeModal("modal-delete");
  loadPaymentMethods()
}
