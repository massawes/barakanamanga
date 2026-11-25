<?php
// robots.txt for whichever website is being viewed (school or Student Portal, see includes/site.php).
// .htaccess sends /robots.txt here. Light on purpose: no database, no session.
require_once __DIR__ . '/includes/site.php';
header('Content-Type: text/plain; charset=utf-8');
$origin = current_site_url();
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /config/\n";
echo "Disallow: /database/\n";
echo "Disallow: /uploads/_needs_review/\n";
echo "\n";
echo "Sitemap: {$origin}/sitemap.xml\n";
