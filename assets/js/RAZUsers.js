/**
 * RAZUsers.js — Logic Manajemen Karyawan SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: CRUD karyawan, toggle aktif, reset password.
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {
  loadUsers();

  // Search
  document.getElementById('userSearch')?.addEventListener('input', RAZ.debounce((e) => {
    loadUsers(e.target.value);
  }, 400));
});

// ========================
// LOAD KARYAWAN
// ========================
async function loadUsers(search = '') {
  const params = new URLSearchParams({ action: 'list', search });
  const data = await RAZ.api(`api/RAZapiUsers.php?${params}`);
  if (!data.success) return;

  // Update stats
  document.getElementById('statTotal').textContent = data.data.total;
  document.getElementById('statActive').textContent = data.data.active;
  document.getElementById('statInactive').textContent = data.data.total - data.data.active;

  window.usersData = data.data.users; // Save for salary dropdown
  renderUserGrid(data.data.users);
}

// ========================
// RENDER USER CARDS
// ========================
function renderUserGrid(users) {
  const grid = document.getElementById('userGrid');
  if (!grid) return;

  if (!users.length) {
    grid.innerHTML = `<div style="grid-column:1/-1;"><div class="raz-table-empty">
      <div class="empty-icon"><i class="ph-bold ph-users"></i></div>
      <div class="empty-title">${window.USR_LANG ? window.USR_LANG.empty_emp : 'Belum Ada Karyawan'}</div>
      <div class="empty-desc">Klik "Tambah Karyawan" untuk membuat akun baru.</div>
    </div></div>`;
    return;
  }

  grid.innerHTML = users.map(u => {
    const initials = u.full_name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
    const activeClass = u.is_active ? '' : 'inactive';
    const statusBadge = u.is_active
      ? '<span class="raz-badge success">Aktif</span>'
      : '<span class="raz-badge danger">Nonaktif</span>';

    return `
    <div class="usr-card">
      <div class="usr-card-top">
        <div class="usr-avatar ${activeClass}">${initials}</div>
        <div class="usr-card-info">
          <div class="usr-card-name">${u.full_name}</div>
          <div class="usr-card-user">@${u.username}</div>
        </div>
        ${statusBadge}
      </div>
      <div class="usr-card-bottom">
        <div class="usr-card-date"><i class="ph-bold ph-calendar"></i> ${u.created_at}</div>
        <div class="usr-card-actions">
          <label class="usr-toggle" data-tooltip="${u.is_active ? 'Nonaktifkan' : 'Aktifkan'}">
            <input type="checkbox" ${u.is_active ? 'checked' : ''} onchange="toggleUser(${u.id})">
            <span class="usr-toggle-slider"></span>
          </label>
          <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" data-tooltip="Edit" onclick="editUser(${u.id})"><i class="ph-bold ph-pencil-simple"></i></button>
          <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" data-tooltip="Reset Password" onclick="openResetPw(${u.id},'${u.full_name}')"><i class="ph-bold ph-key"></i></button>
          <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" data-tooltip="Hapus" onclick="deleteUser(${u.id},'${u.full_name}')" style="color:var(--raz-danger)"><i class="ph-bold ph-trash"></i></button>
        </div>
      </div>
    </div>`;
  }).join('');
}

// ========================
// TAMBAH KARYAWAN
// ========================
function openAddUser() {
  document.getElementById('userFormTitle').textContent = window.USR_LANG ? window.USR_LANG.user_new : 'Tambah Karyawan';
  document.getElementById('userId').value = '';
  document.getElementById('userName').value = '';
  document.getElementById('userUsername').value = '';
  document.getElementById('userPassword').value = '';
  document.getElementById('pwGroup').style.display = 'block';
  document.getElementById('userUsername').disabled = false;
  RAZ.openModal('userModal');
}

// ========================
// EDIT KARYAWAN
// ========================
async function editUser(id) {
  const data = await RAZ.api(`api/RAZapiUsers.php?action=get&id=${id}`);
  if (!data.success) return;
  const u = data.data;

  document.getElementById('userFormTitle').textContent = window.USR_LANG ? window.USR_LANG.user_edit : 'Edit Karyawan';
  document.getElementById('userId').value = u.id;
  document.getElementById('userName').value = u.full_name;
  document.getElementById('userUsername').value = u.username;
  document.getElementById('userUsername').disabled = true;
  document.getElementById('userPassword').value = '';
  document.getElementById('pwGroup').style.display = 'none';
  RAZ.openModal('userModal');
}

// ========================
// SIMPAN KARYAWAN
// ========================
async function saveUser() {
  const id = document.getElementById('userId').value;
  const fullName = document.getElementById('userName').value.trim();
  const username = document.getElementById('userUsername').value.trim();
  const password = document.getElementById('userPassword').value;

  if (!fullName) { RAZ.error('Error', 'Nama lengkap wajib diisi'); return; }

  const btn = document.getElementById('btnSaveUser');
  RAZ.btnLoading(btn, 'Menyimpan...');

  if (id) {
    // Update
    const res = await RAZ.api('api/RAZapiUsers.php?action=update', { method: 'POST', body: { id, full_name: fullName } });
    RAZ.btnReset(btn);
    if (res.success) { RAZ.success('Berhasil', res.message); RAZ.closeModal('userModal'); loadUsers(); }
  } else {
    // Create
    if (!username || !password) { RAZ.error('Error', 'Username dan password wajib diisi'); RAZ.btnReset(btn); return; }
    if (password.length < 6) { RAZ.error('Error', 'Password minimal 6 karakter'); RAZ.btnReset(btn); return; }
    const res = await RAZ.api('api/RAZapiUsers.php?action=create', { method: 'POST', body: { full_name: fullName, username, password } });
    RAZ.btnReset(btn);
    if (res.success) { RAZ.success('Berhasil', res.message); RAZ.closeModal('userModal'); loadUsers(); }
  }
}

// ========================
// TOGGLE AKTIF/NONAKTIF
// ========================
async function toggleUser(id) {
  const res = await RAZ.api('api/RAZapiUsers.php?action=toggle', { method: 'POST', body: { id } });
  if (res.success) { RAZ.info('Status Diubah', res.message); loadUsers(); }
}

// ========================
// HAPUS KARYAWAN
// ========================
async function deleteUser(id, name) {
  const ok = await RAZ.confirm({ title: 'Hapus Karyawan?', message: `"${name}" akan dihapus permanen dan tidak bisa login lagi.`, type: 'danger', confirmText: 'Ya, Hapus' });
  if (!ok) return;
  const res = await RAZ.api('api/RAZapiUsers.php?action=delete', { method: 'POST', body: { id } });
  if (res.success) { RAZ.success('Dihapus', res.message); loadUsers(); }
}

// ========================
// RESET PASSWORD
// ========================
function openResetPw(id, name) {
  document.getElementById('resetPwUserId').value = id;
  document.getElementById('resetPwName').textContent = name;
  document.getElementById('resetPwInput').value = '';
  RAZ.openModal('resetPwModal');
}

async function submitResetPw() {
  const id = document.getElementById('resetPwUserId').value;
  const newPw = document.getElementById('resetPwInput').value;
  if (newPw.length < 6) { RAZ.error('Error', 'Password minimal 6 karakter'); return; }

  const btn = document.getElementById('btnResetPw');
  RAZ.btnLoading(btn, 'Mereset...');
  const res = await RAZ.api('api/RAZapiUsers.php?action=reset_pw', { method: 'POST', body: { id, new_password: newPw } });
  RAZ.btnReset(btn);
  if (res.success) { RAZ.success('Berhasil', res.message); RAZ.closeModal('resetPwModal'); }
}

// ========================
// PROFIL OWNER
// ========================
async function openProfile() {
  const data = await RAZ.api('api/RAZapiUsers.php?action=profile');
  if (!data.success) return;
  document.getElementById('profileName').value = data.data.full_name;
  document.getElementById('profileCurrentPw').value = '';
  document.getElementById('profileNewPw').value = '';
  RAZ.openModal('profileModal');
}

async function saveProfile() {
  const fullName = document.getElementById('profileName').value.trim();
  const currentPw = document.getElementById('profileCurrentPw').value;
  const newPw = document.getElementById('profileNewPw').value;
  if (!fullName) { RAZ.error('Error', 'Nama wajib diisi'); return; }

  const btn = document.getElementById('btnSaveProfile');
  RAZ.btnLoading(btn, 'Menyimpan...');
  const res = await RAZ.api('api/RAZapiUsers.php?action=update_profile', { method: 'POST', body: { full_name: fullName, current_password: currentPw, new_password: newPw } });
  RAZ.btnReset(btn);
  if (res.success) { RAZ.success('Berhasil', res.message); RAZ.closeModal('profileModal'); setTimeout(() => location.reload(), 1000); }
}

// ==========================================
// SALARY (PENGGAJIAN) LOGIC
// ==========================================

function switchUserTab(tabName) {
    document.querySelectorAll('.raz-tab-btn').forEach(btn => btn.classList.remove('active', 'border-bottom'));
    document.querySelectorAll('.usr-tab-content').forEach(content => content.style.display = 'none');
    
    if (tabName === 'karyawan') {
        document.getElementById('btnTabKaryawan').classList.add('active');
        document.getElementById('btnTabKaryawan').style.borderBottom = '2px solid var(--raz-primary)';
        document.getElementById('btnTabKaryawan').style.color = 'var(--raz-primary)';
        
        document.getElementById('btnTabPenggajian').style.borderBottom = 'none';
        document.getElementById('btnTabPenggajian').style.color = 'var(--raz-text-muted)';
        
        document.getElementById('tabKaryawan').style.display = 'block';
    } else {
        document.getElementById('btnTabPenggajian').classList.add('active');
        document.getElementById('btnTabPenggajian').style.borderBottom = '2px solid var(--raz-primary)';
        document.getElementById('btnTabPenggajian').style.color = 'var(--raz-primary)';
        
        document.getElementById('btnTabKaryawan').style.borderBottom = 'none';
        document.getElementById('btnTabKaryawan').style.color = 'var(--raz-text-muted)';
        
        document.getElementById('tabPenggajian').style.display = 'block';
        loadSalaries();
    }
}

async function loadSalaries() {
    const data = await RAZ.api('api/RAZapiSalaries.php?action=list');
    const tbody = document.getElementById('salaryBody');
    if (!data || !data.success) return;

    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="raz-text-center text-muted">Belum ada riwayat penggajian</td></tr>';
        return;
    }

    tbody.innerHTML = data.data.map(s => `
        <tr>
            <td>
                <div style="font-weight:600;">${s.payment_date}</div>
                <div style="font-size:0.75rem;color:var(--raz-text-muted);">${s.created_at.substring(0,16)}</div>
            </td>
            <td>
                <div style="font-weight:600;">${s.employee_name}</div>
                <div style="font-size:0.75rem;color:var(--raz-text-muted);">${s.employee_role}</div>
            </td>
            <td><span class="raz-badge primary" style="font-size:0.7rem;">${s.period_type}</span></td>
            <td style="text-align:right;">
                <strong style="color:var(--raz-success); font-size:1rem;">${RAZ.formatRupiah(s.net_salary)}</strong>
            </td>
            <td style="text-align:center;">
                <button class="raz-btn-icon-only text-primary" onclick="printSalarySlip(${s.id})" title="Cetak Slip Gaji" style="background:none;border:none;cursor:pointer;"><i class="ph-bold ph-printer"></i></button>
                <button class="raz-btn-icon-only text-primary" onclick="editSalary(${s.id}, ${s.user_id}, '${s.period_type}', ${s.base_salary}, ${s.bonus}, ${s.deduction}, '${s.payment_date}', '${(s.notes||'').replace(/'/g, "\\'")}')" style="background:none;border:none;cursor:pointer;margin-left:5px;"><i class="ph-bold ph-pencil-simple"></i></button>
                <button class="raz-btn-icon-only text-danger" onclick="deleteSalary(${s.id})" style="background:none;border:none;cursor:pointer;margin-left:5px;"><i class="ph-bold ph-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

function openAddSalary() {
    document.getElementById('salaryId').value = '';
    document.getElementById('salaryFormTitle').textContent = window.USR_LANG ? window.USR_LANG.salary_new : 'Pembayaran Gaji Baru';
    document.getElementById('salaryPeriod').value = 'Bulanan';
    document.getElementById('salaryBase').value = '';
    document.getElementById('salaryBonus').value = '';
    document.getElementById('salaryDeduction').value = '';
    document.getElementById('salaryDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('salaryNotes').value = '';
    document.getElementById('salaryNetText').textContent = 'Rp 0';
    
    // Load users into select
    const userSelect = document.getElementById('salaryUserId');
    userSelect.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
    if (window.usersData) {
        window.usersData.forEach(u => {
            if (u.role !== 'owner') {
                userSelect.innerHTML += `<option value="${u.id}">${u.full_name} (${u.role})</option>`;
            }
        });
    }
    RAZ.openModal('salaryModal');
}

function editSalary(id, userId, period, base, bonus, deduction, pDate, notes) {
    document.getElementById('salaryId').value = id;
    document.getElementById('salaryFormTitle').textContent = 'Edit Data Gaji';
    
    const userSelect = document.getElementById('salaryUserId');
    userSelect.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
    if (window.usersData) {
        window.usersData.forEach(u => {
            if (u.role !== 'owner') {
                userSelect.innerHTML += `<option value="${u.id}" ${u.id == userId ? 'selected' : ''}>${u.full_name} (${u.role})</option>`;
            }
        });
    }
    
    document.getElementById('salaryPeriod').value = period;
    document.getElementById('salaryBase').value = base;
    document.getElementById('salaryBonus').value = bonus;
    document.getElementById('salaryDeduction').value = deduction;
    document.getElementById('salaryDate').value = pDate;
    document.getElementById('salaryNotes').value = notes;
    calcNetSalary();
    RAZ.openModal('salaryModal');
}

function calcNetSalary() {
    const base = parseFloat(document.getElementById('salaryBase').value) || 0;
    const bonus = parseFloat(document.getElementById('salaryBonus').value) || 0;
    const deduction = parseFloat(document.getElementById('salaryDeduction').value) || 0;
    const net = base + bonus - deduction;
    document.getElementById('salaryNetText').textContent = RAZ.formatRupiah(net);
}

async function saveSalary() {
    const id = document.getElementById('salaryId').value;
    const user_id = document.getElementById('salaryUserId').value;
    const period_type = document.getElementById('salaryPeriod').value;
    const base_salary = document.getElementById('salaryBase').value;
    const bonus = document.getElementById('salaryBonus').value;
    const deduction = document.getElementById('salaryDeduction').value;
    const payment_date = document.getElementById('salaryDate').value;
    const notes = document.getElementById('salaryNotes').value;

    if (!user_id) return RAZ.error('Validasi Gagal', 'Silakan pilih karyawan');
    if (!base_salary || base_salary <= 0) return RAZ.error('Validasi Gagal', 'Gaji pokok harus diisi');

    const body = { id, user_id, period_type, base_salary, bonus, deduction, payment_date, notes };
    const res = await RAZ.api('api/RAZapiSalaries.php?action=save', { method: 'POST', body });
    if (res && res.success) {
        RAZ.success('Berhasil', res.message);
        RAZ.closeModal('salaryModal');
        loadSalaries();
    }
}

async function deleteSalary(id) {
    const ok = await RAZ.confirm({ title: 'Hapus Riwayat Gaji', message: 'Apakah Anda yakin? Data pengeluaran arus kas terkait juga akan dihapus.', confirmText: 'Ya, Hapus' });
    if (!ok) return;
    const res = await RAZ.api('api/RAZapiSalaries.php?action=delete', { method: 'POST', body: { id } });
    if (res && res.success) {
        RAZ.success('Dihapus', res.message);
        loadSalaries();
    }
}

async function printSalarySlip(id) {
    const data = await RAZ.api('api/RAZapiSalaries.php?action=get&id=' + id);
    if (!data.success) return RAZ.error('Gagal', 'Tidak dapat mengambil data slip gaji');

    const s = data.data.salary;
    const store = data.data.store;
    
    const logoHtml = store.store_logo 
        ? `<img src="${window.location.origin}/SIMAJURAZ/uploads/logos/${store.store_logo}" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">` 
        : '';

    const win = window.open('', '_blank', 'width=800,height=900');
    win.document.write(`<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Slip Gaji - ${s.employee_name}</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            @page { size: A5 landscape; margin: 10mm; }
            * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { font-family: 'Inter', sans-serif; color: #1e293b; font-size: 11px; margin: 0; padding: 20px; background: #fff; }
            
            .slip-wrapper { max-width: 100%; border: 1px solid #cbd5e1; padding: 20px; border-radius: 8px; position:relative; }
            .watermark { position:absolute; top:50%; left:50%; transform:translate(-50%, -50%) rotate(-30deg); font-size:60px; color:rgba(226, 232, 240, 0.4); font-weight:bold; pointer-events:none; z-index:0;}
            
            .kop-surat { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; z-index:1; position:relative; }
            .kop-left { text-align: left; }
            .kop-left h1 { font-size: 18px; color: #0f172a; margin: 0 0 5px 0; font-weight: 700; text-transform: uppercase; }
            .kop-left p { font-size: 10px; color: #475569; margin: 2px 0; }
            .kop-right { text-align: right; }
            .kop-right h2 { font-size: 20px; color: #0f172a; margin: 0; font-weight: 700; letter-spacing: 1px; }
            .kop-right .badge { display:inline-block; background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-weight:600; margin-top:5px; font-size:10px;}
            
            .emp-info { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; z-index:1; position:relative; }
            .info-row { margin-bottom: 4px; }
            .info-label { display: inline-block; width: 100px; color: #64748b; font-weight: 600; }
            .info-val { font-weight: 700; color: #1e293b; }
            
            .calc-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; z-index:1; position:relative;}
            .calc-table th { background: #1e293b; color: #fff; text-align: left; padding: 8px; font-size: 10px; text-transform: uppercase; }
            .calc-table td { padding: 8px; border-bottom: 1px solid #cbd5e1; }
            .amount { text-align: right; font-weight: 600; }
            .text-success { color: #059669; }
            .text-danger { color: #dc2626; }
            
            .net-row td { background: #f1f5f9; font-weight: 700; font-size: 14px; border-top: 2px solid #94a3b8; border-bottom: 2px solid #94a3b8; }
            
            .signature-section { display: flex; justify-content: space-between; margin-top: 20px; z-index:1; position:relative;}
            .signature-box { width: 180px; text-align: center; }
            .signature-title { font-size: 10px; margin-bottom: 50px; color: #475569; }
            .signature-name { font-weight: 700; text-decoration: underline; font-size: 11px; color: #0f172a; }
            .signature-role { font-size: 9px; color: #64748b; margin-top: 2px; }
            
            @media print { body { padding: 0; } .slip-wrapper{ border:none; padding:10px; } }
        </style>
    </head>
    <body>
        <div class="slip-wrapper">
            <div class="watermark">LUNAS</div>
            <div class="kop-surat">
                <div class="kop-left">
                    ${logoHtml}
                    <h1>${store.store_name}</h1>
                    <p>${store.store_address || ''}</p>
                    <p>${store.store_phone ? 'Telp/WA: ' + store.store_phone : ''}</p>
                </div>
                <div class="kop-right">
                    <h2>SLIP GAJI</h2>
                    <div class="badge">PERIODE: ${s.period_type.toUpperCase()}</div>
                </div>
            </div>
            
            <div class="emp-info">
                <div>
                    <div class="info-row"><span class="info-label">ID Karyawan</span> <span class="info-val">: EMP-${s.user_id.toString().padStart(4,'0')}</span></div>
                    <div class="info-row"><span class="info-label">Nama Lengkap</span> <span class="info-val">: ${s.employee_name}</span></div>
                    <div class="info-row"><span class="info-label">Posisi / Role</span> <span class="info-val">: ${s.employee_role.toUpperCase()}</span></div>
                </div>
                <div>
                    <div class="info-row"><span class="info-label">No. Slip</span> <span class="info-val">: SLP-${s.id.toString().padStart(5,'0')}</span></div>
                    <div class="info-row"><span class="info-label">Tanggal Bayar</span> <span class="info-val">: ${s.payment_date}</span></div>
                    <div class="info-row"><span class="info-label">Catatan</span> <span class="info-val">: ${s.notes || '-'}</span></div>
                </div>
            </div>
            
            <table class="calc-table">
                <thead>
                    <tr><th style="width: 70%">Deskripsi Pendapatan & Potongan</th><th style="width: 30%; text-align:right;">Jumlah (Rp)</th></tr>
                </thead>
                <tbody>
                    <tr><td>Gaji Pokok (${s.period_type})</td><td class="amount text-success">${RAZ.formatRupiah(s.base_salary)}</td></tr>
                    ${s.bonus > 0 ? `<tr><td>Bonus / Tunjangan Tambahan</td><td class="amount text-success">${RAZ.formatRupiah(s.bonus)}</td></tr>` : ''}
                    ${s.deduction > 0 ? `<tr><td>Potongan / Kasbon</td><td class="amount text-danger">-${RAZ.formatRupiah(s.deduction)}</td></tr>` : ''}
                    <tr class="net-row"><td>TOTAL GAJI BERSIH (TAKE HOME PAY)</td><td class="amount" style="color:#0f172a;">${RAZ.formatRupiah(s.net_salary)}</td></tr>
                </tbody>
            </table>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-title">Penerima Gaji,</div>
                    <div class="signature-name">${s.employee_name}</div>
                    <div class="signature-role">${s.employee_role.toUpperCase()}</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Mengetahui & Disetujui,</div>
                    <div class="signature-name">${store.store_name}</div>
                    <div class="signature-role">Manajemen / Owner</div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function() { setTimeout(()=>{window.print();},800); }
        </script>
    </body>
    </html>`);
    win.document.close();
}

