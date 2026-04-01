// Upload Functions
let selectedDocType = "";
let selectedPaymentMethods = [];
let pubMode = "immediate";
let accessMode = "paid";
let selectedFile = null;
let selectedCover = null;
let file_size = 0;

// ══════════════════════════════════
// SUBMIT
// ══════════════════════════════════
function submitDocument() {
  const title = document.getElementById("doc-title").value;
  const price = document.getElementById("doc-price").value;
  const localization = document.getElementById("doc-localization").value;
  const abstract = document.getElementById("doc-abstract").value;
  const keywords = document
    .getElementById("doc-tags")
    .value.split(",")
    .map((k) => k.trim())
    .filter((k) => k);
  const course = document.getElementById("doc-inst").value;
  const doc_authors = document
    .getElementById("doc-authors")
    .value.split(",")
    .map((a) => a.trim())
    .filter((a) => a);

  if (!title) {
    showToast("⚠️ Preencha o título do documento");
    return;
  }
  if (!abstract || abstract.length < 50) {
    showToast("⚠️ Preencha o resumo (mínimo 50 caracteres)");
    return;
  }
  if (!course) {
    showToast("⚠️ Preencha o curso ou área");
    return;
  }
  if (doc_authors.length === 0) {
    showToast("⚠️ Preencha pelo menos um autor");
    return;
  }
  if (keywords.length < 2) {
    showToast("⚠️ Adicione pelo menos duas palavras-chave");
    return;
  }
  if (!selectedDocType) {
    showToast("⚠️ Seleccione o tipo de documento");
    return;
  }
  if (!selectedFile) {
    showToast("⚠️ Faça upload do ficheiro do documento");
    return;
  }
  if (!price) {
    showToast("⚠️ Deve preencher o preço da venda do livro");
    return;
  }
  if (!location) {
    showToast("⚠️ Deves especificar o local onde será feito a comercialização");
    return;
  }
  if (pubMode === "scheduled") {
    if (!document.getElementById("sched-date").value) {
      showToast("⚠️ Defina a data de publicação");
      return;
    }
    if (!document.getElementById("sched-time").value) {
      showToast("⚠️ Defina a hora de publicação");
      return;
    }
  }

  const date = document.getElementById("doc-date").value;
  const advisor = document.getElementById("doc-advisor").value;
  const sched_date = document.getElementById("sched-date").value;
  const sched_time = document.getElementById("sched-time").value;

  const docType = selectedDocType;
  const document_file = selectedFile;
  const cover_file = selectedCover;
  const authors = JSON.stringify(doc_authors);
  const summary = abstract;
  const doc_keywords = JSON.stringify(keywords);

  uploadForm(
    document_file,
    docType,
    advisor,
    date,
    title,
    authors,
    course,
    summary,
    doc_keywords,
    cover_file,
    pubMode,
    sched_date,
    sched_time,
    price,
    localization,
  );
}

async function uploadForm(
  document,
  docType,
  advisor,
  date,
  title,
  authors,
  course,
  summary,
  keywords,
  cover_file,
  pubMode,
  sched_date,
  sched_time,
  price,
  localization,
) {
  const formData = new FormData();

  formData.append("document", document);
  formData.append("docType", docType);
  formData.append("advisor", advisor);
  formData.append("date", date);
  formData.append("title", title);
  formData.append("authors", authors);
  formData.append("course", course);
  formData.append("summary", summary);
  formData.append("keywords", keywords);
  formData.append("cover_file", cover_file);
  formData.append("pubMode", pubMode);
  formData.append("sched_date", sched_date);
  formData.append("sched_time", sched_time);
  formData.append("file_size", file_size);
  formData.append("price", price);
  formData.append("location", localization);

  // Depurando
  // console.log("====== DEPURAÇÃO");
  // formData.forEach((value, key) => {
  //   console.log(key, value);
  // });
  // console.log("====== FIM ======");

  try {
    const response = await fetch(`${API_BASE_URL}/documents`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${getToken()}`,
      },
      body: formData,
    });

    const data = await response.json();

    if (!data.success) {
      showToast(data.error || data.message);
      return;
    }

    showToast(data.message || "Documento submetido com sucesso!");

    setTimeout(() => {
      window.location.href = "my-documents.php";
    }, 2000);
  } catch (error) {
    console.error(error);
    showToast("Erro ao enviar documento");
  }
}

function handleFileSelect(file) {
  const maxSize = 50 * 1024 * 1024; // 50MB
  const allowedTypes = [
    "application/pdf",
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  ];

  if (file.size > maxSize) {
    showToast("Arquivo muito grande (máximo 50MB)");
    return;
  }

  if (!allowedTypes.includes(file.type)) {
    showToast("Tipo de arquivo não permitido");
    return;
  }

  selectedFile = file;
}

function selectDocType(el, type) {
  document
    .querySelectorAll(".doc-type-option")
    .forEach((o) => o.classList.remove("selected"));
  el.classList.add("selected");
  selectedDocType = type;
  updateSummary();
}

function togglePaymentMethod(el, name) {
  el.classList.toggle("selected");
  const check = el.querySelector(".pm-check");
  if (el.classList.contains("selected")) {
    check.textContent = "✓";
    if (!selectedPaymentMethods.includes(name))
      selectedPaymentMethods.push(name);
  } else {
    check.textContent = "";
    selectedPaymentMethods = selectedPaymentMethods.filter((m) => m !== name);
  }
  updateSummary();
}

function selectPublication(mode) {
  pubMode = mode;
  document
    .getElementById("pub-immediate")
    .classList.toggle("selected", mode === "immediate");
  document
    .getElementById("pub-scheduled")
    .classList.toggle("selected", mode === "scheduled");
  const box = document.getElementById("scheduler-box");
  box.classList.toggle("visible", mode === "scheduled");
  const row = document.getElementById("sum-sched-row");
  row.style.display = mode === "scheduled" ? "flex" : "none";
  document.getElementById("sum-pub").textContent =
    mode === "immediate" ? "Imediata" : "Programada";
  updateSummary();
}

function selectAccess(mode) {
  accessMode = mode;
  document
    .getElementById("access-paid")
    .classList.toggle("selected", mode === "paid");
  document
    .getElementById("access-free")
    .classList.toggle("selected", mode === "free");
  document.getElementById("pricing-card").style.opacity =
    mode === "free" ? "0.5" : "1";
  document.getElementById("pricing-card").style.pointerEvents =
    mode === "free" ? "none" : "all";
  document.getElementById("sum-access").textContent =
    mode === "paid" ? "Pago" : "Gratis";
  if (mode === "free") {
    document.getElementById("doc-price").value = "0";
    document.querySelectorAll(".pm-option").forEach((o) => {
      o.classList.remove("selected");
      o.querySelector(".pm-check").textContent = "";
    });
    selectedPaymentMethods = [];
  }
  updateSummary();
}

function updateSummary() {
  const title = document.getElementById("doc-title").value;
  const date = document.getElementById("doc-date").value;
  const inst = document.getElementById("doc-inst").value;
  const price = document.getElementById("doc-price").value;
  const schedDate = document.getElementById("sched-date").value;
  const schedTime = document.getElementById("sched-time").value;

  setText(
    "sum-title",
    title || '<em class="empty">Não preenchido</em>',
    !title,
  );
  setText(
    "sum-type",
    selectedDocType || '<em class="empty">Não seleccionado</em>',
    !selectedDocType,
  );
  setText(
    "sum-date",
    date ? formatDate(date) : '<em class="empty">—</em>',
    !date,
  );
  setText("sum-inst", inst || '<em class="empty">—</em>', !inst);

  if (pubMode === "scheduled" && schedDate) {
    document.getElementById("sum-sched").textContent =
      formatDate(schedDate) + (schedTime ? " às " + schedTime : "");
  }

  const pmText =
    selectedPaymentMethods.length > 0
      ? selectedPaymentMethods.map((m) => m.split(" ")[0]).join(", ")
      : '<em class="empty">Nenhum</em>';
  setText("sum-payments", pmText, selectedPaymentMethods.length === 0);

  const priceVal = price
    ? parseInt(price).toLocaleString("pt-PT") + " Kz"
    : "0 Kz";
  document.getElementById("sum-price").textContent =
    accessMode === "free" ? "Gratuito" : priceVal;
}

function setText(id, val, empty) {
  const el = document.getElementById(id);
  el.innerHTML = val;
  el.className = "s-val" + (empty ? " empty" : "");
}

function formatDate(d) {
  const [y, m, day] = d.split("-");
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
  return `${day} ${months[parseInt(m) - 1]} ${y}`;
}

function handleDragOver(e) {
  e.preventDefault();
  document.getElementById("drop-zone").classList.add("drag-over");
}

function handleDragLeave(e) {
  document.getElementById("drop-zone").classList.remove("drag-over");
}

function handleDrop(e) {
  e.preventDefault();
  document.getElementById("drop-zone").classList.remove("drag-over");
  const file = e.dataTransfer.files[0];
  if (file) showFilePreview(file);
}

function showFilePreview(file) {
  selectedFile = file;
  const ext = file.name.split(".").pop().toUpperCase();
  const size = (file.size / 1024 / 1024).toFixed(1);
  file_size = size;
  document.getElementById("file-name").textContent = file.name;
  document.getElementById("file-size").textContent = `${size} MB · ${ext}`;
  document.getElementById("file-preview").classList.add("visible");
  document.getElementById("sum-file").innerHTML =
    `<span style="color:var(--success);font-weight:600">✓ ${file.name}</span>`;
  document.getElementById("sum-file").className = "s-val";
  simulateUpload();
}

function simulateUpload() {
  const prog = document.getElementById("upload-progress");
  const fill = document.getElementById("prog-fill");
  const pct = document.getElementById("prog-pct");
  const label = document.getElementById("prog-label");
  prog.classList.add("visible");
  let p = 0;
  const iv = setInterval(() => {
    p += Math.random() * 18;
    if (p >= 100) {
      p = 100;
      clearInterval(iv);
      label.textContent = "✓ Ficheiro carregado";
    }
    fill.style.width = p + "%";
    pct.textContent = Math.round(p) + "%";
  }, 180);
}

function removeFile() {
  selectedFile = null;
  document.getElementById("file-preview").classList.remove("visible");
  document.getElementById("upload-progress").classList.remove("visible");
  document.getElementById("file-input").value = "";
  document.getElementById("sum-file").innerHTML =
    '<em class="empty">Nenhum</em>';
  document.getElementById("sum-file").className = "s-val empty";
}

// document.addEventListener("DOMContentLoaded", () => {
// //   loadCategories();
//   setupFileInput();
// });

// async function loadCategories() {
//   const response = await apiRequest("categories");

//   console.log("Rasteio");
//   if (response.data.success) {
//     const select = document.getElementById("category");
//     select.innerHTML = response.data.categories
//       .map((cat) => `<option value="${cat.id}">${cat.name}</option>`)
//       .join("");
//   }
// }

// function setupFileInput() {
//   const fileInput = document.getElementById("fileInput");
//   const dragZone = fileInput.parentElement;

//   fileInput.addEventListener("change", (e) => {
//     handleFileSelect(e.target.files[0]);
//   });

//   dragZone.addEventListener("dragover", (e) => {
//     e.preventDefault();
//     dragZone.classList.add("border-red-900", "bg-red-50");
//   });

//   dragZone.addEventListener("dragleave", () => {
//     dragZone.classList.remove("border-red-900", "bg-red-50");
//   });

//   dragZone.addEventListener("drop", (e) => {
//     e.preventDefault();
//     dragZone.classList.remove("border-red-900", "bg-red-50");
//     if (e.dataTransfer.files.length) {
//       handleFileSelect(e.dataTransfer.files[0]);
//     }
//   });
// }


// function showError(message) {
//     const div = document.getElementById('error');
//     div.textContent = message;
//     div.classList.remove('hidden');
//     document.getElementById('success').classList.add('hidden');
// }

// function showSuccess(message) {
//     const div = document.getElementById('success');
//     div.textContent = message;
//     div.classList.remove('hidden');
//     document.getElementById('error').classList.add('hidden');
// }

function clearError() {
  document.getElementById("error").classList.add("hidden");
}
