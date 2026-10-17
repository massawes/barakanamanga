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
**AI assistant** ("Ask AI" button, English and Kiswahili) explains topics and
finds real files on the site. See [section 7](#7-ai-assistant).

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
├── api/chat.php             (AI assistant endpoint, JSON)
├── api/search_resources.php (site file search, JSON)
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
- AI and search API keys live only on the server (see section 7). They are
  never sent to the browser and never committed to Git.

---

## 7. AI Assistant

Every public page has an **Ask AI / Uliza AI** button (bottom-right). It
opens a chat that:

- answers in **English or Kiswahili** (EN/SW switch in the chat header; the
  whole chat interface changes language too);
- explains school topics and gives revision help for Forms 1–4;
- searches **this website's own files** (the `resources` table, the school
  exam papers in `exams/`, and the NECTA past-paper links) and shows them as
  clickable cards with the real **View** / **Download** links;
- says clearly when the site has no matching file. It never makes up a file
  or a link: the AI is told not to write URLs, and the server removes any
  URL in its reply that did not come from the search results;
- keeps the conversation while the browser tab is open (`sessionStorage`)
  and sends the last 10 messages with each question, so follow-up questions
  work. **Clear chat** deletes it;
- can optionally search the internet (off until a search key is added).

### How it works

```
browser (assets/js/assistant.js)
   │  POST JSON {message, lang, history}
   ▼
api/chat.php ── checks input, rate limit
   │  ├─ ai_search_resources()  → this site's files (MySQL, prepared statements)
   │  ├─ ai_web_search()        → Brave or Tavily (only if enabled)
   │  └─ ai_chat_completion()   → Groq API with cURL (key read on the server)
   ▼
JSON {reply, resources[], web[]} → shown in the chat
```

| File | Purpose |
|---|---|
| `includes/assistant-widget.php` | Chat button and window, included from `includes/footer.php` |
| `assets/js/assistant.js` | Chat behaviour and EN/SW texts (plain JavaScript) |
| `api/chat.php` | Chat endpoint |
| `api/search_resources.php` | Site search as JSON, e.g. `/api/search_resources.php?q=biology form 2` |
| `includes/ai.php` | Settings, rate limiting, search, Groq and web-search calls, system prompt |
| `config/ai.example.php` | Settings template (copy to `config/ai.php`) |

### Requirements

- PHP **8.0 or newer** (tested on 8.2 under XAMPP; the Docker image uses 8.3).
- PHP extensions **curl**, **mbstring**, **openssl** and **pdo_mysql** (all on
  by default in XAMPP, InfinityFree and the Docker image). Check with
  `php -m`.
- The server must be allowed to make HTTPS requests to `api.groq.com`.

### Getting an AI key (free)

1. Sign in at <https://console.groq.com>.
2. Open **API Keys** → **Create API Key**. Copy the key (it starts with `gsk_`).

The default model is `openai/gpt-oss-120b`, chosen because it gave the most
correct Kiswahili in tests. `qwen/qwen3.8-27b` is used automatically if the
first model is busy. If Groq retires a model, pick a new one from
<https://console.groq.com/docs/models> and set `groq_model`.

### Where to put the key

The site reads its settings in this order (later wins):

1. `config/ai.php`, or the file named by the environment variable
   `NAMANGA_AI_CONFIG`;
2. environment variables `GROQ_API_KEY`, `GROQ_MODEL`,
   `WEB_SEARCH_PROVIDER`, `WEB_SEARCH_API_KEY`.

**Local XAMPP:** copy `config/ai.example.php` to `config/ai.php` and paste
your key into `groq_api_key`. `config/ai.php` is in `.gitignore`, and
`config/.htaccess` blocks it from the web (it returns 403).

**InfinityFree (live site):** you never upload the key by hand. In GitHub go
to the repository → **Settings → Secrets and variables → Actions** and add:

| Name | Kind | Value |
|---|---|---|
| `GROQ_API_KEY` | Secret | your `gsk_…` key (required) |
| `WEB_SEARCH_API_KEY` | Secret | search key (optional) |
| `WEB_SEARCH_PROVIDER` | Variable | `brave` or `tavily` (optional) |
| `GROQ_MODEL` | Variable | another Groq model (optional) |

The next push to `master` (or **Actions → Deploy to InfinityFree → Run
workflow**) writes `config/ai.php` from these and uploads it.

**Your own Apache server (`/var/www/namanga`):** keep the key outside the web
root:

```bash
sudo mkdir -p /var/www/namanga-private
sudo cp /var/www/namanga/config/ai.example.php /var/www/namanga-private/ai.php
sudo nano /var/www/namanga-private/ai.php          # paste the key
sudo chown root:www-data /var/www/namanga-private/ai.php
sudo chmod 640 /var/www/namanga-private/ai.php
```

Then add this line inside the site's `<VirtualHost>` and reload Apache
(`sudo systemctl reload apache2`):

```apache
SetEnv NAMANGA_AI_CONFIG /var/www/namanga-private/ai.php
```

Make sure the PHP cURL extension is installed: `sudo apt install php-curl`.

**Docker:** put `GROQ_API_KEY=…` (and optionally the search settings) in
`.env`, then run `docker compose up -d`.

### Internet search (optional)

The search is off by default and the checkbox does not appear. To switch it
on, get a key from [Brave Search API](https://api-dashboard.search.brave.com)
or [Tavily](https://app.tavily.com), then set `web_search_provider`
(`brave` or `tavily`) and `web_search_api_key` in the same place as the Groq
key. An **"Also search the internet"** checkbox then appears in the chat, and
results are shown as links. Only URLs returned by the search API are shown.
To switch it off again, empty either setting.

### Limits

Per browser session: 8 messages a minute. Per IP address: 30 a minute and
600 a day (high enough for a computer lab sharing one connection). Change
these with `limit_session_per_min`, `limit_ip_per_min` and
`limit_ip_per_day`. Counts are kept in the `ai_rate_limits` table, which
creates itself; only a hash of the IP is stored. Messages are limited to
1,000 characters.

### Testing

```bash
# 1. Site search (no AI needed)
curl "http://localhost/namanga/api/search_resources.php?q=ict+notes+form+1"

# 2. Chat in Kiswahili
curl -X POST http://localhost/namanga/api/chat.php \
     -H "Content-Type: application/json" \
     -d '{"message":"Nipe notes za ICT form 1","lang":"sw","history":[]}'

# 3. The key must NOT be reachable (expect 403)
curl -I http://localhost/namanga/config/ai.php
```

In the browser, check that **Ask AI** opens and closes (Esc also closes),
that EN/SW changes the language, that cards download real files, that the
chat survives a page reload, and that it fills the screen on a phone.

A reply with `"ai": false` means the AI could not be reached. The visitor
still gets the site search results with a friendly message.

### Common problems

| Symptom | Cause and fix |
|---|---|
| "The AI assistant is not available right now…" | No key or a wrong key. Check `config/ai.php` or the GitHub secret. The PHP/Apache error log shows `Namanga AI: Groq HTTP 401` for a wrong key |
| Same message, and the log shows `Groq HTTP 429` | Groq free-tier limit reached. Wait a minute, or upgrade the Groq plan |
| Log shows `cURL error: SSL certificate problem` | PHP has no CA bundle. Set `curl.cainfo` in `php.ini` to a `cacert.pem` file (XAMPP already ships one) |
| Log shows `cURL extension is not enabled` | Enable `extension=curl` in `php.ini` (XAMPP), or `apt install php-curl` |
| Log shows `Groq HTTP 404` / `model … does not exist` | The model was retired. Set `groq_model` to a current model |
| "You are sending messages too quickly" | The rate limit. Wait one minute |
| Chat says it cannot reach the server | `api/chat.php` gave an error. Open it in the browser to see the HTTP status, and check the PHP error log |

### InfinityFree vs your own server

- **InfinityFree:** the key comes from GitHub Secrets on each deploy. Free
  hosting may block or slow outgoing connections. If the live chat always
  says the AI is unavailable while it works locally, outgoing HTTPS to
  `api.groq.com` is being blocked; the site search keeps working anyway.
  InfinityFree's "security check" page can also interrupt the very first
  request of a new visitor. Reloading the page fixes it.
- **Own Apache server:** full control. Keep the key outside the web root
  (above), install `php-curl`, and outgoing HTTPS is normally allowed.

---

**Core principle:** *teachers upload, the system organises, students access
and download — free.*
