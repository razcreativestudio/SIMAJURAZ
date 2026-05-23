<?php
$files = [
    'C:\laragon\www\SIMAJURAZ\index.php',
    'C:\laragon\www\SIMAJURAZ\RAZknowledgebase.php',
    'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\index.php',
    'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\RAZknowledgebase.php'
];

$raz_footer = <<<'HTML'
    <!-- RAZ Creative Studio External Footer -->
    <style>
        .raz-ext-footer {
            background: #050505;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 60px 5% 30px;
            font-family: 'Inter', sans-serif;
            color: #a0a0a0;
        }
        .raz-ext-footer .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        .raz-ext-footer .footer-col h4 {
            font-size: 1rem;
            color: #ffffff;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .raz-ext-footer .footer-col p {
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .raz-ext-footer .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .raz-ext-footer .footer-col ul li { margin-bottom: 10px; }
        .raz-ext-footer .footer-col ul li a {
            color: #a0a0a0;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        .raz-ext-footer .footer-col ul li a:hover {
            color: #FFD700;
            padding-left: 4px;
        }
        .raz-ext-footer .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.05);
            font-size: 0.85rem;
        }
        .raz-ext-footer .social-links { display: flex; gap: 12px; }
        .raz-ext-footer .social-links a {
            color: #a0a0a0;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        .raz-ext-footer .social-links a:hover { color: #FFD700; }
        @media (max-width: 1024px) {
            .raz-ext-footer .footer-grid { grid-template-columns: 1fr 1fr; gap: 30px; }
        }
        @media (max-width: 768px) {
            .raz-ext-footer .footer-grid { grid-template-columns: 1fr; }
            .raz-ext-footer .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
        }
    </style>
    <footer class="raz-ext-footer">
        <div class="footer-grid">
            <div class="footer-col footer-about">
                <img src="https://raz.my.id/img/raz-logo.png" alt="RAZ Logo" style="max-width:160px;height:auto;margin-bottom:16px;">
                <p>RAZ Creative Studio adalah agensi IT & kreatif digital yang mengintegrasikan teknologi, desain, dan inovasi untuk menghasilkan solusi digital bernilai komersial.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="https://raz.my.id/about-raz.html">Tentang Kami</a></li>
                    <li><a href="https://raz.my.id/raz-services.html">Layanan</a></li>
                    <li><a href="https://raz.my.id/raz-portofolio.html">Portofolio</a></li>
                    <li><a href="https://raz.my.id/raz-product.html">Digital Product</a></li>
                    <li><a href="https://raz.my.id/hubungi-raz.html">Kontak</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Layanan</h4>
                <ul>
                    <li><a href="https://raz.my.id/raz-services.html">Web Development</a></li>
                    <li><a href="https://raz.my.id/raz-services.html">Graphic Design</a></li>
                    <li><a href="https://raz.my.id/raz-services.html">3D & CAD</a></li>
                    <li><a href="https://raz.my.id/raz-services.html">AI Tools</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Tools Online</h4>
                <ul>
                    <li><a href="https://raz.my.id/tools.html">Semua Tools</a></li>
                    <li><a href="https://raz.my.id/bikincv/index.html">CV Maker</a></li>
                    <li><a href="https://raz.my.id/pdftools/jpg2pdf/RAZJpg2Pdf.html">PDF Tools</a></li>
                    <li><a href="https://raz.my.id/airazstudio.html">AI Studio</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 RAZ Creative Studio. Hak Cipta Dilindungi.</p>
            <div class="social-links">
                <a href="https://www.instagram.com/raz_studio.id/" aria-label="Instagram" target="_blank">Instagram</a>
                <a href="https://www.youtube.com/@razcreativestudio" aria-label="YouTube" target="_blank">YouTube</a>
                <a href="https://www.tiktok.com/@razcreativestudio" aria-label="TikTok" target="_blank">TikTok</a>
            </div>
        </div>
    </footer>
</body>
HTML;

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Avoid duplicate injections
        if (strpos($content, 'raz-ext-footer') === false) {
            $content = str_replace('</body>', $raz_footer, $content);
            file_put_contents($file, $content);
            echo "Injected footer into $file\n";
        } else {
            echo "Footer already in $file\n";
        }
    }
}
?>
