<?php
/**
 * ============================================================
 * RAZlogout.php — Proses Logout SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Menghancurkan session user dan redirect ke
 *               halaman login dengan pesan sukses.
 * ============================================================
 */

require_once __DIR__ . '/includes/RAZsession.php';

// Hancurkan session
RAZdestroySession();

// Redirect ke halaman login
header('Location: RAZlogin.php?msg=logged_out');
exit;
