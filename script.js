/* ============================================
   NAGACHETHANA PU COLLEGE - MAIN JAVASCRIPT
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

    /* ---- Header/Footer Component Support ---- */
    // These features depend on header/footer DOM elements.
    // They run on DOMContentLoaded (for inline header/footer)
    // AND re-run on 'componentsLoaded' (for dynamically loaded header/footer).
    function initHeaderFooterFeatures() {

        /* ---- 1. Announcement Bar Dismiss ---- */
        var announcementBar = document.getElementById('announcementBar');
        var closeAnnouncementBtn = document.getElementById('closeAnnouncement');

        if (closeAnnouncementBtn && announcementBar && !closeAnnouncementBtn.dataset.bound) {
            closeAnnouncementBtn.dataset.bound = '1';
            closeAnnouncementBtn.addEventListener('click', function () {
                announcementBar.classList.add('hidden');
                sessionStorage.setItem('announcementClosed', 'true');
            });

            if (sessionStorage.getItem('announcementClosed') === 'true') {
                announcementBar.classList.add('hidden');
            }
        }

        /* ---- 2. Sticky Navbar Scroll Effect ---- */
        var header = document.getElementById('header');

        function handleHeaderScroll() {
            if (!header) return;
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }

        window.addEventListener('scroll', handleHeaderScroll);
        handleHeaderScroll();

        /* ---- 3. Mobile Hamburger Menu ---- */
        var hamburgerBtn = document.getElementById('hamburgerBtn');
        var navMenu = document.getElementById('navMenu');
        var overlay = null;

        function createOverlay() {
            overlay = document.createElement('div');
            overlay.className = 'navbar__overlay';
            document.body.appendChild(overlay);
            overlay.addEventListener('click', closeMenu);
        }

        function openMenu() {
            if (!hamburgerBtn || !navMenu) return;
            hamburgerBtn.classList.add('active');
            hamburgerBtn.setAttribute('aria-expanded', 'true');
            navMenu.classList.add('open');
            document.body.style.overflow = 'hidden';

            if (!overlay) createOverlay();
            requestAnimationFrame(function () {
                if (overlay) overlay.classList.add('visible');
            });
        }

        function closeMenu() {
            if (!hamburgerBtn || !navMenu) return;
            hamburgerBtn.classList.remove('active');
            hamburgerBtn.setAttribute('aria-expanded', 'false');
            navMenu.classList.remove('open');
            document.body.style.overflow = '';

            if (overlay) {
                overlay.classList.remove('visible');
            }
        }

        if (hamburgerBtn && !hamburgerBtn.dataset.bound) {
            hamburgerBtn.dataset.bound = '1';
            hamburgerBtn.addEventListener('click', function () {
                var isOpen = navMenu.classList.contains('open');
                if (isOpen) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });
        }

        var navLinks = document.querySelectorAll('.navbar__link');
        navLinks.forEach(function (link) {
            if (link.dataset.bound) return;
            link.dataset.bound = '1';
            link.addEventListener('click', function () {
                if (navMenu && navMenu.classList.contains('open')) {
                    closeMenu();
                }
            });
        });

        /* ---- 8. Back to Top Button ---- */
        var backToTopBtn = document.getElementById('backToTop');

        function handleBackToTopVisibility() {
            if (!backToTopBtn) return;
            if (window.scrollY > 400) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        }

        window.addEventListener('scroll', handleBackToTopVisibility);
        handleBackToTopVisibility();

        if (backToTopBtn && !backToTopBtn.dataset.bound) {
            backToTopBtn.dataset.bound = '1';
            backToTopBtn.addEventListener('click', function () {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        /* ---- 9. Active Nav Link Highlighting ---- */
        function highlightActiveNav() {
            var currentPage = window.location.pathname.split('/').pop() || 'index.html';
            navLinks.forEach(function (link) {
                link.classList.remove('navbar__link--active');
                var href = link.getAttribute('href');
                if (!href) return;
                var linkPage = href.split('/').pop().split('#')[0] || 'index.html';
                if (linkPage === currentPage) {
                    link.classList.add('navbar__link--active');
                }
            });
        }

        highlightActiveNav();

        /* ---- 10. Smooth Scroll for Anchor Links ---- */
        document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
            anchor.addEventListener('click', function (e) {
                var targetId = this.getAttribute('href');
                if (targetId === '#' || targetId === '#!') return;

                var targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    var navHeight = header ? header.offsetHeight : 0;
                    var targetPos = targetElement.getBoundingClientRect().top + window.scrollY - navHeight - 10;

                    window.scrollTo({
                        top: targetPos,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // Run immediately for pages with inline header/footer
    initHeaderFooterFeatures();

    // Also run when components.js finishes loading header/footer
    document.addEventListener('componentsLoaded', initHeaderFooterFeatures);


    /* ---- 4. Animated Counter ---- */
    function animateCounter(element, target, duration) {
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            // Ease out cubic for smooth deceleration
            var easedProgress = 1 - Math.pow(1 - progress, 3);
            var current = Math.floor(easedProgress * target);
            element.textContent = current;

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                element.textContent = target;
            }
        }

        requestAnimationFrame(step);
    }


    /* ---- 5. Intersection Observer for Counters ---- */
    var counterElements = document.querySelectorAll('[data-target]');
    var counterObserved = new Set();

    var counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting && !counterObserved.has(entry.target)) {
                counterObserved.add(entry.target);
                var target = parseInt(entry.target.getAttribute('data-target'), 10);
                var duration = target > 100 ? 2000 : 1500;
                animateCounter(entry.target, target, duration);
            }
        });
    }, { threshold: 0.3 });

    counterElements.forEach(function (el) {
        counterObserver.observe(el);
    });


    /* ---- 6. Scroll Animations for Cards & Sections ---- */
    var animateElements = document.querySelectorAll(
        '.feature-card, .stream-card, .welcome-strip__container, .testimonials__carousel, .section__header'
    );

    var animateObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                animateObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    animateElements.forEach(function (el) {
        el.classList.add('animate-on-scroll');
        animateObserver.observe(el);
    });


    /* ---- 7. Testimonials Carousel ---- */
    var testimonialCards = document.querySelectorAll('.testimonial-card');
    var dots = document.querySelectorAll('.testimonials__dot');
    var prevBtn = document.getElementById('prevTestimonial');
    var nextBtn = document.getElementById('nextTestimonial');
    var currentTestimonial = 0;
    var totalTestimonials = testimonialCards.length;
    var autoPlayInterval = null;

    function showTestimonial(index) {
        // Wrap around
        if (index < 0) index = totalTestimonials - 1;
        if (index >= totalTestimonials) index = 0;

        testimonialCards.forEach(function (card, i) {
            card.classList.remove('testimonial-card--active');
            if (i === index) {
                card.classList.add('testimonial-card--active');
            }
        });

        dots.forEach(function (dot, i) {
            dot.classList.remove('testimonials__dot--active');
            if (i === index) {
                dot.classList.add('testimonials__dot--active');
            }
        });

        currentTestimonial = index;
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            showTestimonial(currentTestimonial - 1);
            resetAutoPlay();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            showTestimonial(currentTestimonial + 1);
            resetAutoPlay();
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            var index = parseInt(this.getAttribute('data-index'), 10);
            showTestimonial(index);
            resetAutoPlay();
        });
    });

    // Auto-play testimonials every 5 seconds
    function startAutoPlay() {
        autoPlayInterval = setInterval(function () {
            showTestimonial(currentTestimonial + 1);
        }, 5000);
    }

    function resetAutoPlay() {
        clearInterval(autoPlayInterval);
        startAutoPlay();
    }

    if (totalTestimonials > 1) {
        startAutoPlay();
    }


    /* ---- 12. Form Validation (for contact/admission pages) ---- */
    var forms = document.querySelectorAll(
        '.admission-form, .contact-form, #admissionForm, #contactForm'
    );

    function validateField(field) {
        var value = field.value.trim();
        var type = field.type;
        var name = (field.name || '').toLowerCase();
        var errorMsg = '';

        clearFieldError(field);

        if (field.hasAttribute('required') && value === '') {
            errorMsg = 'This field is required.';
        } else if (type === 'email' && value !== '') {
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(value)) {
                errorMsg = 'Please enter a valid email address.';
            }
        } else if ((type === 'tel' || name.includes('phone') || name.includes('mobile')) && value !== '') {
            var phonePattern = /^[6-9]\d{9}$/;
            var cleaned = value.replace(/[\s\-\+()]/g, '');
            if (cleaned.startsWith('91') && cleaned.length === 12) {
                cleaned = cleaned.substring(2);
            }
            if (!phonePattern.test(cleaned)) {
                errorMsg = 'Please enter a valid 10-digit mobile number.';
            }
        } else if (field.tagName === 'SELECT' && field.hasAttribute('required')) {
            if (value === '' || value === 'default' || value === 'select') {
                errorMsg = 'Please select an option.';
            }
        }

        if (errorMsg) {
            showFieldError(field, errorMsg);
            return false;
        }

        if (value !== '') showFieldSuccess(field);
        return true;
    }

    function showFieldError(field, message) {
        field.classList.add('input-error');
        field.classList.remove('input-success');
        var wrapper = field.closest('.form-group') || field.parentElement;
        if (!wrapper.querySelector('.field-error')) {
            var span = document.createElement('span');
            span.classList.add('field-error');
            span.textContent = message;
            span.style.cssText = 'color:#d32f2f;font-size:0.8rem;margin-top:4px;display:block;';
            wrapper.appendChild(span);
        }
    }

    function showFieldSuccess(field) {
        field.classList.remove('input-error');
        field.classList.add('input-success');
    }

    function clearFieldError(field) {
        field.classList.remove('input-error', 'input-success');
        var wrapper = field.closest('.form-group') || field.parentElement;
        var existing = wrapper.querySelector('.field-error');
        if (existing) existing.remove();
    }

    forms.forEach(function (form) {
        var fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(function (field) {
            field.addEventListener('blur', function () {
                validateField(field);
            });
            field.addEventListener('input', function () {
                clearFieldError(field);
            });
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var allValid = true;

            fields.forEach(function (field) {
                if (!validateField(field)) {
                    allValid = false;
                }
            });

            if (!allValid) {
                var firstError = form.querySelector('.input-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return;
            }

            // If the form points to Formspree, submit via fetch
            if (form.action && form.action.indexOf('formspree.io') !== -1) {
                var submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Sending…'; }

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (response) {
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message'; }
                    if (response.ok) {
                        var successMsg = document.createElement('div');
                        successMsg.classList.add('form-success-message');
                        successMsg.style.cssText =
                            'background:#E8F5E9;color:#1B5E20;padding:16px 20px;border-radius:8px;' +
                            'margin-top:20px;font-weight:500;border:1px solid #A5D6A7;';
                        successMsg.innerHTML =
                            '<strong>Thank you!</strong> Your message has been sent. We will get back to you shortly.';
                        form.appendChild(successMsg);
                        form.reset();
                        fields.forEach(function (f) { f.classList.remove('input-success'); });
                        setTimeout(function () { successMsg.remove(); }, 5000);
                    } else {
                        var errMsg = document.createElement('div');
                        errMsg.style.cssText =
                            'background:#FFEBEE;color:#C62828;padding:16px 20px;border-radius:8px;' +
                            'margin-top:20px;font-weight:500;border:1px solid #EF9A9A;';
                        errMsg.innerHTML = '<strong>Oops!</strong> Something went wrong. Please try again or call us directly.';
                        form.appendChild(errMsg);
                        setTimeout(function () { errMsg.remove(); }, 5000);
                    }
                })
                .catch(function () {
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Message'; }
                    var errMsg = document.createElement('div');
                    errMsg.style.cssText =
                        'background:#FFEBEE;color:#C62828;padding:16px 20px;border-radius:8px;' +
                        'margin-top:20px;font-weight:500;border:1px solid #EF9A9A;';
                    errMsg.innerHTML = '<strong>Network error.</strong> Please check your connection and try again.';
                    form.appendChild(errMsg);
                    setTimeout(function () { errMsg.remove(); }, 5000);
                });
            } else {
                // Fallback for forms without a real endpoint
                var successMsg = document.createElement('div');
                successMsg.classList.add('form-success-message');
                successMsg.style.cssText =
                    'background:#E8F5E9;color:#1B5E20;padding:16px 20px;border-radius:8px;' +
                    'margin-top:20px;font-weight:500;border:1px solid #A5D6A7;';
                successMsg.innerHTML =
                    '<strong>Thank you!</strong> Your enquiry has been submitted successfully. We will get back to you shortly.';
                form.appendChild(successMsg);
                form.reset();
                fields.forEach(function (f) { f.classList.remove('input-success'); });
                setTimeout(function () { successMsg.remove(); }, 5000);
            }
        });
    });


    /* ---- 13. Gallery Filter & Lightbox (for gallery page) ---- */
    var filterBtns = document.querySelectorAll('.gallery-filter-btn');
    var galleryItems = document.querySelectorAll('.gallery-item');

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            var category = btn.getAttribute('data-filter');

            galleryItems.forEach(function (item) {
                var itemCat = item.getAttribute('data-category');
                if (category === 'all' || itemCat === category) {
                    item.style.display = '';
                    item.classList.remove('hidden');
                    item.classList.add('show');
                } else {
                    item.classList.remove('show');
                    item.classList.add('hidden');
                    setTimeout(function () {
                        if (item.classList.contains('hidden')) {
                            item.style.display = 'none';
                        }
                    }, 300);
                }
            });
        });
    });

    // Lightbox for gallery images
    var galleryImages = document.querySelectorAll('.gallery-item img');
    var lightbox = null;

    function openLightbox(src, alt) {
        if (!lightbox) {
            lightbox = document.createElement('div');
            lightbox.style.cssText =
                'position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;' +
                'display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.3s;';
            lightbox.innerHTML =
                '<button style="position:absolute;top:20px;right:20px;color:#fff;font-size:2rem;' +
                'background:none;border:none;cursor:pointer;z-index:10000;" aria-label="Close">&times;</button>' +
                '<img style="max-width:90%;max-height:90%;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,0.5);" src="" alt="">';
            document.body.appendChild(lightbox);

            lightbox.querySelector('button').addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', function (e) {
                if (e.target === lightbox) closeLightbox();
            });
        }

        lightbox.querySelector('img').src = src;
        lightbox.querySelector('img').alt = alt || '';
        lightbox.style.display = 'flex';
        requestAnimationFrame(function () {
            lightbox.style.opacity = '1';
        });
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.style.opacity = '0';
        setTimeout(function () {
            lightbox.style.display = 'none';
        }, 300);
        document.body.style.overflow = '';
    }

    galleryImages.forEach(function (img) {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function () {
            var fullSrc = img.getAttribute('data-full') || img.src;
            openLightbox(fullSrc, img.alt);
        });
    });

    // Close lightbox on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox();
    });

});
