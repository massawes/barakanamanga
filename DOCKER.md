# Kuendesha tovuti kwenye Linux server kwa Docker

Docker inaendesha tovuti nzima (PHP, Apache na database ya MariaDB) bila
kusakinisha kitu kingine chochote kwenye server.

## Unachohitaji
- Linux server (Ubuntu, Debian n.k.) yenye internet.
- Uwezo wa kuingia kwa `ssh` kama mtumiaji mwenye `sudo`.

## Hatua (mara moja tu)

**1. Sakinisha Docker** (kama bado haipo):
```sh
curl -fsSL https://get.docker.com | sudo sh
```

**2. Pakua project:**
```sh
sudo apt-get install -y git
git clone https://github.com/massawes/barakanamanga.git namanga
cd namanga
```

**3. Weka nywila (passwords):**
```sh
cp .env.example .env
nano .env
```
Badilisha `DB_ROOT_PASSWORD` na `DB_PASSWORD` ziwe nywila zako ndefu, kisha
hifadhi (Ctrl+O, Enter, Ctrl+X).

**4. Washa tovuti:**
```sh
sudo docker compose up -d --build
```
Mara ya kwanza inachukua dakika kadhaa. Ikimaliza, fungua
`http://IP-ya-server/` kwenye browser.

**5. Tengeneza akaunti ya Admin:** fungua
`http://IP-ya-server/admin/create-admin.php` na uweke jina na nywila.

Notes, past papers, picha na slides zinaingia kwenye database zenyewe
mara ukurasa wa kwanza unapofunguliwa.

## Kazi za kila siku

| Unataka | Amri (ndani ya folda `namanga`) |
|---|---|
| Kuona kama inafanya kazi | `sudo docker compose ps` |
| Kuona makosa (logs) | `sudo docker compose logs -f web` |
| Kuzima | `sudo docker compose down` |
| Kuwasha tena | `sudo docker compose up -d` |
| Kuweka toleo jipya kutoka GitHub | `git pull && sudo docker compose up -d --build` |
| Backup ya database | `sudo docker compose exec db sh -c 'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" namanga_resource_centre' > backup.sql` |

Database na faili zilizopakiwa (`uploads`) zinakaa kwenye Docker volumes,
kwa hiyo hazipotei ukizima, ukiwasha upya au ukiweka toleo jipya.
**Usitumie** `docker compose down -v`: `-v` inafuta database na uploads.

## Kubadilisha port
Kama port 80 tayari inatumika kwenye server, weka mfano `WEB_PORT=8080`
ndani ya `.env`, kisha `sudo docker compose up -d`. Tovuti itakuwa
`http://IP-ya-server:8080/`.
