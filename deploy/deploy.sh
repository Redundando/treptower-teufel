#!/usr/bin/env bash
# Deploy des eigenen Codes nach prod oder staging.
# Aufruf (auf dem Server): ~/site-repo/deploy/deploy.sh prod|staging
#
# Synchronisiert AUSSCHLIESSLICH die im Repo versionierten eigenen Pfade.
# WordPress-Core, Fremd-Plugins, Uploads, .htaccess, .wp-secrets.php sowie
# server-generierte Inhalte (hallenbelegung/, intern/) werden nie angefasst.
set -euo pipefail

ENV="${1:?Aufruf: deploy.sh prod|staging}"
case "$ENV" in
  prod|staging) ;;
  *) echo "Unbekannte Umgebung: $ENV (erlaubt: prod, staging)"; exit 1 ;;
esac

REPO="$HOME/site-repo"
DEST="$HOME/public_html/$ENV"

cd "$REPO"
git pull --ff-only

# Eigene Themes: exakt spiegeln (inkl. Löschungen innerhalb des Themes)
rsync -av --delete "$REPO/wp-content/themes/klohn-kit/" "$DEST/wp-content/themes/klohn-kit/"
rsync -av --delete "$REPO/wp-content/themes/blankslate-child/" "$DEST/wp-content/themes/blankslate-child/"

# mu-plugins: nur hinzufügen/aktualisieren, nichts löschen
rsync -av "$REPO/wp-content/mu-plugins/" "$DEST/wp-content/mu-plugins/"

# Einzelne eigene Dateien im Root
for f in wp-config.php .wp-env.production.php .wp-env.staging.php fetch-calendar.php robots.txt; do
  rsync -av "$REPO/$f" "$DEST/$f"
done

echo "Deploy nach $ENV abgeschlossen."
