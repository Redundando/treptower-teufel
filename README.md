# treptower-teufel.de

WordPress-Site des Treptower Teufel Tennisclub e.V. Dieses Repo versioniert
**nur den eigenen Code**, nicht WordPress selbst:

- `wp-content/themes/klohn-kit/` — das Custom-Theme der Seite
- `wp-content/themes/blankslate-child/` — älteres Child-Theme
- `wp-content/mu-plugins/` — Must-Use-Plugins
- `wp-config.php` + `.wp-env.<env>.php` — Config mit Umgebungserkennung über den Pfad
  (`local` / `staging` / `production`); Passwörter liegen ausschließlich in der
  nicht versionierten `.wp-secrets.php`
- `fetch-calendar.php`, `robots.txt`, Artwork-Dateien
- `deploy/deploy.sh` — Deployment

WordPress-Core, Fremd-Plugins und Sprachdateien sind absichtlich nicht im Repo.
Sie werden direkt über wp-admin (bzw. Auto-Updates) aktuell gehalten und
erzeugen dadurch keine Abweichung zwischen Repo und Server.

## Hosting

- Hetzner konsoleH, Server `dedivirt3710.your-server.de`, SSH/SFTP Port 222, User `teufel`
- Umgebungen: `~/public_html/prod` (live) und `~/public_html/staging`
- Datenbank (prod): remote auf `kbky.your-database.de`, Tabellen-Präfix `viva_`
- DNS läuft über 1blu, nicht über Hetzner

## Deployment

Auf dem Server liegt ein Klon dieses Repos unter `~/site-repo`. Deploy:

```bash
ssh -p 222 teufel@dedivirt3710.your-server.de
~/site-repo/deploy/deploy.sh prod     # oder: staging
```

Das Skript pullt das Repo und synchronisiert nur die oben genannten eigenen
Pfade in die Zielumgebung. Core, Plugins, Uploads und server-generierte
Inhalte werden nie angefasst.

## Historie

- Bis Januar 2026 war der komplette WordPress-Baum versioniert; der letzte
  Voll-Snapshot ist als Tag `snapshot-2026-08` konserviert (Stand nach der
  Hack-Bereinigung vom Juli 2026: Core 7.0.2 + aktualisierte Plugins).
- Danach Umstellung auf das schlanke Schema (nur eigener Code).
