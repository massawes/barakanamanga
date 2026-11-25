<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Contact Us | Namanga Secondary School';
$pageDescription = 'Contact Namanga Secondary School: P.O. Box 6560 Namanga, Longido District, Arusha, Tanzania. Phone +255 755 958 326, email namanga@longido.dc.go.tz.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We are happy to hear from parents, students, former students and friends of the school</p>
    </div>
</section>

<section class="page-content">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-card">
                <h3><?= svg_icon('pin', 'icon-svg icon-svg-small') ?> Address</h3>
                <p>Namanga Secondary School<br>P.O. Box 6560, Namanga<br>Longido District, Arusha Region<br>Tanzania</p>
            </div>
            <div class="contact-card">
                <h3><?= svg_icon('chat', 'icon-svg icon-svg-small') ?> Phone &amp; WhatsApp</h3>
                <p><a href="tel:+255755958326">+255 755 958 326</a></p>
                <p><a class="btn btn-gold btn-small" href="https://wa.me/255755958326?text=Habari%20Namanga%20Secondary%20School" target="_blank" rel="noopener">Chat on WhatsApp</a></p>
            </div>
            <div class="contact-card">
                <h3><?= svg_icon('send', 'icon-svg icon-svg-small') ?> Email</h3>
                <p><a href="mailto:namanga@longido.dc.go.tz">namanga@longido.dc.go.tz</a></p>
                <p class="note">For results, use the <a href="https://results.namangasecondary.sc.tz">Student Result System</a>.</p>
            </div>
        </div>

        <div class="location-grid contact-map">
            <div class="location-info">
                <h3>How to find us</h3>
                <p>The school is in Namanga ward, Longido District, in the border town of Namanga where Tanzania
                meets Kenya, on the Arusha&ndash;Nairobi road.</p>
                <a class="btn btn-gold" href="https://www.google.com/maps/dir/?api=1&amp;destination=Namanga+Secondary+School%2C+Namanga%2C+Tanzania" target="_blank" rel="noopener">Get directions</a>
                <h3 style="margin-top:24px;">Follow us</h3>
                <ul class="contact-social">
                    <li><a href="https://www.facebook.com/namangasecondaryschool" target="_blank" rel="noopener">Facebook</a></li>
                    <li><a href="https://www.youtube.com/@namangasecondaryschool" target="_blank" rel="noopener">YouTube</a></li>
                    <li><a href="https://www.tiktok.com/@namangasecondaryschool" target="_blank" rel="noopener">TikTok</a></li>
                </ul>
            </div>
            <div class="map-frame">
                <iframe title="Map: Namanga Secondary School" src="https://www.google.com/maps?q=Namanga+Secondary+School,+Namanga,+Tanzania&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
