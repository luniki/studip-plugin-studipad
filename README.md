Der Stud.IPad Plugin erlaubt es Stud.IP mit einem Etherpad-Lite Server zu verbinden.

Etherpad:
http://etherpad.org

Etherpad Installation
https://github.com/ether/etherpad-lite#installation


Für jede Veranstaltung / Studiengruppe in Stud.IP können Pads von Dozenten/Tutorn angelegt werden. 
Innerhalb von der Veranstaltung kann dann jeder Teilnehmer die bereits angelegten Pads nutzen. 

Config Einstellungen Stud.IPad Plugin


STUDIPAD_APIKEY : API-Key fuer das Etherpad Lite API 
STUDIPAD_PADBASEURL : URL zu Pads auf dem Etherpad Lite Server 
STUDIPAD_APIURL : URL zum Etherpad Lite API
STUDIPAD_INITEXT : Text der in neue Pads automatisch eingefügt wird.
STUDIPAD_COOKIE_DOMAIN : Domain für die Etherpad Lite Session Cookies die vom Plugin erzeugt werden.

Die pad.js in folgendes Etherpad-Verzeichnis kopieren: <Etherpad-Lite-Instanz>/src/static/custom

