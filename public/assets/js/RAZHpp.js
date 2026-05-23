/**
 * RAZHpp.js — Logic Kalkulator HPP SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-22
 * Deskripsi: CRUD kalkulasi HPP, dynamic rows, real-time calculation,
 *            analisis harga jual, push ke inventori.
 */
'use strict';

// State aktif
const HPP = { activeId: null, data: null, dirty: false };

document.addEventListener('DOMContentLoaded', () => { loadHppList(); });

// ========================
// DAFTAR HPP
// ========================
async function loadHppList() {
  const data = await RAZ.api('api/RAZapiHpp.php?action=list', { showError: false });
  const container = document.getElementById('hppListItems');
  if (!data || !data.success || !container) return;

  if (!data.data.length) {
    container.innerHTML = `<div class="hpp-list-empty"><i class="ph-bold ph-calculator"></i>${window.FIN_LANG ? window.FIN_LANG.empty_hpp : 'Belum ada kalkulasi HPP.<br>Buat yang pertama!'}</div>`;
    return;
  }

  HPP.list = data.data;

  container.innerHTML = data.data.map(h => `
    <div class="hpp-list-item ${HPP.activeId == h.id ? 'active' : ''}" onclick="loadHppDetail(${h.id})">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <div class="hpp-list-item-name">${h.product_name}</div>
        <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" style="color:var(--raz-text-muted)" onclick="event.stopPropagation(); printSingleHpp(${h.id})" title="Cetak HPP ini"><i class="ph-bold ph-printer"></i></button>
      </div>
      <div class="hpp-list-item-hpp">HPP: <strong>${formatRp(h.hpp_total)}</strong>/porsi</div>
    </div>
  `).join('');
}

// ========================
// DETAIL HPP
// ========================
async function loadHppDetail(id) {
  HPP.activeId = id;
  HPP.dirty = false;
  const data = await RAZ.api('api/RAZapiHpp.php?action=detail&id=' + id);
  if (!data || !data.success) return;

  HPP.data = data.data;
  renderDetail();
  loadHppList(); // refresh active state
}

function renderDetail() {
  const d = HPP.data;
  const c = d.calc;
  const panel = document.getElementById('hppDetailPanel');
  if (!panel) return;

  // Analisis harga user
  let feedbackHtml = '';
  if (d.current_sell_price > 0 && c.hpp_total > 0) {
    if (c.actual_margin_pct >= c.margin_pct) {
      feedbackHtml = `<div class="hpp-feedback good"><i class="ph-bold ph-check-circle"></i> <strong>Bagus!</strong> Margin Anda ${c.actual_margin_pct}% lebih tinggi dari rekomendasi ${c.margin_pct}%. Profit per porsi: <strong>${formatRp(c.actual_profit)}</strong>.</div>`;
    } else if (c.actual_margin_pct > 0) {
      feedbackHtml = `<div class="hpp-feedback warn"><i class="ph-bold ph-warning"></i> Margin Anda ${c.actual_margin_pct}% lebih rendah dari rekomendasi ${c.margin_pct}%. Sebaiknya naikkan harga jual ke <strong>${formatRp(c.recommended_price)}</strong> atau lebih.</div>`;
    } else {
      feedbackHtml = `<div class="hpp-feedback warn"><i class="ph-bold ph-warning-circle"></i> <strong>Perhatian!</strong> Harga jual Anda di bawah HPP. Anda <strong>rugi ${formatRp(Math.abs(c.actual_profit))}</strong> per porsi!</div>`;
    }
  }

  panel.innerHTML = `
    <!-- Header Produk -->
    <div class="hpp-section">
      <div class="hpp-section-header">
        <div class="hpp-section-title"><i class="ph-bold ph-tag"></i> ${d.product_name}</div>
        <button class="raz-btn raz-btn-ghost raz-btn-sm" style="color:var(--raz-danger)" onclick="deleteHpp(${d.id},'${d.product_name.replace(/'/g, "\\'")}')"><i class="ph-bold ph-trash"></i></button>
      </div>
    </div>

    <!-- BAHAN BAKU -->
    <div class="hpp-section">
      <div class="hpp-section-header">
        <div class="hpp-section-title"><i class="ph-bold ph-cooking-pot"></i> Bahan Baku</div>
        <span style="font-size:0.78rem;color:var(--raz-primary);font-weight:700">Total: ${formatRp(c.ingredient_cost)}/porsi</span>
      </div>
      <div class="hpp-section-body">
        <div class="hpp-info-box">
          <h4><i class="ph-bold ph-info"></i> Panduan Bahan Baku</h4>
          <p>Bagian ini digunakan untuk menghitung modal bahan mentah/utama per satu buah/pcs produk.</p>
          <ul>
            <li><strong>Jml Beli & Satuan:</strong> Nominal pembelian utuh (Misal: Beli Beras 1 karung, atau Minyak 2 liter).</li>
            <li><strong>Harga Beli:</strong> Total uang yang dikeluarkan untuk pembelian tersebut (Misal: Rp 150.000).</li>
            <li><strong>Total Hasil (pcs):</strong> Dari 1 pembelian utuh tersebut, bisa menghasilkan berapa buah/pcs produk? (Sistem otomatis menghitung: Harga Beli ÷ Total Hasil).</li>
          </ul>
        </div>
        <div id="ingRows">${renderIngredientRows(d.ingredients)}</div>
        <button class="raz-btn raz-btn-secondary raz-btn-sm hpp-add-btn" onclick="addIngRow()"><i class="ph-bold ph-plus"></i> Tambah Bahan</button>
      </div>
    </div>

    <!-- PACKAGING -->
    <div class="hpp-section">
      <div class="hpp-section-header">
        <div class="hpp-section-title"><i class="ph-bold ph-package"></i> Packaging / Kemasan</div>
        <span style="font-size:0.78rem;color:var(--raz-primary);font-weight:700">Total: ${formatRp(c.packaging_cost)}/porsi</span>
      </div>
      <div class="hpp-section-body">
        <div class="hpp-info-box">
          <h4><i class="ph-bold ph-info"></i> Panduan Packaging</h4>
          <p>Menghitung modal wadah, kemasan, atau alat makan yang diberikan ke customer per produk.</p>
          <ul>
            <li><strong>Jml Beli & Satuan:</strong> Pembelian grosir (Misal: 1 pack, 1 slop, 1 dus).</li>
            <li><strong>Harga Beli:</strong> Harga untuk 1 pembelian grosir tersebut.</li>
            <li><strong>Isi (pcs):</strong> Dalam 1 pack grosir, berisi berapa buah kemasan satuan? (Untuk mencari harga satuan kemasan).</li>
            <li><strong>1 Pkg Muat (pcs):</strong> 1 buah kemasan satuan tersebut bisa digunakan untuk memuat berapa pcs produk? (Misal: 1 box muat 5 pcs kue).</li>
          </ul>
        </div>
        <div id="pkgRows">${renderPackagingRows(d.packagings)}</div>
        <button class="raz-btn raz-btn-secondary raz-btn-sm hpp-add-btn" onclick="addPkgRow()"><i class="ph-bold ph-plus"></i> Tambah Packaging</button>
      </div>
    </div>

    <!-- BIAYA TAMBAHAN -->
    <div class="hpp-section">
      <div class="hpp-section-header">
        <div class="hpp-section-title"><i class="ph-bold ph-lightning"></i> Biaya Tambahan Operasional</div>
        <span style="font-size:0.78rem;color:var(--raz-primary);font-weight:700">Total: ${formatRp(c.extra_cost)}/porsi</span>
      </div>
      <div class="hpp-section-body">
        <div class="hpp-info-box">
          <h4><i class="ph-bold ph-info"></i> Panduan Biaya Tambahan</h4>
          <p>Menghitung beban biaya operasional langsung yang dibutuhkan untuk memasak/membuat menu.</p>
          <ul>
            <li><strong>Estimasi Biaya:</strong> Pengeluaran seperti Gas LPG, Listrik, atau Upah masak harian.</li>
            <li><strong>Dibagi Porsi:</strong> 1 tabung gas / 1 hari kerja tersebut bisa digunakan untuk memasak berapa porsi total? Sistem akan membaginya rata per porsi.</li>
          </ul>
        </div>
        <div id="extRows">${renderExtraCostRows(d.extra_costs)}</div>
        <button class="raz-btn raz-btn-secondary raz-btn-sm hpp-add-btn" onclick="addExtRow()"><i class="ph-bold ph-plus"></i> Tambah Biaya</button>
      </div>
    </div>

    <!-- OVERHEAD -->
    <div class="hpp-section">
      <div class="hpp-section-header"><div class="hpp-section-title"><i class="ph-bold ph-percent"></i> Persentase Overhead</div></div>
      <div class="hpp-section-body">
        <div class="hpp-overhead">
          <div>
            <div class="hpp-overhead-input">
              <input type="number" id="hppOverhead" value="${d.overhead_pct}" min="0" max="100" step="0.5" onchange="HPP.dirty=true">
              <span style="font-weight:700;font-size:1rem">%</span>
            </div>
            <div style="font-size:0.82rem;color:var(--raz-primary);font-weight:700;margin-top:8px">= ${formatRp(c.overhead_cost)}/porsi</div>
          </div>
          <div class="hpp-overhead-info hpp-info-box" style="margin-bottom:0">
            <h4><i class="ph-bold ph-info"></i> Apa itu Overhead?</h4>
            <p>Overhead adalah dana cadangan (persentase dari total HPP) untuk menutupi biaya tak terduga, penyusutan alat, sewa tempat, internet, pajak, atau risiko bahan basi/rusak.</p>
            <strong>📌 Rekomendasi Overhead per Usaha:</strong>

            • Dagang/Perdagangan: <strong>10% - 20%</strong><br>
            • Jasa/Profesional: <strong>20% - 35%</strong><br>
            • Manufaktur/Produksi: <strong>15% - 25%</strong><br>
            • UMKM: <strong>15% - 30%</strong>
          </div>
        </div>
      </div>
    </div>

    <!-- RINGKASAN HPP -->
    <div class="hpp-summary-card">
      <h3><i class="ph-bold ph-calculator"></i> Ringkasan HPP per Porsi</h3>
      <div class="hpp-sum-row"><span class="hpp-sum-label">Bahan Baku</span><span>${formatRp(c.ingredient_cost)}</span></div>
      <div class="hpp-sum-row"><span class="hpp-sum-label">Packaging</span><span>${formatRp(c.packaging_cost)}</span></div>
      <div class="hpp-sum-row"><span class="hpp-sum-label">Biaya Tambahan</span><span>${formatRp(c.extra_cost)}</span></div>
      <div class="hpp-sum-row"><span class="hpp-sum-label">Subtotal</span><span>${formatRp(c.subtotal)}</span></div>
      <div class="hpp-sum-row"><span class="hpp-sum-label">Overhead (${c.overhead_pct}%)</span><span>${formatRp(c.overhead_cost)}</span></div>
      <div class="hpp-sum-row total"><span class="hpp-sum-label">HPP TOTAL</span><span>${formatRp(c.hpp_total)}</span></div>
    </div>

    <!-- HARGA JUAL -->
    <div class="hpp-section">
      <div class="hpp-section-header"><div class="hpp-section-title"><i class="ph-bold ph-tag"></i> Harga Jual & Margin Profit</div></div>
      <div class="hpp-section-body">
        <div class="hpp-info-box">
          <h4><i class="ph-bold ph-info"></i> Panduan Harga Jual</h4>
          <p>Bandingkan <strong>Harga Rekomendasi</strong> (berdasarkan target margin) dengan <strong>Harga Aktual</strong> yang Anda gunakan saat ini.</p>
          <ul>
            <li><strong>Margin Rekomendasi:</strong> Keuntungan bersih yang ingin didapat setelah dikurangi modal HPP.</li>
            <li>Jika harga aktual Anda di bawah rekomendasi, Anda mungkin perlu menaikkan harga jual agar bisnis tetap sehat.</li>
          </ul>
        </div>
        <div class="hpp-price-section">
          <div class="hpp-price-card recommended">
            <div class="hpp-price-label">Harga Rekomendasi</div>
            <div class="hpp-price-value">${formatRp(c.recommended_price)}</div>
            <div class="hpp-price-sub">Margin ${c.margin_pct}%</div>
            <div class="hpp-overhead-input" style="justify-content:center;margin-top:10px">
              <span style="font-size:0.78rem;color:var(--raz-text-muted)">Margin:</span>
              <input type="number" id="hppMargin" value="${d.margin_pct}" min="1" max="99" step="1" onchange="HPP.dirty=true" style="width:60px">
              <span style="font-size:0.82rem;font-weight:600">%</span>
            </div>
          </div>
          <div class="hpp-price-card user-price">
            <div class="hpp-price-label">Harga Jual Anda</div>
            <input type="number" class="hpp-price-input" id="hppSellPrice" value="${d.current_sell_price || ''}" placeholder="0" onchange="HPP.dirty=true">
            <div class="hpp-price-sub">${d.current_sell_price > 0 ? 'Margin aktual: ' + c.actual_margin_pct + '%' : 'Masukkan harga jual Anda'}</div>
          </div>
        </div>
        ${feedbackHtml}
        <div class="hpp-margin-info">
          <h4>📊 Saran Margin per Jenis Usaha:</h4>
          <div class="hpp-margin-tip"><span>Makanan & Minuman</span><span><strong>30% - 50%</strong></span></div>
          <div class="hpp-margin-tip"><span>Ritel / Kelontong</span><span><strong>15% - 30%</strong></span></div>
          <div class="hpp-margin-tip"><span>Fashion & Aksesoris</span><span><strong>50% - 100%</strong></span></div>
          <div class="hpp-margin-tip"><span>Jasa Profesional</span><span><strong>30% - 60%</strong></span></div>
          <div class="hpp-margin-tip"><span>Produk Digital</span><span><strong>70% - 90%</strong></span></div>
        </div>
      </div>
    </div>

    <!-- ACTION BAR -->
    <div class="hpp-action-bar">
      <button class="raz-btn raz-btn-primary" id="btnSaveHpp" onclick="saveHpp()"><i class="ph-bold ph-floppy-disk"></i> Simpan & Hitung Kalkulasi</button>
      <button class="raz-btn raz-btn-success" onclick="pushToInventory()"><i class="ph-bold ph-package"></i> Push ke Inventori</button>
    </div>
  `;
}

// ========================
// RENDER ROWS
// ========================
function renderIngredientRows(items) {
  if (!items || !items.length) return '';
  return items.map((r, i) => `
    <div class="hpp-row hpp-row-ing">
      <div><label>Nama Bahan</label><input type="text" data-field="name" value="${r.name || ''}"></div>
      <div><label>Jumlah Beli</label><input type="number" data-field="purchase_qty" value="${r.purchase_qty || 1}" min="0.01" step="0.01"></div>
      <div><label>Satuan</label><input type="text" data-field="purchase_unit" value="${r.purchase_unit || 'pcs'}" list="unitList"></div>
      <div><label>Harga Beli (Rp)</label><input type="number" data-field="purchase_price" value="${r.purchase_price || 0}" min="0"></div>
      <div><label>Total Hasil (pcs)</label><input type="number" data-field="portions_yield" value="${r.portions_yield || 1}" min="1"></div>
      <button class="hpp-row-del" onclick="this.closest('.hpp-row').remove();HPP.dirty=true"><i class="ph-bold ph-x"></i></button>
    </div>`).join('');
}

function renderPackagingRows(items) {
  if (!items || !items.length) return '';
  return items.map((r, i) => `
    <div class="hpp-row hpp-row-pkg">
      <div><label>Nama Packaging</label><input type="text" data-field="name" value="${r.name || ''}"></div>
      <div><label>Jml Beli</label><input type="number" data-field="purchase_qty" value="${r.purchase_qty || 1}" min="0.01" step="0.01"></div>
      <div><label>Satuan</label><input type="text" data-field="purchase_unit" value="${r.purchase_unit || 'pack'}"></div>
      <div><label>Harga Beli (Rp)</label><input type="number" data-field="purchase_price" value="${r.purchase_price || 0}" min="0"></div>
      <div><label>Isi (pcs)</label><input type="number" data-field="capacity_pcs" value="${r.capacity_pcs || 1}" min="1"></div>
      <div><label>1 Pkg Muat (pcs)</label><input type="number" data-field="usage_per_portion" value="${r.usage_per_portion || 1}" min="1"></div>
      <button class="hpp-row-del" onclick="this.closest('.hpp-row').remove();HPP.dirty=true"><i class="ph-bold ph-x"></i></button>
    </div>`).join('');
}

function renderExtraCostRows(items) {
  if (!items || !items.length) return '';
  return items.map((r, i) => `
    <div class="hpp-row hpp-row-ext">
      <div><label>Nama Biaya</label><input type="text" data-field="name" value="${r.name || ''}"></div>
      <div><label>Estimasi Biaya (Rp)</label><input type="number" data-field="amount" value="${r.amount || 0}" min="0"></div>
      <div><label>Dibagi Porsi</label><input type="number" data-field="portions_divide" value="${r.portions_divide || 1}" min="1"></div>
      <button class="hpp-row-del" onclick="this.closest('.hpp-row').remove();HPP.dirty=true"><i class="ph-bold ph-x"></i></button>
    </div>`).join('');
}

// ========================
// ADD ROWS
// ========================
function addIngRow() {
  const html = `<div class="hpp-row hpp-row-ing">
    <div><label>Nama Bahan</label><input type="text" data-field="name" value="" placeholder="Beras, Minyak..."></div>
    <div><label>Jumlah Beli</label><input type="number" data-field="purchase_qty" value="1" min="0.01" step="0.01"></div>
    <div><label>Satuan</label><input type="text" data-field="purchase_unit" value="pcs" list="unitList"></div>
    <div><label>Harga Beli (Rp)</label><input type="number" data-field="purchase_price" value="0" min="0"></div>
    <div><label>Dapat Porsi</label><input type="number" data-field="portions_yield" value="1" min="1"></div>
    <button class="hpp-row-del" onclick="this.closest('.hpp-row').remove();HPP.dirty=true"><i class="ph-bold ph-x"></i></button>
  </div>`;
  document.getElementById('ingRows').insertAdjacentHTML('beforeend', html);
  HPP.dirty = true;
}

function addPkgRow() {
  const html = `<div class="hpp-row hpp-row-pkg">
    <div><label>Nama Packaging</label><input type="text" data-field="name" value="" placeholder="Cup, Mika..."></div>
    <div><label>Jml Beli</label><input type="number" data-field="purchase_qty" value="1" min="0.01" step="0.01"></div>
    <div><label>Satuan</label><input type="text" data-field="purchase_unit" value="pack"></div>
    <div><label>Harga Beli (Rp)</label><input type="number" data-field="purchase_price" value="0" min="0"></div>
    <div><label>Isi (pcs)</label><input type="number" data-field="capacity_pcs" value="1" min="1"></div>
    <div><label>Pakai/Porsi</label><input type="number" data-field="usage_per_portion" value="1" min="1"></div>
    <button class="hpp-row-del" onclick="this.closest('.hpp-row').remove();HPP.dirty=true"><i class="ph-bold ph-x"></i></button>
  </div>`;
  document.getElementById('pkgRows').insertAdjacentHTML('beforeend', html);
  HPP.dirty = true;
}

function addExtRow() {
  const html = `<div class="hpp-row hpp-row-ext">
    <div><label>Nama Biaya</label><input type="text" data-field="name" value="" placeholder="Gas, Listrik..."></div>
    <div><label>Estimasi Biaya (Rp)</label><input type="number" data-field="amount" value="0" min="0"></div>
    <div><label>Dibagi Porsi</label><input type="number" data-field="portions_divide" value="1" min="1"></div>
    <button class="hpp-row-del" onclick="this.closest('.hpp-row').remove();HPP.dirty=true"><i class="ph-bold ph-x"></i></button>
  </div>`;
  document.getElementById('extRows').insertAdjacentHTML('beforeend', html);
  HPP.dirty = true;
}

// ========================
// COLLECT DATA FROM DOM
// ========================
function collectRows(containerId, fields) {
  const rows = document.querySelectorAll('#' + containerId + ' .hpp-row');
  const result = [];
  rows.forEach(row => {
    const obj = {};
    fields.forEach(f => { const el = row.querySelector(`[data-field="${f}"]`); if (el) obj[f] = el.value; });
    if (obj.name && obj.name.trim()) result.push(obj);
  });
  return result;
}

// ========================
// SIMPAN
// ========================
async function saveHpp() {
  if (!HPP.activeId) return;
  const body = {
    id: HPP.activeId,
    product_name: HPP.data.product_name,
    portions: HPP.data.portions || 1,
    overhead_pct: parseFloat(document.getElementById('hppOverhead')?.value || 15),
    margin_pct: parseFloat(document.getElementById('hppMargin')?.value || 30),
    current_sell_price: parseFloat(document.getElementById('hppSellPrice')?.value || 0),
    notes: HPP.data.notes || '',
    ingredients: collectRows('ingRows', ['name', 'purchase_qty', 'purchase_unit', 'purchase_price', 'portions_yield']),
    packagings: collectRows('pkgRows', ['name', 'purchase_qty', 'purchase_unit', 'purchase_price', 'capacity_pcs', 'usage_per_portion']),
    extra_costs: collectRows('extRows', ['name', 'amount', 'portions_divide']),
  };
  const btn = document.getElementById('btnSaveHpp');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const res = await RAZ.api('api/RAZapiHpp.php?action=update', { method: 'POST', body });
  RAZ.btnReset(btn);
  if (res.success) { RAZ.success('Tersimpan', res.message); HPP.dirty = false; loadHppDetail(HPP.activeId); }
}

// ========================
// BUAT BARU
// ========================
async function createNewHpp() {
  const name = document.getElementById('hppNewName')?.value?.trim();
  if (!name) { RAZ.error('Error', 'Nama produk/menu wajib diisi'); return; }
  const res = await RAZ.api('api/RAZapiHpp.php?action=create', { method: 'POST', body: { product_name: name } });
  if (res.success) {
    RAZ.success('Berhasil', 'Kalkulasi HPP baru dibuat');
    RAZ.closeModal('newHppModal');
    loadHppList();
    loadHppDetail(res.data.id);
  }
}

// ========================
// HAPUS
// ========================
async function deleteHpp(id, name) {
  const ok = await RAZ.confirm({ title: 'Hapus Kalkulasi?', message: `Hapus "${name}" beserta semua komponen?`, confirmText: 'Ya, Hapus' });
  if (!ok) return;
  const res = await RAZ.api('api/RAZapiHpp.php?action=delete', { method: 'POST', body: { id } });
  if (res.success) {
    RAZ.success('Dihapus', res.message);
    HPP.activeId = null;
    document.getElementById('hppDetailPanel').innerHTML = '<div class="hpp-detail-empty"><i class="ph-bold ph-calculator"></i><p>Pilih menu dari daftar kiri<br>atau buat kalkulasi baru</p></div>';
    loadHppList();
  }
}

// ========================
// PUSH KE INVENTORI
// ========================
async function pushToInventory() {
  if (!HPP.activeId) return;
  // Simpan dulu sebelum push
  await saveHpp();
  const ok = await RAZ.confirm({ title: 'Push ke Inventori?', message: 'Produk akan ditambahkan ke daftar barang inventori dengan HPP dan harga jual dari kalkulasi ini.', confirmText: 'Ya, Tambahkan' });
  if (!ok) return;
  const res = await RAZ.api('api/RAZapiHpp.php?action=push_inventory', { method: 'POST', body: { id: HPP.activeId } });
  if (res.success) RAZ.success('Berhasil', `Produk ditambahkan ke Inventori. HPP: ${formatRp(res.data.hpp)}, Harga Jual: ${formatRp(res.data.sell_price)}`);
}

// ========================
// FORMAT HELPER
// ========================
function formatRp(n) {
  if (!n && n !== 0) return 'Rp 0';
  return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

// ========================
// PRINT HPP REPORT
// ========================
function openPrintModal() {
    document.getElementById('printHppMode').value = 'all';
    togglePrintHppMode();
    RAZ.openModal('printHppModal');
}

function togglePrintHppMode() {
    const mode = document.getElementById('printHppMode').value;
    const selDiv = document.getElementById('printHppSelection');
    const chkDiv = document.getElementById('printHppCheckboxes');
    
    if (mode === 'selected') {
        selDiv.style.display = 'block';
        chkDiv.innerHTML = HPP.list.map(item => `
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 4px 8px; border-radius: 4px; background: rgba(0,0,0,0.02); border: 1px solid var(--raz-border);">
                <input type="checkbox" class="hpp-print-chk" value="${item.id}" checked style="width: 16px; height: 16px; accent-color: var(--raz-primary);">
                <span>${item.product_name}</span>
            </label>
        `).join('');
    } else {
        selDiv.style.display = 'none';
        chkDiv.innerHTML = '';
    }
}

function executePrintHpp() {
    const mode = document.getElementById('printHppMode').value;
    const showSuggest = document.getElementById('printHppShowSuggestion').checked ? 1 : 0;
    
    let ids = '';
    if (mode === 'selected') {
        const checkboxes = document.querySelectorAll('.hpp-print-chk:checked');
        if (checkboxes.length === 0) {
            RAZ.warning('Pilih Menu', 'Silakan centang minimal 1 menu untuk dicetak.');
            return;
        }
        const selectedIds = Array.from(checkboxes).map(c => c.value);
        ids = selectedIds.join(',');
    }
    
    RAZ.closeModal('printHppModal');
    
    // Buka tab baru untuk laporan
    const url = `RAZhppReport.php?mode=${mode}&ids=${ids}&suggest=${showSuggest}`;
    window.open(url, '_blank');
}

function printSingleHpp(id) {
    // Dipanggil langsung dari tombol card per item
    const url = `RAZhppReport.php?mode=single&ids=${id}&suggest=1`;
    window.open(url, '_blank');
}
