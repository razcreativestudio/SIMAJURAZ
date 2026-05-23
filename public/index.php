<?php
/**
 * ============================================================
 * index.php ?" Landing Page SIMAJURAZ
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZlang.php'; // Include i18n

// Jika belum diinstall, redirect ke halaman instalasi
if (!RAZisInstalled()) {
    header('Location: RAZinstall.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAJURAZ ?" Point of Sale & Manajemen Jualan</title>
    <meta name="description" content="<?= t('hero_desc') ?>">
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Landing CSS & JS -->
    <link rel="stylesheet" href="assets/css/RAZLanding.css">
    <script src="assets/js/RAZLanding.js" defer></script>
    
    <script>
        // Set theme immediately to prevent flashing
        if(localStorage.getItem('raz_theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
</head>
<body class="<?= isset($_COOKIE['raz_theme']) && $_COOKIE['raz_theme'] === 'light' ? 'light-mode' : '' ?>">

    <!-- Background Animasi -->
    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <!-- Navigasi -->
    <nav>
        <div class="logo">
            <img src="assets/images/logo.svg" alt="SIMAJURAZ">
        </div>
        <div class="nav-links">
            <a href="#fitur"><?= t('nav_features') ?></a>
            <a href="#teknologi"><?= t('nav_tech') ?></a>
            <a href="RAZknowledgebase.php"><?= t('nav_kb') ?></a>
        </div>
        <div class="nav-actions" style="display: flex; gap: 12px; align-items: center;">
            <!-- Language Switcher -->
            <a href="?lang=<?= $current_lang === 'id' ? 'en' : 'id' ?>" class="nav-action-icon" style="color:var(--l-text); text-decoration:none; font-weight:bold; font-size:14px; border:1px solid var(--l-border); padding:4px 8px; border-radius:8px;">
                <?= $current_lang === 'id' ? 'EN' : 'ID' ?>
            </a>
            <!-- Theme Switcher -->
            <a href="#" id="theme-toggle" class="nav-action-icon" style="color:var(--l-text); font-size:1.2rem; text-decoration:none;">
                <i class="ph-bold ph-sun"></i>
            </a>
            <a href="RAZlogin.php" class="btn-login">
                <i class="ph-bold ph-sign-in"></i> <span class="login-text"><?= t('nav_login') ?></span>
            </a>
            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" style="background:none; border:none; color:var(--l-text); font-size:1.5rem; cursor:pointer; display:none;">
                <i class="ph-bold ph-list"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-badge">
            <i class="ph-bold ph-star"></i> <?= t('hero_badge') ?>
        </div>
        <h1 class="reveal"><?= t('hero_title_1') ?><br><?= t('hero_title_2') ?> <span>SIMAJURAZ</span></h1>
        <p class="reveal reveal-delay-1"><?= t('hero_desc') ?></p>
        
        <div class="hero-cta reveal reveal-delay-2">
            <a href="RAZlogin.php" class="btn-primary pulse">
                <?= t('hero_btn_start') ?>
            </a>
            <a href="#fitur" class="btn-outline">
                <?= t('hero_btn_learn') ?>
            </a>
        </div>
        
        <!-- Mockup Visual Placeholder (Dashboard) -->
        <div style="margin-top: 60px; max-width: 900px; width: 100%; position: relative;">
            <div style="background: var(--l-card-bg); backdrop-filter: blur(20px); border: 1px solid var(--l-border); border-radius: 24px; padding: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
                <img src="assets/images/ss_dashboard.png" alt="SIMAJURAZ Dashboard" style="width: 100%; border-radius: 12px; display: block; background: #e2e8f0; min-height: 400px; object-fit: cover;">
                <div style="position:absolute; bottom:-10px; right:10px; background:var(--l-accent); color:#fff; padding:4px 12px; border-radius:20px; font-size:0.8rem; font-weight:bold;">Tampilan Asli</div>
            </div>
        </div>
    </section>

    <!-- Teknologi Section -->
    <section class="features" id="teknologi" style="background: rgba(0,0,0,0.2);">
        <h2 class="section-title reveal"><?= t('tech_title') ?></h2>
        <p style="text-align: center; max-width: 800px; margin: -40px auto 60px; color: var(--l-text-muted);"><?= t('tech_desc') ?></p>
        <div class="features-grid">
            <div class="feature-card reveal" style="text-align: center;">
                <i class="ph-bold ph-file-code" style="font-size: 3rem; color: var(--l-primary); margin-bottom: 16px;"></i>
                <h3 style="font-size:1.2rem;"><?= t('tech_php') ?></h3>
            </div>
            <div class="feature-card reveal" style="text-align: center;">
                <i class="ph-bold ph-database" style="font-size: 3rem; color: var(--l-accent); margin-bottom: 16px;"></i>
                <h3 style="font-size:1.2rem;"><?= t('tech_db') ?></h3>
            </div>
            <div class="feature-card reveal" style="text-align: center;">
                <i class="ph-bold ph-paint-brush" style="font-size: 3rem; color: #10b981; margin-bottom: 16px;"></i>
                <h3 style="font-size:1.2rem;"><?= t('tech_css') ?></h3>
            </div>
            <div class="feature-card reveal" style="text-align: center;">
                <i class="ph-bold ph-lightning" style="font-size: 3rem; color: #f43f5e; margin-bottom: 16px;"></i>
                <h3 style="font-size:1.2rem;"><?= t('tech_js') ?></h3>
            </div>
        </div>
    </section>

    <!-- Extended Visual Showcase (Zig-Zag Layout) -->
    <section class="features" style="padding-top:60px;">
        <h2 class="section-title reveal"><?= t('showcase_title') ?></h2>
        
        <!-- Showcase 1: POS -->
        <div class="reveal" style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px; margin-bottom: 80px; max-width: 1200px; margin-left: auto; margin-right: auto;">
            <div style="flex: 1; min-width: 300px;">
                <div style="background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 8px;">
                    <img src="assets/images/ss_pos.png" alt="POS Kasir" style="width: 100%; border-radius: 8px; background: #e2e8f0; min-height: 250px; object-fit: cover;">
                </div>
            </div>
            <div style="flex: 1; min-width: 300px;">
                <h3 style="font-size: 2rem; margin-bottom: 16px; color: var(--l-primary-light);"><?= t('sc_1_title') ?></h3>
                <p style="color: var(--l-text-muted); font-size: 1.1rem; line-height: 1.8;"><?= t('sc_1_desc') ?></p>
            </div>
        </div>

        <!-- Showcase 2: Finance -->
        <div class="reveal" style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px; margin-bottom: 80px; max-width: 1200px; margin-left: auto; margin-right: auto; flex-direction: row-reverse;">
            <div style="flex: 1; min-width: 300px;">
                <div style="background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 8px;">
                    <img src="assets/images/ss_finance.png" alt="Laporan Keuangan" style="width: 100%; border-radius: 8px; background: #e2e8f0; min-height: 250px; object-fit: cover;">
                </div>
            </div>
            <div style="flex: 1; min-width: 300px;">
                <h3 style="font-size: 2rem; margin-bottom: 16px; color: var(--l-accent);"><?= t('sc_3_title') ?></h3>
                <p style="color: var(--l-text-muted); font-size: 1.1rem; line-height: 1.8;"><?= t('sc_3_desc') ?></p>
            </div>
        </div>

        <!-- Showcase 3: HPP Calculator -->
        <div class="reveal" style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px; margin-bottom: 80px; max-width: 1200px; margin-left: auto; margin-right: auto;">
            <div style="flex: 1; min-width: 300px;">
                <div style="background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 8px;">
                    <img src="assets/images/ss_hpp.png" alt="Kalkulator HPP" style="width: 100%; border-radius: 8px; background: #e2e8f0; min-height: 250px; object-fit: cover;">
                </div>
            </div>
            <div style="flex: 1; min-width: 300px;">
                <h3 style="font-size: 2rem; margin-bottom: 16px; color: #10b981;"><?= t('feat_6_title') ?></h3>
                <p style="color: var(--l-text-muted); font-size: 1.1rem; line-height: 1.8;"><?= t('feat_6_desc') ?></p>
            </div>
        </div>

        <!-- Showcase 4: Inventory & Reports -->
        <div class="reveal" style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px; margin-bottom: 80px; max-width: 1200px; margin-left: auto; margin-right: auto; flex-direction: row-reverse;">
            <div style="flex: 1; min-width: 300px;">
                <div style="background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 8px;">
                    <img src="assets/images/ss_reports.png" alt="Laporan & Ekspor" style="width: 100%; border-radius: 8px; background: #e2e8f0; min-height: 250px; object-fit: cover;">
                </div>
            </div>
            <div style="flex: 1; min-width: 300px;">
                <h3 style="font-size: 2rem; margin-bottom: 16px; color: #f43f5e;"><?= t('feat_7_title') ?></h3>
                <p style="color: var(--l-text-muted); font-size: 1.1rem; line-height: 1.8;"><?= t('feat_7_desc') ?></p>
            </div>
        </div>
    </section>

    <!-- Fitur Section Grid Lengkap -->
    <section class="features" id="fitur" style="background: var(--l-bg-nav);">
        <h2 class="section-title reveal"><?= t('feat_title') ?></h2>
        <div class="features-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-chart-pie-slice"></i></div>
                <h3><?= t('feat_1_title') ?></h3>
                <p><?= t('feat_1_desc') ?></p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-package"></i></div>
                <h3><?= t('feat_2_title') ?></h3>
                <p><?= t('feat_2_desc') ?></p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-shopping-cart"></i></div>
                <h3><?= t('feat_3_title') ?></h3>
                <p><?= t('feat_3_desc') ?></p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-chart-line-up"></i></div>
                <h3><?= t('feat_4_title') ?></h3>
                <p><?= t('feat_4_desc') ?></p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-money"></i></div>
                <h3><?= t('feat_5_title') ?></h3>
                <p><?= t('feat_5_desc') ?></p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-calculator"></i></div>
                <h3><?= t('feat_6_title') ?></h3>
                <p><?= t('feat_6_desc') ?></p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-file-pdf"></i></div>
                <h3><?= t('feat_7_title') ?></h3>
                <p><?= t('feat_7_desc') ?></p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon"><i class="ph-bold ph-users-three"></i></div>
                <h3><?= t('feat_8_title') ?></h3>
                <p><?= t('feat_8_desc') ?></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date('Y') ?> <?= t('footer_text') ?> <br> <a href="https://raz.my.id/" target="_blank" style="color: var(--l-primary-light); text-decoration: none; font-weight: bold; margin-top: 10px; display: inline-block;">RAZ Creative Studio Official</a></p>
    </footer>

    <!-- RAZ Creative Studio External Footer -->
    <style>
        .raz-ext-footer { text-align: left;
            background: #050505;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 60px 5% 30px;
            font-family: 'Inter', sans-serif;
            color: #a0a0a0;
        }
        .raz-ext-footer .footer-grid { text-align: left;
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
            display: inline-block;
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
            display: inline-block;
        }
        .raz-ext-footer .social-links a:hover { color: #FFD700; transform: translateY(-3px); }
        @media (max-width: 1024px) {
            .raz-ext-footer .footer-grid { text-align: left; grid-template-columns: 1fr 1fr; gap: 30px; }
        }
        @media (max-width: 768px) {
            .raz-ext-footer .footer-grid { text-align: left; grid-template-columns: 1fr; }
            .raz-ext-footer .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <a href="https://www.instagram.com/raz_studio.id/" aria-label="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/@razcreativestudio" aria-label="YouTube" target="_blank"><i class="fab fa-youtube"></i></a>
                <a href="https://www.tiktok.com/@razcreativestudio" aria-label="TikTok" target="_blank"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>

