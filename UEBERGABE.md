# Übergabe und Betrieb: treptower-teufel.de

Stand: 14.08.2026. Dieses Dokument erklärt, wie das Setup funktioniert, wie
ein Neuaufbau bzw. eine Server-Migration abläuft und welche Sicherheitspunkte
dabei zu beachten sind. Es ist bewusst so geschrieben, dass es ohne weiteres
Vorwissen funktioniert.

## 1. Wie dieses Setup funktioniert

Dieses Repo versioniert **nur den eigenen Code** der Website, nicht
WordPress selbst. Eine WordPress-Site besteht aus drei Teilen:

| Teil | Wo er lebt | Wie er gepflegt wird |
|---|---|---|
| Eigener Code (Theme, mu-plugins, Config-Gerüst, Deploy) | dieses Repo | Git-Workflow: ändern, committen, deployen |
| WordPress-Core, Fremd-Plugins, Sprachdateien | nur auf dem Server | wp-admin bzw. Auto-Updates, bewusst ohne Git |
| Datenbank (alle Inhalte, Menüs, Einstellungen, Benutzer) und `wp-content/uploads/` | nur auf dem Server | Backups |

Der Grund für diese Aufteilung: Bis Januar 2026 war der komplette
WordPress-Baum im Repo. Jedes Update über wp-admin machte das Repo veraltet,
und ein Deploy aus dem veralteten Repo hätte Sicherheitsupdates rückwärts
überschrieben. Im schlanken Schema kann das nicht mehr passieren: Updates
per Klick im Backend erzeugen keine Abweichung zum Repo, weil Core und
Plugins gar nicht versioniert sind.

**Umgebungen:** `~/public_html/prod` (live) und `~/public_html/staging`.
Die `wp-config.php` erkennt die Umgebung am Verzeichnispfad (enthält der
Pfad `prod` oder `staging`?) und lädt dann `.wp-env.production.php` bzw.
`.wp-env.staging.php` (unkritische Werte wie DB-Host und URL). Passwörter
und Salts liegen ausschließlich in der **nicht versionierten**
`.wp-secrets.php` im jeweiligen Docroot.

**Deployment:** Auf dem Server liegt ein Klon dieses Repos unter
`~/site-repo`. Ein Deploy (`~/site-repo/deploy/deploy.sh prod` oder
`staging`) pullt das Repo und synchronisiert ausschließlich die eigenen
Pfade in die Zielumgebung. Core, Plugins, Uploads, `.htaccess` und
server-generierte Inhalte (`hallenbelegung/`, `intern/`) werden nie
angefasst.

**Drift-Kontrolle:** Der Prod-Docroot ist selbst eine Git-Arbeitskopie.
`git status` dort zeigt sofort, wenn jemand eigenen Code direkt auf dem
Server geändert hat. Der Sollzustand ist ein leerer Status.

## 2. Plugin-Bestand

Stand nach der Hack-Bereinigung Juli 2026 (Tag `snapshot-2026-08`; der
komplette damalige Baum inklusive aller Plugin-Dateien ist über dieses Tag
in der Git-History nachschlagbar):

| Plugin | Version 07/2026 | Hinweis |
|---|---|---|
| The SEO Framework | 5.1.4 | |
| The SEO Framework Extension Manager | 2.7.2 | |
| Complianz (GDPR Cookie Consent) | 7.5.2 | |
| Matomo Analytics | 5.12.1 | lokale Statistik, keine externen Dienste |
| PDF Embedder | 5.0.1 | |
| **Polylang Pro** | 3.8.6 | **Bezahl-Plugin, Lizenz erforderlich**; trägt die komplette DE/EN-Zweisprachigkeit |
| Shortcoder | 6.5.4 | |
| Wordfence Security | 9.0.0 | war zuletzt deaktiviert; Entscheidung offen: entfernen oder schlankere Alternative |

Welche Plugins aktiv sind, steht in der Datenbank. Mit importierter DB sind
die Aktivierungszustände automatisch korrekt, sobald die Plugin-Ordner
existieren.

## 3. Neuaufbau bzw. Server-Migration, Schritt für Schritt

Voraussetzungen: PHP 8.3+, MySQL/MariaDB, ein aktuelles Datenbank-Backup und
eine Kopie von `wp-content/uploads/` vom alten Server.

1. **Docroot anlegen, dessen Pfad `prod` enthält** (z.B.
   `.../public_html/prod`), sonst greift die Umgebungserkennung nicht und
   die Seite hält sich für die Local-Umgebung.
2. **Dieses Repo in den Docroot klonen.** Damit ist der Docroot von Anfang
   an eine Git-Arbeitskopie (Drift-Kontrolle inklusive).
3. **Aktuelles WordPress von wordpress.org darüber entpacken.** Die
   Core-Dateien stören Git nicht, sie stehen alle in der `.gitignore`. Die
   `wp-config.php` aus dem Repo **nicht** überschreiben lassen (die
   WordPress-eigene heißt ohnehin nur `wp-config-sample.php`).
4. **`.wp-env.production.php` prüfen/anpassen:** DB-Host, DB-Name, DB-User,
   `WP_HOME`/`WP_SITEURL` für den neuen Server.
5. **`.wp-secrets.php` im Docroot anlegen** (nie committen), Muster:
   ```php
   <?php
   return [
       'DB_PASSWORD' => '...',
       'AUTH_KEY' => '...',
       // ... übrige WP-Salts, frisch generieren: https://api.wordpress.org/secret-key/1.1/salt/
   ];
   ```
6. **Datenbank-Dump einspielen** und `wp-content/uploads/` hineinkopieren.
   Falls sich die Domain nicht ändert, ist danach nichts weiter nötig; bei
   Domainwechsel Suchen/Ersetzen der URLs in der DB (wp-cli
   `search-replace` oder Plugin).
7. **Plugins nachinstallieren** (Liste oben; Polylang Pro braucht die
   Lizenz). Aktivierungszustände kommen aus der DB.
8. **Deploy-Kette auf dem neuen Server aufsetzen:** Repo zusätzlich als
   `~/site-repo` klonen, einen frischen Read-only-Deploy-Key für GitHub
   erzeugen und am Repo hinterlegen, dann `~/deploy-prod.sh` und
   `~/deploy-staging.sh` als Einzeiler anlegen, die
   `~/site-repo/deploy/deploy.sh prod|staging` aufrufen.
9. **Härtung von Anfang an** (Abschnitt 4 unten): Auto-Updates, 2FA,
   frische Passwörter und Salts.
10. **Funktionsprobe:** Startseite DE/EN, eine Unterseite mit Kalender-Block,
    Login, ein Testartikel-Entwurf. Danach `git status` im Docroot: sollte
    leer sein.

## 4. Sicherheitspunkte für die Migration

Kontext: Im Juli 2026 wurde die Seite über eine WordPress-Core-Lücke
(REST-Endpunkt `/wp-json/batch/v1`, Massen-Ausnutzung "wp2shell")
kompromittiert. Webshells und Fake-Admins wurden entfernt, Core und Plugins
aktualisiert, das DB-Passwort rotiert.

**Nicht auf den neuen Server mitnehmen (Altlasten im alten Docroot bzw. Home):**

- `.htbvhe55.appconfig.php`: Installatron-Altlast mit einem
  **Klartext-Passwort aus 1blu-Zeiten**, war für den Angreifer lesbar.
  Prüfen, ob dieses Passwort bei 1blu noch irgendwo gilt (1blu bleibt für
  DNS und Mail auch nach der Migration im Einsatz), dann Datei löschen.
- `~/quarantine_hack_20260720/` (Webshells in Quarantäne),
  `~/public_html/prod_copy_20260119/` (alter Site-Klon), Backups
  `*.bak_20260720` im Docroot (enthalten alte Passwörter).
- Installatron-Reste: `wp-itapi.php` (Fernwartungs-Endpunkt),
  `wp-content/deleteme.*.php`, mu-plugin `installatron_hide_status_test.php`.
- Diverses: `wherephp.php`, `.idea/`, `.user.ini` (zeigt auf nicht
  existenten alten 1blu-Pfad), `wp-content/plugins.OFF/`,
  `wp-content/wps-hide-login/` (deplatzierte Plugin-Kopie), alte Themes
  `blankslate`/`blankslate-child` sobald bestätigt ist, dass nichts mehr
  darauf zeigt.

**Auf dem neuen Server von Anfang an:**

- WordPress-Core-Auto-Updates aktivieren (das Einfallstor war ein
  veralteter Core; das schlanke Repo ist genau dafür gebaut).
- 2FA für alle Admin-Konten erzwingen (Stand 08/2026 hat kein Konto 2FA).
- `xmlrpc.php` sperren, Login-Rate-Limit, REST-API einschränken wo möglich.
- Alle Zugänge frisch erzeugen statt übernehmen: Admin-Passwörter,
  WP-Salts, SSH-Keys, SFTP-Passwort, GitHub-Deploy-Key.
- Backup-Konzept festlegen: regelmäßige DB-Dumps plus Datei-Backup. Das
  Repo ersetzt kein Backup; Rollbacks nach kaputten Updates brauchen
  DB-Snapshots.

**Eigenheiten des aktuellen (alten) Servers:**

- SSH/SFTP auf Port 222; `~/.ssh/authorized_keys` wird vom sshd des
  Webhostings ignoriert, Login geht nur per Passwort.
- wp-cli erreicht die Remote-DB von der Server-CLI nicht; DB-nahe Aktionen
  laufen über wp-admin.
- DNS und Postfächer liegen bei 1blu, nicht beim Hosting-Anbieter.

## 5. Chronik: Umbau August 2026

- Repo war seit Januar 2026 veraltet; die Hack-Bereinigung vom Juli lief
  nur auf dem Server. Der bereinigte Server-Stand wurde als Tag
  `snapshot-2026-08` vollständig konserviert (letzter Voll-Snapshot des
  gesamten WordPress-Baums).
- Danach Umstellung auf das schlanke Schema (nur eigener Code), neues
  Deploy-Skript, Prod-Docroot als saubere Git-Arbeitskopie.
- Alter GitHub-Deploy-Key (beim Hack lesbar, nur Read-only) widerrufen und
  durch "Hetzner Deploy 2026" ersetzt.

## 6. Offene Punkte am Theme (Review 14.08.2026)

Das Theme `klohn-kit` ist klein (~1.800 Zeilen), konsistent modularisiert
und sauber escaped. Offen, nach Gewicht:

1. Build-Pipeline fehlt im Repo: `assets/src/` (SCSS/JS) wird zu
   `assets/dist/` kompiliert, aber es gibt kein Build-Setup und keine Doku.
   Empfehlung: minimales Setup (sass + esbuild) einchecken oder
   JS-Minifizierung aufgeben.
2. Kurze Wartungs-README im Theme ergänzen (wo ändert man was, wie baut
   und deployt man).
3. Die XML-Dokumente im Theme-Ordner (`hosting-setup.xml` u.a.) sind
   öffentlich über die Website abrufbar. Keine Secrets enthalten, aber
   Infrastruktur-Beschreibung; in einen nicht deployten Ordner verschieben.
4. Kleine Altlasten: KI-Artefakt-Kommentar in `inc/blocks.php`, toter Code
   in `inc/performance.php`, Legacy-Codepfad für das nicht mehr
   funktionierende `fetch-calendar.php`.
5. `blankslate`/`blankslate-child` (Vorgänger-Themes) entfernen, sobald
   bestätigt ist, dass nichts mehr darauf zeigt.
