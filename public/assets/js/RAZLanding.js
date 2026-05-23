document.addEventListener('DOMContentLoaded', () => {
    // Theme Switcher Logic
    const themeBtn = document.getElementById('theme-toggle');
    const body = document.body;

    // Check localStorage for saved theme
    const savedTheme = localStorage.getItem('raz_theme');
    if (savedTheme === 'light') {
        body.classList.add('light-mode');
        if (themeBtn) themeBtn.innerHTML = '<i class="ph-bold ph-moon"></i>';
    } else {
        if (themeBtn) themeBtn.innerHTML = '<i class="ph-bold ph-sun"></i>';
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (body.classList.contains('light-mode')) {
                body.classList.remove('light-mode');
                localStorage.setItem('raz_theme', 'dark');
                themeBtn.innerHTML = '<i class="ph-bold ph-sun"></i>';
            } else {
                body.classList.add('light-mode');
                localStorage.setItem('raz_theme', 'light');
                themeBtn.innerHTML = '<i class="ph-bold ph-moon"></i>';
            }
        });
    }

    // Smooth Scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = mobileMenuBtn.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.replace('ph-list', 'ph-x');
            } else {
                icon.classList.replace('ph-x', 'ph-list');
            }
        });
        
        // Close menu when clicking a link
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                if(mobileMenuBtn.querySelector('i')) {
                    mobileMenuBtn.querySelector('i').classList.replace('ph-x', 'ph-list');
                }
            });
        });
    }


    // Scroll Reveal Animation
    const revealOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    };

    const revealOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target); // Optional: stop observing once revealed
            }
        });
    }, revealOptions);

    document.querySelectorAll('.reveal').forEach(element => {
        revealOnScroll.observe(element);
    });

});
