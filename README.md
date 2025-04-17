# Familyboard – Installationsanleitung

## Testzugang

Login-Daten zum Testen:

- **E-Mail:** `dozent@example.com`  
- **Passwort:** `test123!`

---

## 1. Projektdateien bereitstellen

1. **Ordner erstellen:**  
   Im XAMPP-Ordner `htdocs` muss ein Ordner namens `files` erstellt werden.  
   Beispielpfad: `C:\xampp\htdocs\files`

2. **Projekt kopieren:**  
   Den Familyboard-Projektordner in den neu erstellten Ordner `files` kopieren und den Ordner in `C:\xampp\htdocs\files\Do-IT` umbenennen.

---

## 2. Mailtrap für E-Mail-Funktionalität einrichten

1. Kostenloses Konto bei [Mailtrap](https://mailtrap.io) erstellen.
2. SMTP-Zugangsdaten im Mailtrap-Dashboard abrufen.
3. Datei `private/dashboard/dashboard.php` öffnen.
4. An den Stellen (ca. Zeile 131–132) die Platzhalter mit den Mailtrap-Zugangsdaten ersetzen:

   ```php
   $mail->Username = 'DEIN_MAILTRAP_USERNAME';
   $mail->Password = 'DEIN_MAILTRAP_PASSWORT';

---

## 3. Datenbank importieren

1. DBeaver (oder ein vergleichbares Datenbankverwaltungstool) starten und mit dem lokalen MySQL-Server verbinden.
2. Neue Datenbank anlegen oder eine vorhandene Datenbank auswählen.
3. Im Reiter **"Importieren"** folgende Datei auswählen:

DatenbankDump/dump-familyboard-202504151239.sql


4. Importvorgang bestätigen.  
Nach Abschluss stehen die Testdaten zur Verfügung.

---

## 4. Datenbankverbindung konfigurieren

1. Datei `private/config/db.php` öffnen.
2. Falls erforderlich, die Zugangsdaten für die Datenbankverbindung anpassen:

```php
$username = 'root';
$password = '';
(Standardwerte für XAMPP, andernfalls entsprechend der eigenen Konfiguration anpassen.)
```
---


## 5. Familyboard starten

1. Apache- und MySQL-Server über das XAMPP-Control-Panel starten.

2. Familyboard im Browser aufrufen:

        Aufruf über: http://localhost/files/Do-IT/

Die Anwendung sollte nun bereit zur Nutzung sein.

---