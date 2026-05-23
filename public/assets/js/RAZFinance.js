/**
 * RAZFinance.js â€” Logic Keuangan SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Arus kas, shift, ringkasan laba rugi, profit share.
 */
'use strict';

const FIN = { period: 'month', dateFrom: '', dateTo: '', cfPage: 1, typeFilter: '' };

document.addEventListener('DOMContentLoaded', () => {
  loadSummary();
  loadCashflows();
  loadCapitalFlows();
  loadSpoilages();
  loadAdditionalExpenses();
  loadCurrentShift();
  setupFinEvents();
  // Inisialisasi modul bagi hasil (dari RAZProfitShare.js)
  if (typeof initProfitShare === 'function') initProfitShare();
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
  // Period chips (tab ringkasan)
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
  document.getElementById('finOtherIncome').textContent = RAZ.formatRupiah(s.other_income);
  document.getElementById('finExpense').textContent = RAZ.formatRupiah(data.data.expense_total);
  document.getElementById('finNet').textContent = RAZ.formatRupiah(data.data.net_profit);
  
  if(document.getElementById('finSpoilage')) document.getElementById('finSpoilage').textContent = RAZ.formatRupiah(data.data.spoilages || 0);
  if(document.getElementById('finCapIn')) document.getElementById('finCapIn').textContent = RAZ.formatRupiah(data.data.capital_in || 0);
  if(document.getElementById('finCapRemain')) document.getElementById('finCapRemain').textContent = RAZ.formatRupiah(data.data.capital_remain || 0);

  const netEl = document.getElementById('finNet');
  netEl.className = 'fin-value ' + (s.net_profit >= 0 ? 'profit' : 'loss');

  // Update date display
  const periodEl = document.getElementById('finPeriodRange');
  if (periodEl) periodEl.textContent = `${s.period.from} s/d ${s.period.to}`;
}

// ========================
// DAFTAR ARUS KAS
// ========================
async function loadCashflows() {
  const params = new URLSearchParams({
    action: 'list', page: FIN.cfPage, type: FIN.typeFilter
  });
  if (FIN.dateFrom) params.append('date_from', FIN.dateFrom);
  if (FIN.dateTo) params.append('date_to', FIN.dateTo);
  const data = await RAZ.api(`api/RAZapiCashflow.php?${params}`);
  if (!data.success) return;

  const tbody = document.getElementById('cfBody');
  if (!tbody) return;

  if (!data.data.cashflows.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="raz-table-empty"><div class="empty-icon"><i class="ph-bold ph-wallet"></i></div><div class="empty-title">${window.FIN_LANG ? window.FIN_LANG.empty_cf_title : 'Belum Ada Arus Kas'}</div><div class="empty-desc">${window.FIN_LANG ? window.FIN_LANG.empty_cf_desc : 'Klik "Catat Kas" untuk menambah data.'}</div></div></td></tr>`;
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
function openAddCashflow(type) { document.getElementById('cfId').value = '';
  document.getElementById('cfType').value = type;
  document.getElementById('cfFormTitle').textContent = type === 'income' ? 'Catat Pemasukan' : 'Catat Pengeluaran';
  document.getElementById('cfAmount').value = '';
  document.getElementById('cfCategory').value = '';
  document.getElementById('cfDesc').value = '';

  const deductWrapper = document.getElementById('cfDeductWrapper');
  if (deductWrapper) {
      if (type === 'expense') {
          deductWrapper.style.display = 'block';
          loadProfitShareOptions();
      } else {
          deductWrapper.style.display = 'none';
      }
  }

  RAZ.openModal('cfModal');
}

async function loadProfitShareOptions() {
  const data = await RAZ.api('api/RAZapiCashflow.php?action=profit_shares_list');
  const select = document.getElementById('cfDeductShare');
  if (!data || !data.success || !select) return;

  select.innerHTML = '<option value="">-- Potong dari Kas Umum (Memengaruhi Laba Bersih) --</option>' + 
      data.data.filter(s => s.is_active == 1).map(s => `<option value="${s.id}">Potong jatah: ${s.name}</option>`).join('');
}

async function saveCashflow() {
  const type = document.getElementById('cfType').value;
  const category = document.getElementById('cfCategory').value.trim();
  const amount = parseFloat(document.getElementById('cfAmount').value) || 0;
  const description = document.getElementById('cfDesc').value.trim();
  const deduct_from_share_id = type === 'expense' ? document.getElementById('cfDeductShare').value : null;

  if (!category) { RAZ.error('Error', 'Kategori wajib diisi'); return; }
  if (amount <= 0) { RAZ.error('Error', 'Nominal harus lebih dari 0'); return; }

  const btn = document.getElementById('btnSaveCf');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const res = await RAZ.api('api/RAZapiCashflow.php?action=create', { method: 'POST', body: { type, category, amount, description, deduct_from_share_id } });
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
    el.innerHTML = `<div class="fin-shift-card"><div class="fin-shift-icon"><i class="ph-bold ph-clock-clockwise"></i></div><div class="fin-shift-info"><div class="fin-shift-status">âœ… Shift Aktif</div><div class="fin-shift-detail">Dibuka: ${data.data.opened_at} Â· Modal: ${RAZ.formatRupiah(data.data.opening_cash)}</div></div><button class="raz-btn raz-btn-warning" onclick="openCloseShift()"><i class="ph-bold ph-lock"></i> Tutup Shift</button></div>`;
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


// ============================================================
// CAPITAL FLOWS (MODAL AWAL)
// ============================================================
async function loadCapitalFlows() {
    const data = await RAZ.api('api/RAZapiCashflow.php?action=capital_list');
    const tbody = document.getElementById('capBody');
    if (!data || !data.success || !tbody) return;

    if (data.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" class="raz-text-center">${window.FIN_LANG ? window.FIN_LANG.empty_cap : 'Belum ada riwayat modal awal'}</td></tr>`;
        return;
    }

    tbody.innerHTML = data.data.map(c => `
        <tr>
            <td>
                <div style="font-size:0.75rem;color:var(--raz-text-muted);">${c.created_at.substring(0,16)}</div>
                <div style="margin:2px 0;"><strong>${c.source_name}</strong> <span class="raz-badge ${c.type === 'in' ? 'success' : 'danger'}" style="font-size:0.65rem;padding:2px 6px;">${c.type === 'in' ? 'Masuk' : 'Ditarik'}</span></div>
                ${c.notes ? `<div style="font-size:0.75rem;color:var(--raz-text-muted);">${c.notes}</div>` : ''}
            </td>
            <td style="text-align:right; vertical-align:middle;">
                <strong style="color:var(--raz-${c.type === 'in' ? 'success' : 'danger'}); font-size:0.95rem;">
                    ${c.type === 'in' ? '+' : '-'}${RAZ.formatRupiah(c.amount)}
                </strong>
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:5px; font-size:0.7rem;color:var(--raz-text-muted); margin-top:2px;">
                    <span><i class="ph-bold ph-user"></i> ${c.creator_name || '-'}</span>
                    <button class="raz-btn-icon-only text-primary" onclick="editCapitalFlow(${c.id}, '${c.type}', '${c.source_name.replace(/'/g, "\\'")}', ${c.amount}, '${(c.notes||'').replace(/'/g, "\\'")}')" style="background:none;border:none;padding:0;cursor:pointer;"><i class="ph-bold ph-pencil-simple"></i></button>
                    <button class="raz-btn-icon-only text-danger" onclick="deleteCapitalFlow(${c.id})" style="background:none;border:none;padding:0;cursor:pointer;"><i class="ph-bold ph-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openAddCapitalModal(type) { document.getElementById('capId').value = '';
    document.getElementById('capType').value = type;
    document.getElementById('capFormTitle').innerText = type === 'in' ? 'Tambah Modal Masuk' : 'Tarik Modal Keluar';
    document.getElementById('capSource').value = '';
    document.getElementById('capAmount').value = '';
    document.getElementById('capNotes').value = '';
    RAZ.openModal('addCapitalModal');
}

async function saveCapital() {
    const type = document.getElementById('capType').value;
    const source = document.getElementById('capSource').value.trim();
    const amount = document.getElementById('capAmount').value;
    const notes = document.getElementById('capNotes').value.trim();

    if (!source) return RAZ.error('Error', 'Sumber / Keterangan wajib diisi');
    if (!amount || amount <= 0) return RAZ.error('Error', 'Nominal tidak valid');

    const btn = document.getElementById('btnSaveCap');
    RAZ.btnLoading(btn);
    const res = await RAZ.api('api/RAZapiCashflow.php?action=capital_add', {
        method: 'POST', body: { type, source_name: source, amount, notes }
    });
    RAZ.btnReset(btn);

    if (res.success) {
        RAZ.success('Berhasil', res.message);
        RAZ.closeModal('addCapitalModal');
        loadCapitalFlows();
        loadSummary(); // Refresh summary values
    }
}

// ============================================================
// SPOILAGES (BARANG RUSAK)
// ============================================================
async function loadSpoilages() {
    const data = await RAZ.api('api/RAZapiCashflow.php?action=spoilage_list');
    const tbody = document.getElementById('spoilBody');
    if (!data || !data.success || !tbody) return;

    if (data.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" class="raz-text-center">${window.FIN_LANG ? window.FIN_LANG.empty_spoil : 'Belum ada laporan barang rusak'}</td></tr>`;
        return;
    }

    tbody.innerHTML = data.data.map(s => `
        <tr>
            <td>
                <div style="font-size:0.75rem;color:var(--raz-text-muted);">${s.created_at.substring(0,16)}</div>
                <div style="margin:2px 0;"><strong>${s.item_name}</strong></div>
                <div style="font-size:0.75rem;color:var(--raz-text-muted);">${s.qty}x @ ${RAZ.formatRupiah(s.hpp_value)}</div>
                ${s.notes ? `<div style="font-size:0.75rem;color:var(--raz-text-muted); margin-top:2px;">Keterangan: ${s.notes}</div>` : ''}
            </td>
            <td style="text-align:right; vertical-align:middle;">
                <strong style="color:var(--raz-danger); font-size:0.95rem;">
                    ${RAZ.formatRupiah(s.total_loss)}
                </strong>
                <div style="text-align:right; margin-top:2px;">
                    <button class="raz-btn-icon-only text-primary" onclick="editSpoilage(${s.id}, ${s.item_id}, ${s.qty}, '${(s.notes||'').replace(/'/g, "\\'")}')" style="background:none;border:none;padding:0;cursor:pointer;"><i class="ph-bold ph-pencil-simple"></i></button>
                    <button class="raz-btn-icon-only text-danger" onclick="deleteSpoilage(${s.id})" style="background:none;border:none;padding:0;cursor:pointer;"><i class="ph-bold ph-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function loadAdditionalExpenses() {
    const data = await RAZ.api('api/RAZapiCashflow.php?action=additional_expense_list');
    const tbody = document.getElementById('addExpenseBody');
    if (!data || !data.success || !tbody) return;

    if (data.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" class="raz-text-center">${window.FIN_LANG ? window.FIN_LANG.empty_exp : 'Belum ada pengeluaran tambahan'}</td></tr>`;
        return;
    }

    tbody.innerHTML = data.data.map(e => `
        <tr>
            <td>
                <div style="font-size:0.75rem;color:var(--raz-text-muted);">${e.created_at.substring(0,16)}</div>
                <div style="margin:2px 0;"><strong>${e.category}</strong></div>
                <div style="font-size:0.75rem;color:var(--raz-danger);"><i class="ph-bold ph-minus-circle"></i> Potong: ${e.share_name || 'Tidak Diketahui'}</div>
                ${e.description ? `<div style="font-size:0.75rem;color:var(--raz-text-muted); margin-top:2px;">${e.description}</div>` : ''}
            </td>
            <td style="text-align:right; vertical-align:middle;">
                <strong style="color:var(--raz-danger); font-size:0.95rem;">
                    -${RAZ.formatRupiah(e.amount)}
                </strong>
                <div style="text-align:right; margin-top:2px;">
                    <button class="raz-btn-icon-only text-primary" onclick="editAdditionalExpense(${e.id}, ${e.amount}, '${e.category.replace(/'/g, "\\'")}', '${(e.description||'').replace(/'/g, "\\'")}', ${e.deduct_from_share_id})" style="background:none;border:none;padding:0;cursor:pointer;"><i class="ph-bold ph-pencil-simple"></i></button>
                    <button class="raz-btn-icon-only text-danger" onclick="deleteAdditionalExpense(${e.id})" style="background:none;border:none;padding:0;cursor:pointer;"><i class="ph-bold ph-trash"></i></button>
                </div>
            </td>
        </tr>
    `).join('');
}

function openAddSpoilageModal() { document.getElementById('spoilId').value = '';
    document.getElementById('spoilQty').value = '';
    document.getElementById('spoilNotes').value = '';
    loadSpoilageItems();
    RAZ.openModal('addSpoilageModal');
}

async function loadSpoilageItems() {
    const data = await RAZ.api('api/RAZapiItems.php?action=list');
    const select = document.getElementById('spoilItem');
    if (!data || !data.success || !select) return;

    select.innerHTML = '<option value="">-- Pilih Barang --</option>' + 
        data.data.items.map(i => `<option value="${i.id}">${i.name} (Stok: ${i.stock}) - HPP: ${RAZ.formatRupiah(i.hpp)}</option>`).join('');
}

async function saveSpoilage() {
    const item_id = document.getElementById('spoilItem').value;
    const qty = document.getElementById('spoilQty').value;
    const notes = document.getElementById('spoilNotes').value.trim();

    if (!item_id) return RAZ.error('Error', 'Pilih barang terlebih dahulu');
    if (!qty || qty <= 0) return RAZ.error('Error', 'Jumlah (Qty) tidak valid');

    const ok = await RAZ.confirm({ title: 'Konfirmasi Input', message: 'Jumlah stok inventori akan dikurangi dan kerugian akan dicatat. Lanjutkan?', confirmText: 'Ya, Lanjutkan' });
    if (!ok) return;

    const btn = document.getElementById('btnSaveSpoil');
    RAZ.btnLoading(btn);
    const res = await RAZ.api('api/RAZapiCashflow.php?action=spoilage_add', {
        method: 'POST', body: { item_id, qty, notes }
    });
    RAZ.btnReset(btn);

    if (res.success) {
        RAZ.success('Berhasil', res.message);
        RAZ.closeModal('addSpoilageModal');
        loadSpoilages();
        loadSummary(); // Refresh laba rugi
    }
}

// Deletion Logic
async function deleteCapitalFlow(id) {
    const ok = await RAZ.confirm({ title: 'Hapus Modal', message: 'Apakah Anda yakin ingin menghapus data modal ini?', confirmText: 'Ya, Hapus' });
    if (!ok) return;
    const res = await RAZ.api('api/RAZapiCashflow.php?action=capital_delete', { method: 'POST', body: { id } });
    if (res && res.success) {
        RAZ.success('Berhasil', res.message);
        loadCapitalFlows();
        loadProfitShareData();
    }
}

async function deleteSpoilage(id) {
    const ok = await RAZ.confirm({ title: 'Hapus Barang Rusak', message: 'Apakah Anda yakin ingin menghapus data ini? Stok barang akan dikembalikan.', confirmText: 'Ya, Hapus' });
    if (!ok) return;
    const res = await RAZ.api('api/RAZapiCashflow.php?action=spoilage_delete', { method: 'POST', body: { id } });
    if (res && res.success) {
        RAZ.success('Berhasil', res.message);
        loadSpoilages();
        loadProfitShareData();
    }
}

async function deleteAdditionalExpense(id) {
    const ok = await RAZ.confirm({ title: 'Hapus Pengeluaran', message: 'Apakah Anda yakin ingin menghapus pengeluaran ini?', confirmText: 'Ya, Hapus' });
    if (!ok) return;
    const res = await RAZ.api('api/RAZapiCashflow.php?action=delete', { method: 'POST', body: { id } });
    if (res && res.success) {
        RAZ.success('Berhasil', res.message);
        loadAdditionalExpenses();
        loadCashflows();
        loadProfitShareData();
    }
}

function editCapitalFlow(id, type, source_name, amount, notes) {
    document.getElementById('capId').value = id;
    document.getElementById('capType').value = type;
    document.getElementById('capFormTitle').textContent = 'Edit Modal';
    document.getElementById('capSource').value = source_name;
    document.getElementById('capAmount').value = amount;
    document.getElementById('capNotes').value = notes;
    RAZ.openModal('addCapitalModal');
}

function editSpoilage(id, item_id, qty, notes) {
    document.getElementById('spoilId').value = id;
    document.getElementById('spoilItem').value = item_id;
    document.getElementById('spoilQty').value = qty;
    document.getElementById('spoilNotes').value = notes;
    RAZ.openModal('addSpoilageModal');
}

function editAdditionalExpense(id, amount, category, description, share_id) {
    document.getElementById('cfId').value = id;
    document.getElementById('cfType').value = 'expense';
    document.getElementById('cfFormTitle').textContent = 'Edit Pengeluaran';
    document.getElementById('cfAmount').value = amount;
    document.getElementById('cfCategory').value = category;
    document.getElementById('cfDesc').value = description;
    
    const deductWrapper = document.getElementById('cfDeductWrapper');
    if (deductWrapper) {
        deductWrapper.style.display = 'block';
        loadProfitShareOptions().then(() => {
            document.getElementById('cfDeductShare').value = share_id;
        });
    }
    
    RAZ.openModal('cfModal');
}
