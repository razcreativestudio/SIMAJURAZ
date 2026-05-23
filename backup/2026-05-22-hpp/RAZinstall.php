<?php
/**
 * ============================================================
 * RAZinstall.php — Wizard Instalasi SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Halaman GUI untuk setup awal aplikasi.
 *               - Memilih tipe database (SQLite/MySQL)
 *               - Membuat tabel-tabel database (DDL)
 *               - Membuat akun Super Admin pertama
 *               Setelah instalasi selesai, file ini tidak bisa
 *               diakses lagi (redirect ke index.php).
 * ============================================================
 */

// Jika sudah terinstall, redirect ke halaman utama
require_once __DIR__ . '/RAZconfig.php';
if (RAZisInstalled()) {
    header('Location: index.php');
    exit;
}

// ============================================================
// PROSES INSTALASI (Handle POST Request)
// ============================================================
$installError = '';
$installSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbType = $_POST['db_type'] ?? 'sqlite';
    $adminUser = trim($_POST['admin_username'] ?? '');
    $adminPass = $_POST['admin_password'] ?? '';
    $adminName = trim($_POST['admin_fullname'] ?? 'Super Admin');

    // Validasi input Super Admin
    if (empty($adminUser) || empty($adminPass)) {
        $installError = 'Username dan password Super Admin wajib diisi!';
    } elseif (strlen($adminPass) < 6) {
        $installError = 'Password minimal 6 karakter!';
    } else {
        try {
            // === BUAT KONEKSI DATABASE ===
            if ($dbType === 'sqlite') {
                // Pastikan folder data ada
                if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
                $pdo = new PDO('sqlite:' . RAZ_SQLITE_FILE);
                $pdo->exec('PRAGMA journal_mode=WAL');
                $pdo->exec('PRAGMA foreign_keys=ON');
            } else {
                // MySQL: ambil kredensial dari form
                $dbHost = $_POST['db_host'] ?? 'localhost';
                $dbPort = $_POST['db_port'] ?? '3306';
                $dbName = $_POST['db_name'] ?? 'simajuraz';
                $dbUser = $_POST['db_user'] ?? 'root';
                $dbPass = $_POST['db_pass'] ?? '';

                $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbUser, $dbPass);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");
            }

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // === DDL: BUAT TABEL-TABEL ===
            // Deteksi SQL syntax berdasarkan tipe database
            $autoInc = ($dbType === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
            $timestamp = ($dbType === 'sqlite') ? "TEXT DEFAULT (datetime('now','localtime'))" : "DATETIME DEFAULT CURRENT_TIMESTAMP";
            $decimalType = 'DECIMAL(15,2)';
            $textType = 'TEXT';
            $enumRole = ($dbType === 'sqlite') ? "TEXT CHECK(role IN ('superadmin','owner','employee'))" : "ENUM('superadmin','owner','employee')";
            $enumPayment = ($dbType === 'sqlite') ? "TEXT CHECK(payment_method IN ('cash','transfer','qris'))" : "ENUM('cash','transfer','qris')";
            $enumStatus = ($dbType === 'sqlite') ? "TEXT CHECK(status IN ('completed','voided'))" : "ENUM('completed','voided')";
            $enumCashflow = ($dbType === 'sqlite') ? "TEXT CHECK(type IN ('income','expense'))" : "ENUM('income','expense')";

            // Tabel Users (Pengguna)
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id {$autoInc},
                store_id INT DEFAULT NULL,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                full_name VARCHAR(100) NOT NULL,
                role {$enumRole} NOT NULL DEFAULT 'employee',
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at {$timestamp},
                updated_at {$timestamp}
            )");

            // Tabel Stores (Toko/Tenant)
            $pdo->exec("CREATE TABLE IF NOT EXISTS stores (
                id {$autoInc},
                owner_id INT NOT NULL,
                store_name VARCHAR(100) NOT NULL,
                store_description {$textType},
                store_type VARCHAR(50),
                store_address {$textType},
                store_phone VARCHAR(20),
                store_logo VARCHAR(255),
                tax_percentage {$decimalType} DEFAULT 0,
                receipt_header {$textType},
                receipt_footer {$textType},
                receipt_template INT DEFAULT 1,
                receipt_show_logo TINYINT(1) DEFAULT 1,
                created_at {$timestamp}
            )");

            // Tabel Categories (Kategori Barang)
            $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
                id {$autoInc},
                store_id INT NOT NULL,
                name VARCHAR(50) NOT NULL,
                color VARCHAR(7) DEFAULT '#4F46E5',
                created_at {$timestamp}
            )");

            // Tabel Items (Barang)
            $pdo->exec("CREATE TABLE IF NOT EXISTS items (
                id {$autoInc},
                store_id INT NOT NULL,
                category_id INT DEFAULT NULL,
                name VARCHAR(100) NOT NULL,
                sku VARCHAR(50),
                hpp {$decimalType} NOT NULL DEFAULT 0,
                sell_price {$decimalType} NOT NULL DEFAULT 0,
                stock INT NOT NULL DEFAULT 0,
                min_stock INT NOT NULL DEFAULT 5,
                image VARCHAR(255),
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at {$timestamp},
                updated_at {$timestamp}
            )");

            // Tabel Transactions (Transaksi)
            $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
                id {$autoInc},
                store_id INT NOT NULL,
                user_id INT NOT NULL,
                invoice_number VARCHAR(30) NOT NULL UNIQUE,
                subtotal {$decimalType} DEFAULT 0,
                discount_amount {$decimalType} DEFAULT 0,
                tax_amount {$decimalType} DEFAULT 0,
                grand_total {$decimalType} DEFAULT 0,
                payment_method {$enumPayment} DEFAULT 'cash',
                amount_paid {$decimalType} DEFAULT 0,
                change_amount {$decimalType} DEFAULT 0,
                status {$enumStatus} DEFAULT 'completed',
                created_at {$timestamp}
            )");

            // Tabel Transaction Items (Detail Transaksi)
            $pdo->exec("CREATE TABLE IF NOT EXISTS transaction_items (
                id {$autoInc},
                transaction_id INT NOT NULL,
                item_id INT NOT NULL,
                item_name VARCHAR(100),
                qty INT NOT NULL DEFAULT 1,
                hpp {$decimalType} DEFAULT 0,
                sell_price {$decimalType} DEFAULT 0,
                subtotal {$decimalType} DEFAULT 0
            )");

            // Tabel Cash Flows (Arus Kas)
            $pdo->exec("CREATE TABLE IF NOT EXISTS cash_flows (
                id {$autoInc},
                store_id INT NOT NULL,
                user_id INT NOT NULL,
                type {$enumCashflow} NOT NULL,
                category VARCHAR(50),
                amount {$decimalType} NOT NULL DEFAULT 0,
                description {$textType},
                created_at {$timestamp}
            )");

            // Tabel Shifts (Shift Kasir)
            $pdo->exec("CREATE TABLE IF NOT EXISTS shifts (
                id {$autoInc},
                store_id INT NOT NULL,
                user_id INT NOT NULL,
                opening_cash {$decimalType} DEFAULT 0,
                closing_cash {$decimalType} DEFAULT NULL,
                opened_at {$timestamp},
                closed_at TEXT DEFAULT NULL,
                notes {$textType}
            )");

            // Tabel Profit Shares (Penerima Bagi Hasil)
            // Menyimpan daftar penerima bagi hasil per toko beserta persentasenya
            $pdo->exec("CREATE TABLE IF NOT EXISTS profit_shares (
                id {$autoInc},
                store_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                role_label VARCHAR(50) DEFAULT 'custom',
                percentage {$decimalType} NOT NULL DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                sort_order INT DEFAULT 0,
                created_at {$timestamp}
            )");

            // Tabel Profit Share Reports (Riwayat Laporan Bagi Hasil)
            // Menyimpan snapshot distribusi saat laporan di-generate
            $pdo->exec("CREATE TABLE IF NOT EXISTS profit_share_reports (
                id {$autoInc},
                store_id INT NOT NULL,
                period_from TEXT NOT NULL,
                period_to TEXT NOT NULL,
                net_profit {$decimalType} DEFAULT 0,
                distribution_json {$textType},
                notes {$textType},
                created_by INT,
                created_at {$timestamp}
            )");

            // === INSERT SUPER ADMIN ===
            $hashedPass = password_hash($adminPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, store_id) VALUES (?, ?, ?, 'superadmin', NULL)");
            $stmt->execute([$adminUser, $hashedPass, $adminName]);

            // === SIMPAN KONFIGURASI KE FILE JSON ===
            $config = ['db_type' => $dbType, 'installed_at' => date('Y-m-d H:i:s')];
            if ($dbType === 'mysql') {
                $config['db_host'] = $dbHost;
                $config['db_port'] = $dbPort;
                $config['db_name'] = $dbName;
                $config['db_user'] = $dbUser;
                $config['db_pass'] = $dbPass;
            }

            if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);
            file_put_contents(RAZ_CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT));

            $installSuccess = true;

        } catch (PDOException $e) {
            $installError = 'Database Error: ' . $e->getMessage();
        } catch (Exception $e) {
            $installError = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi — SIMAJURAZ</title>
    <meta name="description" content="Setup wizard untuk instalasi awal SIMAJURAZ POS System">
    <!-- Phosphor Icons -->
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <style>
        /* Style khusus halaman instalasi */
        .install-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1E1B4B 0%, #312E81 40%, #4F46E5 100%);
            padding: 24px;
        }
        .install-card {
            background: var(--raz-card);
            border-radius: var(--raz-radius-lg);
            box-shadow: var(--raz-shadow-xl);
            width: 100%;
            max-width: 560px;
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #312E81, #4F46E5);
            color: #fff;
            padding: 32px;
            text-align: center;
        }
        .install-header h1 { color: #fff; font-size: 1.75rem; margin-bottom: 6px; }
        .install-header p { color: rgba(255,255,255,0.7); font-size: 0.9rem; }
        .install-logo {
            width: 64px; height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: var(--raz-radius-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 16px;
        }
        .install-body { padding: 32px; }
        .install-footer { padding: 0 32px 24px; text-align: center; }
        .install-footer p { font-size: 0.8rem; color: var(--raz-text-muted); }

        /* Step indicator */
        .install-steps { display: flex; gap: 4px; margin-bottom: 28px; }
        .install-step {
            flex: 1; height: 4px;
            background: var(--raz-bg-dark);
            border-radius: 2px;
            transition: var(--raz-transition-slow);
        }
        .install-step.active { background: var(--raz-primary); }
        .install-step.done { background: var(--raz-success); }

        /* DB Type selector */
        .db-type-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .db-type-card {
            border: 2px solid var(--raz-border);
            border-radius: var(--raz-radius-md);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--raz-transition);
        }
        .db-type-card:hover { border-color: var(--raz-primary-light); }
        .db-type-card.selected {
            border-color: var(--raz-primary);
            background: var(--raz-primary-bg);
        }
        .db-type-card .db-icon { font-size: 2rem; margin-bottom: 8px; color: var(--raz-primary); }
        .db-type-card .db-name { font-weight: 600; font-size: 0.95rem; }
        .db-type-card .db-desc { font-size: 0.78rem; color: var(--raz-text-muted); margin-top: 4px; }

        /* MySQL fields */
        .mysql-fields { display: none; }
        .mysql-fields.show { display: block; }
        .mysql-fields .field-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; }

        /* Steps */
        .install-step-content { display: none; }
        .install-step-content.active { display: block; }

        /* Alert */
        .install-alert {
            padding: 14px 18px;
            border-radius: var(--raz-radius);
            margin-bottom: 20px;
            font-size: 0.875rem;
            display: flex; align-items: center; gap: 10px;
        }
        .install-alert.error { background: var(--raz-danger-light); color: var(--raz-danger); }
        .install-alert.success { background: var(--raz-success-light); color: var(--raz-success); }

        /* Success screen */
        .install-success { text-align: center; padding: 20px 0; }
        .install-success .success-icon {
            width: 80px; height: 80px;
            background: var(--raz-success-light);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 2.5rem; color: var(--raz-success);
            animation: razBounce 0.6s ease;
        }
        @keyframes razBounce { 0% { transform: scale(0); } 60% { transform: scale(1.1); } 100% { transform: scale(1); } }

        /* Form styling inline */
        .install-body .raz-form-group { margin-bottom: 16px; }
        .install-body label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--raz-text-secondary); margin-bottom: 6px; }
        .install-body label .required { color: var(--raz-danger); }
        .install-body input, .install-body select {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--raz-border); border-radius: var(--raz-radius);
            font-family: var(--raz-font); font-size: 0.9rem; color: var(--raz-text);
            outline: none; transition: var(--raz-transition);
        }
        .install-body input:focus { border-color: var(--raz-primary); box-shadow: 0 0 0 3px var(--raz-primary-bg); }

        .raz-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; border: none; border-radius: var(--raz-radius); font-family: var(--raz-font); font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: var(--raz-transition); }
        .raz-btn-primary { background: var(--raz-primary); color: #fff; }
        .raz-btn-primary:hover { background: var(--raz-primary-dark); }
        .raz-btn-success { background: var(--raz-success); color: #fff; }
        .raz-btn-secondary { background: var(--raz-bg); color: var(--raz-text-secondary); border: 1px solid var(--raz-border); }
        .raz-btn-block { width: 100%; }
    </style>
</head>
<body>
    <div class="install-page">
        <div class="install-card">
            <!-- Header -->
            <div class="install-header">
                <div class="install-logo"><i class="ph-bold ph-storefront"></i></div>
                <h1>SIMAJURAZ</h1>
                <p>Sistem Manajemen Jualan — Setup Wizard</p>
            </div>

            <div class="install-body">
                <?php if ($installSuccess): ?>
                    <!-- ==================== SUKSES ==================== -->
                    <div class="install-steps">
                        <div class="install-step done"></div>
                        <div class="install-step done"></div>
                        <div class="install-step done"></div>
                    </div>
                    <div class="install-success">
                        <div class="success-icon"><i class="ph-bold ph-check-circle"></i></div>
                        <h2>Instalasi Berhasil! 🎉</h2>
                        <p style="color:var(--raz-text-muted); margin: 12px 0 24px;">Database dan akun Super Admin telah dibuat.</p>
                        <a href="index.php" class="raz-btn raz-btn-success raz-btn-block">
                            <i class="ph-bold ph-sign-in"></i> Mulai Login
                        </a>
                    </div>
                <?php else: ?>
                    <!-- ==================== FORM INSTALASI ==================== -->
                    <div class="install-steps">
                        <div class="install-step active" id="step1"></div>
                        <div class="install-step" id="step2"></div>
                        <div class="install-step" id="step3"></div>
                    </div>

                    <?php if ($installError): ?>
                        <div class="install-alert error">
                            <i class="ph-bold ph-warning-circle"></i> <?= htmlspecialchars($installError) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="installForm">
                        <!-- STEP 1: Pilih Database -->
                        <div class="install-step-content active" id="stepContent1">
                            <h3 style="margin-bottom:4px;">Pilih Tipe Database</h3>
                            <p style="font-size:0.85rem; color:var(--raz-text-muted); margin-bottom:20px;">Pilih mode penyimpanan data untuk aplikasi.</p>

                            <div class="db-type-grid">
                                <div class="db-type-card selected" onclick="selectDbType('sqlite')">
                                    <div class="db-icon"><i class="ph-bold ph-hard-drive"></i></div>
                                    <div class="db-name">SQLite</div>
                                    <div class="db-desc">Internal & Portable. Cocok untuk satu perangkat.</div>
                                </div>
                                <div class="db-type-card" onclick="selectDbType('mysql')">
                                    <div class="db-icon"><i class="ph-bold ph-database"></i></div>
                                    <div class="db-name">MySQL</div>
                                    <div class="db-desc">Eksternal & Skalabel. Cocok untuk server.</div>
                                </div>
                            </div>
                            <input type="hidden" name="db_type" id="dbTypeInput" value="sqlite">

                            <!-- MySQL fields (hidden by default) -->
                            <div class="mysql-fields" id="mysqlFields">
                                <div class="field-grid">
                                    <div class="raz-form-group">
                                        <label>Host</label>
                                        <input type="text" name="db_host" value="localhost" placeholder="localhost">
                                    </div>
                                    <div class="raz-form-group">
                                        <label>Port</label>
                                        <input type="text" name="db_port" value="3306" placeholder="3306">
                                    </div>
                                </div>
                                <div class="raz-form-group">
                                    <label>Nama Database</label>
                                    <input type="text" name="db_name" value="simajuraz" placeholder="simajuraz">
                                </div>
                                <div class="field-grid">
                                    <div class="raz-form-group">
                                        <label>Username</label>
                                        <input type="text" name="db_user" value="root" placeholder="root">
                                    </div>
                                    <div class="raz-form-group">
                                        <label>Password</label>
                                        <input type="password" name="db_pass" placeholder="Kosongkan jika tidak ada">
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="raz-btn raz-btn-primary raz-btn-block" onclick="goToStep(2)">
                                Lanjutkan <i class="ph-bold ph-arrow-right"></i>
                            </button>
                        </div>

                        <!-- STEP 2: Akun Super Admin -->
                        <div class="install-step-content" id="stepContent2">
                            <h3 style="margin-bottom:4px;">Buat Akun Super Admin</h3>
                            <p style="font-size:0.85rem; color:var(--raz-text-muted); margin-bottom:20px;">Akun ini akan mengelola infrastruktur aplikasi.</p>

                            <div class="raz-form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="admin_fullname" placeholder="Contoh: Administrator" required>
                            </div>
                            <div class="raz-form-group">
                                <label>Username <span class="required">*</span></label>
                                <input type="text" name="admin_username" placeholder="Contoh: admin" required>
                            </div>
                            <div class="raz-form-group">
                                <label>Password <span class="required">*</span> <small style="color:var(--raz-text-muted)">(min. 6 karakter)</small></label>
                                <input type="password" name="admin_password" placeholder="Masukkan password" required minlength="6">
                            </div>

                            <div style="display:flex; gap:10px;">
                                <button type="button" class="raz-btn raz-btn-secondary" onclick="goToStep(1)" style="flex:1">
                                    <i class="ph-bold ph-arrow-left"></i> Kembali
                                </button>
                                <button type="submit" class="raz-btn raz-btn-primary" style="flex:2">
                                    <i class="ph-bold ph-rocket-launch"></i> Install Sekarang
                                </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="install-footer">
                <p>SIMAJURAZ v<?= RAZ_VERSION ?> — RAZ Creative Studio © <?= date('Y') ?></p>
            </div>
        </div>
    </div>

    <script>
        // Pilih tipe database (SQLite atau MySQL)
        function selectDbType(type) {
            document.getElementById('dbTypeInput').value = type;
            document.querySelectorAll('.db-type-card').forEach(c => c.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
            
            const mysqlFields = document.getElementById('mysqlFields');
            if (type === 'mysql') {
                mysqlFields.classList.add('show');
            } else {
                mysqlFields.classList.remove('show');
            }
        }

        // Navigasi antar step
        function goToStep(step) {
            // Update step indicators
            document.querySelectorAll('.install-step').forEach((s, i) => {
                s.classList.remove('active', 'done');
                if (i < step - 1) s.classList.add('done');
                if (i === step - 1) s.classList.add('active');
            });

            // Toggle konten step
            document.querySelectorAll('.install-step-content').forEach(c => c.classList.remove('active'));
            const target = document.getElementById('stepContent' + step);
            if (target) target.classList.add('active');
        }
    </script>
</body>
</html>
