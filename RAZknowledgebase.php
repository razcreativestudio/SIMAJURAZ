<?php
/**
 * ============================================================
 * RAZknowledgebase.php ?" Pusat Bantuan SIMAJURAZ
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZlang.php'; // Include i18n
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('kb_title') ?> ?" SIMAJURAZ</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Landing CSS & JS -->
    <link rel="stylesheet" href="assets/css/RAZLanding.css">
    <script src="assets/js/RAZLanding.js" defer></script>
    
    <script>
        if(localStorage.getItem('raz_theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
    <style>
        .kb-container {
            max-width: 1200px;
            margin: 120px auto 60px;
            padding: 0 5%;
            display: flex;
            gap: 40px;
        }
        .kb-sidebar {
            flex: 0 0 250px;
            position: sticky;
            top: 100px;
            align-self: flex-start;
            background: var(--l-card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--l-border);
            border-radius: 16px;
            padding: 20px;
        }
        .kb-sidebar a {
            display: block;
            color: var(--l-text-muted);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.3s;
            font-weight: 500;
        }
        .kb-sidebar a:hover, .kb-sidebar a.active {
            background: rgba(37, 99, 235, 0.1);
            color: var(--l-primary-light);
        }
        .kb-content {
            flex: 1;
        }
        .kb-section {
            background: var(--l-card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--l-border);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 30px;
        }
        .kb-section h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--l-primary-light);
            border-bottom: 1px solid var(--l-border);
            padding-bottom: 16px;
        }
        .kb-section p {
            margin-bottom: 16px;
            color: var(--l-text);
        }
        .kb-section ul {
            margin-left: 20px;
            margin-bottom: 24px;
            color: var(--l-text-muted);
        }
        .kb-section li {
            margin-bottom: 8px;
        }
        .kb-section code {
            background: rgba(255,255,255,0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        @media (max-width: 768px) {
            .kb-container { flex-direction: column; }
            .kb-sidebar { position: relative; top: 0; width: 100%; flex: auto; }
        }
    </style>

    <style>
        .kb-img-container {
            display: flex;
            gap: 20px;
            margin-top: 24px;
            margin-bottom: 32px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .kb-img-showcase {
            max-width: 100%;
            width: auto;
            max-height: 400px;
            object-fit: contain;
            border-radius: 12px;
            border: 1px solid var(--l-border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .kb-img-showcase:hover {
            transform: scale(1.02);
        }
        @media (max-width: 768px) {
            .kb-img-showcase {
                max-height: 300px;
            }
        }
    </style>
</head>
<body class="<?= isset($_COOKIE['raz_theme']) && $_COOKIE['raz_theme'] === 'light' ? 'light-mode' : '' ?>">

    <div class="bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <!-- Navigasi -->
    <nav>
        <div class="logo">
            <a href="index.php"><img src="assets/images/logo.svg" alt="SIMAJURAZ"></a>
        </div>
        <div class="nav-links">
            <a href="index.php#fitur"><?= t('nav_features') ?></a>
            <a href="index.php#teknologi"><?= t('nav_tech') ?></a>
            <a href="RAZknowledgebase.php" style="color:var(--l-primary-light);"><?= t('nav_kb') ?></a>
            <a href="RAZdownload.php" style="color:var(--l-text); font-weight:600;"><i class="ph-bold ph-download-simple"></i> Download</a>
        </div>
        <div class="nav-actions" style="display: flex; gap: 12px; align-items: center;">
            <a href="?lang=<?= $current_lang === 'id' ? 'en' : 'id' ?>" class="nav-action-icon" style="color:var(--l-text); text-decoration:none; font-weight:bold; font-size:14px; border:1px solid var(--l-border); padding:4px 8px; border-radius:8px;">
                <?= $current_lang === 'id' ? 'EN' : 'ID' ?>
            </a>
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

    <div class="kb-container">
        <!-- Sidebar Navigation -->
        <aside class="kb-sidebar">
            <h3 style="margin-bottom:16px; padding-left:12px; font-size:1.1rem; color:var(--l-text);"><?= t('kb_title') ?></h3>
            <a href="#about-opensource" class="active"><?= t('kb_nav_about') ?></a>
            <a href="#tech-stack"><?= t('kb_nav_tech') ?></a>
            <a href="#installation"><?= t('kb_nav_install') ?></a>
            <a href="#raz-services" style="color:var(--l-primary-light);"><?= t('kb_nav_raz') ?></a>
            <a href="#cloud-version" style="color:#f43f5e; font-weight: 600;"><?= t('kb_nav_cloud') ?></a>
            <hr style="border:0; border-top:1px solid var(--l-border); margin: 12px 0;">
            <a href="#install"><?= t('kb_sidebar_1') ?></a>
            <a href="#inventory"><?= t('kb_sidebar_2') ?></a>
            <a href="#pos"><?= t('kb_sidebar_3') ?></a>
            <a href="#finance"><?= t('kb_sidebar_4') ?></a>
            <a href="#payroll"><?= t('kb_sidebar_5') ?></a>
            <a href="#hpp"><?= t('kb_sidebar_6') ?></a>
            <a href="#profitshare"><?= t('kb_sidebar_7') ?></a>
            <a href="#reports"><?= t('kb_sidebar_8') ?></a>
        </aside>

        <!-- Main Content -->
        <main class="kb-content">
            <div style="margin-bottom: 40px;">
                <h1 style="font-size: 2.5rem; margin-bottom:12px;"><?= t('kb_title') ?></h1>
                <p style="color:var(--l-text-muted); font-size:1.1rem;"><?= t('kb_desc') ?></p>
            </div>

            <div class="kb-section" id="about-opensource">
                <h2><?= t('kb_about_os_title') ?></h2>
                <p><?= t('kb_about_os_p1') ?></p>
                <p style="margin-top: 16px;">Proyek ini dikerjakan, disponsori, dan dikelola langsung oleh tim dari <strong><a href="https://raz.my.id" target="_blank" style="color:var(--l-primary-light); text-decoration:none;">RAZ Creative Studio</a></strong>. Kami merilis keseluruhan basis kode (<i>source code</i>) aplikasi ini ke publik dengan lisensi <strong>Open Source (MIT)</strong>. Ini berarti Anda bebas mengunduh, mempelajari, memodifikasi, dan menggunakan aplikasi ini untuk keperluan pribadi maupun bisnis (komersial) secara 100% gratis.</p>
            </div>

            <div class="kb-section" id="tech-stack">
                <h2><?= t('kb_nav_tech') ?></h2>
                <p><?= t('kb_tech_p1') ?></p>
                <ul class="kb-list" style="margin-left: 20px; line-height: 1.8; margin-bottom: 20px; margin-top: 12px; color: var(--l-text-muted);">
                    <li><?= t('kb_tech_l1') ?></li>
                    <li><?= t('kb_tech_l2') ?></li>
                    <li><?= t('kb_tech_l3') ?></li>
                    <li><?= t('kb_tech_l4') ?></li>
                    <li><?= t('kb_tech_l5') ?></li>
                </ul>
                
                <h3 style="margin-top:30px; margin-bottom:12px; color:var(--l-accent);">Struktur Folder Utama (Project Structure)</h3>
                <div style="background:rgba(0,0,0,0.3); padding:16px; border-radius:8px; border:1px solid var(--l-border); font-family:monospace; color:#a0a0a0; line-height:1.6; overflow-x:auto;">
<span style="color:#10b981;">SIMAJURAZ/</span><br>
├── <span style="color:#3b82f6;">api/</span>          <span style="color:#6b7280;"># Kumpulan endpoint (Logika pemrosesan data, AJAX, Backend)</span><br>
├── <span style="color:#3b82f6;">assets/</span>       <span style="color:#6b7280;"># File CSS utama, JavaScript interaktif, Font, dan Gambar</span><br>
├── <span style="color:#3b82f6;">includes/</span>     <span style="color:#6b7280;"># File bantuan seperti fungsi i18n (Bahasa) dan library eksternal</span><br>
├── index.php     <span style="color:#6b7280;"># Landing page dan antarmuka depan pengguna publik</span><br>
├── RAZconfig.php <span style="color:#6b7280;"># File konfigurasi dinamis (Routing koneksi database)</span><br>
├── RAZinstall.php<span style="color:#6b7280;"># GUI Instalasi (Otomatisasi migrasi struktur tabel DDL)</span><br>
├── RAZpos.php    <span style="color:#6b7280;"># Halaman antarmuka Kasir (Point of Sale Utama)</span><br>
├── RAZdashboard.php <span style="color:#6b7280;"># Panel ringkasan analitik dan pusat komando Owner</span><br>
└── README.md     <span style="color:#6b7280;"># Dokumentasi esensial repositori GitHub</span>
                </div>
            </div>

            <div class="kb-section" id="installation">
                <h2><?= t('kb_install_title') ?></h2>
                <p><?= t('kb_install_p1') ?></p>
                
                <h3 style="margin-top:30px; margin-bottom:12px; color:var(--l-primary-light);"><?= t('kb_install_a') ?></h3>
                <ol class="kb-list" style="margin-left: 20px; line-height: 1.8; margin-bottom: 20px; color: var(--l-text-muted);">
                    <li><?= t('kb_install_a1') ?></li>
                    <li><?= t('kb_install_a2') ?></li>
                    <li><?= t('kb_install_a3') ?></li>
                    <li><?= t('kb_install_a4') ?></li>
                    <li><?= t('kb_install_a5') ?></li>
                    <li><?= t('kb_install_a6') ?></li>
                </ol>

                <h3 style="margin-top:30px; margin-bottom:12px; color:#f59e0b;"><?= t('kb_install_b') ?></h3>
                <ol class="kb-list" style="margin-left: 20px; line-height: 1.8; color: var(--l-text-muted);">
                    <li><?= t('kb_install_b1') ?></li>
                    <li><?= t('kb_install_b2') ?></li>
                    <li><?= t('kb_install_b3') ?></li>
                    <li><?= t('kb_install_b4') ?></li>
                    <li><?= t('kb_install_b5') ?></li>
                    <li><?= t('kb_install_b6') ?></li>
                </ol>
            </div>

            <div class="kb-section" id="raz-services" style="border: 2px solid var(--l-primary-light); background: rgba(37, 99, 235, 0.05);">
                <div style="text-align:center; margin-bottom:24px;">
                    <i class="ph-bold ph-magic-wand" style="font-size: 3rem; color: var(--l-primary-light); margin-bottom: 12px;"></i>
                    <h2 style="border:none; margin:0; padding:0; display:inline-block; font-size:2rem;"><?= t("kb_raz_title") ?></h2>
                </div>
                <p style="text-align: center; color: var(--l-text-muted); margin-bottom: 40px; font-size: 1.1rem; line-height: 1.6;">
                    <?= t('kb_raz_p1') ?>
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
                    <div style="background:var(--l-card-bg); padding:24px; border-radius:16px; border:1px solid var(--l-border); text-align:center;">
                        <i class="ph-bold ph-hard-drives" style="font-size: 2.5rem; color: #10b981; margin-bottom: 16px;"></i>
                        <h4 style="color:var(--l-text); margin-bottom:8px; font-size:1.1rem;"><?= t('kb_raz_s1') ?></h4>
                        <p style="font-size:0.9rem; color:var(--l-text-muted); line-height: 1.6;"><?= t('kb_raz_s1_d') ?></p>
                    </div>
                    <div style="background:var(--l-card-bg); padding:24px; border-radius:16px; border:1px solid var(--l-border); text-align:center;">
                        <i class="ph-bold ph-code-block" style="font-size: 2.5rem; color: var(--l-accent); margin-bottom: 16px;"></i>
                        <h4 style="color:var(--l-text); margin-bottom:8px; font-size:1.1rem;"><?= t('kb_raz_s2') ?></h4>
                        <p style="font-size:0.9rem; color:var(--l-text-muted); line-height: 1.6;"><?= t('kb_raz_s2_d') ?></p>
                    </div>
                    <div style="background:var(--l-card-bg); padding:24px; border-radius:16px; border:1px solid var(--l-border); text-align:center;">
                        <i class="ph-bold ph-lifebuoy" style="font-size: 2.5rem; color: var(--l-primary-light); margin-bottom: 16px;"></i>
                        <h4 style="color:var(--l-text); margin-bottom:8px; font-size:1.1rem;"><?= t('kb_raz_s3') ?></h4>
                        <p style="font-size:0.9rem; color:var(--l-text-muted); line-height: 1.6;"><?= t('kb_raz_s3_d') ?></p>
                    </div>
                </div>

                <div style="text-align: center;">
                    <a href="RAZdownload.php" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; padding: 14px 32px; font-size:1.1rem;">
                        <i class="ph-bold ph-headset"></i> <?= t('kb_raz_btn') ?>
                    </a>
                </div>
            </div>

            <div class="kb-section" id="cloud-version" style="border: 2px solid #f43f5e; background: rgba(244, 63, 94, 0.05);">
                <div style="text-align:center; margin-bottom:24px;">
                    <i class="ph-bold ph-cloud" style="font-size: 3rem; color: #f43f5e; margin-bottom: 12px;"></i>
                    <h2 style="border:none; margin:0; padding:0; display:inline-block; font-size:2rem;"><?= t("kb_cloud_title") ?></h2>
                </div>
                <p style="text-align: center; color: var(--l-text-muted); margin-bottom: 30px; font-size: 1.1rem; line-height: 1.6;">
                    <?= t('kb_cloud_p1') ?>
                </p>
                <div style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); padding: 16px; border-radius: 8px; text-align: center;">
                    <i class="ph-bold ph-info" style="color: #f43f5e; font-size: 1.2rem; vertical-align: middle; margin-right: 8px;"></i>
                    <span style="color: var(--l-text);"><?= t('kb_cloud_info') ?></span>
                </div>
            </div>

            <div class="kb-section" id="install">
                <h2>1. <?= t('kb_c1_title') ?></h2>
                <?= t('kb_c1_desc') ?>
                
                
            </div>

            <div class="kb-section" id="inventory">
                <h2>2. <?= t('kb_c2_title') ?></h2>
                <?= t('kb_c2_desc') ?>
                
                
            </div>

            <div class="kb-section" id="pos">
                <h2>3. <?= t('kb_c3_title') ?></h2>
                <?= t('kb_c3_desc') ?>
                
                
            </div>

            <div class="kb-section" id="finance">
                <h2>4. <?= t('kb_c4_title') ?></h2>
                <?= t('kb_c4_desc') ?>
                
                
            </div>

            <div class="kb-section" id="payroll">
                <h2>5. <?= t('kb_c5_title') ?></h2>
                <?= t('kb_c5_desc') ?>
                
                
            </div>

            <div class="kb-section" id="hpp">
                <h2>6. <?= t('kb_c6_title') ?></h2>
                <?= t('kb_c6_desc') ?>
                
                
            </div>

            <div class="kb-section" id="profitshare">
                <h2>7. <?= t('kb_c7_title') ?></h2>
                <?= t('kb_c7_desc') ?>
                
                
            </div>
            
            <div class="kb-section" id="reports">
                <h2>8. <?= t('kb_c8_title') ?></h2>
                <?= t('kb_c8_desc') ?>
                
                
            </div>
        </main>
    </div>

    <script>
        // Update active sidebar link on scroll
        const sections = document.querySelectorAll('.kb-section');
        const navLinks = document.querySelectorAll('.kb-sidebar a');
        
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (scrollY >= sectionTop - 150) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
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
            <p>&copy; <?= date('Y') ?> SIMAJURAZ by RAZ Creative Studio. Membantu UMKM Indonesia Go Digital.</p>
            <div class="social-links">
                <a href="https://www.instagram.com/raz_studio.id/" aria-label="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/@razcreativestudio" aria-label="YouTube" target="_blank"><i class="fab fa-youtube"></i></a>
                <a href="https://www.tiktok.com/@razcreativestudio" aria-label="TikTok" target="_blank"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>


