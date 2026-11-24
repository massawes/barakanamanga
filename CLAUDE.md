# Namanga Secondary School website, newer copy (repo `massawes/barakanamanga`): notes for every Claude session

Claude does not remember old chats: **this file is the memory**. Update "WHERE WE STOPPED" after each piece of work,
so the owner can continue from any terminal. The owner writes Swahili: reply in short, simple Swahili.

## What it is
- Same school website as repo `massawes/namanga` (notes, past papers, gallery, admin panel `/admin/`), plus Docker
  files (`Dockerfile`, `docker-compose.yml`, `DOCKER.md`) and an AI assistant ("Ask AI", Groq key from GitHub secrets
  -> `config/ai.php`). Plain PHP + MySQL. Full description: `README.md`. Branch **master**.
- Live: https://namangawebsite.freepage.cc (InfinityFree). Ask the owner which repo (this one or `namanga`) is live
  before deploying: both workflows upload to InfinityFree.
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
- 2026-11-17: last site work (top bar phone number; Head of School message under the slideshow).
- 2026-11-24: CLAUDE.md added (and excluded from the FTP deploy). No code change.
