#!/usr/bin/env bash
# Local -> GitHub -> VPS (namangasecondary.sc.tz).  Tumia:  bash deploy.sh   (baada ya git commit)
# VPS: /var/www/namanga-website. config/env.php, config/ai.php za VPS hazibadilishwi.
# uploads/: faili mpya kutoka git zinaongezwa tu; faili zilizopo kwenye VPS (walizopakia walimu) hazifutwi wala kuandikwa juu.
set -euo pipefail
cd "$(dirname "$0")"
APP=namanga-website
VPS=root@185.205.209.83
EXCLUDE=(CLAUDE.md deploy.sh README.md DOCKER.md Dockerfile docker-compose.yml .dockerignore .env.example docker database .github uploads)

if [ -n "$(git status --porcelain)" ]; then echo "Kuna mabadiliko hayajacommitiwa. Fanya git commit kwanza."; exit 1; fi
git push origin master
SHA=$(git rev-parse --short HEAD)

PATHSPEC=(.)
for x in "${EXCLUDE[@]}"; do PATHSPEC+=(":(exclude)$x"); done
PKG="${TMPDIR:-/tmp}/$APP-deploy.tgz"; UPK="${TMPDIR:-/tmp}/$APP-uploads.tgz"
git -c core.autocrlf=false archive --format=tar.gz -o "$PKG" HEAD -- "${PATHSPEC[@]}"
git -c core.autocrlf=false archive --format=tar.gz -o "$UPK" HEAD -- uploads ':(exclude)uploads/software'
scp -q "$PKG" "$VPS:/tmp/$APP-deploy.tgz"
scp -q "$UPK" "$VPS:/tmp/$APP-uploads.tgz"

ssh "$VPS" "APP=$APP SHA=$SHA bash -s" <<'REMOTE'
set -euo pipefail
D=/var/www/$APP
T=$(date +%Y%m%d_%H%M%S)
B=/root/backups/${APP}_kabla_deploy_$T.tgz
tar czf "$B" -C /var/www --exclude="$APP/uploads" "$APP"
tar tzf /tmp/$APP-deploy.tgz > /tmp/$APP-deploy.list
tar xzf /tmp/$APP-deploy.tgz -C "$D" --no-same-owner --no-same-permissions
tar xzf /tmp/$APP-uploads.tgz -C "$D" --no-same-owner --skip-old-files
cd "$D"
while read -r p; do
  p=${p%/}; [ -z "$p" ] && continue
  chown root:www-data "$p"
  if [ -d "$p" ]; then chmod 750 "$p"; else chmod 640 "$p"; fi
done < /tmp/$APP-deploy.list
chown -R www-data:www-data uploads
rm -f /tmp/$APP-deploy.tgz /tmp/$APP-uploads.tgz /tmp/$APP-deploy.list
echo "$SHA" > .deployed_version; chown root:www-data .deployed_version; chmod 640 .deployed_version
echo "VPS: $APP sasa = commit $SHA  (backup $B)"
REMOTE
