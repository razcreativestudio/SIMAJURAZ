/**
 * RAZInventory.js — Logic Inventori SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Mengelola CRUD barang dan kategori via API.
 *            Tab switching, search, filter, pagination, modal form.
 */
'use strict';

// State inventori
const INV = {
  items: [], categories: [],
  page: 1, perPage: 10, totalPages: 1,
  search: '', categoryFilter: '',
};

document.addEventListener('DOMContentLoaded', () => {
  loadCategories();
  loadItems();
  setupEventListeners();
});

// ========================
// EVENT LISTENERS
// ========================
function setupEventListeners() {
  // Tab switching
  document.querySelectorAll('.inv-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.inv-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.inv-tab-content').forEach(c => c.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.tab)?.classList.add('active');
    });
  });

  // Search dengan debounce
  const searchInput = document.getElementById('itemSearch');
  if (searchInput) {
    searchInput.addEventListener('input', RAZ.debounce((e) => {
      INV.search = e.target.value;
      INV.page = 1;
      loadItems();
    }, 400));
  }

  // Filter kategori
  const catFilter = document.getElementById('catFilter');
  if (catFilter) {
    catFilter.addEventListener('change', (e) => {
      INV.categoryFilter = e.target.value;
      INV.page = 1;
      loadItems();
    });
  }

  // Kalkulasi margin di form
  const hppInput = document.getElementById('itemHpp');
  const priceInput = document.getElementById('itemSellPrice');
  if (hppInput && priceInput) {
    const calcMargin = () => {
      const hpp = parseFloat(hppInput.value) || 0;
      const sell = parseFloat(priceInput.value) || 0;
      const marginEl = document.getElementById('marginInfo');
      if (marginEl && hpp > 0) {
        const margin = sell - hpp;
        const pct = ((margin / hpp) * 100).toFixed(1);
        marginEl.textContent = `Margin: ${RAZ.formatRupiah(margin)} (${pct}%)`;
        marginEl.className = margin >= 0 ? 'inv-margin-info' : 'inv-margin-info negative';
        marginEl.style.display = 'flex';
      }
    };
    hppInput.addEventListener('input', calcMargin);
    priceInput.addEventListener('input', calcMargin);
  }

  // Submit form barang
  document.getElementById('itemForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    await saveItem();
  });
}

// ========================
// LOAD ITEMS
// ========================
async function loadItems() {
  const params = new URLSearchParams({
    action: 'list', page: INV.page, per_page: INV.perPage,
    search: INV.search, category: INV.categoryFilter,
  });
  const data = await RAZ.api(`api/RAZapiItems.php?${params}`);
  if (!data.success) return;

  INV.items = data.data.items;
  INV.totalPages = data.data.pages;

  renderItemsTable();
  renderPagination(data.data);
}

// ========================
// RENDER TABEL BARANG
// ========================
function renderItemsTable() {
  const tbody = document.getElementById('itemsBody');
  if (!tbody) return;

  if (!INV.items.length) {
    tbody.innerHTML = `<tr><td colspan="7">
      <div class="raz-table-empty">
        <div class="empty-icon"><i class="ph-bold ph-package"></i></div>
        <div class="empty-title">Belum Ada Barang</div>
        <div class="empty-desc">Klik tombol "Tambah Barang" untuk memulai.</div>
      </div>
    </td></tr>`;
    return;
  }

  tbody.innerHTML = INV.items.map(item => {
    // Status stok
    let stockClass = 'stock-ok', stockText = item.stock;
    if (item.stock <= 0) { stockClass = 'stock-out'; stockText = 'Habis'; }
    else if (item.stock <= item.min_stock) { stockClass = 'stock-low'; stockText = item.stock + ' ⚠️'; }

    // Kategori badge
    const catBadge = item.category_name
      ? `<span class="raz-badge" style="background:${item.category_color}20;color:${item.category_color}">${item.category_name}</span>`
      : '<span class="raz-text-muted">-</span>';

    // Gambar
    const imgHtml = item.image
      ? `<div class="inv-item-img"><img src="uploads/items/${item.image}" alt=""></div>`
      : `<div class="inv-item-img"><i class="ph-bold ph-image"></i></div>`;

    return `<tr>
      <td><div class="inv-item-cell">${imgHtml}<div><div class="inv-item-name">${item.name}</div>${item.sku ? `<div class="inv-item-sku">${item.sku}</div>` : ''}</div></div></td>
      <td>${catBadge}</td>
      <td class="raz-text-rupiah">${RAZ.formatRupiah(item.sell_price)}</td>
      <td><span class="${stockClass}">${stockText}</span></td>
      <td class="col-action">
        <div class="action-btns">
          <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" data-tooltip="Edit" onclick="editItem(${item.id})"><i class="ph-bold ph-pencil-simple"></i></button>
          <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" data-tooltip="Hapus" onclick="deleteItem(${item.id},'${item.name}')" style="color:var(--raz-danger)"><i class="ph-bold ph-trash"></i></button>
        </div>
      </td>
    </tr>`;
  }).join('');
}

// ========================
// RENDER PAGINATION
// ========================
function renderPagination(data) {
  const container = document.getElementById('itemsPagination');
  if (!container) return;

  const info = container.querySelector('.raz-pagination-info');
  const btns = container.querySelector('.raz-pagination-buttons');
  if (info) info.textContent = `Halaman ${data.page} dari ${data.pages} (${data.total} barang)`;

  if (btns && data.pages > 1) {
    let html = `<button ${data.page<=1?'disabled':''} onclick="goPage(${data.page-1})"><i class="ph-bold ph-caret-left"></i></button>`;
    for (let i = 1; i <= Math.min(data.pages, 5); i++) {
      html += `<button class="${i===data.page?'active':''}" onclick="goPage(${i})">${i}</button>`;
    }
    html += `<button ${data.page>=data.pages?'disabled':''} onclick="goPage(${data.page+1})"><i class="ph-bold ph-caret-right"></i></button>`;
    btns.innerHTML = html;
  }
}
function goPage(p) { INV.page = p; loadItems(); }

// ========================
// LOAD CATEGORIES
// ========================
async function loadCategories() {
  const data = await RAZ.api('api/RAZapiItems.php?action=categories');
  if (!data.success) return;
  INV.categories = data.data;

  // Update filter dropdown
  const filter = document.getElementById('catFilter');
  if (filter) {
    filter.innerHTML = '<option value="">Semua Kategori</option>' +
      INV.categories.map(c => `<option value="${c.id}">${c.name} (${c.item_count})</option>`).join('');
  }

  // Update form dropdown
  const formCat = document.getElementById('itemCategory');
  if (formCat) {
    formCat.innerHTML = '<option value="">Tanpa Kategori</option>' +
      INV.categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  }

  // Render kategori grid
  renderCategoryGrid();

  // Update tab count
  const countEl = document.getElementById('catCount');
  if (countEl) countEl.textContent = INV.categories.length;
}

function renderCategoryGrid() {
  const grid = document.getElementById('catGrid');
  if (!grid) return;
  if (!INV.categories.length) {
    grid.innerHTML = '<div class="raz-table-empty"><div class="empty-icon"><i class="ph-bold ph-tag"></i></div><div class="empty-title">Belum Ada Kategori</div></div>';
    return;
  }
  grid.innerHTML = INV.categories.map(c => `
    <div class="cat-card" style="border-left-color:${c.color}">
      <div class="cat-dot" style="background:${c.color}"></div>
      <div class="cat-info"><div class="cat-name">${c.name}</div><div class="cat-count">${c.item_count} barang</div></div>
      <div class="cat-actions">
        <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" data-tooltip="Edit" onclick="editCategory(${c.id},'${c.name}','${c.color}')"><i class="ph-bold ph-pencil-simple"></i></button>
        <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" data-tooltip="Hapus" onclick="deleteCategory(${c.id},'${c.name}')" style="color:var(--raz-danger)"><i class="ph-bold ph-trash"></i></button>
      </div>
    </div>
  `).join('');
}

// ========================
// MODAL BARANG (Tambah / Edit)
// ========================
function openAddItem() {
  document.getElementById('itemFormTitle').textContent = 'Tambah Barang';
  document.getElementById('itemForm').reset();
  document.getElementById('itemId').value = '';
  document.getElementById('marginInfo').style.display = 'none';
  RAZ.openModal('itemModal');
}

async function editItem(id) {
  const data = await RAZ.api(`api/RAZapiItems.php?action=get&id=${id}`);
  if (!data.success) return;
  const item = data.data;

  document.getElementById('itemFormTitle').textContent = 'Edit Barang';
  document.getElementById('itemId').value = item.id;
  document.getElementById('itemName').value = item.name;
  document.getElementById('itemSku').value = item.sku || '';
  document.getElementById('itemCategory').value = item.category_id || '';
  document.getElementById('itemHpp').value = item.hpp;
  document.getElementById('itemSellPrice').value = item.sell_price;
  document.getElementById('itemStock').value = item.stock;
  document.getElementById('itemMinStock').value = item.min_stock;
  RAZ.openModal('itemModal');
}

async function saveItem() {
  const form = document.getElementById('itemForm');
  const btn = document.getElementById('btnSaveItem');
  const fd = new FormData(form);
  const isEdit = !!fd.get('id');

  RAZ.btnLoading(btn, 'Menyimpan...');
  const action = isEdit ? 'update' : 'create';
  const res = await RAZ.upload(`api/RAZapiItems.php?action=${action}`, fd);
  RAZ.btnReset(btn);

  if (res.success) {
    RAZ.success('Berhasil', res.message);
    RAZ.closeModal('itemModal');
    loadItems();
    loadCategories();
  }
}

async function deleteItem(id, name) {
  const ok = await RAZ.confirm({ title: 'Hapus Barang?', message: `"${name}" akan dinonaktifkan.`, confirmText: 'Ya, Hapus' });
  if (!ok) return;
  const res = await RAZ.api('api/RAZapiItems.php?action=delete', { method: 'POST', body: { id } });
  if (res.success) { RAZ.success('Dihapus', res.message); loadItems(); }
}

// ========================
// MODAL KATEGORI
// ========================
function openAddCategory() {
  document.getElementById('catFormTitle').textContent = 'Tambah Kategori';
  document.getElementById('catId').value = '';
  document.getElementById('catName').value = '';
  document.getElementById('catColor').value = '#4F46E5';
  RAZ.openModal('catModal');
}

function editCategory(id, name, color) {
  document.getElementById('catFormTitle').textContent = 'Edit Kategori';
  document.getElementById('catId').value = id;
  document.getElementById('catName').value = name;
  document.getElementById('catColor').value = color;
  RAZ.openModal('catModal');
}

async function saveCategory() {
  const id = document.getElementById('catId').value;
  const name = document.getElementById('catName').value.trim();
  const color = document.getElementById('catColor').value;
  if (!name) { RAZ.error('Error', 'Nama kategori wajib diisi'); return; }

  const btn = document.getElementById('btnSaveCat');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const action = id ? 'update_cat' : 'create_cat';
  const res = await RAZ.api(`api/RAZapiItems.php?action=${action}`, { method: 'POST', body: { id: id || undefined, name, color } });
  RAZ.btnReset(btn);

  if (res.success) {
    RAZ.success('Berhasil', res.message);
    RAZ.closeModal('catModal');
    loadCategories();
  }
}

async function deleteCategory(id, name) {
  const ok = await RAZ.confirm({ title: 'Hapus Kategori?', message: `"${name}" akan dihapus. Barang di kategori ini akan menjadi tanpa kategori.`, type: 'warning', confirmText: 'Ya, Hapus' });
  if (!ok) return;
  const res = await RAZ.api('api/RAZapiItems.php?action=delete_cat', { method: 'POST', body: { id } });
  if (res.success) { RAZ.success('Dihapus', res.message); loadCategories(); loadItems(); }
}

// ========================
// CAMERA SCANNER (HTML5-QRCode)
// ========================
let html5QrcodeScanner = null;

function openScanner() {
  RAZ.openModal('scannerModal');
  if (!html5QrcodeScanner) {
    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 250} }, false);
  }
  html5QrcodeScanner.render(onScanSuccess, onScanFailure);
}

function closeScanner() {
  RAZ.closeModal('scannerModal');
  if (html5QrcodeScanner) {
    html5QrcodeScanner.clear().catch(err => console.error(err));
  }
}

function onScanSuccess(decodedText, decodedResult) {
  // Ketika barcode terdeteksi
  closeScanner();
  const skuInput = document.getElementById('itemSku');
  if (skuInput) {
    skuInput.value = decodedText;
  }
}

function onScanFailure(error) {
  // Biarkan kosong
}
