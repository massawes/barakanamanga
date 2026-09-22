/**
 * Namanga Digital Resource Centre — small progressive-enhancement script.
 * No frameworks. Handles the mobile hamburger menu and delete confirmations
 * (delete confirmation is also inline as a fallback in manage-resources.php).
 */
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close the mobile menu when a link inside it is clicked.
        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
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
    var slides = document.querySelectorAll('.hero-slide');
    if (slides.length > 1) {
        var current = 0;
        setInterval(function () {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
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
