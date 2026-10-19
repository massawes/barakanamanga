<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Namanga School Android app. The APK is hosted as a GitHub Release asset
// (fixed address: each new version replaces the file, so the QR code and
// button never change). The QR image is assets/images/app-qr.svg; the step
// pictures in assets/images/app-install/ are real screenshots from an
// Android 15 phone, with the button to tap marked in orange.
const APP_DOWNLOAD_URL = 'https://github.com/massawes/namanga-files/releases/download/app/NamangaSchool.apk';

// Install guide: [picture, Kiswahili title, Kiswahili text, English text].
$installSteps = [
    ['step-1-download-anyway.jpg', 'Bonyeza "Download anyway"',
        'Chrome huonyesha onyo hili kwa kila app isiyotoka Play Store. Ni la kawaida: bonyeza Download anyway.',
        'Chrome shows this for every app that is not from the Play Store. Tap Download anyway.'],
    ['step-2-install.jpg', 'Fungua faili, bonyeza "Install"',
        'Simu ikiuliza ruhusa ya "Install unknown apps", iwashe kwa Chrome (au Files), kisha rudi nyuma.',
        'Open the downloaded file and tap Install. If asked, allow "Install unknown apps", then go back.'],
    ['step-3-more-details.jpg', 'Bonyeza "More details"',
        'Google Play Protect inasema haijaiona app hii kwa sababu bado haiko Play Store. Bonyeza More details.',
        'Play Protect has not seen this app because it is not on the Play Store yet. Tap More details.'],
    ['step-4-install-anyway.jpg', 'Bonyeza "Install anyway"',
        'Bonyeza Install anyway (si OK). App itaanza kusakinishwa.',
        'Tap Install anyway (not OK). The app installs.'],
    ['step-5-open.jpg', 'Bonyeza "Open" – tayari!',
        'App ya Namanga School iko kwenye simu yako. Utaipata pia kwenye orodha ya app.',
        'Tap Open. You will also find Namanga School with your other apps.'],
];

$pageTitle = 'Download the App | Namanga Secondary School';
$pageDescription = 'Pakua app ya Namanga School bure kwa Android: notes, summaries, past papers, NECTA results na zaidi. Download the free Namanga School Android app.';
$base = base_url();
include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Namanga School App</h1>
        <p>Pakua app ya shule bure kwa simu za Android. &middot; Download the free school app for Android.</p>
    </div>
</section>

<section class="page-content">
    <div class="container app-download">
        <div class="app-download-info">
            <h2 class="section-title-left">Pakua app &middot; Get the app</h2>
            <p>Notes, summaries, past papers, NECTA results na zaidi, kwenye simu yako.<br>
            <span class="app-en">Notes, summaries, past papers, NECTA results and more, on your phone.</span></p>

            <div class="app-download-qr">
                <div class="app-qr-frame">
                    <img src="<?= e($base) ?>/assets/images/app-qr.svg" alt="QR code ya kupakua app ya Namanga School / QR code to download the app" width="220" height="220">
                    <img class="app-qr-logo" src="<?= e($base) ?>/assets/images/logo-square.png" alt="" width="52" height="52">
                </div>
                <div class="app-qr-text">
                    <p class="app-qr-caption">Skani kwa kamera ya simu<br><span class="app-en">Scan with your phone camera</span></p>
                    <a class="btn btn-gold" href="<?= e(APP_DOWNLOAD_URL) ?>" rel="noopener">Pakua / Download</a>
                    <p class="note">Bure &middot; Android 7.0 au zaidi &middot; takriban MB 44<br>
                    <span class="app-en">Free &middot; Android 7.0 or newer &middot; about 44 MB</span></p>
                </div>
            </div>

            <div class="app-trust">
                <h3>Je, app ni salama? &middot; Is the app safe?</h3>
                <p>Ndiyo. Imetengenezwa na Namanga Secondary School. Inaomba ruhusa <strong>moja tu: Internet</strong>.
                Haisomi picha, namba za simu (contacts), SMS wala mahali ulipo (location).<br>
                <span class="app-en">Yes. It is made by Namanga Secondary School and asks for one permission only: Internet.
                It does not read your photos, contacts, messages or location.</span></p>
            </div>
        </div>

        <div class="app-download-phone" aria-hidden="true">
            <div class="app-phone-frame">
                <img src="<?= e($base) ?>/assets/images/app-screenshot.jpg" alt="" width="480" height="1067" loading="lazy">
            </div>
        </div>
    </div>

    <div class="container">
        <h2 class="section-title-left">Jinsi ya kuweka app &middot; How to install</h2>
        <p>Kwa sababu app bado haiko Play Store, simu itaonyesha maonyo haya. Ni ya kawaida; fuata hatua hizi.
        Kitufe cha kubonyeza kimewekwa alama ya rangi ya chungwa.<br>
        <span class="app-en">Because the app is not on the Play Store yet, your phone shows these warnings. They are normal:
        follow these steps. The button to tap is marked in orange.</span></p>

        <ol class="app-install-steps">
            <?php foreach ($installSteps as $i => [$image, $titleSw, $textSw, $textEn]): ?>
                <li class="app-install-step">
                    <span class="app-step-number"><?= $i + 1 ?></span>
                    <img src="<?= e($base) ?>/assets/images/app-install/<?= e($image) ?>" alt="<?= e($titleSw) ?>" loading="lazy" width="440">
                    <h3><?= e($titleSw) ?></h3>
                    <p><?= e($textSw) ?></p>
                    <p class="app-en"><?= e($textEn) ?></p>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
