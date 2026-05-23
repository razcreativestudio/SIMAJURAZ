<?php
\ = 'C:\laragon\www\SIMAJURAZ\index.php';
\ = file_get_contents(\);

\ = str_replace('<div class="feature-card">', '<div class="feature-card reveal">', \);
\ = preg_replace('/<div class="feature-card"([^>]*)>/', '<div class="feature-card reveal">', \);
// Clean up duplicate reveals just in case
\ = str_replace('reveal reveal', 'reveal', \);

file_put_contents(\, \);
file_put_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\index.php', \);
echo "Cards updated";
?>
