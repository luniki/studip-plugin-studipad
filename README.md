# Stud.IP-"Etherpad Lite"-Integration
Der Stud.IPad Plugin erlaubt es Stud.IP mit einem Etherpad-Lite Server zu verbinden.

![Stud.IP Etherpad in einer Veranstaltung](screenshot.jpg?raw=true)

## Installation

Zuerst muss `etherpad-lite` installiert werden. Folgen Sie dazu der Beschreibung auf https://github.com/ether/etherpad-lite#installation

Nun muss ihr Etherpad noch konfiguriert werden. Grundlegendes dazu finden Sie unter https://github.com/ether/etherpad-lite#tweak-the-settings

Sie müssen zwingend die Datei APIKEY.txt anlegen und dort ein beliebiges Token anlegen. Dieses Token wird für API-Zugriffe von Stud.IP auf ihr Etherpad benötigt. Mehr dazu findet sich unter https://etherpad.org/doc/v1.6.2/#index_authentication

Wenn Sie möchten, installieren Sie sich weitere Plugins aus der Liste https://github.com/ether/etherpad-lite/wiki/Available-Plugins

Für den Betrieb mit Stud.IP müssen aber zwei Plugins installiert sein:

``` shell
npm install github:luniki/ep_auth_session
npm install github:virtUOS/ep_resize
```

Starten Sie jetzt Ihre Etherpad-Installation.

Nun können sie das Stud.IP-Plugin aus diesem Repository installieren.
Zum Schluss müssen Sie noch ein paar Konfigurationsoptionen in Stud.IP vornehmen (also unter https://<mein-studip>/dispatch.php/admin/configuration/configuration ):

* STUDIPAD_APIKEY : API-Key fuer das Etherpad Lite API. Tragen Sie hier den Inhalt aus APIKEY.txt ein!
* STUDIPAD_PADBASEURL : URL zu Pads auf dem Etherpad Lite Server, z.B. https://<mein-studip>:9001/p
* STUDIPAD_APIURL : URL zum Etherpad Lite API, z.B. https://<mein-studip>:9001/api
* STUDIPAD_INITEXT : Text der in neue Pads automatisch eingefügt wird.


## Verwendung

Für jede Veranstaltung / Studiengruppe in Stud.IP können Pads von Dozenten/Tutorn angelegt werden.
Innerhalb von der Veranstaltung kann dann jeder Teilnehmer die bereits angelegten Pads nutzen.
