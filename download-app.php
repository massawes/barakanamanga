<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Namanga School Android app. The APK is hosted as a GitHub Release asset
// (fixed address: each new version replaces the file, so the QR code and
// button never change). The QR image is assets/images/app-qr.svg.
const APP_DOWNLOAD_URL = 'https://github.com/massawes/namanga-files/releases/download/app/NamangaSchool.apk';

$pageTitle = 'Download the App | Namanga Secondary School';
$pageDescription = 'Download the free Namanga School Android app: notes, summaries, past papers, NECTA results and more from Namanga Secondary School, on your phone.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Namanga School App</h1>
        <p>All the school's learning resources on your Android phone, free.</p>
    </div>
</section>

<section class="page-content">
    <div class="container app-download">
        <div class="app-download-info">
            <h2 class="section-title-left">Get the app in 3 steps</h2>

            <ol class="app-download-steps">
                <li>
                    <span class="app-step-number">1</span>
                    <div>
                        <strong>Download</strong>
                        <p>Scan the QR code with your phone's camera, or tap the button below on your phone.</p>
                    </div>
                </li>
                <li>
                    <span class="app-step-number">2</span>
                    <div>
                        <strong>Install</strong>
                        <p>Open the downloaded file and tap <em>Install</em>. If your phone asks, allow installing
                        apps from this source. If Play Protect shows a warning, tap <em>More details</em> &rarr;
                        <em>Install anyway</em>: the app is not on the Play Store yet.</p>
                    </div>
                </li>
                <li>
                    <span class="app-step-number">3</span>
                    <div>
                        <strong>Open</strong>
                        <p>Find <em>Namanga School</em> on your phone and start learning.</p>
                    </div>
                </li>
            </ol>

            <div class="app-download-qr">
                <div class="app-qr-frame">
                    <img src="<?= e($base) ?>/assets/images/app-qr.svg" alt="QR code to download the Namanga School app" width="220" height="220">
                    <img class="app-qr-logo" src="<?= e($base) ?>/assets/images/logo-square.png" alt="" width="52" height="52">
                </div>
                <div class="app-qr-text">
                    <p class="app-qr-caption">Scan to download</p>
                    <a class="btn btn-gold" href="<?= e(APP_DOWNLOAD_URL) ?>" rel="noopener">Download for Android</a>
                    <p class="note">Free &middot; Android 7.0 or newer &middot; about 22 MB</p>
                </div>
            </div>
        </div>

        <div class="app-download-phone" aria-hidden="true">
            <div class="app-phone-frame">
                <img src="<?= e($base) ?>/assets/images/app-screenshot.jpg" alt="" width="480" height="1067" loading="lazy">
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
