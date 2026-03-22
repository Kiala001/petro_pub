// dashboard-data.js
// Preenche dashboards dinâmicos para common user e admin

async function fetchDashboardCommonUser() {
  try {
    const res = await apiRequest('dashboard/user');
    const data = res.data;
    if (!data.success) return;
    document.getElementById('stat-downloads').textContent = data.downloads;
    document.getElementById('stat-saved').textContent = data.saved_docs;
    document.getElementById('stat-points').textContent = data.points;
    document.getElementById('stat-remaining').textContent = data.remaining;
    document.getElementById('cycle-label').textContent = data.cycle_label;
    document.getElementById('downloads-label').textContent = `Downloads (${data.downloads}/${data.download_limit})`;
    document.getElementById('downloads-percent').textContent = data.download_percent + '%';
    document.getElementById('downloads-bar').style.width = data.download_percent + '%';
    // Planos
    document.getElementById('plan-list').innerHTML = data.plans.map(plan => `
      <div class="plan-card${plan.active ? ' active' : ''}"><div class="plan-name">${plan.name}</div><div class="plan-price">${plan.price} <span>Kz/mês</span></div><div class="plan-desc">${plan.desc}</div></div>
    `).join('');
    // Recent downloads
    document.getElementById('recent-downloads').innerHTML = data.recent_downloads.map(d => `
      <tr><td>${d.title}</td><td>${d.author}</td><td>${d.area}</td><td>${d.year}</td><td>${d.rating}</td><td>${d.price}</td><td></td></tr>
    `).join('');
    // Badges
    document.getElementById('badge-list').innerHTML = data.badges.map(b => `
      <div class="badge-item${b.earned ? ' earned' : ''}"><div class="b-icon">${b.icon}</div><div class="b-name">${b.name}</div></div>
    `).join('');
    // Ranking
    document.getElementById('ranking-list').innerHTML = data.ranking.map((user, i) => `
      <div class="rank-item${user.is_me ? ' me' : ''}"><div class="rank-num${user.tier ? ' ' + user.tier : ''}">${i+1}</div><div class="avatar${user.is_me ? ' gold' : ''}" style="width:32px;height:32px;font-size:12px">${user.initials}</div><div style="flex:1"><div style="font-size:14px;font-weight:600${user.is_me ? ';color:var(--crimson)' : ''}">${user.name}${user.is_me ? ' <small>(Você)</small>' : ''}</div><div style="font-size:11px;color:var(--text-light)">${user.institution}</div></div><div class="rank-points"${user.is_me ? ' style="color:var(--crimson)"' : ''}>${user.points} pts</div></div>
    `).join('');
    // Notificações
    document.getElementById('notification-list').innerHTML = data.notifications.map(n => `
      <div class="notif-item${n.unread ? ' unread' : ''}"><span class="n-icon">${n.icon}</span><div class="n-body"><div class="n-title">${n.title}</div><div class="n-sub">${n.sub}</div></div>${n.unread ? '<div class="notif-unread-dot"></div>' : ''}</div>
    `).join('');
  } catch (e) { /* erro silencioso */ }
}

async function fetchDashboardAdmin() {
  try {
    const res = await apiRequest('dashboard/admin');
    const data = res.data;
    if (!data.success) return;
    document.getElementById('admin-users').textContent = data.users;
    document.getElementById('admin-docs').textContent = data.docs;
    document.getElementById('admin-revenue').textContent = data.revenue;
    document.getElementById('admin-downloads').textContent = data.downloads;
    document.getElementById('admin-pending-receipts').textContent = data.pending_receipts;
    document.getElementById('admin-pending-uploads').textContent = data.pending_uploads;
    document.getElementById('admin-premium').textContent = data.premium;
    document.getElementById('admin-fraud-alerts').textContent = data.fraud_alerts;
    // Adicione mais preenchimento conforme necessário
  } catch (e) { /* erro silencioso */ }
}

// Detecta e executa dashboard correto
window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('dash-student')) fetchDashboardCommonUser();
  if (document.getElementById('dash-admin')) fetchDashboardAdmin();
});
