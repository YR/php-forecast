# Værvarsel i PHP-format

Vi har laget et PHP-script som kan inkluderes på nettsidene dine.

PHP er et utbredt scriptspråk, og de fleste webhotell og nettskyleverandører tilbyr støtte for PHP.

##Slik gjør du det
1. Du må ha tilgang på en webserver med PHP versjon 5 eller nyere.
2. [Last ned scriptet (zip-fil)](https://github.com/YR/php-forecast/archive/master.zip) og pakk ut filene til en mappe på maskinen din. Du kan også klone dette repoet dersom du bruker Git.
3. Rediger fila yr.php med en tekseditor (f.eks. Notepad). Øverst i yr.php finner du instruksjoner på hva du må redigere.
4. Bruk et FTP-program, eller Git for å laste opp fila til serveren din (kontakt leverandøren av ditt webhotell eller nettskyleverandør dersom du trenger hjelp til dette).
5. Gå til http://www.dittdomene/yr.php. Her vil du nå kunne se varselet.

Du kan også kopiere ut PHP-koden i dette scriptet og lime det inn på en annen side (inkludert i designet ditt). Pass på at du gjør om innstillingene i yr.php i samsvar med dette.

Du må gjerne endre på scriptet dersom du kan PHP, men husk at du må følge [vilkårene for bruk av data fra yr.no](http://om.yr.no/verdata/vilkar/) (du må bl.a. ha en lenke til yr.no godt synlig på siden din).

**Husk at du alltid må bruke siste versjon av scriptet.**

Lykke til!

##Viktig!
* Dersom det blir gjort endringer på yr.no som har innvirkning på hvordan PHP-varselet fungerer, må du laste ned en oppdatert versjon her. For å få varlser om oppdateringer, kan du bruke "Watch"-funksjonen til GitHub.
* Les vilkårene for bruk av data fra yr.no på [vilkår for bruk av data fra yr.no](http://om.yr.no/verdata/vilkar/).
* yr.no kan dessverre ikke gi brukerstøtte eller hjelpe deg med å implementere PHP-scriptet på nettsidene dine. Dersom du ikke får scriptet til å fungere, må du få hjelp av en person som kan PHP og enkel servaradministrasjon.
* Dersom du videreutvikler scriptet (fjernar eventuelle feil eller legger til nye funksjoner), vil yr.no gjerne dele det du har gjort med andre brukere av yr.php! Dersom du ønsker å dele det du har gjort med andre, send en e-post til yr (krøllalfa) met.no.
 
##Kreditering
* Den første versjonen av scriptet ble laget av Øyvind Skau, anubix.net. Scriptet er senere videreutviklet og forbedret av Lennart André Rolland, med fler i NRK.
* Scriptet er inspirert av XML Parser Class / Eric Rosebrock,phpfreaks.com.
* Klassene er opprinneleg fra kris@h3x.com, devdump.com/phpxml.php
