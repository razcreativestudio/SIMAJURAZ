<?php
$content = file_get_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php');
$content = str_replace("    ]\n];\n];\n", "    ]\n];\n", $content);
file_put_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php', $content);
file_put_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\includes\RAZlang.php', $content);
echo "Fixed!";
?>
