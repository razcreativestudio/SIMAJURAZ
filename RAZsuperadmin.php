<?php
require_once 'RAZconfig.php';
require_once 'includes/RAZsession.php';
require_once 'includes/RAZhelpers.php';
RAZrequireRole(['superadmin']);

$pdo = RAZgetConnection();

// Ambil Statistik
$totalStores = $pdo->query("SELECT COUNT(*) FROM stores")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
$bannedUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn();

// Ambil semua pengguna
$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name, u.role, u.is_active, s.store_name 
    FROM users u 
    LEFT JOIN stores s ON u.store_id = s.id 
    ORDER BY u.role DESC, u.created_at DESC
");
$usersList = $stmt->fetchAll();

// Ambil Activity Logs terbaru
$logsStmt = $pdo->query("
    SELECT a.*, u.username, s.store_name 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.id 
    LEFT JOIN stores s ON a.store_id = s.id 
    ORDER BY a.created_at DESC 
    LIMIT 100
");
$activityLogs = $logsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>God Mode - Super Admin | SIMAJURAZ</title>
    
    <!-- Phosphor Icons -->
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZDashboard.css">
    <style>
        .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary) !important; }
        .action-btn { background: none; border: none; cursor: pointer; color: var(--text-muted); transition: color 0.2s; padding: 4px; }
        .action-btn:hover { color: var(--text-light); }
        .action-btn.ban:hover { color: var(--warning); }
        .action-btn.delete:hover { color: var(--danger); }
        .action-btn.key:hover { color: var(--primary); }
    </style>
</head>
<body data-role="superadmin">
    <div class="raz-app">
        <!-- ============================
             SIDEBAR NAVIGASI
             ============================ -->
        <aside class="raz-sidebar" id="sidebar">
            <div class="raz-sidebar-logo">
                <div style="width:36px;height:36px;background:rgba(255,255,255,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                    <i class="ph-bold ph-shield-check" style="color:#fff"></i>
                </div>
                <span>SIMAJURAZ</span>
            </div>
            <ul class="raz-sidebar-menu">
                <li class="raz-sidebar-section">Administrasi</li>
                <li><a href="RAZdashboard.php"><span class="menu-icon"><i class="ph-bold ph-squares-four"></i></span><span class="menu-text">Dashboard</span></a></li>
                <li><a href="RAZsuperadmin.php" class="active"><span class="menu-icon"><i class="ph-bold ph-shield-check"></i></span><span class="menu-text">User Management</span></a></li>
                <li><a href="RAZlogout.php"><span class="menu-icon"><i class="ph-bold ph-sign-out"></i></span><span class="menu-text">Logout</span></a></li>
            </ul>
            <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
        </aside>

        <main class="raz-main">
            <header class="raz-topbar">
                <div class="raz-topbar-left">
                    <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                    <h1 class="raz-topbar-title">Command Center</h1>
                </div>
                <div class="raz-topbar-right">
                    <!-- User Dropdown -->
                    <div style="position:relative;">
                        <button class="raz-topbar-user" onclick="toggleUserDropdown()">
                            <div class="raz-topbar-avatar"><?= strtoupper(substr($_SESSION['raz_full_name'] ?? 'Admin', 0, 2)) ?></div>
                            <div class="raz-topbar-info">
                                <span class="raz-topbar-name"><?= htmlspecialchars($_SESSION['raz_full_name'] ?? 'Admin Pusat') ?></span>
                                <span class="raz-topbar-role">Super Admin</span>
                            </div>
                            <i class="ph-bold ph-caret-down" style="color:var(--raz-text-muted);font-size:0.8rem;"></i>
                        </button>
                        <div class="raz-dropdown" id="userDropdown">
                            <a href="RAZlogout.php" class="danger">
                                <i class="ph-bold ph-sign-out"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="raz-content">

        <!-- Stats -->
        <div class="raz-stats" style="margin-bottom: 30px;">
            <div class="raz-stat-card">
                <div class="raz-stat-icon primary"><i class="ph-bold ph-storefront"></i></div>
                <div><div class="raz-stat-value"><?= $totalStores ?></div><div class="raz-stat-label">Total Tenant</div></div>
            </div>
            <div class="raz-stat-card">
                <div class="raz-stat-icon success"><i class="ph-bold ph-users"></i></div>
                <div><div class="raz-stat-value"><?= $totalUsers ?></div><div class="raz-stat-label">Total Pengguna</div></div>
            </div>
            <div class="raz-stat-card">
                <div class="raz-stat-icon danger"><i class="ph-bold ph-prohibit"></i></div>
                <div><div class="raz-stat-value"><?= $bannedUsers ?></div><div class="raz-stat-label">Akun Dibanned</div></div>
            </div>
        </div>

        <!-- Tabs -->
        <div style="border-bottom: 1px solid var(--border); margin-bottom: 20px;">
            <button class="tab-btn active" onclick="switchTab('users')" style="background: transparent; border: none; color: var(--text-muted); padding: 10px 20px; font-size: 1rem; cursor: pointer; border-bottom: 2px solid transparent;"><i class="ph ph-users"></i> Daftar Pengguna</button>
            <button class="tab-btn" onclick="switchTab('logs')" style="background: transparent; border: none; color: var(--text-muted); padding: 10px 20px; font-size: 1rem; cursor: pointer; border-bottom: 2px solid transparent;"><i class="ph ph-activity"></i> Activity Logs (Audit)</button>
        </div>

        <!-- Users Tab -->
        <div id="tab-users" class="tab-content" style="display: block;">
            <div class="raz-card">
                <div class="raz-table-wrapper">
                    <table class="raz-table">
                        <thead>
                            <tr>
                                <th>Toko / Cabang</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usersList as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['store_name'] ?? 'Sistem Pusat') ?></td>
                                <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                <td><?= htmlspecialchars($u['full_name']) ?></td>
                                <td>
                                    <?php
                                        $rc = 'bg-gray';
                                        if($u['role'] == 'superadmin') $rc = 'bg-primary';
                                        if($u['role'] == 'owner') $rc = 'bg-green';
                                        echo "<span class='badge {$rc}'>" . strtoupper($u['role']) . "</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php if($u['is_active']): ?>
                                        <span class="badge bg-green">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-red">Banned</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if($u['role'] !== 'superadmin'): ?>
                                    <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" style="color:var(--primary);" title="Force Reset Password" onclick="resetPassword(<?= $u['id'] ?>)"><i class="ph-bold ph-key"></i></button>
                                    <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" style="color:var(--warning);" title="<?= $u['is_active'] ? 'Ban User' : 'Unban User' ?>" onclick="toggleBan(<?= $u['id'] ?>, <?= $u['is_active'] ?>)"><i class="ph-bold ph-prohibit"></i></button>
                                    <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" style="color:var(--danger);" title="Delete User" onclick="deleteUser(<?= $u['id'] ?>)"><i class="ph-bold ph-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Logs Tab -->
        <div id="tab-logs" class="tab-content" style="display: none;">
            <div class="raz-card">
                <div class="raz-table-wrapper">
                    <table class="raz-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna (Toko)</th>
                                <th>Aksi</th>
                                <th>Detail Tambahan</th>
                                <th>IP & Info Perangkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($activityLogs as $log): 
                                $act = strtoupper($log['action']);
                                $badgeClass = 'primary'; // Default blue
                                if (strpos($act, 'DELETE') !== false || strpos($act, 'BAN') !== false || strpos($act, 'FAIL') !== false) {
                                    $badgeClass = 'danger'; // Red
                                } elseif (strpos($act, 'SUCCESS') !== false || strpos($act, 'UNBAN') !== false || strpos($act, 'RESET') !== false) {
                                    $badgeClass = 'success'; // Green
                                }
                            ?>
                            <tr>
                                <td style="white-space: nowrap;"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                                <td><strong><?= htmlspecialchars($log['username'] ?? 'UNKNOWN') ?></strong><br><small><?= htmlspecialchars($log['store_name'] ?? '-') ?></small></td>
                                <td><span class="raz-badge <?= $badgeClass ?>"><?= htmlspecialchars($log['action']) ?></span></td>
                                <td><?= htmlspecialchars($log['details']) ?></td>
                                <td>
                                    <span style="color: #3b82f6; font-family: monospace;"><?= htmlspecialchars($log['ip_address']) ?></span>
                                    <p style="font-size: 0.75rem; color: #64748b; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($log['user_agent']) ?>"><?= htmlspecialchars($log['user_agent']) ?></p>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

            </div> <!-- /raz-content -->
        </main>
    </div> <!-- /raz-app -->

    <!-- Modal Reset Password -->
    <div class="raz-modal-overlay" id="resetModal">
        <div class="raz-modal modal-sm">
            <div class="raz-modal-header">
                <div class="raz-modal-title"><i class="ph-bold ph-key modal-icon"></i> Force Reset Password</div>
                <button class="raz-modal-close" onclick="RAZ.closeModal('resetModal')"><i class="ph-bold ph-x"></i></button>
            </div>
            <div class="raz-modal-body">
                <form id="resetForm">
                    <input type="hidden" id="resetUserId" name="user_id">
                    <div class="raz-form-group">
                        <label class="raz-form-label">Password Baru <span class="required">*</span></label>
                        <input type="text" class="raz-form-input" name="new_password" required placeholder="Masukkan password baru yang kuat">
                        <small style="color: var(--danger); font-size: 0.8rem; margin-top: 5px; display: block;">Peringatan: Password akan langsung terganti dan user akan ter-logout dari semua sesi jika token direfresh.</small>
                    </div>
                </form>
            </div>
            <div class="raz-modal-footer">
                <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('resetModal')">Batal</button>
                <button class="raz-btn raz-btn-danger" onclick="submitReset()"><i class="ph-bold ph-floppy-disk"></i> Simpan Password</button>
            </div>
        </div>
    </div>

    <script src="assets/js/RAZMain.js"></script>
    <script>
        // User Dropdown
        function toggleUserDropdown() {
            const dd = document.getElementById('userDropdown');
            if (dd) dd.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const btn = document.querySelector('.raz-topbar-user');
            const dd = document.getElementById('userDropdown');
            if(dd && btn && !btn.contains(event.target) && !dd.contains(event.target)) {
                dd.classList.remove('show');
            }
        });

        // Tampilkan tombol menu mobile di viewport kecil
        const checkMobile = () => {
            const btn = document.getElementById('mobileMenuBtn');
            if (btn) btn.style.display = window.innerWidth <= 1024 ? 'flex' : 'none';
        };
        checkMobile();
        window.addEventListener('resize', checkMobile);

        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
            
            event.target.classList.add('active');
            document.getElementById('tab-' + tabId).style.display = 'block';
        }

        async function toggleBan(userId, currentStatus) {
            let isBanning = currentStatus == 1;
            let confirmTitle = isBanning ? "Blokir Pengguna?" : "Buka Blokir Pengguna?";
            let confirmMsg = isBanning ? 
                "Apakah Anda yakin ingin memblokir (Ban) user ini? Mereka tidak akan bisa login." : 
                "Apakah Anda yakin ingin membuka blokir (Unban) user ini? Mereka akan bisa login kembali.";
            let type = isBanning ? 'danger' : 'success';
            let btnText = isBanning ? 'Ya, Blokir' : 'Ya, Buka Blokir';
            
            const confirmed = await RAZ.confirm({
                title: confirmTitle,
                message: confirmMsg,
                type: type,
                confirmText: btnText
            });

            if(!confirmed) return;

            try {
                let res = await fetch('api/RAZapiSuperAdmin.php?action=toggle_ban', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ user_id: userId, is_active: currentStatus == 1 ? 0 : 1 })
                });
                let data = await res.json();
                if(data.success) location.reload();
                else RAZ.error('Gagal', data.message);
            } catch(e) {
                RAZ.error('Error Jaringan', 'Terjadi kesalahan jaringan');
            }
        }

        function resetPassword(userId) {
            document.getElementById('resetUserId').value = userId;
            RAZ.openModal('resetModal');
        }

        async function submitReset() {
            let userId = document.getElementById('resetUserId').value;
            let newPass = document.querySelector('input[name="new_password"]').value;

            if(!newPass) { RAZ.warning('Peringatan', "Password tidak boleh kosong"); return; }

            try {
                let res = await fetch('api/RAZapiSuperAdmin.php?action=reset_password', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ user_id: userId, new_password: newPass })
                });
                let data = await res.json();
                if(data.success) {
                    RAZ.success('Berhasil', data.message);
                    RAZ.closeModal('resetModal');
                } else {
                    RAZ.error('Gagal', data.message);
                }
            } catch(e) {
                RAZ.error('Error Jaringan', "Terjadi kesalahan jaringan saat me-reset password.");
            }
        }

        async function deleteUser(userId) {
            const confirmed = await RAZ.confirm({
                title: 'Hapus Permanen?',
                message: 'BAHAYA: Menghapus user ini bersifat permanen dan tidak bisa dikembalikan! Apakah Anda yakin?',
                type: 'danger',
                confirmText: 'Ya, Hapus Permanen'
            });

            if(!confirmed) return;

            try {
                let res = await fetch('api/RAZapiSuperAdmin.php?action=delete_user', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ user_id: userId })
                });
                let data = await res.json();
                if(data.success) location.reload();
                else RAZ.error('Gagal', data.message);
            } catch(e) {
                RAZ.error('Error Jaringan', 'Terjadi kesalahan jaringan');
            }
        }
    </script>
</body>
</html>
