# IO – Der Grundbaustein für plentymarkets Template-Plugins

**IO** ist das offizielle Logik-Plugin für den Standard-Webshop von plentymarkets. Im neuen Webshop für plentymarkets sind Design und Logik voneinander getrennt. Der Webshop wird über zwei Plugins in deinem plentymarkets System eingebunden. Das Plugin **plentyShop LTS** beinhaltet das Standard-Design des Webshops und kann nach deinen Wünschen angepasst werden. Das Plugin **IO** beinhaltet die Webshop-Logik, stellt eine allgemeine Grundlage für alle Design-Plugins dar und kann auch von anderen Plugins verwendet werden.

## IO in plentymarkets einrichten

Das Plugin **IO** selbst muss nur in plentymarkets bereitgestellt werden. Hinweise zur Einrichtung von IO findest du [im plentymarkets Handbuch](https://knowledge.plentymarkets.com/de-de/manual/main/webshop/io-einrichten.html).

<div class="alert alert-warning" role="alert">
  Bei der Einrichtung von IO ist es zwingend notwendig, dass <b>IO</b> in der Plugin-Übersicht mit der Aktion <b>Position festlegen</b> die höchste Positionsnummer (z.B. 999) zugewiesen wird, damit sämtliche Routen des Webshops korrekt abgerufen werden.
</div>

**IO** ist ein vorausgesetztes Plugin für das Template-Plugin **plentyShop LTS**.

## Lizenz

Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen findest du in der [LICENSE.md](https://github.com/plentymarkets/plugin-io/blob/stable/LICENSE.md).
