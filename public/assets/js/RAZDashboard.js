/**
 * RAZDashboard.js — Logic Dashboard SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Memuat statistik dashboard, chart penjualan,
 *            transaksi terbaru, dan item terlaris via API.
 *            Menggunakan Chart.js untuk visualisasi grafik.
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const role = document.body.dataset.role || 'owner';

  if (role === 'superadmin') {
    loadAdminDashboard();
  } else {
    loadStoreDashboard();
  }
});

// ============================================================
// DASHBOARD OWNER / EMPLOYEE
// ============================================================
async function loadStoreDashboard() {
  // Muat semua data secara paralel untuk performa
  await Promise.all([
    loadStats(),
    loadSalesChart(),
    loadRecentTransactions(),
    loadTopItems(),
  ]);
}

/**
 * Muat statistik utama (penjualan, produk, laba, pengeluaran)
 */
async function loadStats() {
  const data = await RAZ.api('api/RAZapiStores.php?action=dashboard_stats');
  if (!data.success) return;

  const s = data.data;

  // Update angka di stat cards
  updateStat('statSalesToday', RAZ.formatRupiah(s.sales_today.total));
  updateStat('statSalesCount', s.sales_today.count + ' transaksi');
  updateStat('statSalesMonth', RAZ.formatRupiah(s.sales_month.total));
  updateStat('statProducts', s.products + ' item');
  updateStat('statLowStock', s.low_stock + ' item');
  updateStat('statProfit', RAZ.formatRupiah(s.profit_today));
  updateStat('statExpense', RAZ.formatRupiah(s.expense_today));

  // Tampilkan peringatan stok menipis jika ada
  if (s.low_stock > 0) {
    const badge = document.getElementById('lowStockBadge');
    if (badge) { badge.textContent = s.low_stock; badge.style.display = 'inline-flex'; }
  }
}

/** Helper: Update teks elemen stat */
function updateStat(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

/**
 * Muat chart penjualan 7 hari terakhir (Chart.js)
 */
async function loadSalesChart() {
  const data = await RAZ.api('api/RAZapiStores.php?action=sales_chart');
  if (!data.success || !data.data.length) return;

  const ctx = document.getElementById('salesChart');
  if (!ctx) return;

  const labels = data.data.map(d => d.date);
  const values = data.data.map(d => d.total);

  // Buat gradient untuk area chart
  const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 280);
  gradient.addColorStop(0, 'rgba(79, 70, 229, 0.3)');
  gradient.addColorStop(1, 'rgba(79, 70, 229, 0.01)');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Penjualan',
        data: values,
        borderColor: '#4F46E5',
        backgroundColor: gradient,
        borderWidth: 2.5,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#4F46E5',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1E1B4B',
          titleFont: { family: 'Inter', size: 12 },
          bodyFont: { family: 'Inter', size: 13 },
          padding: 12,
          cornerRadius: 8,
          callbacks: {
            label: (ctx) => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { family: 'Inter', size: 12 }, color: '#6B7280' },
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(0,0,0,0.05)' },
          ticks: {
            font: { family: 'Inter', size: 11 },
            color: '#6B7280',
            callback: (v) => v >= 1000000 ? (v/1000000).toFixed(1) + 'jt' : v >= 1000 ? (v/1000) + 'rb' : v,
          },
        }
      }
    }
  });

  // Hapus skeleton
  const skeleton = document.getElementById('chartSkeleton');
  if (skeleton) skeleton.style.display = 'none';
}

/**
 * Muat transaksi terbaru ke tabel mini
 */
async function loadRecentTransactions() {
  const data = await RAZ.api('api/RAZapiStores.php?action=recent_transactions');
  const tbody = document.getElementById('recentTransBody');
  if (!tbody) return;

  if (!data.success || !data.data.length) {
    tbody.innerHTML = `<tr><td colspan="4" class="dash-empty"><p>${window.DASH_LANG ? window.DASH_LANG.empty_trx : 'Belum ada transaksi'}</p></td></tr>`;
    return;
  }

  tbody.innerHTML = data.data.slice(0, 5).map(t => `
    <tr>
      <td><span class="raz-text-mono">${t.invoice_number}</span></td>
      <td>${t.cashier_name}</td>
      <td class="raz-text-rupiah">${RAZ.formatRupiah(t.grand_total)}</td>
      <td><span class="raz-badge ${t.status === 'completed' ? 'success' : 'danger'}">${t.status === 'completed' ? 'Sukses' : 'Void'}</span></td>
    </tr>
  `).join('');
}

/**
 * Muat item terlaris ke tabel mini
 */
async function loadTopItems() {
  const data = await RAZ.api('api/RAZapiStores.php?action=top_items');
  const tbody = document.getElementById('topItemsBody');
  if (!tbody) return;

  if (!data.success || !data.data.length) {
    tbody.innerHTML = `<tr><td colspan="3" class="dash-empty"><p>${window.DASH_LANG ? window.DASH_LANG.empty_data : 'Belum ada data'}</p></td></tr>`;
    return;
  }

  tbody.innerHTML = data.data.map((item, i) => `
    <tr>
      <td><span class="raz-badge primary">#${i + 1}</span> ${item.item_name}</td>
      <td>${item.total_qty}x</td>
      <td class="raz-text-rupiah">${RAZ.formatRupiah(item.total_sales)}</td>
    </tr>
  `).join('');
}

// ============================================================
// DASHBOARD SUPER ADMIN
// ============================================================
async function loadAdminDashboard() {
  const data = await RAZ.api('api/RAZapiStores.php?action=admin_stats');
  if (!data.success) return;

  const s = data.data;
  updateStat('statTotalStores', s.total_stores);
  updateStat('statTotalOwners', s.total_owners);
  updateStat('statTotalEmployees', s.total_employees);
  updateStat('statTotalUsers', s.total_users);

  // Render tenant list
  const list = document.getElementById('tenantList');
  if (list && s.recent_stores.length) {
    list.innerHTML = s.recent_stores.map(store => `
      <div class="dash-tenant-card">
        <div class="dash-tenant-avatar">${store.store_name.charAt(0).toUpperCase()}</div>
        <div class="dash-tenant-info">
          <div class="dash-tenant-name">${store.store_name}</div>
          <div class="dash-tenant-owner">Owner: ${store.owner_name}</div>
        </div>
      </div>
    `).join('');
  }
}


