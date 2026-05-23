/**
 * RAZSettings.js — Logic Pengaturan Toko SIMAJURAZ
 * Versi: 1.0.0
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  initTabs();
  initTemplates();
  loadSettings();

  // Preview logo upload
  document.getElementById('setLogoFile')?.addEventListener('change', function() {
    if (this.files && this.files[0]) {
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('setLogoPreview').innerHTML = `<img src="${e.target.result}">`;
        updateFullReceiptPreview();
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

  // Attach events for live receipt preview
  ['setHeader', 'setFooter', 'setShowLogo', 'setInvoiceFormat'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updateFullReceiptPreview);
    document.getElementById(id)?.addEventListener('change', updateFullReceiptPreview);
  });
  
  // Profile events
  ['setName', 'setAddress', 'setPhone'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', updateFullReceiptPreview);
  });
});

function initTabs() {
  document.querySelectorAll('.set-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.set-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.set-panel').forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      document.getElementById(tab.dataset.target).classList.add('active');
    });
  });
}

// ========================
// RENDER 30 TEMPLATES
// ========================
function initTemplates() {
  const grid = document.getElementById('tplGrid');
  if (!grid) return;

  const fonts = ['mono', 'sans'];
  const aligns = ['center', 'left'];
  const seps = ['dash', 'solid', 'double'];
  const widths = ['58mm', '80mm'];
  let count = 1;
  let html = '';

  // Generate 30 combinations
  for (let f of fonts) {
    for (let a of aligns) {
      for (let s of seps) {
        for (let w of widths) {
          if (count > 30) break;
          const tplClass = `tpl-font-${f} tpl-align-${a} tpl-sep-${s}`;
          const isWide = w === '80mm';
          
          html += `
          <div class="set-tpl-card" data-id="${count}" onclick="selectTemplate(${count})">
            <div class="set-tpl-preview ${tplClass}">
              <div class="mini-receipt" style="width:${isWide ? '90%' : '70%'}">
                <div class="mr-logo"></div>
                <div class="mr-text"></div>
                <div class="mr-text" style="width:40%"></div>
                <div class="mr-line"></div>
                <div class="mr-text" style="width:80%"></div>
                <div class="mr-text" style="width:80%"></div>
                <div class="mr-line"></div>
                <div class="mr-text"></div>
              </div>
            </div>
            <div class="set-tpl-name">Template ${count}<br><small style="color:#888">${w} | ${f} | ${s}</small></div>
          </div>`;
          count++;
        }
      }
    }
  }
  
  // Fill the rest up to 30 if combinations fall short
  while (count <= 30) {
    html += `
    <div class="set-tpl-card" data-id="${count}" onclick="selectTemplate(${count})">
      <div class="set-tpl-preview tpl-font-mono tpl-align-center tpl-sep-dash">
        <div class="mini-receipt"><div class="mr-logo"></div><div class="mr-text"></div><div class="mr-line"></div></div>
      </div>
      <div class="set-tpl-name">Template ${count}<br><small style="color:#888">Special</small></div>
    </div>`;
    count++;
  }
  
  grid.innerHTML = html;
}

function selectTemplate(id) {
  document.querySelectorAll('.set-tpl-card').forEach(c => c.classList.remove('active'));
  const card = document.querySelector(`.set-tpl-card[data-id="${id}"]`);
  if (card) card.classList.add('active');
  document.getElementById('inputTemplateId').value = id;
  updateFullReceiptPreview();
}

// ========================
// LOAD DATA
// ========================
async function loadSettings() {
  const res = await RAZ.api('api/RAZapiSettings.php?action=get');
  if (!res.success) return;
  const s = res.data;

  // Profil Toko
  document.getElementById('setName').value = s.store_name || '';
  document.getElementById('setType').value = s.store_type || '';
  document.getElementById('setDesc').value = s.store_description || '';
  document.getElementById('setPhone').value = s.store_phone || '';
  document.getElementById('setAddress').value = s.store_address || '';
  
  if (s.store_logo_url) {
    document.getElementById('setLogoPreview').innerHTML = `<img src="${s.store_logo_url}">`;
  }

  // Struk
  document.getElementById('setInvoiceFormat').value = s.invoice_format || 'INV-{Ymd}-{SEQ5}';
  updateInvoicePreview();
  document.getElementById('setHeader').value = s.receipt_header || '';
  document.getElementById('setFooter').value = s.receipt_footer || '';
  document.getElementById('setShowLogo').value = s.receipt_show_logo || '1';
  selectTemplate(s.receipt_template || 1);
}

// ========================
// SAVE PROFIL
// ========================
async function saveProfile() {
  const form = document.getElementById('formProfile');
  const fd = new FormData();
  fd.append('store_name', document.getElementById('setName').value);
  fd.append('store_type', document.getElementById('setType').value);
  fd.append('store_description', document.getElementById('setDesc').value);
  fd.append('store_phone', document.getElementById('setPhone').value);
  fd.append('store_address', document.getElementById('setAddress').value);
  
  const fileInput = document.getElementById('setLogoFile');
  if (fileInput.files[0]) fd.append('store_logo', fileInput.files[0]);

  const btn = document.getElementById('btnSaveProfile');
  RAZ.btnLoading(btn, 'Menyimpan...');
  
  const res = await fetch('api/RAZapiSettings.php?action=update_profile', { method: 'POST', body: fd }).then(r => r.json());
  RAZ.btnReset(btn);
  
  if (res.success) {
    RAZ.success('Berhasil', res.message);
    setTimeout(() => location.reload(), 1500); // Reload agar sidebar logo/nama toko terupdate
  }
  else RAZ.error('Gagal', res.message);
}

// ========================
// SAVE STRUK
// ========================
async function saveReceipt() {
  const data = {
    invoice_format: document.getElementById('setInvoiceFormat').value,
    receipt_template: document.getElementById('inputTemplateId').value,
    receipt_header: document.getElementById('setHeader').value,
    receipt_footer: document.getElementById('setFooter').value,
    receipt_show_logo: document.getElementById('setShowLogo').value
  };

  const btn = document.getElementById('btnSaveReceipt');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const res = await RAZ.api('api/RAZapiSettings.php?action=update_receipt', { method: 'POST', body: data });
  RAZ.btnReset(btn);
  if (res.success) RAZ.success('Berhasil', res.message);
}

// ========================
// SAVE PASSWORD
// ========================
async function savePassword() {
  const current = document.getElementById('setCurPw').value;
  const newPw = document.getElementById('setNewPw').value;
  
  if (!current || !newPw) { RAZ.error('Error', 'Semua field wajib diisi'); return; }

  const btn = document.getElementById('btnSavePw');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const res = await RAZ.api('api/RAZapiSettings.php?action=update_password', { method: 'POST', body: { current_password: current, new_password: newPw } });
  RAZ.btnReset(btn);
  
  if (res.success) {
    RAZ.success('Berhasil', res.message);
    document.getElementById('setCurPw').value = '';
    document.getElementById('setNewPw').value = '';
  }
}

// ========================
// INVOICE BUILDER PREVIEW
// ========================
function addFormatTag(tag) {
  const input = document.getElementById('setInvoiceFormat');
  input.value = input.value + tag;
  updateInvoicePreview();
  input.focus();
}

function updateInvoicePreview() {
  const input = document.getElementById('setInvoiceFormat');
  const preview = document.getElementById('invoicePreview');
  if (!input || !preview) return;

  let format = input.value;
  
  // Tanggal dummy: 20260522 atau 220526
  format = format.replace(/\{Ymd\}/g, '20260522');
  format = format.replace(/\{Y-m-d\}/g, '2026-05-22');
  format = format.replace(/\{dmY\}/g, '22052026');
  format = format.replace(/\{dmy\}/g, '220526');
  format = format.replace(/\{ymd\}/g, '260522');
  format = format.replace(/\{mdy\}/g, '052226');
  format = format.replace(/\{Ym\}/g, '202605');
  format = format.replace(/\{ym\}/g, '2605');
  format = format.replace(/\{my\}/g, '0526');

  // Sequence dummy
  format = format.replace(/\{SEQ3\}/g, '001');
  format = format.replace(/\{SEQ4\}/g, '0001');
  format = format.replace(/\{SEQ5\}/g, '00001');

  // Random number dummy
  format = format.replace(/\{RAND4\}/g, '4291');
  format = format.replace(/\{RAND5\}/g, '58192');
  format = format.replace(/\{RAND6\}/g, '194821');

  // Mix dummy
  format = format.replace(/\{MIX4\}/g, 'A9X2');
  format = format.replace(/\{MIX5\}/g, 'B7F1Z');
  format = format.replace(/\{MIX6\}/g, 'K9B1X2');

  preview.textContent = format || '-';
  updateFullReceiptPreview();
}

// ========================
// FULL RECEIPT PREVIEW
// ========================
function updateFullReceiptPreview() {
  const container = document.getElementById('fullReceiptPreview');
  if (!container) return;

  // Gather values
  const storeName = document.getElementById('setName')?.value || 'Nama Toko';
  const storeAddress = document.getElementById('setAddress')?.value || 'Alamat Toko';
  const storePhone = document.getElementById('setPhone')?.value || '08xx-xxxx';
  const header = document.getElementById('setHeader')?.value || '';
  const footer = document.getElementById('setFooter')?.value || '';
  // Get logo position / visibility
  const showLogo = document.getElementById('setShowLogo')?.value || '1';
  
  // Get active template class and other variables
  const activeCard = document.querySelector('.set-tpl-card.active .set-tpl-preview');
  const tplClass = activeCard ? activeCard.className.replace('set-tpl-preview', '').trim() : 'tpl-font-mono tpl-align-center tpl-sep-dash';
  const isWide = document.querySelector('.set-tpl-card.active .set-tpl-name')?.textContent.includes('80mm');
  const widthStr = isWide ? '80mm' : '58mm';
  const invoice = document.getElementById('invoicePreview')?.textContent || 'INV-20260522-00001';

  // Get logo image source if available
  const logoPreviewImg = document.querySelector('#setLogoPreview img');
  const logoSrc = (logoPreviewImg && logoPreviewImg.src) ? logoPreviewImg.src : '';
  const fallbackIcon = `<i class="ph-fill ph-storefront"></i>`;

  let topLogoHtml = '';
  let bottomLogoHtml = '';
  let watermarkStyle = '';
  let watermarkInner = '';

  if (showLogo !== '0') {
    // Generate inner img tag
    let imgTag = logoSrc ? `<img src="${logoSrc}" style="max-height:60px; max-width:80%; object-fit:contain;">` : `<span style="font-size:24px; color:var(--raz-primary);">${fallbackIcon}</span>`;
    
    // Top positions
    if (['1', '2', '3'].includes(showLogo)) {
      const align = showLogo === '2' ? 'left' : (showLogo === '3' ? 'right' : 'center');
      topLogoHtml = `<div style="text-align:${align}; margin-bottom:10px;">${imgTag}</div>`;
    }
    // Bottom positions
    else if (['4', '5', '6'].includes(showLogo)) {
      const align = showLogo === '5' ? 'left' : (showLogo === '6' ? 'right' : 'center');
      bottomLogoHtml = `<div style="text-align:${align}; margin-top:10px; margin-bottom:5px;">${imgTag}</div>`;
    }
    // Watermark Center
    else if (showLogo === '7') {
      watermarkStyle = `position:relative; z-index:1;`;
      let bg = logoSrc ? `background: url('${logoSrc}') no-repeat center center; background-size: 70%;` : `display:flex; justify-content:center; align-items:center; font-size:60px; color:var(--raz-primary);`;
      let content = logoSrc ? '' : fallbackIcon;
      watermarkInner = `<div style="position:absolute; top:0; left:0; right:0; bottom:0; z-index:-1; pointer-events:none; opacity:0.15; filter:grayscale(100%); ${bg}">${content}</div>`;
    }
    // Watermark Tile
    else if (showLogo === '8') {
      watermarkStyle = `position:relative; z-index:1;`;
      let bg = logoSrc ? `background: url('${logoSrc}') repeat; background-size: 50px 50px;` : ``; // tile fallback icon is hard, just leave empty
      watermarkInner = `<div style="position:absolute; top:0; left:0; right:0; bottom:0; z-index:-1; pointer-events:none; opacity:0.15; filter:grayscale(100%); ${bg}"></div>`;
    }
  }

  // Define separator based on class
  let sepChar = '-';
  if (tplClass.includes('tpl-sep-solid')) sepChar = '_';
  else if (tplClass.includes('tpl-sep-double')) sepChar = '=';
  const sepLen = isWide ? 40 : 32;
  const separatorHtml = `<div style="overflow:hidden; white-space:nowrap; letter-spacing:1px;">${sepChar.repeat(sepLen)}</div>`;

  // Align class mappings
  let alignCss = 'text-align:center;';
  if (tplClass.includes('tpl-align-left')) alignCss = 'text-align:left;';
  
  // Font class mappings
  let fontFam = 'monospace';
  if (tplClass.includes('tpl-font-sans')) fontFam = 'sans-serif';

  const formatHeader = text => text ? `<div style="margin-top:8px; white-space:pre-wrap;">${text}</div>` : '';

  // Generate HTML
  container.innerHTML = `
    <div style="font-family:${fontFam}; font-size:12px; ${alignCss} width:100%; margin:0 auto; line-height:1.4; ${watermarkStyle}">
      ${watermarkInner}
      ${topLogoHtml}
      <h3 style="margin:0 0 5px; font-size:16px;">${storeName}</h3>
      <div style="font-size:11px; margin-bottom:10px; white-space:pre-wrap;">${storeAddress}<br>${storePhone}</div>
      ${separatorHtml}
      ${formatHeader(header)}
      ${header ? separatorHtml : ''}
      <div style="text-align:left; font-size:11px; margin:8px 0; display:flex; flex-direction:column; gap:4px;">
          <div style="display:flex; justify-content:space-between;"><span>Inv:</span><span>${invoice}</span></div>
          <div style="display:flex; justify-content:space-between;"><span>Tgl:</span><span>22 Mei 2026 14:30</span></div>
          <div style="display:flex; justify-content:space-between;"><span>Kasir:</span><span>Kasir Demo</span></div>
      </div>
      ${separatorHtml}
      <div style="text-align:left; font-size:11px; margin:8px 0;">
          <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="flex:2">Kopi Susu Gula Aren x2</span><span style="flex:1; text-align:right;">36.000</span>
          </div>
          <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
              <span style="flex:2">Croissant Almond x1</span><span style="flex:1; text-align:right;">25.000</span>
          </div>
      </div>
      ${separatorHtml}
      <div style="text-align:right; font-size:11px; margin:8px 0;">
          <div style="display:flex; justify-content:space-between;"><span>Total:</span><strong>Rp 61.000</strong></div>
          <div style="display:flex; justify-content:space-between;"><span>Tunai:</span><span>Rp 100.000</span></div>
          <div style="display:flex; justify-content:space-between;"><span>Kembali:</span><span>Rp 39.000</span></div>
      </div>
      ${bottomLogoHtml}
      ${footer ? separatorHtml : ''}
      ${formatHeader(footer)}
    </div>
  `;
}
