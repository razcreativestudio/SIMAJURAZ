<?php
$file = 'C:\laragon\www\SIMAJURAZ\includes\RAZlang.php';
$content = file_get_contents($file);

// Find the misplaced string:
$misplaced = <<<EOT
        // Newly added (Landing & KB)
        'os_title' => 'Open Source & Free',
EOT;

$pos = strpos($content, $misplaced);
if ($pos !== false) {
    // Cut the misplaced part from the end of the file.
    // The misplaced part starts at $pos. Wait, the whole $en_add was placed at the end.
    // Let's just restore from backup if we have one, or recreate it cleanly.
}
?>
