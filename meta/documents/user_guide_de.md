# IO – Der Grundbaustein für plentymarkets 7 Template-Plugins

**IO** ist das offizielle Logik-Plugin für den Standard-Webshop von plentymarkets 7. Im neuen Webshop für plentymarkets 7 sind Design und Logik voneinander getrennt. Der Webshop wird über zwei Plugins in Ihrem plentymarkets System eingebunden. Das Plugin **Ceres** beinhaltet das Standard-Design des Webshops und kann nach Ihren Wünschen angepasst werden. Das Plugin **IO** beinhaltet die Webshop-Logik, stellt eine allgemeine Grundlage für alle Design-Plugins dar und kann auch von anderen Plugins verwendet werden.

## IO in plentymarkets einrichten

Das Plugin **IO** selbst muss nur in plentymarkets bereitgestellt werden. Hinweise zur Einrichtung von IO finden Sie [hier](https://knowledge.plentymarkets.com/omni-channel/online-shop/ceres-einrichten#10).

<div class="alert alert-warning" role="alert">
  Bei der Einrichtung von IO ist es zwingend notwendig, dass <b>IO</b> in der Plugin-Übersicht mit der Aktion <b>Position festlegen</b> die höchste Positionsnummer (z.B. 999) zugewiesen wird, damit sämtliche Routen des Webshops korrekt abgerufen werden.
</div>

**IO** ist ein vorausgesetztes Plugin für das Template-Plugin **Ceres**.

<div class="alert alert-danger" role="alert">
    Wenn Sie das Plugin <b>IO</b> in <b>Productive</b> bereitstellen, ist der alte plentymarkets Webshop nicht mehr erreichbar, da <b>IO</b> die URL des Webshops übernimmt.
</div>

## Lizenz

Das gesamte Projekt unterliegt der GNU AFFERO GENERAL PUBLIC LICENSE – weitere Informationen finden Sie in der [LICENSE.md](https://github.com/plentymarkets/plugin-io/blob/stable/LICENSE.md).
