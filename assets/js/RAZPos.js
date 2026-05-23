/**
 * RAZPos.js — Logic Point of Sale SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Mengelola keranjang belanja, pencarian barang,
 *            proses pembayaran, dan cetak struk digital.
 */
'use strict';

// State POS
const POS = {
  products: [], categories: [], cart: [],
  search: '', categoryFilter: '',
  paymentMethod: 'cash', storeInfo: null,
};

document.addEventListener('DOMContentLoaded', () => {
  loadProducts();
  loadStoreInfo();

  // Search dengan debounce
  document.getElementById('posSearch')?.addEventListener('input', RAZ.debounce((e) => {
    POS.search = e.target.value;
    loadProducts();
  }, 300));

  // Auto-focus search bar (barcode scanner ready)
  document.getElementById('posSearch')?.focus();
});

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
  const searchInput = document.getElementById('posSearch');
  if (searchInput) {
    searchInput.value = decodedText;
    POS.search = decodedText;
    loadProducts();
    
    // Opsional: otomatis tambahkan jika hanya ada 1 hasil yang persis
    setTimeout(() => {
      if (POS.products.length === 1 && POS.products[0].sku === decodedText) {
        addToCart(POS.products[0].id);
        searchInput.value = '';
        POS.search = '';
        loadProducts();
      }
    }, 500);
  }
}

function onScanFailure(error) {
  // Biarkan kosong, ini dipanggil terus saat tidak ada barcode
}

// ========================
// LOAD PRODUCTS
// ========================
async function loadProducts() {
  const params = new URLSearchParams({ action: 'products', search: POS.search, category: POS.categoryFilter });
  const data = await RAZ.api(`api/RAZapiTransactions.php?${params}`);
  if (!data.success) return;

  POS.products = data.data.products;
  POS.categories = data.data.categories;
  renderProducts();
  renderCategoryChips();
}

async function loadStoreInfo() {
  const data = await RAZ.api('api/RAZapiTransactions.php?action=store_info');
  if (data.success) POS.storeInfo = data.data;
}

// ========================
// RENDER PRODUCT GRID
// ========================
function renderProducts() {
  const grid = document.getElementById('posGrid');
  if (!grid) return;

  if (!POS.products.length) {
    grid.innerHTML = `<div class="pos-empty" style="grid-column:1/-1"><i class="ph-bold ph-magnifying-glass"></i><p>Barang tidak ditemukan</p></div>`;
    return;
  }

  grid.innerHTML = POS.products.map(p => {
    const outClass = p.stock <= 0 ? 'out-of-stock' : '';
    const img = p.image
      ? `<div class="pos-item-img"><img src="uploads/items/${p.image}" alt=""></div>`
      : `<div class="pos-item-img"><i class="ph-bold ph-package"></i></div>`;

    return `<div class="pos-item ${outClass}" onclick="addToCart(${p.id})" title="${p.name}">
      ${img}
      <div class="pos-item-name">${p.name}</div>
      <div class="pos-item-price">${RAZ.formatRupiah(p.sell_price)}</div>
      <div class="pos-item-stock">${p.stock <= 0 ? 'Habis' : 'Stok: ' + p.stock}</div>
    </div>`;
  }).join('');
}

function renderCategoryChips() {
  const container = document.getElementById('posCats');
  if (!container) return;
  let html = `<button class="pos-cat-chip ${!POS.categoryFilter ? 'active' : ''}" onclick="filterCategory('')">Semua</button>`;
  html += POS.categories.map(c =>
    `<button class="pos-cat-chip ${POS.categoryFilter == c.id ? 'active' : ''}" onclick="filterCategory('${c.id}')">${c.name}</button>`
  ).join('');
  container.innerHTML = html;
}

function filterCategory(id) {
  POS.categoryFilter = id;
  loadProducts();
}

// ========================
// CART MANAGEMENT
// ========================
function addToCart(productId) {
  const product = POS.products.find(p => p.id === productId);
  if (!product || product.stock <= 0) return;

  const existing = POS.cart.find(c => c.id === productId);
  if (existing) {
    if (existing.qty >= product.stock) {
      RAZ.warning('Stok Limit', `Stok "${product.name}" hanya tersisa ${product.stock}`);
      return;
    }
    existing.qty++;
  } else {
    POS.cart.push({
      id: product.id, name: product.name, price: parseFloat(product.sell_price),
      hpp: parseFloat(product.hpp), qty: 1, maxStock: product.stock,
    });
  }
  renderCart();
}

function updateQty(productId, delta) {
  const item = POS.cart.find(c => c.id === productId);
  if (!item) return;

  item.qty += delta;
  if (item.qty <= 0) { POS.cart = POS.cart.filter(c => c.id !== productId); }
  else if (item.qty > item.maxStock) {
    item.qty = item.maxStock;
    RAZ.warning('Stok Limit', 'Melebihi stok tersedia');
  }
  renderCart();
}

function removeFromCart(productId) {
  POS.cart = POS.cart.filter(c => c.id !== productId);
  renderCart();
}

function clearCart() {
  if (!POS.cart.length) return;
  POS.cart = [];
  renderCart();
  RAZ.info('Keranjang Dikosongkan', '');
}

// ========================
// RENDER CART
// ========================
function renderCart() {
  const container = document.getElementById('cartItems');
  const summary = document.getElementById('cartSummary');
  const countEl = document.getElementById('cartCount');
  const payBtn = document.getElementById('payBtn');

  if (!container) return;

  const totalItems = POS.cart.reduce((a, c) => a + c.qty, 0);
  if (countEl) countEl.textContent = totalItems;

  if (!POS.cart.length) {
    container.innerHTML = `<div class="pos-cart-empty"><i class="ph-bold ph-shopping-cart"></i><p>Keranjang kosong</p></div>`;
    if (summary) summary.style.display = 'none';
    return;
  }

  if (summary) summary.style.display = 'block';

  container.innerHTML = POS.cart.map(item => `
    <div class="pos-cart-item">
      <div class="pos-cart-item-info">
        <div class="pos-cart-item-name">${item.name}</div>
        <div class="pos-cart-item-price">${RAZ.formatRupiah(item.price)}</div>
      </div>
      <div class="pos-qty">
        <button onclick="updateQty(${item.id}, -1)"><i class="ph-bold ph-minus"></i></button>
        <span>${item.qty}</span>
        <button onclick="updateQty(${item.id}, 1)"><i class="ph-bold ph-plus"></i></button>
      </div>
      <div class="pos-cart-item-total">${RAZ.formatRupiah(item.price * item.qty)}</div>
      <button class="pos-cart-remove" onclick="removeFromCart(${item.id})"><i class="ph-bold ph-x"></i></button>
    </div>
  `).join('');

  // Update summary
  const subtotal = POS.cart.reduce((a, c) => a + (c.price * c.qty), 0);
  const taxPct = parseFloat(POS.storeInfo?.tax_percentage || 0);
  const tax = Math.round(subtotal * (taxPct / 100));
  const grandTotal = subtotal + tax;

  document.getElementById('sumSubtotal').textContent = RAZ.formatRupiah(subtotal);
  document.getElementById('sumTax').textContent = RAZ.formatRupiah(tax);
  document.getElementById('sumTaxLabel').textContent = `Pajak (${taxPct}%)`;
  document.getElementById('sumTotal').textContent = RAZ.formatRupiah(grandTotal);

  if (payBtn) payBtn.disabled = false;
}

// ========================
// PAYMENT MODAL
// ========================
function openPayment() {
  if (!POS.cart.length) return;

  const subtotal = POS.cart.reduce((a, c) => a + (c.price * c.qty), 0);
  const taxPct = parseFloat(POS.storeInfo?.tax_percentage || 0);
  const tax = Math.round(subtotal * (taxPct / 100));
  const grandTotal = subtotal + tax;

  document.getElementById('payTotal').textContent = RAZ.formatRupiah(grandTotal);
  document.getElementById('payAmount').value = '';
  document.getElementById('payChange').style.display = 'none';
  POS.paymentMethod = 'cash';
  document.querySelectorAll('.pay-method-card').forEach(c => c.classList.remove('selected'));
  document.querySelector('[data-method="cash"]')?.classList.add('selected');

  // Show/hide cash input based on method
  document.getElementById('cashInputGroup').style.display = 'block';

  RAZ.openModal('payModal');
  setTimeout(() => document.getElementById('payAmount')?.focus(), 200);
}

function selectPayMethod(method) {
  POS.paymentMethod = method;
  document.querySelectorAll('.pay-method-card').forEach(c => c.classList.remove('selected'));
  document.querySelector(`[data-method="${method}"]`)?.classList.add('selected');

  const cashGroup = document.getElementById('cashInputGroup');
  if (method === 'cash') {
    cashGroup.style.display = 'block';
    document.getElementById('payAmount')?.focus();
  } else {
    cashGroup.style.display = 'none';
  }
  calculateChange();
}

function calculateChange() {
  const subtotal = POS.cart.reduce((a, c) => a + (c.price * c.qty), 0);
  const taxPct = parseFloat(POS.storeInfo?.tax_percentage || 0);
  const grandTotal = subtotal + Math.round(subtotal * (taxPct / 100));
  const paid = parseFloat(document.getElementById('payAmount')?.value || 0);

  const changeDiv = document.getElementById('payChange');
  if (POS.paymentMethod === 'cash' && paid >= grandTotal) {
    changeDiv.style.display = 'block';
    document.getElementById('changeAmount').textContent = RAZ.formatRupiah(paid - grandTotal);
  } else if (POS.paymentMethod !== 'cash') {
    changeDiv.style.display = 'none';
  } else {
    changeDiv.style.display = 'none';
  }
}

// ========================
// PROCESS CHECKOUT
// ========================
async function processPayment() {
  const subtotal = POS.cart.reduce((a, c) => a + (c.price * c.qty), 0);
  const taxPct = parseFloat(POS.storeInfo?.tax_percentage || 0);
  const grandTotal = subtotal + Math.round(subtotal * (taxPct / 100));

  let amountPaid = grandTotal;
  if (POS.paymentMethod === 'cash') {
    amountPaid = parseFloat(document.getElementById('payAmount')?.value || 0);
    if (amountPaid < grandTotal) {
      RAZ.error('Pembayaran Kurang', `Kurang ${RAZ.formatRupiah(grandTotal - amountPaid)}`);
      return;
    }
  }

  const btn = document.getElementById('btnProcessPay');
  RAZ.btnLoading(btn, 'Memproses...');

  const res = await RAZ.api('api/RAZapiTransactions.php?action=checkout', {
    method: 'POST',
    body: {
      items: POS.cart.map(c => ({ id: c.id, qty: c.qty })),
      payment_method: POS.paymentMethod,
      amount_paid: amountPaid,
      discount_amount: 0,
    }
  });

  RAZ.btnReset(btn);

  if (res.success) {
    RAZ.closeModal('payModal');
    RAZ.success('Transaksi Berhasil!', `Invoice: ${res.data.invoice_number}`);

    // Tampilkan struk
    showReceipt(res.data.transaction_id);

    // Reset cart & reload produk
    POS.cart = [];
    renderCart();
    loadProducts();
  }
}

// ========================
// RECEIPT / STRUK (30 Templates Logic)
// ========================
let currentReceiptStyle = ''; // Simpan style untuk diprint
let currentTransId = 0; // Simpan ID untuk dibagikan

// (Removed getReceiptTemplateProps as it is now handled by RAZreceipt.php)

function showReceipt(transId) {
  currentTransId = transId;
  
  // Gunakan iframe agar tampilan preview sama persis dengan hasil cetak (RAZreceipt.php)
  const iframeHtml = `
    <div style="width:100%; height: 60vh; min-height:400px; display:flex; justify-content:center; background:#f0f0f0; border-radius:8px; overflow:hidden;">
        <iframe src="RAZreceipt.php?id=${transId}&preview=1" style="width:100%; height:100%; border:none;"></iframe>
    </div>
  `;
  
  document.getElementById('receiptContent').innerHTML = iframeHtml;
  RAZ.openModal('receiptModal');
}

function printReceipt() {
  if (!currentTransId) return;
  window.open('RAZreceipt.php?id=' + currentTransId, '_blank', 'width=400,height=600');
}

function shareReceipt() {
  if (!currentTransId) return;
  // Dapatkan URL struk publik
  const url = window.location.origin + window.location.pathname.replace('RAZpos.php', '') + 'RAZreceipt.php?id=' + currentTransId;
  const text = encodeURIComponent(`Halo! Berikut adalah e-struk / resi pembelian Anda:\n\n` + url + `\n\nTerima kasih telah berbelanja!`);
  window.open(`https://api.whatsapp.com/send?text=${text}`, '_blank');
}
