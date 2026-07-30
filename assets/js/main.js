/**
 * Fut Arena — Main JavaScript
 * Performance optimized, no jQuery dependency
 */

(function() {
    'use strict';

    // ============================================
    // Lazy Load Images (fallback for older browsers)
    // ============================================
    if ('loading' in HTMLImageElement.prototype) {
        // Native lazy loading supported — no action needed
    } else {
        // Fallback: Intersection Observer
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        observer.unobserve(img);
                    }
                });
            });
            lazyImages.forEach(function(img) {
                if (img.dataset.src) observer.observe(img);
            });
        }
    }

    // ============================================
    // Mobile Menu Toggle
    // ============================================
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('is-open');
            const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !expanded);
        });
    }

    // ============================================
    // Smooth Scroll for Anchor Links
    // ============================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href').substring(1);
            if (!targetId) return;
            const target = document.getElementById(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ============================================
    // Newsletter Form
    // ============================================
    document.querySelectorAll('.newsletter-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = form.querySelector('input[type="email"]');
            if (!email || !email.value) return;

            // AJAX submit
            const formData = new FormData();
            formData.append('action', 'futarena_newsletter');
            formData.append('email', email.value);
            formData.append('nonce', window.futarenaData?.nonce || '');

            fetch(window.futarenaData?.ajaxUrl || '/', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    email.value = '';
                    email.placeholder = 'Obrigado por se inscrever! ✅';
                    email.disabled = true;
                } else {
                    email.placeholder = 'Tente novamente...';
                }
            })
            .catch(function() {
                email.placeholder = 'Erro. Tente novamente.';
            });
        });
    });

    // ============================================
    // Reading Progress Bar
    // ============================================
    const progressBar = document.getElementById('reading-progress');
    const articleContent = document.querySelector('.single-post__content');

    if (progressBar && articleContent) {
        const updateProgress = function() {
            const rect = articleContent.getBoundingClientRect();
            const total = articleContent.offsetHeight;
            const scrolled = Math.max(0, -rect.top);
            const progress = Math.min(100, (scrolled / total) * 100);
            progressBar.style.width = progress + '%';
        };

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    // ============================================
    // Preload LCP Image
    // ============================================
    const heroImg = document.querySelector('.hero__featured-img');
    if (heroImg && heroImg.dataset.src) {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = heroImg.dataset.src;
        document.head.appendChild(link);
    }

    // ============================================
    // Detect external links — add rel
    // ============================================
    document.querySelectorAll('a[href^="http"]').forEach(function(link) {
        if (!link.href.includes(window.location.hostname)) {
            if (!link.getAttribute('target')) {
                link.setAttribute('target', '_blank');
            }
            if (!link.getAttribute('rel')) {
                link.setAttribute('rel', 'noopener noreferrer');
            }
        }
    });

})();
