# do-it Family Board
***
Short Description about the project.

## Installation

1. Klone das Projekt:  
     $ git clone https://github.com/fiaTG/do-it.git

2.  Kopiere die Datenbank-Konfigurationsdatei:
    cp private/config/db.example.php private/config/db.php
3.  Fülle die Zugangsdaten in private/config/db.php aus.
4.  Installiere Abhängigkeiten mit Composer: composer install
5.  Starte den lokalen Server: php -S localhost:8000 -t public

mysql -u user -p database_name < database.sql  // toDO