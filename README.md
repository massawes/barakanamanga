# Namanga Secondary School Website

The official website and digital learning resource centre of **Namanga
Secondary School** (NECTA centre S2911), Longido District, Arusha, Tanzania.
Its motto is *"Education for Liberty and Prosperity"*.

**Live site:** https://namangawebsite.freepage.cc

The website introduces the school to the public. It also gives students
free access to notes, summaries, past papers, practical exams, software and
school media. Teachers and staff manage the content from a secure admin panel
and do not need to edit any code.

---

## 1. What the website offers

### About the school
- **Home**: a photo slideshow, resource categories and quick links.
- **Our School** menu: About Us, Mission & Vision, Our History and Our
  Students.
- **The Latest**: a gallery of school photos and videos. Visitors can filter
  by Photos, Videos or All Media and download everything at once.
- **Results**: links to the official NECTA CSEE (Form 4) and FTNA (Form 2)
  results pages for the school, year by year.

### Learning resources (Form 1–4)
| Section | What it contains |
|---|---|
| **Notes** | Complete notes books, by Form |
| **Summary** | Short revision summaries, by Form |
| **Theory Past Papers** | NECTA national papers (FTNA and CSEE) with marking schemes, plus the school's own theory exams for Forms 1–4, grouped by subject |
| **Form 4 Practical** | NECTA practical papers plus school practical exams (ICS, sciences, etc.) |
| **Software** | Programs used in ICT / Computer Science lessons |
| **Other Subjects** | Extra papers and materials for non-ICT subjects |
| **Upload Tasks** | Students upload their assignments (name, Form, file). Anyone can view and download submitted tasks |

Every resource is one complete file. PDFs can be **viewed** in the browser or
**downloaded**. A global **search** box covers every section. A built-in
**"Need help?" assistant** answers visitors' questions using only the site's
own content, with no external AI service.

### For staff (admin panel, `/admin/`)
- Secure login with two roles:
  - **Admin**: full control, including deleting content and managing staff
    accounts.
  - **Staff**: can upload, edit and view content, but cannot delete.
- Staff create their own accounts on the **Register** page. The first account
  (made via `admin/create-admin.php`) is the Admin.
- Upload forms for Notes, Summary, Theory, Practical, Software, Other Subjects
  and the Gallery. Bulk upload lets you send several files or a whole folder
  at once.
- Manage resources (edit, replace file, delete), homepage slides, gallery
  items and staff accounts.
- **Automatic folder sync**: you can copy a file directly into the matching
  `uploads/…` folder and it appears on the website by itself. Deleting the
  file removes it from the site.

---

## 2. Technology

PHP 8 (PDO), MySQL / MariaDB, HTML5, CSS3 and vanilla JavaScript on Apache.
No frameworks. Icons are inline SVG, so pages render fully offline under
XAMPP.

---

## 3. Repository layout

```
namanga/
├── index.php, about.php, mission-vision.php, history.php, our-students.php
├── notes.php, summaries.php, theory.php, practical.php, software.php,
│   others.php, gallery.php, results.php, search.php
├── tasks.php, upload-task.php, task-view.php, task-download.php
├── resource-view.php, download.php, download-all.php   (secure view/download)
├── assistant.php            (offline "Need help?" assistant endpoint)
├── sitemap.php, robots.txt  (SEO; served as /sitemap.xml)
├── admin/                   (login, dashboard, upload & manage pages)
├── includes/                (layout, navbar, functions, exam lists, importer)
├── config/                  (database settings; web access denied)
├── assets/                  (CSS, JavaScript, logos, school photos)
├── exams/form1 … form4/     (cleaned school exam papers, by subject)
├── uploads/                 (files uploaded through the site)
│   ├── notes/ summaries/ theory/ practical/ others/   (by Form)
│   ├── gallery/ slides/ tasks/
│   └── software/            (kept empty in Git: installers are too large)
├── database/namanga.sql     (database structure)
├── EXMSVICENTNAMANGA/       (original exam collection, Forms 2 & 4)
└── SIMONFORM13NAMA/         (original exam collection, Forms 1 & 3)
```

The two upper-case folders hold the original exam collections exactly as
received. The website serves the cleaned and sorted copies in `exams/`.

---

## 4. Running it locally (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org) and start **Apache** and
   **MySQL**.
2. Copy this project into `C:\xampp\htdocs\namanga\`.
3. Open `http://localhost/phpmyadmin`, then **Import** the file
   `database/namanga.sql`. This creates the `namanga_resource_centre`
   database.
4. If your MySQL login is not the XAMPP default (`root` with no password),
   edit `config/database.php`.
5. Create the first Admin account at
   `http://localhost/namanga/admin/create-admin.php`. The page disables itself
   once an Admin exists.
6. Visit `http://localhost/namanga/`.

---

## 5. Deployment (InfinityFree)

The live site deploys automatically from the
[`massawes/barakanamanga`](https://github.com/massawes/barakanamanga)
repository. Every push to `master` runs `.github/workflows/deploy.yml`, which
does two things:

1. It writes `config/env.php` from the repository's GitHub Secrets and
   Variables, so real passwords are never stored in Git.
2. It uploads the site to InfinityFree over FTP.

The deploy skips three things:
- `uploads/software/`, because installers are too large;
- files over InfinityFree's **10 MB per-file limit** (listed in
  `.gitignore`);
- `database/` and local-only config.

`config/live-import.json` and `includes/live-import.php` copy the local
resource, gallery, slide and task records into the live database. This
happens once, on the first page visit after a deploy.

---

## 6. Security

- Passwords are hashed with `password_hash()` and never stored in plain text.
- All SQL uses PDO prepared statements. All output is escaped.
- Every form that changes data is protected with a CSRF token. After 5 failed
  logins in a row, further attempts are blocked for 60 seconds.
- Uploads are checked by file extension **and** MIME type, and executable
  types are always rejected. Stored files get random names. `uploads/` has
  script execution disabled.
- `config/`, `includes/` and `database/` deny direct web access.
  `download.php` refuses any path outside the allowed folders.

---

**Core principle:** *teachers upload, the system organises, students access
and download — free.*
