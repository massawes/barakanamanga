#!/usr/bin/env bash
# Local -> GitHub -> VPS (namangasecondary.sc.tz).  Tumia:  bash deploy.sh   (baada ya git commit)
# VPS: /var/www/namanga-website. config/env.php, config/ai.php za VPS hazibadilishwi.
# Inapeleka MAFAILI YALIYOBADILIKA TU tangu deploy iliyopita (.deployed_version kwenye VPS).
# Ukitaka kupeleka kila kitu upya:  bash deploy.sh --full
# uploads/: faili mpya kutoka git zinaongezwa tu; faili zilizopo VPS (walizopakia walimu) hazifutwi wala kuandikwa juu.
set -euo pipefail
cd "$(dirname "$0")"
APP=namanga-website
VPS=root@185.205.209.83
EXCLUDE_RE='^(CLAUDE\.md|deploy\.sh|README\.md|DOCKER\.md|Dockerfile|docker-compose\.yml|\.dockerignore|\.env\.example|\.gitignore|\.gitattributes|docker/|database/|\.github/|uploads/software/)'

if [ -n "$(git status --porcelain)" ]; then echo "Kuna mabadiliko hayajacommitiwa. Fanya git commit kwanza."; exit 1; fi
git push origin master
SHA=$(git rev-parse --short HEAD)
OLD=$(ssh "$VPS" "cat /var/www/$APP/.deployed_version 2>/dev/null || true")

if [ "${1:-}" != "--full" ] && [ -n "$OLD" ] && git merge-base --is-ancestor "$OLD" HEAD 2>/dev/null; then
  CHANGED=$(git diff --name-only --diff-filter=ACMRT "$OLD" HEAD | grep -Ev "$EXCLUDE_RE" || true)
  DELETED=$(git diff --name-only --diff-filter=D "$OLD" HEAD | grep -Ev "$EXCLUDE_RE" | grep -v '^uploads/' || true)
  echo "Tangu $OLD: mafaili $(printf '%s' "$CHANGED" | grep -c . || true) yamebadilika, $(printf '%s' "$DELETED" | grep -c . || true) yamefutwa."
else
  CHANGED=$(git ls-files | grep -Ev "$EXCLUDE_RE" || true)
  DELETED=""
  echo "Deploy kamili: mafaili $(printf '%s' "$CHANGED" | grep -c . || true)."
fi

PKG="${TMPDIR:-/tmp}/$APP-deploy.tgz"
printf '%s\n' "$CHANGED" | grep . > "${PKG%.tgz}.list" || true
if [ -s "${PKG%.tgz}.list" ]; then
  tar czf "$PKG" -T "${PKG%.tgz}.list"
else
  tar czf "$PKG" -T /dev/null
fi
printf '%s\n' "$DELETED" > "${PKG%.tgz}.deleted"
scp -q "$PKG" "$VPS:/tmp/$APP-deploy.tgz"
scp -q "${PKG%.tgz}.deleted" "$VPS:/tmp/$APP-deploy.deleted"

ssh "$VPS" "APP=$APP SHA=$SHA bash -s" <<'REMOTE'
set -euo pipefail
D=/var/www/$APP
T=$(date +%Y%m%d_%H%M%S)
B=/root/backups/${APP}_kabla_deploy_$T.tgz
tar czf "$B" -C /var/www --exclude="$APP/uploads" --exclude="$APP/exams" "$APP"
cd "$D"
# Mafaili ya code (yasiyo uploads/) yanaandikwa juu; ya uploads/ yanaongezwa tu kama hayapo.
tar xzf /tmp/$APP-deploy.tgz -C "$D" --no-same-owner --no-same-permissions --exclude='uploads/*'
tar xzf /tmp/$APP-deploy.tgz -C "$D" --no-same-owner --no-same-permissions --skip-old-files --wildcards 'uploads/*' 2>/dev/null || true
tar tzf /tmp/$APP-deploy.tgz | grep -v '^uploads/' | while read -r p; do
  p=${p%/}; [ -z "$p" ] && continue
  d=$(dirname "$p"); while [ "$d" != "." ]; do chown root:www-data "$d"; chmod 750 "$d"; d=$(dirname "$d"); done
  chown root:www-data "$p"; if [ -d "$p" ]; then chmod 750 "$p"; else chmod 640 "$p"; fi
done
while read -r p; do [ -n "$p" ] && [ -f "$p" ] && rm -f -- "$p" && echo "imefutwa: $p"; done < /tmp/$APP-deploy.deleted
chown -R www-data:www-data uploads
rm -f /tmp/$APP-deploy.tgz /tmp/$APP-deploy.deleted
echo "$SHA" > .deployed_version; chown root:www-data .deployed_version; chmod 640 .deployed_version
echo "VPS: $APP sasa = commit $SHA  (backup $B)"
REMOTE
