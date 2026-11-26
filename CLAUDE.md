# Namanga Secondary School website, newer copy (repo `massawes/barakanamanga`): notes for every Claude session

Claude does not remember old chats: **this file is the memory**. Update "WHERE WE STOPPED" after each piece of work,
so the owner can continue from any terminal. The owner writes Swahili: reply in short, simple Swahili.

## What it is
- Same school website as repo `massawes/namanga` (notes, past papers, gallery, admin panel `/admin/`), plus Docker
  files (`Dockerfile`, `docker-compose.yml`, `DOCKER.md`) and an AI assistant ("Ask AI", Groq key from GitHub secrets
  -> `config/ai.php`). Plain PHP + MySQL. Full description: `README.md`. Branch **master**.
- **THIS repo is the live website** (repo `namanga` deploys have failed since 2026-10; ignore it).
- **Live (MAIN since 2026-11-25): https://namangasecondary.sc.tz** (+ www) on the VPS 185.205.209.83, folder
  `/var/www/namanga-website`, database `namanga_website` (password only in the VPS `config/env.php`), AI key in VPS
  `config/ai.php`. Apache: `/etc/apache2/sites-available/namangasecondary.conf` (+ `-le-ssl.conf`, Let's Encrypt, auto-renew).
- **Deploy: commit, then `bash deploy.sh`** (push GitHub + copy code to VPS, backup in /root/backups). Uploads on the VPS
  are never overwritten or deleted; new uploads from git are only added.
- Old live: https://namangawebsite.freepage.cc (InfinityFree). A push still FTP-deploys there too until the owner turns it off.
- DNS at Sakurahost (billing.sakurahost.co.tz -> My Domains -> Sakura DNS Manager): A @ and A results/form1/ssms =
  185.205.209.83, CNAME www -> @, MX 10 mail.namangasecondary.sc.tz (no mail server yet).
- Other Namanga projects: namanga-ssms, namanga-form1 (VPS 185.205.209.83), namanga-results, namanga-files,
  namanga-keepalive. Each has its own CLAUDE.md.

## SYNC RULE (owner 2026-11-24): local = GitHub = live
- Local folder: `C:\xampp\htdocs\barakanamanga` (clone with `GIT_LFS_SKIP_SMUDGE=1 git clone git@github.com:massawes/barakanamanga.git`;
  big repo because of uploads/). `git pull` before starting.
- Every push to master runs `.github/workflows/deploy.yml` (builds config/env.php + config/ai.php from secrets, FTP to
  InfinityFree `htdocs/`). So: commit -> push -> `gh run watch` -> check the live page.
- Files in the deploy `exclude:` list never go to the website (README.md, CLAUDE.md, Docker files, database/**, ...).
- Commits that only change notes: add `[skip ci]` to the message so no deploy runs.

## WHERE WE STOPPED
- 2026-11-25: **MOVED TO THE VPS + domain namangasecondary.sc.tz.** Manual workflow `fetch-live-copy.yml` copied the live
  database (temporary PHP file, deleted again) + uploads/ + config/ai.php by FTP; artifact deleted after download.
  Imported: admins 63, resources 278, tasks 42, gallery 12, hero_slides 8; uploads 272 MB. Copies:
  `C:\Users\admin\Documents\NAMANGA BACKUP\website_infinityfree_2026-11-25\` and `/root/backups/namanga-website_infinityfree_2026-11-25.tgz`.
  Checked: home, about, notes, gallery, results, admin/login.php = 200; PDF download OK; config/ = 403.
  Anything uploaded on InfinityFree after 2026-11-25 20:16 is NOT on the VPS (copy again the same way if needed).
- 2026-11-17: last site work (top bar phone number; Head of School message under the slideshow).
- 2026-11-24: CLAUDE.md added (and excluded from the FTP deploy). No code change.
- 2026-11-25 (late): **Google moved to namangasecondary.sc.tz.** SITE_URL (includes/functions.php), robots.txt, schema
  alternateName and all result links now use the new domain. Old `deploy.yml` (full FTP deploy) REMOVED; new
  `redirect-old-site.yml` puts a 301 .htaccess on InfinityFree (google*.html verification file stays reachable).
  Search Console (owner's Google account): new URL-prefix property https://namangasecondary.sc.tz/ (auto-verified by the
  HTML file), sitemap submitted, **Change of address** freepage.cc -> namangasecondary.sc.tz CONFIRMED.
  deploy.sh now sends only files changed since the last deploy (~20 s); `bash deploy.sh --full` for everything.
- 2026-11-25 (night): **TWO WEBSITES, ONE CODEBASE** (owner: main site = promote the school; separate site for students).
  includes/site.php decides by host: namangasecondary.sc.tz = school website, masomo.namangasecondary.sc.tz = Student
  Portal (notes, theory, practical, search, Ask AI). Wrong-host pages 301 to the right host. Local test: ?site=portal|school.
  New school pages: academics.php (18 subjects from the Results System, includes/school-info.php), admissions.php
  (TAMISEMI selection; admission-type announcements listed), announcements.php, contact.php (map). New home page:
  quick links, facts, latest announcements, latest photos, map. Admin: Manage > Announcements / News
  (admin/manage-announcements.php, table announcements auto-created, attachments in uploads/announcements/).
  Per-site navbar, footer, sitemap.php, robots.php (.htaccess rewrites robots.txt). Portal home: includes/portal-home.php.
  VPS vhost sites-available/namanga-masomo.conf (+le-ssl), same folder. DNS A masomo -> 185.205.209.83 (Sakurahost).
  Search Console: property https://masomo.namangasecondary.sc.tz/ added + sitemap submitted.
  **MAINTENANCE MODE IS ON** (config/maintenance.on on the VPS, holds the preview code; includes/maintenance.php).
  Visitors get 503 + maintenance page; staff signed in and ?preview=<code> see the site. Turn OFF:
  ssh root@185.205.209.83 rm /var/www/namanga-website/config/maintenance.on   (do not leave it on for weeks: Google).
  Preview code is in C:\Users\admin\Documents\NAMANGA BACKUP\website_preview_link.txt (not in git).
- 2026-11-25 (late night): NECTA results automatic (includes/necta-results.php, cron/necta-check.php, /etc/cron.d/namanga-necta every 3 h,
  log /var/log/namanga-necta.log, state uploads/.necta-results.json). OWNER RULE: ONLY VERIFIED FACTS, never invent.
  Official contacts: email namanga@longido.dc.go.tz, P.O. Box 6560 Namanga (owner). Removed unverified 'founded 2007'.
  Mission & Vision page hidden (old text was invented) -> 301 to about.php until the school gives its official wording.
  Verified sources: girls dormitory + ICT room (LSF + CNS Luxembourg, Wanawake Tunaweza 2023-2024; Daily News, cns-asbl.org),
  subjects (Results System), NECTA S2911 results 2022-2025.
- 2026-11-25 (night): OWNER asked: website Staff Login super admin (admins.id 1, username 'admin') password reset to the
  owner's chosen password (not written here), must_change_password = 0. Backup: /root/backups/namanga-website_admins_kabla_reset_*.sql.
- 2026-11-26: MAINTENANCE TURNED OFF (owner rule: ON only while working on the site, OFF as soon as the work is done).
- 2026-11-26: PERMISSIONS (owner). Admins = 'admin' (owner) and 'second' (Second Master, admins.id 64, is_super_admin 1,
  temporary password must be changed at first login): both do everything. Teachers (role staff): upload notes, theory
  papers and practicals only (upload-others/software/summary now Admin-only); edit/delete ONLY their own files
  (resources.uploaded_by, can_manage_resource(), admin/my-resources.php 'My Uploads'); no gallery, slideshow,
  announcements, all-resources list or staff accounts; can change their own password. Old files (uploaded_by NULL)
  are managed by Admins only. Tested locally with two test teachers (A cannot edit/delete B's files, and vice versa).
- 2026-11-26: Reset Password (Manage Staff Accounts, admin + second): teacher password -> 12345678 + must change at next
  login (RESET_PASSWORD_DEFAULT in admin/manage-users.php). Admin accounts cannot be reset there. Tested locally.
- 2026-11-26: Ideas from ilboru.ac.tz (owner chose): slideshow replaced by ONE big photo (first image of Manage > Homepage
  Photo) + highlights (Best ICT Lab, Science Labs [icon until assets/images/school/science-lab.jpg exists], Skilled Teachers);
  'What We Learn' flip tiles (table departments, admin/manage-departments.php; Head shown only when a name is entered);
  Services (verified facts only); Alumni register alumni.php -> table alumni (private, admin/manage-alumni.php, CSV).
  No 'Get Our App' button (owner). Red maintenance strip for staff/preview while maintenance is on.
  Heads of Department: printable form for the Academic Master at
  C:\Users\admin\Documents\ORODHA ZA WANAFUNZI 2026\Wakuu wa Idara - Fomu ya Kujaza 2026.pdf (owner will bring the names).
- 2026-11-26: **TAKWIMU ZA WAGENI** (visitor statistics) kwenye mifumo yote 4. Maktaba moja `analytics.php` (nakala SAWA kwenye
  barakanamanga/includes, namanga-results/app, namanga-form1/app, namanga-ssms/includes: ukibadilisha moja, nakili kwa mingine).
  Kila ukurasa (GET, HTML, si robot, si AJAX) -> database `site_analytics` jedwali `hits` (VPS: user `nva`, nenosiri /etc/namanga-analytics.php;
  local: root, DB inajiunda). Hakuna IP inayohifadhiwa; cookie `nva` (mgeni, miaka 2) + `nva_s` (ziara, dakika 30). Data > miezi 26 inafutwa.
  Kurasa: tovuti admin/statistics.php (Super Admin = mifumo YOTE), results staff/visitors.php (super), form1 visitors.php (admin), ssms pages/visitors.php (admin).
