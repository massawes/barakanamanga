<?php
/**
 * Maintenance mode (owner 2026-11-25).
 *
 * ON  when the file config/maintenance.on exists  (on the VPS:  touch /var/www/namanga-website/config/maintenance.on)
 * OFF when it does not                            (on the VPS:  rm    /var/www/namanga-website/config/maintenance.on)
 *
 * While ON, visitors see a "maintenance" page (HTTP 503 + Retry-After, so Google keeps the site listed).
 * These still work normally: staff who are signed in, everything under /admin/ (so staff can sign in),
 * the sitemap, and anyone who opened the site once with ?preview=<code in config/maintenance.on>.
 */
function maintenance_gate(): void
{
    $flag = __DIR__ . '/../config/maintenance.on';
    if (!is_file($flag) || PHP_SAPI === 'cli') {
        return;
    }
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (str_contains($script, '/admin/') || str_contains($script, '/api/') || basename($script) === 'sitemap.php') {
        return;
    }
    $code = trim((string) @file_get_contents($flag));
    $signedInStaff = !empty($_SESSION['admin_id']);
    $previewCookieOk = $code !== '' && hash_equals($code, (string) ($_COOKIE['nss_preview'] ?? ''));
    if ($code !== '' && ($signedInStaff || $previewCookieOk || hash_equals($code, (string) ($_GET['preview'] ?? '')))) {
        // One preview works on both websites (school + masomo.): the cookie is shared by all
        // *.namangasecondary.sc.tz addresses. Its name is unique, so the other systems ignore it.
        // Always (re)issued, so an older cookie that only worked on one address is upgraded,
        // and signed-in staff can move between the two websites.
        $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
        $shared = $host === 'namangasecondary.sc.tz' || str_ends_with($host, '.namangasecondary.sc.tz');
        setcookie('nss_preview', $code, ['expires' => time() + 30 * 86400, 'path' => '/', 'domain' => $shared ? '.namangasecondary.sc.tz' : '',
            'secure' => true, 'httponly' => true, 'samesite' => 'Lax']);
        return;
    }
    if ($signedInStaff) {
        return;
    }

    http_response_code(503);
    header('Retry-After: 86400');
    header('Cache-Control: no-store');
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Maintenance | Namanga Secondary School</title>
<meta name="robots" content="noindex">
<link rel="icon" type="image/png" href="/assets/images/favicon.png">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
       font-family:Inter,"Segoe UI",Roboto,Arial,sans-serif;background:#0b1d3a;color:#fff;text-align:center}
  .card{max-width:620px;width:100%}
  .crest{display:flex;align-items:center;justify-content:center;gap:18px;margin-bottom:22px}
  .crest img{height:86px;width:auto}
  .country{font-size:.85rem;letter-spacing:.08em;text-transform:uppercase;color:#cfd8e6}
  h1{font-size:1.6rem;margin:6px 0 2px;color:#f5b301;letter-spacing:.02em}
  .motto{font-style:italic;color:#cfd8e6;font-size:.95rem}
  .badge{display:inline-flex;align-items:center;gap:8px;margin:28px 0 14px;padding:8px 16px;border:1.5px solid #f5b301;
         border-radius:999px;color:#f5b301;font-weight:700;letter-spacing:.06em;text-transform:uppercase;font-size:.85rem}
  .gear{width:18px;height:18px;animation:spin 4s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
  h2{font-size:1.35rem;margin-bottom:10px}
  p{color:#dfe6f1;line-height:1.6;margin-bottom:8px}
  .en{color:#9fb0c8;font-size:.92rem}
  .links{margin-top:26px;display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
  .links a{color:#fff;text-decoration:none;border:1.5px solid #3b5b8f;border-radius:10px;padding:10px 14px;font-size:.92rem}
  .links a:hover{border-color:#f5b301;color:#f5b301}
  .contact{margin-top:26px;font-size:.9rem;color:#9fb0c8}
  .contact a{color:#f5b301;text-decoration:none}
</style>
</head>
<body>
<main class="card">
  <div class="crest">
    <img src="/assets/images/coat-of-arms.png" alt="Nembo ya Taifa">
    <img src="/assets/images/logo-white.png" alt="Namanga Secondary School" onerror="this.src='/assets/images/logo-square.png'">
  </div>
  <div class="country">The United Republic of Tanzania</div>
  <h1>NAMANGA SECONDARY SCHOOL</h1>
  <div class="motto">Education for Liberty and Prosperity</div>

  <div class="badge">
    <svg class="gear" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.14 12.94a7.07 7.07 0 0 0 0-1.88l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.61-.22l-2.39.96a7 7 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.9 2h-3.8a.5.5 0 0 0-.49.42l-.36 2.54a7 7 0 0 0-1.63.94l-2.39-.96a.5.5 0 0 0-.61.22L2.7 8.48a.5.5 0 0 0 .12.64l2.03 1.58a7.07 7.07 0 0 0 0 1.88l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32c.13.22.39.3.61.22l2.39-.96c.5.39 1.05.7 1.63.94l.36 2.54c.05.24.25.42.49.42h3.8c.24 0 .45-.18.49-.42l.36-2.54a7 7 0 0 0 1.63-.94l2.39.96c.22.08.48 0 .61-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 12 8.5a3.5 3.5 0 0 1 0 7z"/></svg>
    Maintenance Mode
  </div>
  <h2>Tovuti iko kwenye maboresho</h2>
  <p>Tunaboresha tovuti ya shule ili ikuhudumie vizuri zaidi. Tafadhali rudi baada ya muda mfupi.</p>
  <p class="en">Our website is undergoing maintenance and improvements. Please check back soon.</p>

  <div class="links">
    <a href="https://results.namangasecondary.sc.tz">Matokeo ya Wanafunzi</a>
  </div>
  <div class="contact">Mawasiliano: <a href="tel:+255755958326">0755 958 326</a> &middot; <a href="mailto:namanga@longido.dc.go.tz">namanga@longido.dc.go.tz</a></div>
</main>
</body>
</html>
<?php
    exit;
}
