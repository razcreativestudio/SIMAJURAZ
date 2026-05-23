<?php
\ = 'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\index.php';
\ = file_get_contents(\);

// Hero section
\ = str_replace('<h1>', '<h1 class="reveal">', \);
\ = str_replace('<p><?= t(\'hero_desc\') ?></p>', '<p class="reveal reveal-delay-1"><?= t(\'hero_desc\') ?></p>', \);
\ = str_replace('<div class="hero-cta">', '<div class="hero-cta reveal reveal-delay-2">', \);
\ = str_replace('class="btn-primary"', 'class="btn-primary pulse"', \); // Add pulse to CTA button

// Section Titles
\ = str_replace('class="section-title"', 'class="section-title reveal"', \);

// Feature Cards
\ = preg_replace('/<div class="feature-card"(.*?)>/', '<div class="feature-card reveal">', \);

file_put_contents(\, \);
echo "Classes injected";
?>
