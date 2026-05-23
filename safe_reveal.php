<?php
\ = 'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\index.php';
\ = file_get_contents(\);

\ = str_replace('<h1><?= t(\'hero_title_1\') ?>', '<h1 class="reveal"><?= t(\'hero_title_1\') ?>', \);
\ = str_replace('<p><?= t(\'hero_desc\') ?></p>', '<p class="reveal reveal-delay-1"><?= t(\'hero_desc\') ?></p>', \);
\ = str_replace('<div class="hero-cta">', '<div class="hero-cta reveal reveal-delay-2">', \);
\ = str_replace('class="btn-primary"', 'class="btn-primary pulse"', \);
\ = str_replace('<h2 class="section-title">', '<h2 class="section-title reveal">', \);
\ = str_replace('<div class="feature-card"', '<div class="feature-card reveal"', \);

file_put_contents(\, \);
file_put_contents('C:\laragon\www\SIMAJURAZ\index.php', \);
echo "Injected safely";
?>
