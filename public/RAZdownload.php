<?php
/**
 * ============================================================
 * RAZdownload.php ?" Halaman Download SIMAJURAZ
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZlang.php';

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
    <title>Unduh SIMAJURAZ ?" Point of Sale & Manajemen Jualan</title>
    <meta name="description" content="Download source code SIMAJURAZ gratis atau sewa jasa instalasi dan hosting dari RAZ Creative Studio.">
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
</head>
<body class="<?= isset($_COOKIE['raz_theme']) && $_COOKIE['raz_theme'] === 'light' ? 'light-mode' : '' ?>">

    <!-- Navigasi -->
    <nav style="background: var(--l-bg-nav); border-bottom: 1px solid var(--l-border);">
        <div class="logo">
            <a href="index.php" style="text-decoration:none;"><img src="assets/images/logo.svg" alt="SIMAJURAZ"></a>
        </div>
        <div class="nav-links">
            <a href="index.php#fitur"><?= t('nav_features') ?></a>
            <a href="index.php#teknologi"><?= t('nav_tech') ?></a>
            <a href="RAZknowledgebase.php"><?= t('nav_kb') ?></a>
            <a href="RAZdownload.php" style="color:var(--l-primary-light); font-weight:600;"><i class="ph-bold ph-download-simple"></i> Download</a>
        </div>
        <div class="nav-actions" style="display: flex; gap: 12px; align-items: center;">
            <a href="#" id="theme-toggle" class="nav-action-icon" style="color:var(--l-text); font-size:1.2rem; text-decoration:none;">
                <i class="ph-bold ph-sun"></i>
            </a>
            <a href="RAZlogin.php" class="btn-login">
                <i class="ph-bold ph-sign-in"></i> <span class="login-text"><?= t('nav_login') ?></span>
            </a>
            <button class="mobile-menu-btn" style="background:none; border:none; color:var(--l-text); font-size:1.5rem; cursor:pointer; display:none;">
                <i class="ph-bold ph-list"></i>
            </button>
        </div>
    </nav>

    <div style="padding-top: 100px;"></div>

    <!-- Hero Download -->
    <section class="features" style="padding-top: 40px; padding-bottom: 40px;">
        <h1 class="section-title reveal" style="font-size: 3rem; margin-bottom: 20px;">Unduh <span style="color:var(--l-primary-light);">SIMAJURAZ</span></h1>
        <p class="reveal reveal-delay-1" style="text-align: center; max-width: 800px; margin: 0 auto 40px; color: var(--l-text-muted); font-size: 1.2rem; line-height: 1.6;">
            Aplikasi Point of Sale, Manajemen Inventori, dan Keuangan yang dibangun untuk mendukung transformasi digital UMKM Indonesia. 100% Gratis dan Open Source.
        </p>

        <div style="text-align: center; margin-bottom: 60px;" class="reveal reveal-delay-2">
            <a href="https://github.com/razcreativestudio/SIMAJURAZ" target="_blank" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; font-size: 1.1rem; padding: 16px 32px;">
                <i class="ph-bold ph-github-logo" style="font-size: 1.4rem;"></i> Unduh Source Code di GitHub
            </a>
            <p style="margin-top: 16px; color: var(--l-text-muted); font-size: 0.9rem;">
                Lisensi MIT - Bebas dikembangkan dan dimodifikasi untuk kebutuhan personal maupun komersial.
            </p>
        </div>
    </section>

    <!-- Layanan RAZ -->
    <section class="features" style="background: var(--l-card-bg); border-top: 1px solid var(--l-border); border-bottom: 1px solid var(--l-border);">
        <h2 class="section-title reveal">Tidak Ingin Repot? Biar Kami yang Urus!</h2>
        <p style="text-align: center; max-width: 800px; margin: -40px auto 40px; color: var(--l-text-muted);">
            Bagi Bapak/Ibu pemilik bisnis yang tidak memiliki tim IT, RAZ Creative Studio menyediakan layanan profesional siap pakai.
        </p>
        
        <div class="features-grid">
            <div class="feature-card reveal">
                <i class="ph-bold ph-hard-drives" style="font-size: 3rem; color: var(--l-primary); margin-bottom: 16px;"></i>
                <h3>Cloud Hosting & Domain</h3>
                <p>Kami bantu belikan nama domain (misal: kasir-toko-anda.com) dan siapkan server hosting yang super cepat, stabil, dan aman selama setahun penuh.</p>
            </div>
            <div class="feature-card reveal">
                <i class="ph-bold ph-wrench" style="font-size: 3rem; color: var(--l-accent); margin-bottom: 16px;"></i>
                <h3>Instalasi & Setup Awal</h3>
                <p>Aplikasi SIMAJURAZ akan kami install dan kami atur (setup) hingga siap pakai. Mulai dari logo toko hingga database eksternal akan beres tanpa ribet.</p>
            </div>
            <div class="feature-card reveal">
                <i class="ph-bold ph-magic-wand" style="font-size: 3rem; color: #10b981; margin-bottom: 16px;"></i>
                <h3>Kustomisasi Fitur</h3>
                <p>Butuh modul khusus (seperti integrasi WhatsApp, absensi, atau sistem diskon poin pelanggan)? Kami siap mengembangkan fitur custom khusus untuk bisnis Anda.</p>
            </div>
            <div class="feature-card reveal">
                <i class="ph-bold ph-lifebuoy" style="font-size: 3rem; color: #f43f5e; margin-bottom: 16px;"></i>
                <h3>Maintenance Bulanan</h3>
                <p>Dukungan teknis prioritas (Technical Support), backup database rutin setiap minggu, dan pemeliharaan performa agar aplikasi berjalan mulus 24/7.</p>
            </div>
        </div>
    </section>

    <!-- Kontak & Konsultasi -->
    <section class="features" style="background: var(--l-bg);">
        <h2 class="section-title reveal">Mulai Transformasi Digital Bersama RAZ</h2>
        <p style="text-align: center; max-width: 800px; margin: -40px auto 40px; color: var(--l-text-muted);">
            Hubungi kami sekarang untuk berkonsultasi mengenai kebutuhan instalasi SIMAJURAZ untuk toko Anda, atau jika Anda ingin membangun website dan aplikasi lainnya.
        </p>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;" class="reveal">
            <div style="background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 30px 20px; text-align: center; width: 320px; display: flex; flex-direction: column;">
                <div style="flex-grow: 1;">
                    <i class="ph-bold ph-whatsapp-logo" style="font-size: 3rem; color: #10b981; margin-bottom: 16px;"></i>
                    <h3>WhatsApp Pribadi</h3>
                    <p style="color: var(--l-text-muted); margin-bottom: 20px;">Respon paling cepat untuk diskusi ringan dan janji temu.</p>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="https://wa.me/6282392925488" target="_blank" class="btn-primary" style="background: #10b981; color: #fff; padding: 10px; font-size: 0.9rem; white-space: nowrap;"><i class="ph-bold ph-whatsapp-logo"></i> +62 823-9292-5488</a>
                    <a href="https://wa.me/6283173002527" target="_blank" class="btn-primary" style="background: #10b981; color: #fff; padding: 10px; font-size: 0.9rem; white-space: nowrap;"><i class="ph-bold ph-whatsapp-logo"></i> +62 831-7300-2527</a>
                </div>
            </div>
            
            <div style="background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 30px 20px; text-align: center; width: 320px; display: flex; flex-direction: column;">
                <div style="flex-grow: 1;">
                    <i class="ph-bold ph-envelope-simple" style="font-size: 3rem; color: var(--l-accent); margin-bottom: 16px;"></i>
                    <h3>Email Resmi</h3>
                    <p style="color: var(--l-text-muted); margin-bottom: 20px;">Kirimkan detail proposal penawaran atau kerjasama bisnis.</p>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px; justify-content: flex-end;">
                    <a href="mailto:razcreativestudio@gmail.com" class="btn-outline" style="padding: 10px; font-size: 0.85rem; white-space: nowrap;"><i class="ph-bold ph-envelope-simple"></i> razcreativestudio@gmail.com</a>
                </div>
            </div>
            
            <div style="background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 30px 20px; text-align: center; width: 320px; display: flex; flex-direction: column;">
                <div style="flex-grow: 1;">
                    <i class="ph-bold ph-globe" style="font-size: 3rem; color: var(--l-primary-light); margin-bottom: 16px;"></i>
                    <h3>Kunjungi Website</h3>
                    <p style="color: var(--l-text-muted); margin-bottom: 20px;">Lihat profil, layanan lain, serta portofolio agensi kami.</p>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px; justify-content: flex-end;">
                    <a href="https://raz.my.id" target="_blank" class="btn-outline" style="padding: 10px; font-size: 0.9rem; white-space: nowrap;"><i class="ph-bold ph-globe"></i> Kunjungi raz.my.id</a>
                </div>
            </div>
        </div>

        <!-- Form Kontak (Redirect WhatsApp) -->
        <div class="reveal reveal-delay-1" style="max-width: 800px; margin: 60px auto 0; background: var(--l-card-bg); border: 1px solid var(--l-border); border-radius: 16px; padding: 40px; text-align: left;">
            <h3 style="font-size: 1.5rem; margin-bottom: 8px; text-align: center;">Tulis Pesan Anda</h3>
            <p style="text-align: center; color: var(--l-text-muted); margin-bottom: 30px; font-size: 0.95rem;">Pesan ini akan otomatis dikirimkan langsung ke WhatsApp tim kami.</p>
            
            <form id="contactForm" style="display: flex; flex-direction: column; gap: 20px;" onsubmit="sendToWhatsApp(event)">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Nama Anda <span style="color:#f43f5e">*</span></label>
                        <input type="text" id="cf_name" required placeholder="Masukkan nama lengkap" style="width: 100%; padding: 12px 16px; background: rgba(0,0,0,0.2); border: 1px solid var(--l-border); border-radius: 8px; color: var(--l-text); outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--l-primary)'" onblur="this.style.borderColor='var(--l-border)'">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Subjek Pesan <span style="color:#f43f5e">*</span></label>
                        <select id="cf_subject" required style="width: 100%; padding: 12px 16px; background: rgba(0,0,0,0.2); border: 1px solid var(--l-border); border-radius: 8px; color: var(--l-text); outline: none; transition: border-color 0.2s; appearance: none; cursor: pointer;" onfocus="this.style.borderColor='var(--l-primary)'" onblur="this.style.borderColor='var(--l-border)'">
                            <option value="Tanya Jasa Instalasi / Hosting" style="background:var(--l-bg);color:var(--l-text)">Tanya Jasa Instalasi / Hosting</option>
                            <option value="Kustomisasi Fitur SIMAJURAZ" style="background:var(--l-bg);color:var(--l-text)">Kustomisasi Fitur SIMAJURAZ</option>
                            <option value="Kerjasama Bisnis & Layanan Lain" style="background:var(--l-bg);color:var(--l-text)">Kerjasama Bisnis & Layanan Lain</option>
                            <option value="Lainnya" style="background:var(--l-bg);color:var(--l-text)">Lainnya...</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Detail Pesan <span style="color:#f43f5e">*</span></label>
                    <textarea id="cf_message" rows="5" placeholder="Jelaskan kebutuhan bisnis atau pertanyaan Anda di sini secara detail..." required style="width: 100%; padding: 12px 16px; background: rgba(0,0,0,0.2); border: 1px solid var(--l-border); border-radius: 8px; color: var(--l-text); outline: none; resize: vertical; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--l-primary)'" onblur="this.style.borderColor='var(--l-border)'"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="align-self: center; padding: 14px 40px; font-size: 1.1rem; margin-top: 10px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; border:none;">
                    <i class="ph-bold ph-paper-plane-tilt"></i> Kirim Pesan Sekarang
                </button>
            </form>
        </div>
    </section>

    <script>
        function sendToWhatsApp(e) {
            e.preventDefault();
            const name = document.getElementById('cf_name').value;
            const subject = document.getElementById('cf_subject').value;
            const msg = document.getElementById('cf_message').value;
            
            // Format pesan dan encode agar aman untuk URL (spasi dan enter tidak error)
            const encodedName = encodeURIComponent(name);
            const encodedSubject = encodeURIComponent(subject);
            const encodedMsg = encodeURIComponent(msg);
            
            let text = `Halo tim RAZ Creative Studio,%0A%0APerkenalkan, nama saya *${encodedName}*.%0A%0A*Tujuan:* ${encodedSubject}%0A*Pesan Detail:*%0A${encodedMsg}%0A%0AMohon informasi lebih lanjut. Terima kasih!`;
            
            // Redirect ke API WhatsApp
            window.open('https://wa.me/6282392925488?text=' + text, '_blank');
        }
    </script>

    <!-- RAZ Creative Studio External Footer -->
    <style>
        .raz-ext-footer { text-align: left;
            background: #0f0f13;
            color: #d1d5db;
            padding: 60px 20px 30px;
            font-family: 'Inter', sans-serif;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .raz-ext-footer .footer-grid {
            max-width: 1200px;
            margin: 0 auto 40px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
        }
        .raz-ext-footer .footer-col h4 {
            color: #fff;
            font-size: 1.1rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .raz-ext-footer .footer-col p { font-size: 0.9rem; line-height: 1.6; color: #a0a0a0; }
        .raz-ext-footer .footer-col ul { list-style: none; padding: 0; margin: 0; }
        .raz-ext-footer .footer-col ul li { margin-bottom: 12px; }
        .raz-ext-footer .footer-col ul li a {
            color: #a0a0a0;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
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
