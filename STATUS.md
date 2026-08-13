# Status und Übergabe-Notizen

Stand: 14.08.2026. Dieses Dokument hält fest, was im August 2026 am Repo und
am Server umgebaut wurde, welche Baustellen offen sind und welche
Sicherheitspunkte bei der geplanten Server-Migration mitgenommen werden müssen.

## Was im August 2026 passiert ist und warum

Ausgangslage: Das Repo enthielt bis Januar 2026 den kompletten WordPress-Baum
und war seitdem nicht mehr gepflegt. Die Hack-Bereinigung vom Juli 2026
(Core-Update auf 7.0.2, alle Plugins aktualisiert) lief direkt auf dem Server
und stand nie im Repo. Ein Deploy hätte die Sicherheitsupdates rückwärts
überschrieben und zusätzlich per rsync --delete server-generierte Inhalte
gelöscht.

Umgesetzte Schritte:

1. **Voll-Snapshot konserviert.** Der bereinigte Server-Stand wurde als Commit
   committet und als Tag `snapshot-2026-08` gepusht. Damit ist der letzte
   vollständige WordPress-Baum (Core 7.0.2 + Plugins nach der Bereinigung)
   dauerhaft in der Git-History nachschlagbar.
2. **Repo verschlankt.** Seitdem ist nur noch eigener Code versioniert
   (siehe README.md). WordPress-Core und Fremd-Plugins werden über wp-admin
   bzw. Auto-Updates gepflegt und erzeugen keine Abweichung zwischen Repo und
   Server mehr. Genau diese Abweichung war die Ursache des Problems.
3. **Deployment neu gebaut.** `deploy/deploy.sh` (versioniert) synchronisiert
   ausschließlich die eigenen Pfade nach prod bzw. staging. Kein
   rsync --delete über den ganzen Baum mehr. Die alten Skripte
   `~/deploy-prod.sh` und `~/deploy-staging.sh` auf dem Server sind nur noch
   Aufrufer.
4. **Prod als Git-Arbeitskopie nutzbar gemacht.** In `~/public_html/prod`
   liegt ein `.git` auf dem aktuellen Stand; `git status` dort ist sauber und
   dient als Drift-Melder: Änderungen am eigenen Code direkt auf dem Server
   fallen sofort auf.
5. **Deploy-Key rotiert.** Der alte GitHub-Deploy-Key war beim Hack im Juli
   für den Angreifer lesbar (nur Lesezugriff aufs Repo, kein Schreibzugriff)
   und wurde widerrufen. Neuer Key: "Hetzner Deploy 2026", read-only.

## Theme-Review klohn-kit (14.08.2026, offene Punkte)

Gesamturteil: klein (~1.800 Zeilen), konsistent modularisiert, sauberes
Escaping, maschinenlesbare Design-Doku. Gut wartbar, auch und gerade durch KI.
Offene Punkte nach Gewicht:

1. **Build-Pipeline fehlt im Repo.** `assets/src/` (SCSS, JS) und
   `assets/dist/` (kompiliert/minifiziert) existieren, aber es gibt kein
   package.json, kein Build-Skript und keine Doku, womit gebaut wird. Wer
   Styles ändert, hat keinen definierten Weg zu einer neuen `base.css`.
   Empfehlung: minimales Build-Setup (sass + esbuild) einchecken und im
   README dokumentieren, alternativ JS-Minifizierung aufgeben (Ersparnis
   ist minimal).
2. **Wartungsanleitung fehlt.** Kurze README im Theme ergänzen: Styles in
   `src/scss` ändern, bauen, deployen; Templates liegen in `views/`; der
   Kalender-Block rendert über den Shortcode.
3. **XML-Dokumente sind öffentlich abrufbar** (`hosting-setup.xml`,
   `style-guidelines.xml` usw. liegen im Theme-Ordner und damit im Web).
   Keine Secrets enthalten, aber `hosting-setup.xml` beschreibt die
   Infrastruktur. Aus dem Theme in einen nicht deployten Ordner verschieben.
4. **Altlasten im Code:** KI-Artefakt-Kommentar in `inc/blocks.php`
   (`:contentReference[...]`), auskommentierter toter Code in
   `inc/performance.php`, Legacy-Pfad für das nicht mehr funktionierende
   `fetch-calendar.php` im Kalender-Modul.
5. **Alte Themes `blankslate` + `blankslate-child`** (Vorgänger-Design) auf
   dem Server löschen, sobald sicher ist, dass nichts mehr darauf zeigt.
   `blankslate-child` liegt noch mit im Repo und kann dann ebenfalls raus.

## Sicherheitspunkte für die Server-Migration

Kontext: Im Juli 2026 wurde die Seite über eine WordPress-Core-Lücke
(REST-Endpunkt `/wp-json/batch/v1`, Massen-Ausnutzung "wp2shell")
kompromittiert. Webshells und Fake-Admins wurden entfernt, Core und Plugins
aktualisiert, DB-Passwort rotiert. Für die Migration auf den neuen Server
sollte mitgenommen werden:

**Nicht mitmigrieren (Altlasten auf dem aktuellen Server):**

- `.htbvhe55.appconfig.php` im Docroot: Installatron-Altlast, enthält ein
  **Klartext-Passwort aus 1blu-Zeiten** und war für den Angreifer lesbar.
  Wichtig: Prüfen, ob dieses Passwort bei 1blu (Kundencenter, Postfächer)
  noch irgendwo gilt. 1blu bleibt auch nach der Server-Migration im Einsatz
  (DNS, Mail), das Passwort überlebt die Migration also gegebenenfalls.
- `~/quarantine_hack_20260720/` (Webshells in Quarantäne),
  `~/public_html/prod_copy_20260119/` (kompletter alter Site-Klon),
  Backups `*.bak_20260720` im Docroot (enthalten alte Passwörter).
- Installatron-Reste: `wp-itapi.php` (Fernwartungs-Endpunkt mit hartkodiertem
  Hash), `wp-content/deleteme.*.php`, mu-plugin
  `installatron_hide_status_test.php`.
- Diverses: `wherephp.php`, `.idea/`-Ordner im Docroot, `.user.ini` (zeigt
  auf nicht existenten alten 1blu-Pfad), `wp-content/plugins.OFF/`,
  `wp-content/wps-hide-login/` (Plugin-Kopie außerhalb von `plugins/`).

**Auf dem neuen Server von Anfang an:**

- WordPress-Core-Auto-Updates aktivieren (das Einfallstor war ein veralteter
  Core; das schlanke Repo ist genau dafür gebaut, Updates erzeugen keine
  Drift mehr).
- 2FA für alle Admin-Konten erzwingen (aktuell hat kein Konto 2FA).
- `xmlrpc.php` sperren, REST-API bzw. Login begrenzen (Rate-Limit o.ä.).
- Admin-Passwörter und WP-Salts bei der Migration frisch setzen.
- Frische Zugänge statt übernommener: SSH-Keys, SFTP-Passwort und
  Deploy-Key neu erzeugen (der aktuelle Deploy-Key "Hetzner Deploy 2026"
  ist sauber, kann aber bei der Gelegenheit gleich mit rotiert werden).
- Backup-Konzept festlegen: Datei- und vor allem DB-Backups. Das schlanke
  Repo ersetzt kein Backup; Rollbacks nach kaputten Updates brauchen
  DB-Snapshots.

**Bekannte Besonderheiten des aktuellen Setups:**

- SSH/SFTP auf Port 222; `~/.ssh/authorized_keys` wird vom sshd des
  Hetzner-Webhostings offenbar ignoriert (Login nur per Passwort möglich).
- wp-cli erreicht die Remote-Datenbank von der Server-CLI aus nicht;
  DB-nahe Aktionen laufen über wp-admin.
- DNS und Postfächer liegen bei 1blu, nicht beim Hosting-Anbieter.
- Umgebungserkennung der Site läuft über den Pfad (`prod`/`staging` im
  Verzeichnisnamen), Secrets liegen in der nicht versionierten
  `.wp-secrets.php`.
