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
