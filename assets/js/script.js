/**
 * Namanga Digital Resource Centre — small progressive-enhancement script.
 * No frameworks. Handles the mobile hamburger menu and delete confirmations
 * (delete confirmation is also inline as a fallback in manage-resources.php).
 */
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');

    if (toggle && nav) {
        var setMenuOpen = function (isOpen) {
            nav.classList.toggle('open', isOpen);
            document.body.classList.toggle('nav-open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };
        var isMobileMenu = function () {
            return window.matchMedia('(max-width: 1100px)').matches;
        };

        toggle.addEventListener('click', function () {
            setMenuOpen(!nav.classList.contains('open'));
        });

        var closeBtn = document.getElementById('navClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () { setMenuOpen(false); });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setMenuOpen(false);
        });
        // Tapping the dimmed area beside the slide-in menu closes it.
        nav.addEventListener('click', function (e) {
            if (e.target === nav) setMenuOpen(false);
        });

        // On phones, tapping "Our School" / "The Latest" opens its sub-links
        // instead of leaving the page.
        nav.querySelectorAll('.dropdown-trigger').forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
                if (!isMobileMenu()) return;
                e.preventDefault();
                trigger.parentElement.classList.toggle('open');
            });
        });

        // Close the mobile menu when a real link inside it is clicked.
        nav.querySelectorAll('a:not(.dropdown-trigger)').forEach(function (link) {
            link.addEventListener('click', function () { setMenuOpen(false); });
        });
    }

    // Photo lightbox on The Latest (gallery) page — click a photo to see it
    // large, then use the arrows / keyboard to move between photos.
    var lightbox = document.getElementById('lightbox');
    var photoLinks = Array.prototype.slice.call(document.querySelectorAll('.gallery-photo-link'));
    if (lightbox && photoLinks.length) {
        var lbImg = lightbox.querySelector('img');
        var lbCaption = lightbox.querySelector('.lightbox-caption');
        var lbIndex = 0;
        var showPhoto = function (i) {
            lbIndex = (i + photoLinks.length) % photoLinks.length;
            lbImg.src = photoLinks[lbIndex].href;
            lbImg.alt = photoLinks[lbIndex].getAttribute('data-title') || '';
            lbCaption.textContent = photoLinks[lbIndex].getAttribute('data-title') || '';
        };
        var closeLightbox = function () {
            lightbox.hidden = true;
            document.body.classList.remove('nav-open');
        };
        photoLinks.forEach(function (link, i) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                showPhoto(i);
                lightbox.hidden = false;
                document.body.classList.add('nav-open');
            });
        });
        lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
        lightbox.querySelector('.lightbox-prev').addEventListener('click', function () { showPhoto(lbIndex - 1); });
        lightbox.querySelector('.lightbox-next').addEventListener('click', function () { showPhoto(lbIndex + 1); });
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) closeLightbox();
        });
        document.addEventListener('keydown', function (e) {
            if (lightbox.hidden) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') showPhoto(lbIndex - 1);
            if (e.key === 'ArrowRight') showPhoto(lbIndex + 1);
        });
        var prevSingle = photoLinks.length < 2;
        lightbox.querySelector('.lightbox-prev').hidden = prevSingle;
        lightbox.querySelector('.lightbox-next').hidden = prevSingle;
    }

    // Auto-dismiss alert banners after a few seconds.
    document.querySelectorAll('.alert').forEach(function (alertBox) {
        setTimeout(function () {
            alertBox.style.transition = 'opacity 0.4s ease';
            alertBox.style.opacity = '0';
            setTimeout(function () { alertBox.remove(); }, 400);
        }, 6000);
    });

    // Homepage hero slideshow — cross-fades through the images uploaded
    // in admin/manage-slides.php. Pure CSS opacity transition, no library.
    // Only the first photo is downloaded with the page; each later one is
    // fetched a few seconds before its turn, so the homepage opens fast.
    var slides = document.querySelectorAll('.hero-slide');
    var loadSlide = function (slide) {
        var url = slide && slide.getAttribute('data-bg');
        if (url) {
            slide.style.backgroundImage = "url('" + url + "')";
            slide.removeAttribute('data-bg');
        }
    };
    if (slides.length > 1) {
        var current = 0;
        setTimeout(function () { loadSlide(slides[1]); }, 1500);
        setInterval(function () {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            loadSlide(slides[current]);
            slides[current].classList.add('active');
            loadSlide(slides[(current + 1) % slides.length]);
        }, 5000);
    }

    // Show/hide password toggle (the "eye" icon) — works on any password
    // field wrapped as: <div class="password-field"> <input type="password">
    // <button type="button" class="password-toggle-btn">...eye svg...</button> </div>
    document.querySelectorAll('.password-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var field = btn.closest('.password-field');
            var input = field ? field.querySelector('input') : null;
            if (!input) return;

            var isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.classList.toggle('is-visible', isHidden);
            btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        });
    });
});
