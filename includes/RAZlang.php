<?php
/**
 * ============================================================
 * RAZlang.php — Language Dictionary Loader (Modular)
 * ============================================================
 */

// Menentukan bahasa
$current_lang = 'id';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en'])) {
    $current_lang = $_GET['lang'];
    setcookie('raz_lang', $current_lang, time() + (86400 * 30), "/"); // 30 hari
} elseif (isset($_COOKIE['raz_lang']) && in_array($_COOKIE['raz_lang'], ['id', 'en'])) {
    $current_lang = $_COOKIE['raz_lang'];
}

// Global dictionary
$LANG_DICT = ['id' => [], 'en' => []];

// Load modular dictionaries
$modules = [
    'index', 'kb', 'dashboard', 'pos', 'inventory', 'finance', 'reports', 'users', 'settings'
];

foreach ($modules as $mod) {
    $file = __DIR__ . '/lang/RAZlang_' . $mod . '.php';
    if (file_exists($file)) {
        $dict = require $file;
        if (isset($dict['id']) && is_array($dict['id'])) {
            $LANG_DICT['id'] = array_merge($LANG_DICT['id'], $dict['id']);
        }
        if (isset($dict['en']) && is_array($dict['en'])) {
            $LANG_DICT['en'] = array_merge($LANG_DICT['en'], $dict['en']);
        }
    }
}

// Helper function
function t($key) {
    global $LANG_DICT, $current_lang;
    return $LANG_DICT[$current_lang][$key] ?? strtoupper($key);
}
?>
