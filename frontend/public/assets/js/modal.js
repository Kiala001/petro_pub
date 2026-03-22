/* ═══ MODAL ═══ */
function openModal(id) {
  document.getElementById(id).classList.add("open");
  document.body.style.overflow = "hidden";
}
function closeModal(id) {
  document.getElementById(id).classList.remove("open");
  document.body.style.overflow = "";
}
function ovClose(e, id) {
  if (e.target.id === id) closeModal(id);
}
