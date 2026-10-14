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
`http://IP-ya-server:8080/` kwenye browser.

**5. Tengeneza akaunti ya Admin:** fungua
`http://IP-ya-server:8080/admin/create-admin.php` na uweke jina na nywila.

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

## Port na Apache iliyopo kwenye server

Tovuti hii ya Docker inatumia port **8080**, si 80. Kwa hiyo haigongani na
Apache2 (au website nyingine) inayotumia port 80/443 kwenye server hiyo hiyo.

- **Kubadilisha port:** weka mfano `WEB_PORT=8090` ndani ya `.env`, kisha
  `sudo docker compose up -d`. Kuona port zinazotumika tayari:
  `sudo ss -tlnp`.
- **Firewall:** Docker inafungua port yenyewe hata ukitumia `ufw`. Kama
  server ina firewall ya cloud (AWS, DigitalOcean n.k.), ruhusu port 8080
  huko.

### Kutumia domain bila `:8080` (kupitia Apache iliyopo)

Kama unataka watu wafungue mfano `http://namanga.example.com/` badala ya
`IP:8080`, Apache iliyopo inaweza kupeleka domain hiyo kwenye Docker.
Website kuu haiguswi.

1. Elekeza DNS ya domain (A record) kwenye IP ya server.
2. Ndani ya `.env` weka `WEB_BIND=127.0.0.1` (8080 inafungwa kwa watu wa
   nje), kisha `sudo docker compose up -d`.
3. Washa modules za proxy: `sudo a2enmod proxy proxy_http headers`
4. Tengeneza `/etc/apache2/sites-available/namanga.conf`:
   ```apache
   <VirtualHost *:80>
       ServerName namanga.example.com
       ProxyPreserveHost On
       ProxyPass        / http://127.0.0.1:8080/
       ProxyPassReverse / http://127.0.0.1:8080/
       LimitRequestBody 0
       ProxyTimeout 3600
   </VirtualHost>
   ```
5. `sudo a2ensite namanga && sudo apache2ctl configtest && sudo systemctl reload apache2`
6. HTTPS (hiari): `sudo apt-get install -y certbot python3-certbot-apache`
   kisha `sudo certbot --apache -d namanga.example.com`
