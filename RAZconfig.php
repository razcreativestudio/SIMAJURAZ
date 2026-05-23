<?php
/**
 * ============================================================
 * RAZconfig.php — Koneksi Database Dinamis SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : File ini menangani koneksi database secara dinamis.
 *               Mendukung dua mode: SQLite (internal/portable) dan
 *               MySQL/MariaDB (eksternal/skala besar).
 *               Konfigurasi disimpan di file data/RAZdbconfig.json
 *               yang dibuat oleh RAZinstall.php.
 * ============================================================
 */

// Lokasi file konfigurasi database (dibuat oleh installer)
define('RAZ_CONFIG_FILE', __DIR__ . '/data/RAZdbconfig.json');

// Lokasi file database SQLite (jika mode internal)
define('RAZ_SQLITE_FILE', __DIR__ . '/data/simajuraz.sqlite');

// Versi aplikasi
define('RAZ_VERSION', '1.0.0');

// Nama aplikasi
define('RAZ_APP_NAME', 'SIMAJURAZ');

// Deskripsi aplikasi
define('RAZ_APP_DESC', 'Sistem Manajemen Jualan oleh RAZ Creative Studio');

/**
 * Fungsi untuk mendapatkan koneksi PDO ke database.
 * Akan membaca konfigurasi dari file JSON yang dibuat installer.
 * 
 * @return PDO|null Objek koneksi PDO atau null jika gagal
 */
function RAZgetConnection() {
    // Variabel statis agar koneksi di-reuse (singleton pattern)
    static $pdo = null;
    
    // Jika sudah ada koneksi, langsung kembalikan (hemat resource)
    if ($pdo !== null) {
        return $pdo;
    }

    // Cek apakah file konfigurasi ada (berarti sudah di-install)
    if (!file_exists(RAZ_CONFIG_FILE)) {
        return null; // Belum di-install
    }

    // Baca konfigurasi database dari file JSON
    $configJson = file_get_contents(RAZ_CONFIG_FILE);
    $config = json_decode($configJson, true);

    // Validasi format konfigurasi
    if (!$config || !isset($config['db_type'])) {
        return null; // Format konfigurasi tidak valid
    }

    try {
        // Pilih mode koneksi berdasarkan tipe database
        if ($config['db_type'] === 'sqlite') {
            // === MODE SQLITE (Internal/Portable) ===
            // Koneksi ke file SQLite lokal
            $pdo = new PDO('sqlite:' . RAZ_SQLITE_FILE);
            
            // Aktifkan WAL mode untuk performa lebih baik
            $pdo->exec('PRAGMA journal_mode=WAL');
            
            // Aktifkan foreign key constraint di SQLite
            $pdo->exec('PRAGMA foreign_keys=ON');
            
        } elseif ($config['db_type'] === 'mysql') {
            // === MODE MYSQL (Eksternal/Skala Besar) ===
            // Bangun DSN (Data Source Name) untuk koneksi MySQL
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['db_host'],
                $config['db_port'] ?? '3306',
                $config['db_name']
            );
            
            // Buat koneksi dengan kredensial
            $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        } else {
            return null; // Tipe database tidak dikenali
        }

        // Set error mode ke exception agar error terdeteksi
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Set default fetch mode ke associative array
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Matikan emulated prepared statements (lebih aman)
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;

    } catch (PDOException $e) {
        // Catat error (di produksi sebaiknya log ke file)
        error_log('SIMAJURAZ DB Error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Fungsi untuk mengecek apakah aplikasi sudah di-install.
 * Mengecek keberadaan file konfigurasi dan koneksi database.
 * 
 * @return bool True jika sudah terinstall
 */
function RAZisInstalled() {
    // Cek file konfigurasi ada
    if (!file_exists(RAZ_CONFIG_FILE)) {
        return false;
    }
    
    // Cek koneksi bisa dibuat
    $pdo = RAZgetConnection();
    return $pdo !== null;
}

/**
 * Fungsi untuk mendapatkan tipe database yang digunakan.
 * 
 * @return string|null 'sqlite', 'mysql', atau null
 */
function RAZgetDbType() {
    if (!file_exists(RAZ_CONFIG_FILE)) {
        return null;
    }
    
    $config = json_decode(file_get_contents(RAZ_CONFIG_FILE), true);
    return $config['db_type'] ?? null;
}
