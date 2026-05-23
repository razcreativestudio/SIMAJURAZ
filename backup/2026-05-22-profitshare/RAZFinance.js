/**
 * RAZFinance.js — Logic Keuangan SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Arus kas, shift, ringkasan laba rugi, profit share.
 */
'use strict';

const FIN = { period: 'month', dateFrom: '', dateTo: '', cfPage: 1, typeFilter: '' };

document.addEventListener('DOMContentLoaded', () => {
  loadSummary();
  loadCashflows();
  loadCurrentShift();
  setupFinEvents();
});

function setupFinEvents() {
  // Tab switching
  document.querySelectorAll('.fin-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.fin-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.fin-tab-content').forEach(c => c.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.tab)?.classList.add('active');
    });
  });
  // Period chips
  document.querySelectorAll('.fin-period-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.fin-period-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      FIN.period = chip.dataset.period;
      FIN.dateFrom = ''; FIN.dateTo = '';
      loadSummary(); loadCashflows();
    });
  });
  // Cashflow type filter
  document.getElementById('cfTypeFilter')?.addEventListener('change', (e) => {
    FIN.typeFilter = e.target.value; FIN.cfPage = 1; loadCashflows();
  });
  // Profit share slider
  document.getElementById('shareSlider')?.addEventListener('input', (e) => {
    document.getElementById('sharePct').textContent = e.target.value + '%';
    loadProfitShare(e.target.value);
  });
}

// ========================
// RINGKASAN LABA RUGI
// ========================
async function loadSummary() {
  const params = new URLSearchParams({ action: 'summary', period: FIN.period, date_from: FIN.dateFrom, date_to: FIN.dateTo });
  const data = await RAZ.api(`api/RAZapiCashflow.php?${params}`);
  if (!data.success) return;
  const s = data.data;

  document.getElementById('finSales').textContent = RAZ.formatRupiah(s.sales_total);
  document.getElementById('finHpp').textContent = RAZ.formatRupiah(s.hpp_total);
  document.getElementById('finGross').textContent = RAZ.formatRupiah(s.gross_profit);
  document.getElementById('finExpense').textContent = RAZ.formatRupiah(s.expense_total);
  document.getElementById('finOtherIncome').textContent = RAZ.formatRupiah(s.other_income);

  const netEl = document.getElementById('finNet');
  netEl.textContent = RAZ.formatRupiah(s.net_profit);
  netEl.className = 'fin-value ' + (s.net_profit >= 0 ? 'profit' : 'loss');

  // Update date display
  const periodEl = document.getElementById('finPeriodRange');
  if (periodEl) periodEl.textContent = `${s.period.from} s/d ${s.period.to}`;

  // Load profit share
  loadProfitShare(document.getElementById('shareSlider')?.value || 50);
}

// ========================
// DAFTAR ARUS KAS
// ========================
async function loadCashflows() {
  const params = new URLSearchParams({
    action: 'list', page: FIN.cfPage, type: FIN.typeFilter,
    date_from: FIN.dateFrom || undefined, date_to: FIN.dateTo || undefined,
  });
  const data = await RAZ.api(`api/RAZapiCashflow.php?${params}`);
  if (!data.success) return;

  const tbody = document.getElementById('cfBody');
  if (!tbody) return;

  if (!data.data.cashflows.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="raz-table-empty"><div class="empty-icon"><i class="ph-bold ph-wallet"></i></div><div class="empty-title">Belum Ada Arus Kas</div><div class="empty-desc">Klik "Catat Kas" untuk menambah data.</div></div></td></tr>`;
    return;
  }

  tbody.innerHTML = data.data.cashflows.map(cf => `
    <tr>
      <td>${cf.created_at}</td>
      <td><span class="cf-type-badge ${cf.type}"><i class="ph-bold ph-${cf.type === 'income' ? 'arrow-down-left' : 'arrow-up-right'}"></i> ${cf.type === 'income' ? 'Masuk' : 'Keluar'}</span></td>
      <td>${cf.category}</td>
      <td class="raz-text-rupiah" style="color:var(--raz-${cf.type === 'income' ? 'success' : 'danger'})">${cf.type === 'income' ? '+' : '-'}${RAZ.formatRupiah(cf.amount)}</td>
      <td>${cf.description || '-'}</td>
      <td class="col-action"><button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="deleteCashflow(${cf.id})" style="color:var(--raz-danger)" data-tooltip="Hapus"><i class="ph-bold ph-trash"></i></button></td>
    </tr>
  `).join('');

  // Pagination
  const pgInfo = document.getElementById('cfPgInfo');
  if (pgInfo) pgInfo.textContent = `Hal ${data.data.page}/${data.data.pages} (${data.data.total} data)`;

  // Summary badges
  document.getElementById('cfSumIncome').textContent = RAZ.formatRupiah(data.data.summary.income);
  document.getElementById('cfSumExpense').textContent = RAZ.formatRupiah(data.data.summary.expense);
}

async function deleteCashflow(id) {
  const ok = await RAZ.confirm({ title: 'Hapus Arus Kas?', message: 'Data ini akan dihapus permanen.', confirmText: 'Ya, Hapus' });
  if (!ok) return;
  const res = await RAZ.api('api/RAZapiCashflow.php?action=delete', { method: 'POST', body: { id } });
  if (res.success) { RAZ.success('Dihapus', res.message); loadCashflows(); loadSummary(); }
}

// ========================
// TAMBAH ARUS KAS
// ========================
function openAddCashflow(type) {
  document.getElementById('cfType').value = type;
  document.getElementById('cfFormTitle').textContent = type === 'income' ? 'Catat Pemasukan' : 'Catat Pengeluaran';
  document.getElementById('cfAmount').value = '';
  document.getElementById('cfCategory').value = '';
  document.getElementById('cfDesc').value = '';
  RAZ.openModal('cfModal');
}

async function saveCashflow() {
  const type = document.getElementById('cfType').value;
  const category = document.getElementById('cfCategory').value.trim();
  const amount = parseFloat(document.getElementById('cfAmount').value) || 0;
  const description = document.getElementById('cfDesc').value.trim();

  if (!category) { RAZ.error('Error', 'Kategori wajib diisi'); return; }
  if (amount <= 0) { RAZ.error('Error', 'Nominal harus lebih dari 0'); return; }

  const btn = document.getElementById('btnSaveCf');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const res = await RAZ.api('api/RAZapiCashflow.php?action=create', { method: 'POST', body: { type, category, amount, description } });
  RAZ.btnReset(btn);
  if (res.success) { RAZ.success('Berhasil', res.message); RAZ.closeModal('cfModal'); loadCashflows(); loadSummary(); }
}

// ========================
// SHIFT KAS
// ========================
async function loadCurrentShift() {
  const data = await RAZ.api('api/RAZapiCashflow.php?action=current_shift');
  const el = document.getElementById('shiftStatus');
  if (!el) return;
  if (data.data) {
    el.innerHTML = `<div class="fin-shift-card"><div class="fin-shift-icon"><i class="ph-bold ph-clock-clockwise"></i></div><div class="fin-shift-info"><div class="fin-shift-status">✅ Shift Aktif</div><div class="fin-shift-detail">Dibuka: ${data.data.opened_at} · Modal: ${RAZ.formatRupiah(data.data.opening_cash)}</div></div><button class="raz-btn raz-btn-warning" onclick="openCloseShift()"><i class="ph-bold ph-lock"></i> Tutup Shift</button></div>`;
  } else {
    el.innerHTML = `<div class="fin-shift-card" style="background:var(--raz-bg)"><div class="fin-shift-icon" style="color:var(--raz-text-muted)"><i class="ph-bold ph-clock"></i></div><div class="fin-shift-info"><div class="fin-shift-status" style="color:var(--raz-text-muted)">Tidak Ada Shift Aktif</div><div class="fin-shift-detail">Buka shift untuk mulai bekerja.</div></div><button class="raz-btn raz-btn-primary" onclick="openOpenShift()"><i class="ph-bold ph-play"></i> Buka Shift</button></div>`;
  }
}

function openOpenShift() { document.getElementById('shiftOpenCash').value = ''; RAZ.openModal('openShiftModal'); }
async function submitOpenShift() {
  const cash = parseFloat(document.getElementById('shiftOpenCash').value) || 0;
  const res = await RAZ.api('api/RAZapiCashflow.php?action=open_shift', { method: 'POST', body: { opening_cash: cash } });
  if (res.success) { RAZ.success('Shift Dibuka', ''); RAZ.closeModal('openShiftModal'); loadCurrentShift(); }
}

function openCloseShift() { document.getElementById('shiftCloseCash').value = ''; document.getElementById('shiftNotes').value = ''; RAZ.openModal('closeShiftModal'); }
async function submitCloseShift() {
  const cash = parseFloat(document.getElementById('shiftCloseCash').value) || 0;
  const notes = document.getElementById('shiftNotes').value;
  const res = await RAZ.api('api/RAZapiCashflow.php?action=close_shift', { method: 'POST', body: { closing_cash: cash, notes } });
  if (res.success) { RAZ.success('Shift Ditutup', ''); RAZ.closeModal('closeShiftModal'); loadCurrentShift(); }
}

// ========================
// PROFIT SHARE
// ========================
async function loadProfitShare(pct) {
  const params = new URLSearchParams({ action: 'profit_share', percentage: pct, date_from: FIN.dateFrom, date_to: FIN.dateTo });
  const data = await RAZ.api(`api/RAZapiCashflow.php?${params}`, { showError: false });
  if (!data.success) return;
  document.getElementById('shareAmount')?.textContent && (document.getElementById('shareAmount').textContent = RAZ.formatRupiah(data.data.share_amount));
  document.getElementById('shareNet')?.textContent && (document.getElementById('shareNet').textContent = `Laba Bersih: ${RAZ.formatRupiah(data.data.net_profit)}`);
}
