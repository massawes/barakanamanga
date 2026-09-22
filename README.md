# Namanga Secondary School — ICT & Computer Science Digital Resource Centre

A complete, working, database-driven learning resource website built for
Namanga Secondary School. Students browse and download complete ICT &
Computer Science resources (Form 1–4); a teacher/administrator uploads and
manages those resources through a secure admin panel — no code editing
required for day-to-day use.

## 1. Project Purpose

This is **only** a digital resource centre — notes, summaries, past papers
and software. It intentionally does not include fees, attendance, payroll,
exams, or a student login/parent portal.

**Core principle:** the teacher uploads a complete file → the system stores
it and saves a database record → the resource automatically appears on the
public website → the student downloads the complete file.

## 2. Features

- Public site: Home, Notes, Summary, Theory Past Papers, Form 4 Practical,
  Software, Search — fully responsive with a mobile hamburger menu.
  (About/Contact pages still exist as files but are currently unlinked from
  the navigation and footer; re-add them any time by editing
  `includes/navbar.php` and `includes/footer.php`.)
- Every resource is **one complete downloadable file** (a full notes book,
  a full past paper, a full software package) — nothing is split into
  chapters.
- **Theory Past Papers** uses a drill-down flow: choose a Form first, then
  see only that Form's papers as a simple list of titles, then click a
  title to land on a page with clear **View** / **Download** buttons
  (`resource-view.php`).
- **Bulk upload**: Notes, Summary, Theory and Practical upload forms let
  you select several files at once, or an entire folder in one click —
  every file inside becomes its own resource under the Form (and Year,
  where relevant) you choose once for the whole batch. Titles are
  auto-generated from filenames when left blank.
- **Automatic filesystem sync**: you don't have to use the upload forms at
  all if you'd rather work directly in Windows. Copy a file straight into
  the right `uploads/...` folder (see the table in section 6) and it
  appears on the website by itself — no button to click. Delete a file
  from that folder in Windows Explorer and it disappears from the website
  by itself too. See **"Adding or removing files by hand"** below for how
  this works and its limits.
- **Homepage slideshow**: manage the rotating banner photos on the
  homepage entirely from the admin panel (`admin/manage-slides.php`) —
  upload, reorder, or delete images with no code editing. If no slides are
  uploaded, a plain navy banner is shown instead.
- Clean inline SVG icons throughout (no emoji, no external icon fonts) —
  everything renders identically with no internet connection, which
  matters since this runs locally under XAMPP.
- Admin panel (`/admin/`): secure login, dashboard with live statistics,
  upload forms for each resource type, resource management (edit/delete),
  homepage slideshow management, logout.
- **Two account roles — Admin and Staff:**
  - **Admin** — full control: upload, edit, view, **delete** resources and
    slides, and manage every staff account (promote, demote, remove).
  - **Staff** — can upload, edit and view resources, but **cannot delete**
    anything (no Delete buttons are shown, and the delete actions also
    refuse the request on the server even if attempted directly).
  - Anyone can create their own **Staff** account from the public
    **Register** link on the login page (`admin/register.php`) — no
    approval step, just a username and password. Only the very first
    account created via `admin/create-admin.php` is an Admin; every
    self-registered account is Staff by design.
  - An Admin manages every account from **Manage Staff Accounts**
    (`admin/manage-users.php`): promote a Staff member to Admin, demote an
    Admin back to Staff, or delete an account. The system will not let the
    last remaining Admin account be demoted, deleted, or change its own
    role/delete itself while logged in, so the site can never end up with
    zero Admins.
- Global search across all resource types.
- Resources are 100% database-driven — nothing is hard-coded in HTML.

## 3. Technology

HTML5, CSS3, vanilla JavaScript, PHP 8+ with PDO, MySQL/MariaDB, Apache
(via XAMPP). No frameworks.

## 4. Requirements

- XAMPP (PHP 8.0+, MySQL/MariaDB, Apache) — https://www.apachefriends.org
- A modern web browser

## 5. Installation (XAMPP)

1. **Install XAMPP** and start it. In the XAMPP Control Panel, click
   **Start** next to both **Apache** and **MySQL**.

2. **Copy the project.** Extract/copy the whole `namanga-resource-centre`
   folder into your XAMPP `htdocs` folder, so you end up with:

   ```
   C:\xampp\htdocs\namanga-resource-centre\      (Windows)
   /Applications/XAMPP/htdocs/namanga-resource-centre/   (macOS)
   /opt/lampp/htdocs/namanga-resource-centre/    (Linux)
   ```

3. **Create the database.**
   - Open `http://localhost/phpmyadmin` in your browser.
   - Click **Import**, choose the file `database/namanga.sql` from this
     project, and click **Go**.
   - This creates the `namanga_resource_centre` database with the
     `admins` and `resources` tables. It does **not** create an admin
     account yet — that's done safely in step 5.

4. **Configure the database connection** (only needed if your MySQL
   username/password differ from the XAMPP defaults). Open
   `config/database.php` and edit:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'namanga_resource_centre');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // XAMPP default is an empty password
   ```

   Also set `define('APP_DEBUG', false);` once the site is live/public, so
   raw errors are never shown to visitors.

5. **Create the first admin account.** Open:

   ```
   http://localhost/namanga-resource-centre/admin/create-admin.php
   ```

   Fill in a username and a password (minimum 8 characters) and submit.
   The password is hashed with PHP's `password_hash()` before being
   stored — it is never saved as plain text. **This page disables itself
   automatically** once an admin account exists, so it's safe to leave in
   place.

6. **Visit the site:**

   ```
   http://localhost/namanga-resource-centre/
   ```

### Upgrading an existing install (already imported an older `namanga.sql`)

If your database already has `admins` and `resources` but was set up before
the homepage slideshow feature existed, just add the one missing table —
your existing data is untouched. In phpMyAdmin, open the **SQL** tab on
`namanga_resource_centre` and run:

```sql
CREATE TABLE IF NOT EXISTS hero_slides (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_name VARCHAR(255) NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    caption VARCHAR(255) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

If your install was set up before the **Admin/Staff roles** feature
existed, also run this (your existing account keeps working — it is
promoted to a full Admin automatically by the second line):

```sql
ALTER TABLE admins ADD COLUMN role ENUM('admin','staff') NOT NULL DEFAULT 'staff';
UPDATE admins SET role = 'admin';
```

If you have more than one existing account and only want a specific one
to be Admin (everyone else stays/becomes Staff), replace the second line
with, for example:

```sql
UPDATE admins SET role = 'admin' WHERE username = 'headteacher';
```

## 6. Logging In & Uploading Resources

1. Go to `http://localhost/namanga-resource-centre/admin/login.php` and
   sign in with the account you created in step 5.
2. From the **Dashboard**, use the quick-action buttons, or the sidebar,
   to reach an upload page:

   - **Upload Notes** (`admin/upload-notes.php`) — Title, Form, optional
     description, one complete file.
   - **Upload Summary** (`admin/upload-summary.php`) — same fields as
     Notes.
   - **Upload Theory Examination** (`admin/upload-theory.php`) — Title,
     Form, Year, optional description, one complete file.
   - **Upload Practical** (`admin/upload-practical.php`) — Title, Year,
     optional description, one complete file. Form is always locked to
     Form 4 — it cannot be changed.
   - **Upload Software** (`admin/upload-software.php`) — Software Name,
     Version, Description, one file (ZIP/RAR/7Z/EXE/MSI).

3. Click **Upload**. You'll see "Resource uploaded successfully," and the
   resource immediately appears on the matching public page — no code
   changes needed.

**Allowed document file types:** PDF, DOC, DOCX, PPT, PPTX, ZIP, RAR.
**Allowed software file types:** ZIP, RAR, 7Z, EXE, MSI.
**Maximum upload size:** 200 MB per file (raise this by also increasing
`upload_max_filesize` and `post_max_size` in your `php.ini` if you need
larger files, then restart Apache).

### Adding or removing files by hand (no upload form needed)

You can skip the website's upload form entirely and just copy files
straight into the right folder on disk — the website notices and
publishes them by itself, usually within a few seconds of the next page
being opened by anyone (you or a student). The same goes in reverse: if
you delete a file from these folders in Windows Explorer, it disappears
from the website by itself too, without you having to go into Manage
Resources.

Copy files into the matching folder, using a Form sub-folder where shown:

| Resource type | Folder to copy into |
|---|---|
| Notes | `uploads\notes\form1\`, `form2\`, `form3\`, or `form4\` |
| Summary | `uploads\summaries\form1\` ... `form4\` |
| Theory Past Paper | `uploads\theory\form1\` ... `form4\` |
| Form 4 Practical | `uploads\practical\form4\` |
| Software | `uploads\software\` (no Form sub-folder) |

A few things worth knowing about this:

- **Title** is generated automatically from the filename (e.g.
  `Form2_Networking_Notes.pdf` becomes "Form2 Networking Notes"). Rename
  the file to whatever you want the title to read before copying it in.
- **Year** (for Theory papers) is picked up automatically only if the
  filename contains a 4-digit year, e.g. `ICT_Theory_2022.pdf`. Otherwise
  it is left blank and you can fill it in afterwards from **Manage
  Resources → Edit**.
- Only the same file types the upload form accepts (see the list just
  above) are ever picked up this way — a `.php` file or any other
  unsupported/unsafe file type dropped into `uploads/` is silently
  ignored, exactly like the upload form would reject it. This keeps the
  automatic sync just as safe as uploading through the website.
- This check runs at most once every few seconds (not on literally every
  single page view), so there can be a short delay — normally just a few
  seconds — between copying a file in and seeing it appear on the site.
- This is one-way reconciliation with what's on disk: it will never
  touch a file you haven't added or removed yourself, and it never
  invents a Form/Year it can't detect — it just leaves those blank for
  you to fill in via Edit if needed.

## 7. Editing & Deleting Resources

Go to **Manage Resources** (`admin/manage-resources.php`) to see every
resource in a table, filterable by type.

- **Edit** — update the title, description, Form, year or version, and
  optionally replace the file itself (leave the file field empty to keep
  the current file).
- **Delete** — asks for confirmation, then removes both the database
  record and the physical file from the server, so no orphaned files are
  left behind. **Delete is Admin-only** — Staff accounts do not see the
  Delete button, and the delete action itself refuses the request even if
  a Staff account tries it directly.

## 8. How Students Download Resources

Students need no account. They browse to a category (Notes, Summary,
Theory Past Papers, Form 4 Practical, Software), pick a Form where
applicable, and click **Download** (PDFs also offer a **View** button that
opens the file in a new tab). The global search box in the navbar searches
titles and descriptions across every resource type.

## 9. Project Structure

```
namanga-resource-centre/
├── index.php, notes.php, summaries.php, theory.php,
│   practical.php, software.php, search.php, about.php, contact.php
├── download.php                  (secure file download/view handler)
├── admin/
│   ├── login.php, logout.php, create-admin.php, dashboard.php
│   ├── upload-notes.php, upload-summary.php, upload-theory.php,
│   │   upload-practical.php, upload-software.php
│   ├── manage-resources.php, edit-resource.php, delete-resource.php
├── config/database.php           (single source of DB credentials)
├── includes/                     (header, navbar, footer, auth, functions,
│                                   admin layout, resource-card partial)
├── assets/css/style.css, assets/js/script.js, assets/images/
├── uploads/                       (notes/, summaries/, theory/, practical/, software/)
└── database/namanga.sql
```

## 10. Security

- Passwords hashed with `password_hash()` / verified with
  `password_verify()` — never stored or shown in plain text.
- All database queries use PDO **prepared statements** — no string
  concatenation of user input into SQL.
- All output is escaped with `htmlspecialchars()`.
- Every admin page calls `require_admin_login()`, which redirects
  unauthenticated visitors straight to `admin/login.php`.
- Sessions are regenerated on login (session fixation protection) and
  fully destroyed on logout.
- All state-changing forms (upload, edit, delete, login, create-admin)
  are protected with **CSRF tokens**.
- Uploads are validated by extension **and** MIME type; PHP and other
  server-executable extensions are always rejected; files are renamed to
  random names on disk (the original filename is kept only as a label,
  never trusted or used as a path); the `uploads/` folder has an
  `.htaccess` that disables script execution entirely, so even if an
  unexpected file got in, the server would never run it.
- `download.php` resolves the real file path and checks it stays inside
  `uploads/` before serving anything, preventing path-traversal.
- `config/`, `includes/` and `database/` all carry `.htaccess` files that
  deny direct web access.
- A basic login rate-limit locks out further attempts for 60 seconds
  after 5 failed logins in a row.
- The automatic filesystem sync (files copied into `uploads/` manually)
  applies the exact same extension whitelist and block-list as the
  upload form, so it can never publish a `.php` file or any other
  disallowed type dropped into those folders — it will just ignore it.

## 11. Testing Checklist

**Admin:** login works · wrong password rejected · logout works ·
dashboard stats correct · each of the 5 upload forms works · edit works ·
delete removes both record and file.

**Public:** homepage loads · navbar + mobile hamburger work · each
category page lists uploaded resources by Form/year · search returns
matching results · empty categories show a friendly message instead of a
blank page · View works for PDFs · Download saves the complete file.

**Security:** admin pages redirect to login when logged out · passwords
are hashed in the `admins` table · uploading a `.php` file is rejected ·
deleting requires confirmation.

## 12. Troubleshooting

| Problem | Likely cause / fix |
|---|---|
| "Database connection error" | Check MySQL is running in XAMPP, and that `config/database.php` credentials match your setup. |
| Blank/white page | Set `APP_DEBUG` to `true` temporarily in `config/database.php` to see the real error, then fix and set it back to `false`. |
| Upload fails with "file too large" | Increase `upload_max_filesize` and `post_max_size` in `php.ini`, restart Apache, and check the 200 MB limit in `includes/functions.php` (`handle_resource_upload`). |
| Upload fails with "file type not allowed" | Only PDF/DOC/DOCX/PPT/PPTX/ZIP/RAR are allowed for documents, and ZIP/RAR/7Z/EXE/MSI for software. Rename/convert the file if needed. |
| Admin login page keeps asking to "create-admin" | No admin account exists yet in the `admins` table — visit `admin/create-admin.php` once. |
| A resource shows on the site but download fails | The file was likely just removed manually from the `uploads/` folder — the next page load will automatically remove that resource from the site too (see section 6, "Adding or removing files by hand"). |
| I copied a file into `uploads/` but it isn't on the site yet | Wait a few seconds and refresh — the sync check only runs once every few seconds, not on every page view. Also double check it's in the correct Form sub-folder (see the table in section 6) and is an allowed file type. |
| Logo doesn't show | Add `assets/images/school-logo.png` — see `assets/images/README.txt`. |

## 13. SEO (search engine visibility)

The site ships with the basics that help search engines find and
understand it once it is deployed on a **public, live domain**:

- Per-page `<title>` and `<meta name="description">` (set via `$pageTitle`
  / `$pageDescription` before including `includes/header.php`).
- Canonical URL, Open Graph and Twitter Card tags on every page (link
  previews on WhatsApp/Facebook/X look correct).
- `EducationalOrganization` structured data (JSON-LD) on the homepage.
- `robots.txt` and a dynamic `sitemap.php` that lists every page and every
  individual resource from the database, so search engines can crawl and
  index them directly.

**Important, honestly:** none of this — or anything else — can guarantee
a "#1 on Google" result. Search ranking depends on far more than a
site's own code: how long the site has been live, how many other sites
link to it, how much genuine traffic and engagement it gets, how it
compares to competing sites, and Google's own algorithm changes over
time. What's included here gives the site a correct, crawlable
foundation; ranking itself is earned over time, not something a codebase
can promise. Once the site has a real public domain, submitting
`sitemap.php` in [Google Search Console](https://search.google.com/search-console)
is the next concrete step to speed up indexing.

## 14. Future Improvements (not built now, by design)

Announcements/news, download statistics, multiple administrators with
roles, admin activity logs, featured resources, additional resource
categories, and notifications can all be added later without changing the
current architecture.

---

**Core principle, always:** *the teacher uploads, the system organises,
the student accesses and downloads.*
