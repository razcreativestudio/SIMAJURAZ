/**
 * RAZProfitShare.js — Logic Bagi Hasil (Profit Sharing) SIMAJURAZ
 * Versi: 1.1.0 | Diupdate: 2026-05-22
 * Deskripsi: CRUD penerima bagi hasil, kalkulasi distribusi,
 *            generate laporan, dan download PDF.
 */
'use strict';

// State bagi hasil
const PS = { period: 'month', shares: [], totalPct: 0, netProfit: 0, profitData: null };

// ========================
// INISIALISASI
// ========================
function initProfitShare() {
  // Event: klik chip periode
  document.querySelectorAll('.ps-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.ps-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      PS.period = chip.dataset.period;
      loadProfitShareData();
    });
  });
  loadProfitShareData();
  loadProfitReports();
}

// ========================
// LOAD DATA DISTRIBUSI
// ========================
async function loadProfitShareData() {
  const params = new URLSearchParams({ action: 'profit_share', period: PS.period });
  const data = await RAZ.api('api/RAZapiCashflow.php?' + params, { showError: false });
  if (!data || !data.success) return;

  const d = data.data;
  PS.profitData = d.profit;
  PS.netProfit = d.profit.net_profit;
  PS.shares = d.distribution;
  PS.totalPct = d.total_pct;

  // Update header keuntungan bersih
  const amtEl = document.getElementById('psNetAmount');
  if (amtEl) {
    amtEl.textContent = RAZ.formatRupiah(PS.netProfit);
    amtEl.className = 'ps-net-amount ' + (PS.netProfit >= 0 ? '' : 'negative');
  }
  document.getElementById('psRevenue').textContent = RAZ.formatRupiah(d.profit.total_revenue);
  document.getElementById('psCost').textContent = RAZ.formatRupiah(d.profit.total_cost);

  // Render daftar penerima
  renderSharesList();
  updateTotalBar();
}

// ========================
// RENDER DAFTAR PENERIMA
// ========================
function renderSharesList() {
  const container = document.getElementById('psSharesList');
  if (!container) return;

  if (!PS.shares.length) {
    container.innerHTML = `<div class="ps-empty"><i class="ph-bold ph-users-three"></i><p>${window.FIN_LANG ? window.FIN_LANG.empty_shares : 'Belum ada penerima bagi hasil.<br>Klik tombol di bawah untuk menambahkan.'}</p></div>`;
    document.getElementById('btnSaveShares').style.display = 'none';
    return;
  }

  // Warna berdasarkan role_label
  const colors = { kas_toko: '#4F46E5', owner: '#10B981', investor: '#F59E0B', bonus: '#EC4899', custom: '#6366F1' };

  let html = '';
  PS.shares.forEach((s, idx) => {
    const color = colors[s.role_label] || colors.custom;
    const labelMap = { kas_toko: 'Kas Toko', owner: 'Owner', investor: 'Investor', bonus: 'Bonus', custom: 'Lainnya' };
    const label = labelMap[s.role_label] || s.role_label;

    html += `
    <div class="ps-share-item" data-id="${s.id}" data-idx="${idx}">
      <div class="ps-share-color" style="background:${color}"></div>
      <div class="ps-share-info">
        <div class="ps-share-name">${s.name} <span class="ps-share-badge" style="background:${color}15;color:${color}">${label}</span></div>
        <div class="ps-share-bar-wrap">
          <div class="ps-share-bar" style="width:${s.percentage}%;background:${color}"></div>
        </div>
      </div>
      <div class="ps-share-pct">
        <input type="number" class="ps-pct-input" value="${s.percentage}" min="0" max="100" step="0.5" onchange="onPctChange(this, ${idx})" onfocus="this.select()">
        <span>%</span>
      </div>
      <div class="ps-share-amount">${RAZ.formatRupiah(s.amount)}</div>
      <button class="ps-share-delete" onclick="deleteShare(${s.id}, '${s.name.replace(/'/g, "\\'")}')" title="Hapus"><i class="ph-bold ph-trash"></i></button>
    </div>`;
  });

  container.innerHTML = html;
  document.getElementById('btnSaveShares').style.display = 'inline-flex';
}

// ========================
// UPDATE TOTAL BAR & VALIDASI
// ========================
function updateTotalBar() {
  const totalPct = PS.shares.reduce((sum, s) => sum + (parseFloat(s.percentage) || 0), 0);
  PS.totalPct = totalPct;
  const remaining = 100 - totalPct;

  document.getElementById('psTotalPct').textContent = totalPct.toFixed(1).replace(/\.0$/, '');
  document.getElementById('psRemaining').textContent = remaining.toFixed(1).replace(/\.0$/, '');

  const fill = document.getElementById('psTotalBarFill');
  if (fill) {
    fill.style.width = Math.min(totalPct, 100) + '%';
    fill.style.background = totalPct > 100 ? 'var(--raz-danger)' : totalPct === 100 ? 'var(--raz-success)' : 'var(--raz-primary)';
  }

  const status = document.getElementById('psTotalStatus');
  if (status) {
    if (totalPct === 100) {
      status.innerHTML = '<i class="ph-bold ph-check-circle"></i> Sempurna';
      status.className = 'ps-status-ok';
    } else if (totalPct > 100) {
      status.innerHTML = '<i class="ph-bold ph-warning"></i> Melebihi!';
      status.className = 'ps-status-over';
    } else {
      status.innerHTML = '<i class="ph-bold ph-check-circle"></i> Tersedia';
      status.className = 'ps-status-ok';
    }
  }

  const indicator = document.getElementById('psPctIndicator');
  if (indicator) indicator.className = 'ps-pct-indicator' + (totalPct > 100 ? ' over' : totalPct === 100 ? ' full' : '');
}

// ========================
// EVENT: UBAH PERSENTASE
// ========================
function onPctChange(input, idx) {
  const val = parseFloat(input.value) || 0;
  PS.shares[idx].percentage = val;
  PS.shares[idx].amount = Math.round(PS.netProfit * (val / 100));
  // Update amount display
  const item = input.closest('.ps-share-item');
  const amtEl = item.querySelector('.ps-share-amount');
  if (amtEl) amtEl.textContent = RAZ.formatRupiah(PS.shares[idx].amount);
  // Update bar
  const bar = item.querySelector('.ps-share-bar');
  if (bar) bar.style.width = val + '%';
  updateTotalBar();
}

// ========================
// TAMBAH PENERIMA
// ========================
function openAddShareModal() {
  document.getElementById('psName').value = '';
  document.getElementById('psRoleLabel').value = 'custom';
  document.getElementById('psPercentage').value = '';
  const avail = 100 - PS.totalPct;
  document.getElementById('psAvailPct').textContent = avail.toFixed(1).replace(/\.0$/, '');
  RAZ.openModal('addShareModal');
}

async function submitAddShare() {
  const name = document.getElementById('psName').value.trim();
  const roleLabel = document.getElementById('psRoleLabel').value;
  const percentage = parseFloat(document.getElementById('psPercentage').value) || 0;

  if (!name) { RAZ.error('Error', 'Nama penerima wajib diisi'); return; }
  if (percentage <= 0) { RAZ.error('Error', 'Persentase harus lebih dari 0'); return; }

  const btn = document.getElementById('btnAddShare');
  RAZ.btnLoading(btn, 'Menambah...');
  const res = await RAZ.api('api/RAZapiCashflow.php?action=profit_shares_add', {
    method: 'POST', body: { name, role_label: roleLabel, percentage }
  });
  RAZ.btnReset(btn);

  if (res.success) {
    RAZ.success('Berhasil', 'Penerima berhasil ditambahkan');
    RAZ.closeModal('addShareModal');
    loadProfitShareData();
  }
}

// ========================
// HAPUS PENERIMA
// ========================
async function deleteShare(id, name) {
  const ok = await RAZ.confirm({ title: 'Hapus Penerima?', message: 'Hapus "' + name + '" dari daftar bagi hasil?', confirmText: 'Ya, Hapus' });
  if (!ok) return;
  const res = await RAZ.api('api/RAZapiCashflow.php?action=profit_shares_delete', { method: 'POST', body: { id } });
  if (res.success) { RAZ.success('Dihapus', res.message); loadProfitShareData(); }
}

// ========================
// SIMPAN SEMUA PERSENTASE
// ========================
async function saveAllShares() {
  const sharesData = PS.shares.map(s => ({ id: s.id, percentage: parseFloat(s.percentage) || 0 }));
  const btn = document.getElementById('btnSaveShares');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const res = await RAZ.api('api/RAZapiCashflow.php?action=profit_shares_update', {
    method: 'POST', body: { shares: sharesData }
  });
  RAZ.btnReset(btn);
  if (res.success) { RAZ.success('Tersimpan', res.message); loadProfitShareData(); }
}

// ========================
// GENERATE LAPORAN
// ========================
function generateReport() {
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
  document.getElementById('rptDateFrom').value = firstDay.toISOString().split('T')[0];
  document.getElementById('rptDateTo').value = today.toISOString().split('T')[0];
  document.getElementById('rptNotes').value = '';
  RAZ.openModal('generateReportModal');
}

async function submitGenerateReport() {
  const dateFrom = document.getElementById('rptDateFrom').value;
  const dateTo = document.getElementById('rptDateTo').value;
  const notes = document.getElementById('rptNotes').value;
  if (!dateFrom || !dateTo) { RAZ.error('Error', 'Tanggal wajib diisi'); return; }

  const btn = document.getElementById('btnGenReport');
  RAZ.btnLoading(btn, 'Generating...');
  const res = await RAZ.api('api/RAZapiCashflow.php?action=profit_share_report', {
    method: 'POST', body: { date_from: dateFrom, date_to: dateTo, notes }
  });
  RAZ.btnReset(btn);

  if (res.success) {
    RAZ.success('Berhasil', 'Laporan berhasil di-generate');
    RAZ.closeModal('generateReportModal');
    loadProfitReports();
    // Langsung buka preview download
    setTimeout(() => downloadReport(res.data.data), 500);
  }
}

// ========================
// RIWAYAT LAPORAN
// ========================
async function loadProfitReports() {
  const data = await RAZ.api('api/RAZapiCashflow.php?action=profit_share_reports', { showError: false });
  if (!data || !data.success) return;

  const container = document.getElementById('psReportsList');
  if (!container) return;

  if (!data.data.length) {
    container.innerHTML = `<div class="ps-empty-sm"><i class="ph-bold ph-file-dashed"></i> ${window.FIN_LANG ? window.FIN_LANG.empty_report : 'Belum ada laporan'}</div>`;
    return;
  }

  container.innerHTML = data.data.map(r => {
    const dist = r.distribution || {};
    const items = (dist.distribution || []);
    const profitClass = (r.net_profit >= 0) ? 'profit' : 'loss';
    return `
    <div class="ps-report-item">
      <div class="ps-report-info">
        <div class="ps-report-period"><i class="ph-bold ph-calendar"></i> ${r.period_from} s/d ${r.period_to}</div>
        <div class="ps-report-meta">${r.notes || 'Tanpa catatan'} · Oleh: ${r.creator_name || '-'}</div>
      </div>
      <div class="ps-report-profit ${profitClass}">${RAZ.formatRupiah(r.net_profit)}</div>
      <button class="raz-btn raz-btn-secondary raz-btn-sm" onclick='downloadReport(${JSON.stringify(dist)})' title="Download PDF"><i class="ph-bold ph-download"></i></button>
    </div>`;
  }).join('');
}

// ========================
// DOWNLOAD PDF (via browser print)
// ========================
function downloadReport(reportData) {
  if (!reportData) return;
  const p = reportData.profit || {};
  const dist = reportData.distribution || [];
  const caps = reportData.capital || [];
  const spoils = reportData.spoilages || [];

  let rows = dist.map(d => {
    let deductHtml = d.deduction > 0 ? `<br><small style="color:#EF4444;font-size:10px;">- Pengeluaran: ${formatRp(d.deduction)}</small>` : '';
    let finalAmount = d.amount;
    return `<tr><td>${d.name}${deductHtml}</td><td style="text-align:center">${d.role_label}</td><td style="text-align:center">${d.percentage}%</td><td style="text-align:right"><strong>${formatRp(finalAmount)}</strong></td></tr>`;
  }).join('');

  let spoilTable = '';
  if (spoils.length > 0) {
      let spRows = spoils.map(s => `<tr><td>${s.item_name}</td><td>${s.qty}</td><td style="text-align:right">${formatRp(s.total_loss)}</td></tr>`).join('');
      spoilTable = `<h4 style="margin-top:20px;margin-bottom:8px;font-size:13px;color:#EF4444;">Daftar Kerugian Barang Rusak/Basi</h4>
      <table><thead><tr><th>Barang</th><th>Qty</th><th style="text-align:right">Kerugian</th></tr></thead><tbody>${spRows}</tbody></table>`;
  }

  const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Laporan Bagi Hasil</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box} body{font-family:'Segoe UI',sans-serif;padding:32px;color:#1a1a2e;font-size:12px;line-height:1.4}
  h1{font-size:18px;margin-bottom:4px;color:#4F46E5} .subtitle{color:#666;margin-bottom:20px;font-size:11px}
  
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
  .box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
  .box-title { font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; margin-bottom: 12px; font-weight: 700; }
  .row { display: flex; justify-content: space-between; margin-bottom: 6px; }
  .row.total { font-weight: 700; border-top: 1px solid #cbd5e1; padding-top: 6px; margin-top: 6px; font-size: 13px; }
  
  .text-green { color: #10B981; } .text-red { color: #EF4444; } .text-blue { color: #4F46E5; }
  
  table{width:100%;border-collapse:collapse;margin-top:16px} th,td{padding:8px 10px;border-bottom:1px solid #f1f5f9;text-align:left}
  th{background:#f8fafc;font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#64748b}
  .footer{margin-top:32px;text-align:center;font-size:10px;color:#94a3b8;border-top:1px solid #f1f5f9;padding-top:16px}
  @media print{body{padding:16px}}
</style></head><body>
<h1>📊 Laporan Akuntansi & Bagi Hasil</h1>
<div class="subtitle">SIMAJURAZ — Advanced Profit Sharing & Capital Report</div>

<div class="grid-2">
  <div class="box">
      <div class="box-title">Aliran Modal (Capital Flow)</div>
      <div class="row"><span>Total Modal Masuk</span><span class="text-green">${formatRp(p.capital_in)}</span></div>
      <div class="row"><span>Total Modal Ditarik</span><span class="text-red">-${formatRp(p.capital_out)}</span></div>
      <div class="row"><span>Pemakaian Modal HPP (Barang Terjual)</span><span class="text-red">-${formatRp(p.hpp)}</span></div>
      <div class="row"><span>Kerugian Barang Basi (Spoilage)</span><span class="text-red">-${formatRp(p.spoilages)}</span></div>
      <div class="row total"><span>Sisa Modal Aktual</span><span class="text-blue">${formatRp(p.remaining_capital)}</span></div>
  </div>

  <div class="box">
      <div class="box-title">Laba Rugi Bersih (Net Profit)</div>
      <div class="row"><span>Pendapatan Penjualan</span><span class="text-green">${formatRp(p.sales)}</span></div>
      <div class="row"><span>Pemasukan Lain</span><span class="text-green">${formatRp(p.other_income)}</span></div>
      <div class="row"><span>HPP (Harga Pokok)</span><span class="text-red">-${formatRp(p.hpp)}</span></div>
      <div class="row"><span>Pengeluaran Kas Umum</span><span class="text-red">-${formatRp(p.expense)}</span></div>
      <div class="row"><span>Kerugian Barang Basi</span><span class="text-red">-${formatRp(p.spoilages)}</span></div>
      <div class="row total"><span>Keuntungan Bersih</span><span class="text-blue">${formatRp(p.net_profit)}</span></div>
  </div>
</div>

<h3 style="margin-bottom:8px;font-size:14px;color:#1e293b;">Distribusi Bagi Hasil Final</h3>
<table><thead><tr><th>Penerima</th><th style="text-align:center">Peran</th><th style="text-align:center">Persentase</th><th style="text-align:right">Nominal Diterima</th></tr></thead><tbody>${rows}</tbody></table>

${spoilTable}

<div class="footer">Dicetak pada ${new Date().toLocaleString('id-ID')} — SIMAJURAZ Profit Sharing System</div>
</body></html>`;

  const win = window.open('', '_blank', 'width=800,height=600');
  win.document.write(html);
  win.document.close();
  setTimeout(() => win.print(), 400);
}

function formatRp(n) {
  if (!n && n !== 0) return 'Rp 0';
  return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}
