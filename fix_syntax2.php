<?php
$content = file_get_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php');
$content = preg_replace("/\];\s*\];\s*\/\/ Helper function/", "];\n\n// Helper function", $content);
file_put_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php', $content);
file_put_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\includes\RAZlang.php', $content);
echo "Fixed double bracket!";
?>
