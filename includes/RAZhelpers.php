<?php
/**
 * ============================================================
 * RAZhelpers.php — Fungsi Utilitas PHP SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Kumpulan fungsi helper yang digunakan di seluruh
 *               aplikasi. Termasuk format mata uang, sanitasi input,
 *               generate nomor invoice, dan response JSON API.
 * ============================================================
 */

require_once __DIR__ . '/RAZlang.php';

/**
 * Format angka menjadi format Rupiah Indonesia.
 * Contoh: 150000 → "Rp 150.000"
 * 
 * @param float|int $amount Jumlah uang
 * @param bool $withPrefix Tampilkan prefix "Rp" atau tidak
 * @return string Format rupiah
 */
function RAZformatRupiah($amount, $withPrefix = true) {
    $formatted = number_format((float)$amount, 0, ',', '.');
    return $withPrefix ? 'Rp ' . $formatted : $formatted;
}

/**
 * Sanitasi input dari user untuk mencegah XSS.
 * 
 * @param string $input Input mentah dari user
 * @return string Input yang sudah di-sanitasi
 */
function RAZsanitize($input) {
    if (is_array($input)) {
        return array_map('RAZsanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate nomor invoice unik berdasarkan format dinamis.
 * 
 * @param string $format Format dinamis (contoh: INV-{Ymd}-{SEQ5})
 * @param int $storeId ID Toko
 * @param PDO|null $pdo Instance PDO
 * @return string Nomor invoice
 */
function RAZgenerateInvoice($format = 'INV-{Ymd}-{SEQ5}', $storeId = 0, $pdo = null) {
    if (empty($format)) $format = 'INV-{Ymd}-{SEQ5}';
    
    // Ganti format tanggal
    $format = str_replace('{Ymd}', date('Ymd'), $format);     // 20260522
    $format = str_replace('{Y-m-d}', date('Y-m-d'), $format); // 2026-05-22
    $format = str_replace('{dmY}', date('dmY'), $format);     // 22052026
    $format = str_replace('{dmy}', date('dmy'), $format);     // 220526
    $format = str_replace('{ymd}', date('ymd'), $format);     // 260522
    $format = str_replace('{mdy}', date('mdy'), $format);     // 052226
    $format = str_replace('{Ym}', date('Ym'), $format);       // 202605
    $format = str_replace('{ym}', date('ym'), $format);       // 2605
    $format = str_replace('{my}', date('my'), $format);       // 0526

    // Ganti format sequence harian (SEQ3, SEQ4, SEQ5, dst)
    if (preg_match('/\{SEQ(\d+)\}/', $format, $matches)) {
        $digits = intval($matches[1]);
        $seq = 1;
        if ($pdo && $storeId) {
            $today = date('Y-m-d');
            $stmt = $pdo->prepare("SELECT COUNT(id) FROM transactions WHERE store_id = ? AND DATE(created_at) = ?");
            $stmt->execute([$storeId, $today]);
            $count = (int)$stmt->fetchColumn();
            $seq = $count + 1;
        }
        $format = str_replace($matches[0], str_pad($seq, $digits, '0', STR_PAD_LEFT), $format);
    }

    // Ganti format angka acak (RAND3, RAND4, dst)
    if (preg_match_all('/\{RAND(\d+)\}/', $format, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $digits = intval($match[1]);
            $min = pow(10, $digits - 1);
            $max = pow(10, $digits) - 1;
            $rand = mt_rand($min, $max);
            $format = preg_replace('/' . preg_quote($match[0], '/') . '/', (string)$rand, $format, 1);
        }
    }

    // Ganti format kombinasi huruf+angka acak (MIX3, MIX4, dst)
    if (preg_match_all('/\{MIX(\d+)\}/', $format, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $digits = intval($match[1]);
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $mix = '';
            for ($i = 0; $i < $digits; $i++) {
                $mix .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $format = preg_replace('/' . preg_quote($match[0], '/') . '/', $mix, $format, 1);
        }
    }

    return $format;
}

/**
 * Kirim response JSON untuk API endpoint.
 * 
 * @param bool $success Status berhasil atau gagal
 * @param string $message Pesan untuk ditampilkan
 * @param array $data Data tambahan (opsional)
 * @param int $httpCode HTTP status code
 */
function RAZjsonResponse($success, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Ambil input JSON dari request body (untuk API POST/PUT).
 * 
 * @return array Data yang sudah di-decode
 */
function RAZgetJsonInput() {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    return is_array($data) ? $data : [];
}

/**
 * Validasi bahwa field-field tertentu ada dan tidak kosong.
 * 
 * @param array $data Data yang akan divalidasi
 * @param array $requiredFields Daftar field yang wajib ada
 * @return array ['valid' => bool, 'missing' => array]
 */
function RAZvalidateRequired($data, $requiredFields) {
    $missing = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $missing[] = $field;
        }
    }
    return [
        'valid'   => empty($missing),
        'missing' => $missing,
    ];
}

/**
 * Upload file gambar dengan validasi.
 * 
 * @param array $file Data dari $_FILES
 * @param string $targetDir Folder tujuan upload
 * @param int $maxSizeMB Ukuran maksimal dalam MB
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function RAZuploadImage($file, $targetDir, $maxSizeMB = 2) {
    // Validasi apakah ada file
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => '', 'error' => 'Tidak ada file yang diupload'];
    }

    // Validasi tipe file (hanya gambar)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'filename' => '', 'error' => 'Tipe file tidak didukung. Gunakan JPG, PNG, atau WebP'];
    }

    // Validasi ukuran file
    $maxBytes = $maxSizeMB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'filename' => '', 'error' => "Ukuran file melebihi {$maxSizeMB}MB"];
    }

    // Generate nama file unik
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'raz_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    
    // Pastikan folder tujuan ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Pindahkan file
    $targetPath = rtrim($targetDir, '/') . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'error' => ''];
    }

    return ['success' => false, 'filename' => '', 'error' => 'Gagal menyimpan file'];
}

/**
 * Format tanggal ke format Indonesia.
 * Contoh: "2026-05-21" → "21 Mei 2026"
 * 
 * @param string $date Tanggal format Y-m-d atau datetime
 * @param bool $withTime Tampilkan waktu juga
 * @return string Tanggal format Indonesia
 */
function RAZformatTanggal($date, $withTime = false) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $hari = date('d', $timestamp);
    $bln = (int)date('m', $timestamp);
    $tahun = date('Y', $timestamp);
    
    $result = "{$hari} {$bulan[$bln]} {$tahun}";
    
    if ($withTime) {
        $result .= ' ' . date('H:i', $timestamp);
    }
    
    return $result;
}

/**
 * Generate CSRF token untuk proteksi form.
 * 
 * @return string CSRF token
 */
function RAZgenerateCsrf() {
    if (empty($_SESSION['raz_csrf_token'])) {
        $_SESSION['raz_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['raz_csrf_token'];
}

/**
 * Validasi CSRF token.
 * 
 * @param string $token Token yang dikirim dari form/request
 * @return bool True jika valid
 */
function RAZvalidateCsrf($token) {
    if (empty($_SESSION['raz_csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['raz_csrf_token'], $token);
}

/**
 * Mencatat aktivitas pengguna ke dalam database untuk keperluan audit (Super Admin).
 * 
 * @param PDO $pdo Objek koneksi database
 * @param int|null $user_id ID Pengguna
 * @param int|null $store_id ID Toko
 * @param string $action Aksi yang dilakukan (contoh: 'LOGIN', 'DELETE_USER')
 * @param string $details Detail tambahan dalam format string/JSON
 */
function logActivity($pdo, $user_id, $store_id, $action, $details = '') {
    // Ambil IP Address pengunjung
    $ip_address = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }

    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, store_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $store_id, $action, $details, $ip_address, $user_agent]);
    } catch (PDOException $e) {
        // Abaikan error log agar tidak mengganggu flow utama aplikasi
    }
}
