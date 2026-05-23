<?php
$files = [
    'C:\laragon\www\SIMAJURAZ\index.php',
    'C:\laragon\www\SIMAJURAZ\RAZknowledgebase.php',
    'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\index.php',
    'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\RAZknowledgebase.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // 1. Add FontAwesome CDN if not present
        if (strpos($content, 'font-awesome/6.5.0/css/all.min.css') === false) {
            $content = str_replace('<footer class="raz-ext-footer">', '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">' . "\n" . '    <footer class="raz-ext-footer">', $content);
        }
        
        // 2. Add animation to social links
        $css_old = '.raz-ext-footer .social-links a:hover { color: #FFD700; }';
        $css_new = '.raz-ext-footer .social-links a:hover { color: #FFD700; transform: translateY(-3px); }';
        $content = str_replace($css_old, $css_new, $content);
        
        // Also add display: inline-block for transform to work on <a> tags
        $css_a_old = "text-decoration: none;\n            transition: all 0.3s;";
        $css_a_new = "text-decoration: none;\n            transition: all 0.3s;\n            display: inline-block;";
        $content = str_replace($css_a_old, $css_a_new, $content);

        // 3. Replace text with FontAwesome icons
        $content = str_replace('>Instagram</a>', '><i class="fab fa-instagram"></i></a>', $content);
        $content = str_replace('>YouTube</a>', '><i class="fab fa-youtube"></i></a>', $content);
        $content = str_replace('>TikTok</a>', '><i class="fab fa-tiktok"></i></a>', $content);
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
?>
