# Release Notes für IO

## v4.6.1 (2020-02-18) <a href="https://github.com/plentymarkets/plugin-io/compare/4.6.0...4.6.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler wurden keine Suchergebnisse ausgegeben, wenn keine Seite für Suchergebnisse über den ShopBuilder verknüpft wurde. Dies wurde behoben.
- Durch ein Fehler im Zusammenhang mit Lieferadressen konnte der Bestellvorgang für Gastbestellungen nicht ausgeführt werden. Dieses Verhalten wurde behoben.

## v4.6.0 (2020-02-17) <a href="https://github.com/plentymarkets/plugin-io/compare/4.5.1...4.6.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### TODO

- Google reCAPTCHA wird ab sofort erst nach der Zustimmung zur Verwendung der entsprechenden Cookies durch den Webshop-Besucher ausgeführt. Daher können Formulare, die über das reCAPTCHA abgesichert sind, auch erst nach Zustimmung des Benutzers abgeschickt werden. Dazu zählen das Kontaktformular und die Kundenregistrierung.

### Hinzugefügt

- Bei der Auftragsanlage kann nun ein Bezugszeichen des Kunden angegeben werden.
- Der Assistent enthält nun eine Einstellung mit der man die Umsatzsteueridentifikationsnummerprüfung für die Anlage/Änderung von Addressen und den Bestellabschluss aktivieren kann.

### Geändert

- Der OrderReturnController.php übergibt nun die Kategorie an das Frontend, falls im ShopBuilder eine Kategorie für die Retourenseite verknüpft ist.
- Eigenschaften werden nun in den Datensätzen für Artikellisten nicht mehr ausgegeben, um die Datenmenge zu reduzieren.
- Die übertragenen Daten bei Artikeln mit Bestellmerkmalen wurden optimiert.
- Der ItemImageFilter gibt nun auch Alternativtext und Namen des Bildes aus.
- Das Live-Shopping-Widget zeigt nun das Angebot als beendet an, wenn ein Artikel auf Netto-Warenbestand beschränkt und dieser Bestand erschöpft ist.
- Bei auftretenden Fehler im Zusammenhang mit Google reCAPTCHA werden nun Fehlermeldungen ausgegeben.

### Behoben

- Es wurde eine feste Abhängigkeit zum Plugin Ceres entfernt.
- Falls beim Aktualisieren von Artikelmengen im Warenkorb der Mindestbestellwert unterschritten wurde, wurden bereits eingelöste Gutscheine mit Mindestbestellwert nicht entfernt. Dies wurde behoben.
- Im Navigationsbaum wurden die Einstellungen für Innenabstände nicht auf nachgeladene Listenelemente angewandt. Dies wurde behoben.
- Es werden nun keine Kategorien mehr in der Navigationsleiste ausgegeben, wenn alle Kategorietypen in der Ceres-Einstellung **Kategorietypen, die in der Navigationsleiste angezeigt werden** deaktiviert sind.
- Unter bestimmten Umständen wurde der eingelöste Coupon-Code nicht korrekt am Auftrag im Frontend ausgegeben. Dies wurde behoben.
- Aufträge vom Typ Gewährleistung werden jetzt im Mein Konto-Bereich angezeigt und können retourniert werden.
- Bei der Weiterleitung zu einer im ShopBuilder erstellen Login-Seite wurde nach dem Login nicht korrekt auf den Checkout weitergeleitet. Dies wurde behoben.

## v4.5.1 (2020-01-28) <a href="https://github.com/plentymarkets/plugin-io/compare/4.5.0...4.5.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Auftragsdokumente für Gastbestellungen können nun wieder über die Weiterleitung in der Bestellbestätigung aufgerufen werden.

## v4.5.0 (2019-12-19) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.3...4.5.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### TODO

### Hinzugefügt

- Der Ceres-Assistent enthält jetzt Einstellungen für die Variantenauswahl, mit denen die "Bitte wählen"-Option hinzugefügt und vorausgewählt werden kann.
- Die Route `/rest/io/categorytree/template_for_children` wurde hinzugefügt, welche gerendertes Markup des Navigationsbaum-Widgets zurückgibt.
- Die Route `/rest/io/categorytree/children` wurde hinzugefügt, welche die Unterkategorien einer Kategorie zurückgibt.

### Geändert

- Die Route `io/facet` wurde als `deprecated` markiert.
- Facettenwerte werden nun sortiert ausgeliefert.

### Behoben

- In der Addressauswahl wurde keine Adresse vorausgewählt. Dieses Verhalten wurde behoben.
- Es kam zu einem Fehler bei der Generierung von Artikel-URLs. Dieses Verhalten wurde behoben.
- Es kam zu Routing-Problemen, wenn Kategorien über den ShopBuilder verknüpft waren, deren Übersetzungen in anderen Sprachen fehlten. Dieses Verhalten wurde behoben.
- Es kam zu einem Anzeigefehler im Warenkorb, wenn sich Lieferland und Währung aufgrund eines Sprachwechsels änderten. Dieses Verhalten wurde behoben.

## v4.4.3 (2019-11-29) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.2...4.4.3" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben 

- Durch einen Fehler konnten Artikelkategorieseiten nicht mehr vom ShopBooster in den Cache geschrieben werden. Dies wurde behoben.

## v4.4.2 (2019-11-28) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.1...4.4.2" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben 

- Durch einen Fehler konnten Artikelkategorieseiten nicht mehr vom ShopBooster in den Cache geschrieben werden. Dies wurde behoben.

## v4.4.1 (2019-11-19) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.0...4.4.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben 

- Bestellmerkmale wurden im Warenkorb und im Checkout erst nach einem erneuten Laden der Seite angezeigt und nicht am Auftrag mitgegeben. Dieses Verhalten wurde behoben.

## v4.4.0 (2019-11-14) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.4...4.4.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Über den ShopBuilder ist es nun möglich, Eigenschaften vom Typ **Datei** in der Artikelansicht anzuzeigen.

### Geändert

- Beim Bestellabschluss werden die benötigten Artikeldaten nun aus der Datenbank gelesen, um Fehleranfälligkeit zu reduzieren.
- Das Event **AfterBasketChanged** enthält nun auch basketItems.

### Behoben

- Wenn ein Auftrag durch eine Ereignisaktion aufgeteilt wurde, wurde nicht der korrekte Auftrag auf der Bestätigungsseite angezeigt. Dies wurde behoben.
- Artikel, für die die Einstellung **Aktionsgutschein/POS-Rabatt: Nur mit Gutschein kaufbar** aktiv ist, können nun nicht mehr gekauft werden, wenn kein Aktionsgutschein eingelöst wurde.
- Bei aktiviertem Trailing Slash kam es auf der Bestellbestätigungsseite zu einem Weiterleitungsfehler. Dies wurde behoben.
- Durch einen Fehler wurden keine Kategorien in der mobilen Navigation ausgegeben, wenn nicht alle Kategorien des betreffenden Asts in der Linkliste enthalten waren. Nun werden die Kategorien der ersten Ebene angezeigt.
- Merkmale, für die die Option "Als Zusatzkosten darstellen" (Pfand) aktiv ist, werden nun korrekt als Auftragspositionen übernommen und auf die Gesamtsumme des Auftrags addiert.
- Wenn ein Fehler beim Laden von Artikeldaten auftritt, schreibt der ShopBooster das betreffende Template nicht mehr in den Cache.
- Metadaten werden jetzt korrekt ausgegeben.
- Es kam unter bestimmten Umständen zu einer falschen Anzeige der Versandkosten. Dieses Verhalten wurde behoben.
- Es wurde fälschlicherweise ein Trailing Slash an URLs angehängt, die Query-Parameter enthielten. Dieses Verhalten wurde behoben.
- Unter bestimmten Umständen konnte es zu doppelten Aufträgen kommen. Dieses Verhalten wurde behoben.
- Bei der Nutzung von Callisto mit eingebundenem Ceres-Checkout konnte die Auftragsbestätigung nicht angezeigt werden, wenn diese über den ShopBuilder erstellt wurde. Dies wurde behoben.
- Bei Verwendung einer ShopBuilder-Kategorie als Startseite wurde unter bestimmten Umständen ein falscher Header geladen. Dies wurde behoben.

## v4.3.4 (2019-10-30) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.3...4.3.4" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Bei zwei aufeinanderfolgenden Bestellungen im Webshop wurde die Liste der Zahlungsarten beim Aufruf des Checkouts nicht korrekt geladen. Dieses Verhalten wurde behoben.

## v4.3.3 (2019-10-17) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.2...4.3.3" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### TODO

- Für die Verwendung von IO 4.3.3 muss das Plugin Ceres auf die aktuelle Version 4.3.4 aktualisiert werden.

### Behoben

- Im ShopBuilder wurde eine 404-Seite angezeigt, wenn eine Kategorie für die Retourenseite verknüpft war. Dies wurde behoben.
- Durch einen Fehler wurde die Auftragsbestätigungsseite nicht angezeigt, wenn eine ShopBuilder-Kategorie mit der Route "/confirmation" angelegt und verknüpft wurde.
- Durch einen Fehler funktionierte Verlinkung auf die Retourenseite nicht, wenn diese ohne zusätzliche Parameter aufgerufen wurde. Dies wurde behoben.
- Auftragsdokumente konnten nicht für Gastbestellungen angezeigt werden. Dies wurde behoben.
- Checkboxen, die über den Assistenten gespeichert wurden, konnten nicht korrekt ausgelesen werden. Dieses Verhalten wurde behoben.
- Es kam unter bestimmten Voraussetzungen zu einer erhöhten Anzahl an Log-Einträgen. Dieses Verhalten wurde behoben.
- Beim Aufsplitten von Artikelpaketen kam es zu einer fehlerhaften Anzeige. Dies wurde behoben. 

## v4.3.2 (2019-10-02) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.1...4.3.2" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler war die Registrierungsseite nicht mehr über den Header des Shops erreichbar. Dies wurde behoben.

## v4.3.1 (2019-10-1) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.0...4.3.01" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Ein Fehler führte dazu, dass sprachspezifische URLs nicht korrekt ermittelt werden konnten. Dies wurde behoben.
- Ein Fehler führte dazu, dass die mobile Navigation nicht richtig geladen wurde. Dies wurde behoben.


## v4.3.0 (2019-09-26) <a href="https://github.com/plentymarkets/plugin-io/compare/4.2.0...4.3.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Retouren können jetzt auch für Gastbestellungen angelegt werden.
- Das Link-Button-Widget wurde zum ShopBuilder hinzugefügt. Dieses stellt Links zu Retouren und Sendungsverfolgung als Schaltflächen zur Verfügung.
- Es wurde eine REST-Route zum Abmelden von einzelnen Newslettern hinzugefügt.

### Geändert

- Die Daten der Klasse **LocalizedOrder** enthalten nun auch die Attribute der Variante.
- Die Menge der übertragenen Daten beim Bearbeiten des Warenkorbs wurde minimiert, um die Performance des Webshops zu verbessern.
- Über pluginApp erzeugte Objekte werden nun vor der weiteren Verwendung in eigene Variablen gespeichert. Die direkte Verwendung neuer Instanzen führte in einzelnen Fällen zu Fehlern während der Plugin-Bereitstellung.
- Die Funktion `getBasketForTemplate` fügt dem Warenkorb nun das Feld "isExportDelivery" hinzu. Dieses enthält die Information, ob es sich bei der aktuellen Kombination aus Shop-Standort und ausgewähltem Lieferland um eine Ausfuhrlieferung handelt.
- Die REST-Route `io/itemWishList` gibt nun die gesamten Artikeldaten der Varianten auf der Wunschliste anstatt der Varianten-IDs zurück.
- Die Warenkorbdaten enthalten nun die IDs der Wunschliste.
- Die Wunschlistenansicht wird nun vom ShopBooster gecached.
- Die Newsletter-Abmeldung meldet den Kunden nicht mehr aus allen Newslettern gleichzeitig ab, sondern nur aus dem, der angefordert wurde.

### Behoben 

- Bei der dynamischen Gruppierung von Varianten werden die Ergebnisfelder wieder korrekt berücksichtigt und keine unnötigen Daten mehr geladen.
- Im ShopBuilder sind jetzt mehr als 50 Eigenschaften pro Gruppe als Platzhalter verfügbar.
- Durch einen Fehler wurden doppelte Werte in URL-Parametern entfernt. Dies wurde behoben.
- In Cross-Selling-Listen wurden Varianten angezeigt, für die die Option "unsichtbar in Artikellisten" aktiv war. Dieses Verhalten wurde behoben.
- Es wurden Facettenwerte angezeigt, die nicht die minimale Trefferzahl erreichten. Dieses Verhalten wurde behoben.
- Interne Weiterleitungen erfolgen ab sofort immer auf die gesicherte HTTPS-Domain, sofern diese verfügbar ist.
- Beim Laden der mobilen Navigation werden Kategorien ohne Namen nun aus dem Ergebnis gefiltert.
- Durch einen Fehler konnten manche Attribute in der Artikelansicht nicht ausgewählt werden. Dies wurde behoben.

## v4.2.0 (2019-08-21) <a href="https://github.com/plentymarkets/plugin-io/compare/4.1.2...4.2.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Die statischen Seiten für Widerrufsformular, Widerrufsrecht, AGB, Datenschutzerklärung und Impressum können nun mit dem ShopBuilder erstellt und bearbeitet werden.

### Behoben

- Bei der Darstellung von Grundpreisen konnte es zu Problemen kommen. Dieses Verhalten wurde behoben.
- Änderungen der Artikelmenge im Warenkorb in Verbindung mit einem Gutschein führten zu Problemen, wenn der Mindestbestellwert unterschritten wurde. Dies wurde behoben.
- Bei manchen Sprachen wurde im Warenkorb kein Leerzeichen zwischen dem ISO-Code der Währung und dem Wert ausgegeben. Dies wurde behoben.
- Beim Entfernen von Artikeln konnte es zu Fehlern kommen, wenn bereits ein Gutschein eingelöst wurde. Das Verhalten wurde behoben.
- Es wurde ein Fehler bei der Adressvalidierung behoben, durch welchen bei der Eingabe von Firmenadressen unter bestimmten Umständen eine Fehlermeldung im Shop angezeigt und die Adresse nicht gespeichert wurde.
- Versandarten wurden beim Wechsel des Lieferlandes nicht aktualisiert. Dies wurde behoben.

## v4.1.2 (2019-07-16) <a href="https://github.com/plentymarkets/plugin-io/compare/4.1.1...4.1.2" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Wir haben die neue REST-Route 'rest/io/categorytree' hinzugefügt, um Kategorien der mobilen Navigation zu laden.

### Geändert

- Attribute von Varianten ohne Bestand werden nun angezeigt, wenn an der Variante die Optionen "Automatisch verfügbar, wenn Netto-WB positiv" und "Automatisch nicht verfügbar, wenn kein Netto-WB" deaktiviert sind.

## v4.1.1 (2019-07-10) <a href="https://github.com/plentymarkets/plugin-io/compare/4.1.0...4.1.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler wurde die IO-Route “Seite nicht gefunden” im Bereich **Routing** bei der Bereitstellung der Plugins auch für Callisto-Webshops mit Ceres-Checkout aktiviert. Dieses Verhalten wurde behoben.

## v4.1.0 (2019-07-08) <a href="https://github.com/plentymarkets/plugin-io/compare/4.0.1...4.1.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Plugins können ab sofort zusätzliche Datenfelder angeben, die unabhängig vom Result Fields-Template immer geladen werden.
- Metainformationen von Dateien, die im neuen Webspace abgelegt werden, können ab sofort im Template mithilfe der Twig-Funktion `cdn_metadata()` ausgelesen werden.
- Der TWIG-Filter 'propertySelectionValueName' wurde hinzugefügt. Dieser gibt den Namen eines Werts innerhalb eines Bestellmerkmals vom Typ **Auswahl** aus.
- Es kann nun über den Tab Routing in der IO-Konfiguration gesteuert werden, ob die 404 Seite von IO ausgegeben werden soll.

### Geändert

- Die Variantenauswahl in der Artikeleinzelansicht wurde auf die ElasticSearch-Technologie umgebaut, um bessere Performance bieten zu können.
- Die "Passwort ändern"-Funktion im Mein Konto-Bereich validiert nun serverseitig das Passwort entlang unserer Vorgaben.
- Die Fehlermeldung, die bei Nichterreichen des Mindestbestellwerts angezeigt wird, wurde angepasst und um den benötigten Wert erweitert.
- Die Einstellung "Variantenauswahl für Varianten ohne Bestand in der Varianten-Dropdown-Liste aktivieren" wurde in der Ceres-Konfiguration als "deprecated" markiert und wird im Variantenauswahl-Widget nicht mehr berücksichtigt.

### Behoben

- Das Eingabefeld für Datum wird nun serverseitig korrekt validiert.
- Durch einen Fehler wurde bei Klick auf die Schaltfläche "Auftragsabwicklung" in der Auftragsübersicht keine Seite geöffnet, wenn die Startseite deaktiviert war. Dies wurde behoben.
- Die Mengenangabe auf der Wunschliste hat auch inaktive Artikel mitgezählt. Dieses Verhalten wurde behoben.
- Artikellisten-Widgets, bei denen mit die Einstellung **Hersteller** aktiv waren, hat die Sortierung ignoriert. Dieses Verhalten wurde behoben.
- Durch einen Fehler wurden URLs von Kategorien nicht richtig generiert, wenn der Kategoriename mit den Buchstaben des Sprachkürzels der aktuell ausgewählten Sprache begann. Dies wurde behoben.
- In der Auftragsübersicht konnte es zu dem Fehler "Resource not Found" kommen, wenn das am Auftrag hinterlegte Versandprofil nicht mehr im System vorhanden war. Das Verhalten wurde behoben.
- In der Auftragsübersicht konnte es zu dem Fehler "Resource not Found" kommen, wenn der am Auftrag hinterlegte Status kein Systemstatus war. Das Verhalten wurde behoben.
- Die Bezeichnungen von Bestellmerkmalen werden nun in der richtigen Sprache ausgegeben.
- Es kam bei der Berechnung von Staffelpreisen bei Varianten mit Bestellmerkmalen zu Problemen. Dieses Verhalten wurde behoben.
- Es kam zu Fehlern bei der Anzeige von Netto- und Bruttopreisen im Zusammenhang mit der USt.-ID an der Rechnungsadresse. Dieses Verhalten wurde behoben.
- Wenn nach dem Abschließen eines Auftrags während der Zahlung ein Fehler auftritt, kann der Auftrag erst nach 30 Sekunden erneut abgeschlossen werden. Dies verhindert das Anlegen von doppelten und somit ungültigen Aufträgen.
- Bei Aufrufen von Kategorien die mit a-XXX enden kam es zur Anzeige einer 404 Seite oder einer Artikeldetailansicht. Dieses Verhalten wurde behoben.
- Es wurde ein Fehler behoben, der die Weiterleitung von den Routen /checkout und /my-account auf den entprechenden ShopBuilder-Inhalt verhinderte.
- Die Sprache der aus dem Shop versendeten E-Mails stimmt nun mit der aktuell gewählten Sprache im Shop überein.
- Es wurde ein Fehler behoben, der das Ausliefern der Seite verhindert hat, wenn die Twig-Funktion `queryString` mit einem ungültigen Parameter aufgerufen wurde.

## v4.0.1 (2019-05-14) <a href="https://github.com/plentymarkets/plugin-io/compare/4.0.0...4.0.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Geändert

- Für die Methode `createContact()` in der Klasse CustomerService wurde die Möglichkeit ergänzt, eine Kundensprache in den übermittelten Daten mitzugeben, falls nicht die aktuell im Webshop ausgewählte Sprache verwendet werden soll.

### Behoben

- Bei Gastbestellungen konnte es beim Wechsel des Versandprofils zu Fehlern kommen. Dieses Verhalten wurde behoben.
- Bei Weiterleitungen innerhalb des Webshops wird die Sprache nun korrekt berücksichtigt.

## v4.0.0 (2019-05-02) <a href="https://github.com/plentymarkets/plugin-io/compare/3.2.0...4.0.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### TODO

- Über den Mein Konto-Bereich ist es angemeldeten Kunden jetzt möglich, ihre E-Mail-Adresse zu ändern. Hierfür müssen Änderungen in den E-Mail-Einstellungen unter **System » Systemeinstellungen » Mandant » Mandant wählen » E-Mail** getätigt werden. Unter **Vorlagen** erstellen Sie hierfür eine neue E-Mail-Vorlage. In dieser Vorlage muss der Platzhalter "$NewCustomerEmail" verwendet werden. Dieser Platzhalter enthält einen Bestätigungslink. Verknüpfen Sie diese Vorlage unter **Automatischer Versand** mit dem Ereignis **Kunde möchte E-Mail-Adresse ändern**.
- Um das Ändern der E-Mail-Adresse im Mein-Konto-Bereich zu ermöglichen, muss die Route "/change-mail" in den Einstellungen des Plugins IO aktiviert werden.

### Hinzugefügt

- Am Versandprofil wird nun die maximale Lieferzeit angezeigt. Diese wird aus der Verfügbarkeit mit der höchsten Lieferzeit der Artikel im Warenkorb und der Lieferfrist am Versandprofil berechnet.
- Bei der Registrierung und der Adresseingabe kann nun "Person" als Anrede ausgewählt werden, um eine Anrede für die Geschlechteroption "Divers" bereitzustellen.

### Geändert

- Das Eingabefeld "Ansprechpartner" für Firmenadressen ist nun keine Pflichtangabe mehr.
- Es wurde Logik von Ceres nach IO ausgelagert, die dafür sorgt, dass keine Adresse ausgewählt sein darf, die den Versand an eine Packstation oder Postfiliale beinhaltet, wenn das ausgewählte Versandprofil dies nicht unterstützt.
- Beim Speichern einer Adresse, die den Versand an eine Packstation/Postfiliale enthält, wird der Wert für die Postnummer nun aus dem Feld "postNumber" statt "address3" genommen.
- Alle Klassen des Namespace "IO\Services\ItemLoader" wurden entfernt. Als Alternative werden die Klassen des Namespace "IO\Services\ItemSearch" verwendet.

### Behoben

- Durch einen Fehler wurde die Cross-Selling-Artikelliste in der Artikeleinzelansicht in bestimmten Fällen nicht beim ersten Seitenaufruf geladen. Dies wurde behoben.
- Durch einen Fehler beinflusste die Sortierung der Kategorie auch die Sortierung einzelner Artikel, wenn für die Einstellung **Varianten nach Typ anzeigen** die Option "dynamisch" gewählt war. Ab sofort wird bei einzelnen Artikeln immer die Variante mit dem niedrigsten Preis angezeigt.
- Checkout und Mein Konto-Bereich aus dem ShopBuilder können jetzt auch dann dargestellt werden, wenn die Einstellung "Category routes" in IO deaktiviert ist.
- Es wurde ein Fehler beim Überprüfen von bereits vorhandenen E-Mail-Adressen bei der Newsletter-Registrierung behoben.

## v3.2.0 (2019-03-25) <a href="https://github.com/plentymarkets/plugin-io/compare/3.1.2...3.2.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurde ein neuer TWIG-Filter **addressOptionType** hinzugefügt, um Daten aus dem Adress-Typ einer Adresse ausgeben zu können.
- Es wurde ein neuer TWIG-Filter hinzugefügt, welcher es ermöglicht, Tabulator-Abstände aus Zeichenketten zu entfernen.
- Die im Backend eingestellten Sichtbarkeiten für Auftragsstatus werden nun bei der Ausgabe in Ceres berücksichtigt.

### Geändert

- Zum Ändern des Passworts im Mein-Konto-Bereich ist jetzt die Eingabe des bisherigen Passworts erforderlich.
- Das Laden des Kategoriebaums wurde überarbeitet und ist nun deutlich performanter.

### Behoben

- Kategorien können ab sofort auch im ShopBuilder bearbeitet werden, wenn die Routen in den Einstellungen deaktiviert sind.
- In den Artikellisten konnte es zur fehlerhaften Anzeige der gruppierten Attribute kommen. Das Verhalten wurde behoben.
- Fehler in der Versandkostenberechnung, die durch Einschränkungen an Zahlungsarten und Versandprofilen enstehen, werden nun abgefangen und die Versandkosten im Webshop korrekt ausgegeben.
- Die Kategorie-Option "Sichtbar: Nach Login" wird nun berücksichtigt. Kategorien, für die diese Option aktiv ist, werden erst nach Login in der Navigation angezeigt. Ein direkter Aufruf der URL leitet auf die Login-Seite.

## v3.1.2 (2019-03-20) <a href="https://github.com/plentymarkets/plugin-io/compare/3.1.1...3.1.2" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Checkout und Warenkorb können Wunschmaß-Artikel jetzt korrekt darstellen und verarbeiten.

## v3.1.1 (2019-03-11) <a href="https://github.com/plentymarkets/plugin-io/compare/3.1.0...3.1.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler konnte es dazu kommen, dass 404-Seiten nicht korrekt ausgegeben wurden. Dies wurde behoben.

## v3.1.0 (2019-02-25) <a href="https://github.com/plentymarkets/plugin-io/compare/3.0.1...3.1.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurde eine Schnittstelle geschaffen, um Nutzer beim Anlegen eines Auftrags zu einem oder mehreren Newslettern anzumelden.
- Es ist nun möglich, alle Artikel eines Herstellers per ElasticSearch abzufragen.

### Geändert

- Die im Warenkorb befindlichen Artikel enthalten nun zusätzliche Daten über die Gruppen der Varianteneigenschaften.
- Beim Anlegen einer Retoure wird ein neues Bestellmerkmal angelegt. Dieses dient dazu eine Ereignisaktion ausführen zu können.
- Vor dem Senden des Kontaktformulars wird das Google reCAPTCHA über den geheimen Webseiten-Schlüssen verifiziert.

### Behoben

- Durch einen Fehler wurde der Plugin-Bau unter gewissen Umständen nicht erfolgreich fertiggestellt. Dies wurde behoben.
- Durch einen Fehler wurden artikelabhängige Gutscheine nicht entfernt, wenn der betreffende Artikel aus dem Warenkorb entfernt wurde. Dies wurde behoben.
- Bei zusätzlichen Mandanten kam es zu Fehlern bei der Seitennummerierung von Kategorieseiten. Dieses Verhalten wurde behoben.
- Durch einen Fehler wurde die Währung beim Wechseln der Sprache nicht korrekt aktualisiert. Dies wurde behoben.

## v3.0.1 (2019-02-07) <a href="https://github.com/plentymarkets/plugin-io/compare/3.0.0...3.0.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler kam zu Überverkäufen. Dies wurde behoben.
- Die minimale und maximale Anzahl an Artikeln und Varianten führte teilweise bei Änderungen an Artikeln im Warenkorb zu Fehlern. Dieses Verhalten wurde behoben.
- Durch einen Fehler konnte es zu Speicherauslastung kommen. Dies wurde behoben.

## v3.0.0 (2019-01-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.17.1...3.0.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Artikel, die aufgrund ihrer Einstellungen (z.B. kein Preis für den Webshop) nicht im Shop angezeigt werden würden, werden nun im Vorschaumodus des Webshops angezeigt.
- Es wurde eine neue Route **io/facet** hinzugefügt, um für Kategorie- und Suchansicht die Filter nachzuladen.

### Geändert

- Die Validatoren zum Speichern einer Adresse wurden angepasst, um mit den Änderungen in Ceres bezüglich der Kontaktperson für Firmenadressen umgehen zu können.
- Beim Laden von mehreren Artikeln wird die Anzahl an Artikeln nicht mehr auf 10 begrenzt.
- Die **Middleware.php** referenziert bei der Abfrage auf verfügbare Währungen nicht mehr auf Ceres, sondern auf den in der IO angegebenen Template-Namen. Vielen Dank an <a href="https://github.com/davidisaak" target="_blank" rel="noopener"><b>@davidisaak</b></a> für diese Änderung.

### Behoben

- Durch einen Fehler konnten bei einer Gastbestellung Adressen ohne E-Mail-Adresse angelegt werden. Dies wurde behoben.
- Die Gültigkeit der Kaufabwicklungs-URL kann nun in der Ceres-Konfiguration festgelegt werden.
- Die Ergebnisse von `ItemService::getVariations()` werden jetzt in der angegebenen Reihenfolge zurückgegeben.
- Der automatische E-Mail-Versand war bei Gastzugängen fehlerhaft. Das Verhalten wurde behoben.
- Der Plugin-Bereitstellungsprozess zeigte eine fehlende Methodendeklaration an, obwohl die Methode vorhanden ist. Dies wurde behoben.
- Durch einen Fehler konnte der korrekte Auftragsstatus bei Verwendung eines Verkaufsgutscheins mit größerem oder gleichem Wert bezogen auf den Gesamtbetrag des Auftrags nicht gesetzt werden. Dies wurde behoben.
- Auf der Bestellbestätigungsseite wurden bei mehr als 10 Artikel nicht alle Artikelbilder angezeigt. Das Verhalten wurde behoben.
- Bei der Registrierung eines neuen Kunden wurde immer der Standardmandant am Datensatz gespeichert. Das Verhalten wurde behoben.

## v2.17.1 (2018-11-29) <a href="https://github.com/plentymarkets/plugin-io/compare/2.17.0...2.17.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler wurden Artikelkategorien in der Navigation angezeigt, die keine Artikelverknüpfung hatten. Dies wurde behoben.

## v2.17.0 (2018-11-27) <a href="https://github.com/plentymarkets/plugin-io/compare/2.16.1...2.17.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Die `CategoryItemResource wurde erweitert, um auch Kategoriebeschreibung 2 ausgeben zu können.

### Geändert

- Um mit bestehenden Callisto-URLs kompatibel zu sein, kann die Währung über den URL-Parameter `Currency` angegeben werden.
- Um mit bestehenden Callisto-URLs kompatibel zu sein, kann das Lieferland über den URL-Parameter "ShipToCountry" angegeben werden.

### Behoben

- Bei Aufträgen mit Rechnungssumme 0,00€ wurde der Status nicht korrekt angepasst. Das Verhalten wurde behoben.
- Die Einstellungen zum Aktivieren der Newsletter-Routen wurden bei der Anmeldebestätigung und der Abmeldung von Newsletter nicht berücksichtigt. Dies wurde behoben.

## v2.16.1 (2018-11-15) <a href="https://github.com/plentymarkets/plugin-io/compare/2.16.0...2.16.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler wurden Kategorien, die nicht mit einem Mandant verknüpft waren, als Filteroption in der Artikelsuche ausgegeben. Dies wurde behoben.
- Bei ungültigen Werten für die Sortierung von Artikeln konnte die Artikelansicht nicht dargestellt werden. Dies wurde behoben.
- Beim Hinzufügen oder Bearbeiten von Firmenadressen wurde fälschlicherweise die Kundenklasse zurückgesetzt. Dies wurde behoben.

## v2.16.0 (2018-10-22) <a href="https://github.com/plentymarkets/plugin-io/compare/2.15.0...2.16.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurde eine neue Funktion `getShippingCountryId` hinzugefügt. Durch diese Funktion kann die ID des Lieferlandes im Checkout ermittelt werden.
- Das Newsletter-Widget für den ShopBuilder wurde zu Ceres hinzugefügt.

### Geändert

- Das Kontaktformular versendet jetzt E-Mails mit einer Antwortadresse.
- Beim Anlegen einer Bestellung oder Retoure wird die Kundennotiz nun vor dem Erstellen gespeichert. Hierdurch wird diese Information in der Bestätigungs-E-Mail mitgeschickt.
- Die LocalizedOrder wurde um die ShippingProfileId erweitert.
- Die Datenstrukturen für die Kategorie-Navigation wurden minimiert, um die Ladezeit zu verbessern.
- Die Funktion `getHierarchy()` im CategoryService gibt nun alle Kategorien zurück und nicht nur solche, die in der Navigation angezeigt werden.
- In IO ist es nun möglich, während eines REST-Aufrufs das derzeitige Template auszulesen.

### Behoben

- Durch einen Fehler führte der Link in der Bestellbestätigung zu einer 404-Seite. Dies wurde behoben.
- Durch einen Fehler wurden die Kategoriebeschreibungen des Hauptmandanten auch für zusätzliche Mandanten ausgelesen. Dies wurde behoben.
- Durch einen Fehler wurden in der Varianten-Dropdown-Liste der Artikelansicht auch Varianten angezeigt, für die kein gültiger Verkaufspreis für den Webshop konfiguriert war. Dies wurde behoben.
- Durch einen Fehler wurden Seitenaufrufe per HEAD-Methode immer als Statuscode 404 zurückgegeben. Dies wurde behoben.
- Es wurden verschiedene SEO-relevante Anpassungen durchgeführt.
- Durch einen Fehler wurden nicht alle Artikel in die **Zuletzt gesehen**-Artikelliste aufgenommen. Dies wurde behoben.
- Durch einen Fehler wurden bei Gastbestellungen alle Versandprofile in der Kaufabwicklung angezeigt. Dies wurde behoben.
- Es wurde ein Fehler behoben, durch den die Auswahl einer Variante in der Einzelansicht nicht möglich war, wenn mindestens 2 Varianten aus derselben Attributkombination bestanden oder keine Attribute hatten. In solchen Fällen kann die Auswahl nun über die Dropdown-Liste Inhalt getroffen werden.
- Auf der Bestellbestätigungsseite kam es in seltenen Fällen zu fehlerhaften Darstellung der Versandkosten. Dieses Verhalten wurde behoben.

## v2.15.0 (2018-09-12) <a href="https://github.com/plentymarkets/plugin-io/compare/2.14.0...2.15.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Die am Kunden hinterlegte Login-URL funktioniert nun auch für Ceres.
- Durch einen Fehler wurde die Option **Nur Inland und EU** der Einstellung **Umsatzsteuer der Versandkosten auf Rechnung ausweisen** nicht interpretiert. Dies wurde behoben.

## v2.14.0 (2018-08-27) <a href="https://github.com/plentymarkets/plugin-io/compare/2.13.0...2.14.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Kategorien wurden als Filteroptionen bei Suchergebnissen hinzugefügt.
- Die URL der Callisto-Suche öffnet nun die Suche von Ceres.
- Die Callisto **/Tag/-URL** leitet nun auf die Suchseite von Ceres.
- Artikellisten und Suchergebnisse können jetzt zufällig sortiert werden.
- Es wurde ein neuer Hook hinzugefügt, über welchen auf das Ereignis Plugin-Bau reagiert werden kann. Dadurch wird die Invalidierung des Content Caches für das gebaute Plugin-Set ermöglicht.

### Geändert

- Die Livesuche im Header und die Suchseite wurden aneinander angepasst, sodass sie nun die gleichen Suchergebnisse liefern.

### Behoben

- Es kam zu Fehlern wenn Artikel in den Warenkorb bewegt wurden, wenn dabei Artikelpakete durch Basisartikel ersetzt werden sollten. Dies wurde behoben.
- Durch einen Fehler konnten Währungen gesetzt werden, die nicht in der Ceres-Konfiguration erlaubt waren. Dies wurde behoben.
- Ein Fehler wurde behoben, durch den keine aussagekräftige Fehlermeldung ausgegeben wurde, wenn man versucht hat, einen Artikel ohne Warenbestand in den Warenkorb zu legen.
- Durch einen Fehler wurden Artikel mit mehr als einem konfigurierten Preis nicht angezeigt, wenn die Mindestbestellmenge am Artikel gepflegt war.
- In Callisto Shops mit eingebundenem Ceres Checkout kam es bei Auftragsanlagen, die Artikel mit Live-Shopping-Preisen beinhalteten, zu Fehlern. Dieses Verhalten wurde behoben.

## v2.13.0 (2018-07-30) <a href="https://github.com/plentymarkets/plugin-io/compare/2.12.0...2.13.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Die in Ceres hinterlegte "Passwort vergessen"-E-Mail-Vorlage kann nun über das Backend verschickt werden.
- Der Gutschein-Code wird jetzt zusätzlich aus dem OrderTotalsService bereitgestellt.

### Geändert

- Der Grundpreis wurde für die Stückzahl 1 nicht ausgegeben. Dies wurde behoben. Die Anzeige des Grundpreises wird nun ausschließlich durch den an der Variante hinterlegten Wert bestimmt.

### Behoben

- In der Navigation wurden alle Kategorien ausgegeben. Dieses Verhalten wurde behoben, sodass jetzt unterschiedliche Navigationen abhängig von Kundenklassen ausgegeben werden können.
- Die CDN-URLs für Artikelbilder werden nun korrekt mandantenabhängig geladen.
- Durch einen Fehler wurde beim Anlegen eines Benutzers die falsche Sprache übergeben. Dies wurde behoben.
- Durch einen Fehler wurden Preise von Artikelpaketen auf der Bestellbestätigungsseite als 0 Euro dargestellt. Dies wurde behoben.

## v2.12.0 (2018-07-10) <a href="https://github.com/plentymarkets/plugin-io/compare/2.11.0...2.12.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Gerenderte Inhalte werden gecached, um die Ladezeit des Shops zu verbessern. Für diese Funktion ist ein zusätzliches Modul im plentymarkets Tarif erforderlich.
- Artikelpakete können jetzt im Webshop dargestellt werden.

### Geändert

- Benutzerspezifische Daten wie Warenkorb, Anmeldeinformationen und Wunschliste werden jetzt nachträglich geladen.
- Die Liste der zuletzt gesehenen Artikel wird jetzt nachträglich geladen.
- Die Route "/rest/io/customer" gibt nun keine Addressen mehr zurück. Hierfür wird nun die Route "io/customer/address" verwendet.

### Behoben

- Die Verlinkungen auf die Startseite funktionierten in der Standardsprache nicht richtig. Dieser Fehler wurde behoben.
- In Artikellisten mit Tags wurden Varianten nicht entsprechend der Plugin-Einstellungen gruppiert. Dies wurde behoben.
- Wenn für einen Artikel kein URL-Pfad hinterlegt wurde und zeitgleich die Option **Slash (/) am Ende von URLs* aktiviert war, wurde der URL-Pfad falsch generiert. Dieses Verhalten wurde behoben.
- Die Weiterleitung auf absolute URLs führte zu Fehlern. Dieses Verhalten wurde behoben.
- Es wurden diverse fehlerhafte Verlinkungen behoben.
- Es kam vor, dass die Sitemap bei mehreren Plugin Sets nicht anhand des Ceres-Musters generiert wurde. Dieses Verhalten wurde behoben.
- Unter bestimmten Umständen wurden Kategorien im Webshop angezeigt, die nicht mit dem Mandanten verknüpft waren. Dieses Verhalten wurde behoben.
- Wenn eine Kategorie vom Typ Content in allen Sprachen gespeichert wurde, konnte es passieren, dass sich die 404 Fehlerseite im Webshop öffnet. Dies wurde behoben.

## v2.11.0 (2018-06-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.10.0...2.11.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Für die Standardsprache wurde das Länderkürzel in der URL entfernt. Andere Sprachen werden mit Länderkürzeln in der URL erreicht.
- Durch einen Fehler wurde die Mehrwertsteuer nicht im Warenkorb berechnet, wenn aufgrund einer Kundenklasse Netto-Preise dargestellt wurden. Dies wurde behoben.

## v2.10.0 (2018-06-12) <a href="https://github.com/plentymarkets/plugin-io/compare/2.9.1...2.10.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Die IO-Konfiguration wurde ins Deutsche übersetzt.
- Das Ereignis `AfterBasketChanged` wurde um das Feld `showNetPrices` ergänzt. Dieses Feld bestimmt, ob in der Kaufabwicklung und im Warenkorb Netto- oder Bruttosummen hervorgehoben werden.
- Die Daten eines Auftrags auf der Bestellbestätigungsseite wurden um das Feld `highlightNetPrices` ergänzt. Dieses Feld bestimmt, ob Netto- oder Bruttosummen hervorgehoben werden.

### Geändert

- Die Schnittstellen zum Ausgeben von (Fehler-)Meldungen wurden verbessert.

### Behoben

- Durch einen Fehler führte der Link in der E-Mail einer Bestellbestätigung zu einer 404-Seite, wenn die Option "Weiterleitung zur Login-Seite durch den Link in der Bestellbestätigung" aktiv war. Dies wurde behoben.
- Durch einen Fehler wurden die Versandkosten nicht in die gewählte Währung umgerechnet. Dies wurde behoben.
- Durch einen Fehler wurden die Lieferländer im Adressformular immer auf Deutsch angezeigt. Dies wurde behoben.
- Durch einen Fehler wurden beim Wechsel der Währung Aufpreise für Bestellmerkmale nicht umgerechnet. Dies wurde behoben.
- Durch einen Fehler wurden Aufpreise für Bestellmerkmale immer brutto angezeigt. Dies wurde behoben.

## v2.9.1 (2018-06-05) <a href="https://github.com/plentymarkets/plugin-io/compare/2.9.0...2.9.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

# Behoben

- Verweise auf nicht existierende Kategorien konnte zu Anzeigefehlern im Webshop führen. Dies wurde behoben.
- Es kam zu Problemen wenn ein Gutschein mit Mindestbestellwert eingelöst wurde während gleichzeitig ein Artikel aus dem Warenkorb entfernt und damit der Mindestbestellwert nicht mehr erreicht wurde. Dies wurde behoben.
- Es kam zu falschen Darstellungen von Summen, wenn ein Aktionsgutschein eingelöst wurde, während Artikel mit unterschiedlichen Mehrwertsteuersätzen im Warenkorb lagen. Dies wurde behoben.

## v2.9.0 (2018-05-24) <a href="https://github.com/plentymarkets/plugin-io/compare/2.8.1...2.9.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurde die Methode **getURLById** im CategoryService hinzugefügt, welche die URL einer Kategorie ausgibt.
- Die Route **io/order/additional_information** wurde hinzugefügt, um zusätzliche Auftragsinformationen hinzufügen und ändern zu können.

### Behoben

- Die an der Kategorie hinterlegten Canonical URLs wurden nicht benutzt. Diese werden nun berücksichtigt.
- Durch einen Fehler wurden keine Bilder-URLs von der Funktion ItemService.getVariationImage() zurückgegeben. Dies wurde behoben.

## v2.8.1 (2018-05-16) <a href="https://github.com/plentymarkets/plugin-io/compare/2.8.0...2.8.1" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler konnten Adressen nicht angelegt oder geändert werden wenn für das ausgewählte Lieferland keine Bundesländer verfügbar waren. Dies wurde behoben.

## v2.8.0 (2018-05-08) <a href="https://github.com/plentymarkets/plugin-io/compare/2.7.0...2.8.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Ein neuer Service (TagService) wurde hinzugefügt, um den Namen eines Tags anhand seiner ID im Shop zu laden.
- Es wurden Facetten vom Typ: Preis hinzugefügt.
- Bei der Generierung von URLs wird nun die Einstellung zum Anhängen von Trailing-Slashes berücksichtigt.

### Behoben

- Daten aus dem GlobalContext von Ceres wurden nicht geladen, wenn man über eine Route aus einem anderen Plugin kam.
- Bei der Benutzung von Ceres und IO auf einem weiteren Mandanten konnte es dazu kommen, dass Kategoriedetails vom Hauptmandanten geladen wurden. Dies wurde behoben.

## v2.7.0 (2018-04-13) <a href="https://github.com/plentymarkets/plugin-io/compare/2.6.0...2.7.0" target="_blank" rel="noopener"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Bei Artikeln ohne Bilder wird jetzt das konfigurierte Platzhalter-Bild im Webshop dargestellt.
- Bestellmerkmale vom Typ **Datei** können jetzt verarbeitet werden.

### Behoben

- Durch einen Fehler wurden im Warenkorb keine Staffelpreise angezeigt. Dies wurde behoben.
- Bei Retouren wurde der Sperr-Status nicht vom ursprünglichen Auftrag übernommen. Dies wurde behoben.
- Durch einen Fehler wurden die Daten beim Speichern und Editieren von Adressen nicht serverseitig validiert. Dies wurde behoben.
- Durch einen Fehler wurden in der Bestellbestätigung der Auftragsstatus, der Versanddienstleister und die Zahlungsart immer in der Systemsprache angezeigt. Dies wurde behoben.
- Durch einen Fehler wurde die Rabattstaffel auf den Netto-Warenwert der Kundenklasse bei der Auftragsanlage nicht berücksichtigt. Dies wurde behoben.
- Durch einen Fehler wurde bei fehlerhaftem Login keine Meldung ausgegeben. Dies wurde behoben.

## v2.6.0 (2018-04-03) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.2...2.6.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- IO kann jetzt auf die Generierung der Sitemap reagieren und seine eigenen Muster zur Erzeugung der URLs vorgeben.

### Behoben

- Durch einen Fehler wurden im Warenkorb keine Staffelpreise angezeigt. Dies wurde behoben.
- Nach dem Logout wird nun das Standardlieferland als das aktive Lieferland gesetzt.
- Nach dem Kauf als Gast wird nun die Emailadresse aus der Session entfernt, sodass sie beim erneuten Betreten des Bestellvorgangs neu eingegeben werden muss.
- Unter gewissen Umständen wurde der Button zum Ändern der Zahlungsart auf der Auftragsbestätigungsseite nicht angezeigt.
- Ein Fehler führte dazu, dass man nach einem Kauf mit Paypal auf eine 404 Seite anstatt auf die Auftragsbestätigungsseite geleitet wurde. Dies wurde behoben.

## v2.5.2 (2018-03-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.1...2.5.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler konnten Artikel nicht korrekt nach Name sortiert werden. Dies wurde behoben.

## v2.5.1 (2018-03-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.0...2.5.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler konnte der Warenkorb bei Änderungen nicht aktualisiert werden. Dies wurde behoben.

## v2.5.0 (2018-03-19) <a href="https://github.com/plentymarkets/plugin-io/compare/2.4.0...2.5.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurden Context-Klassen hinzugefügt, die Daten für zugehörige Twig-Templates zur Verfügung stellen.
- Es wurden neue Service-Klassen hinzugefügt, um die Verwendung von ElasticSearch zu vereinfachen.

## v2.4.0 (2018-03-06) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.1...2.3.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurde eine neue Hilfsklasse hinzugefügt, um den Zugriff auf Plugin-Konfigurationen zu erleichtern.

### Geändert

- Für eine bessere Performance werden die globalen Services in Twig erst bei Bedarf instanziiert.

### Behoben

- Durch einen Fehler wurden leere Facetten in den Filtern ausgegeben und die Einstellung **Minimale Trefferanzahl** nicht berücksichtigt. Dies wurde behoben.

## v2.3.2 (2018-02-28) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.1...2.3.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Der Betreff der "Passwort vergessen" Email wird nun über den Rest-Call Parameter "subject" entgegengenommen und übersetzt versendet, sofern der Parameter ein gültiger Übersetzungsschlüssel ist.

## v2.3.1 (2018-02-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.0...2.3.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Gutscheinrabatte werden nun auf der Auftragsbestätigungsseite und in den Auftragsdetails im Mein-Konto-Bereich angezeigt.
- Die Retourenbestätigungsseite wird nun nach Anlage einer Retoure wieder angezeigt. Die Route muss in der IO config aktiviert sein.
- Auf der Seite zur Retourenanlage werden nur noch Artikel angezeigt die auch retourniert werden können.
- Durch einen Fehler wurden einzelne Attribute in der Variantenauswahl nicht angezeigt. Dies wurde behoben.
- Durch einen Fehler wurde die Anzeige von Brutto- und Nettopreisen für Versandkosten nicht korrekt aktualisiert. Dies wurde behoben.
- Bei Fehlern in der Versandkostenberechnung wurde keine Fehlermeldung ausgegeben. Dies wurde behoben.
- In der Liste der zuletzt gesehenen Artikel werden nun keine zufälligen Artikel mehr angezeigt, wenn vorher noch kein Artikel im Shop angeschaut wurde.

## v2.3.0 (2018-02-19) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.2...2.3.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Geändert

- Der Filter `itemName` kann nun anhand der Ceres-Konfiguration ebenfalls den Variantennamen oder eine Kombination aus Artikelnamen und Variantennamen anzeigen.

### Behoben

- Durch einen Fehler wurden Artikel-URLs nicht korrekt generiert. Dies wurde behoben.


## v2.2.2 (2018-02-12) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.1...2.2.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Ein Fehler führte dazu, dass gelegentlich in der Artikelansicht eine 404-Seite angezeigt wurde, wenn man diese ohne Varianten-ID in der URL aufrief. Dies wurde behoben, indem auch in der Artikelansicht die Einstellung **Show variations by type** berücksichtigt wird.

## v2.2.1 (2018-02-07) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.0...2.2.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Geändert

- Die Sortierung der Suchergebnisse wurde verbessert.
- Die Liste der aktiven Sprachen wird nun nicht mehr aus dem `WebstoreConfigurationRepositoryContract` geladen, sondern aus der Konfiguration des zugehörigen Template-Plugins.

### Behoben

- Durch einen Fehler wurden die Preise von Cross-Selling-Artikeln nicht berechnet. Dies wurde behoben.

## v2.2.0 (2018-02-05) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.5...2.2.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt
- `IO.Resources.Import` kann jetzt Parameter entgegennehmen. Beispielsweise können so beim Generieren und Einbinden eines Skripts eigene Werte aus der Plugin-Konfiguration übergeben werden, die beim Rendern des Skripts berücksichtigt werden.
- Inhalte von **.properties**-Dateien können jetzt geladen werden.

### Behoben

- Durch einen Fehler wurde die Fehlerseite mit HTTP-Statuscode 200 ausgegeben. Dies wurde behoben.
- Durch einen Fehler wurde die Relevanz eines Artikels bei der Artikelsuche und -sortierung nicht richtig berücksichtigt. Dies wurde behoben.

## v2.1.5 (2018-02-02) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.4...2.1.5" target="_blank"><b>Übersicht aller Änderungen</b></a>

- Ein Fehler führte dazu, dass die Paginierung bei verwendeter Einstellung **Show variations by type: Dynamically** nicht korrekt dargestellt wurde. Dies wurde behoben.
- Durch einen Fehler wurden Artikeldaten nicht einheitlich ausgeliefert. Dieser wurde behoben.
- Durch einen Fehler wurden Aufpreise für Bestellmerkmale nicht richtig berechnet. Dies wurde behoben.

### Behoben

## v2.1.4 (2018-01-29) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.3...2.1.4" target="_blank"><b>Übersicht aller Änderungen</b></a>

- Ein Fehler führte dazu, dass URLs ohne den Parameter **Varianten-ID** nicht korrekt ausgegeben wurden. Dies wurde behoben.

## v2.1.3 (2018-01-23) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.2...2.1.3" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler wurde die 404-Seite nicht korrekt dargestellt. Dies wurde behoben.
- Durch einen Fehler wurden unnötige Artikelabfragen durchgeführt. Dies wurde behoben.

## v2.1.2 (2018-01-22) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.1...2.1.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurde eine Sicherheitsabfrage hinzugefügt, die verhindert, dass Artikel mehrfach retourniert werden können.

### Behoben

- Ein Fehler führte dazu, dass zu viele Artikel in der Wunschliste vorhanden sind. Dies wurde behoben.

## v2.1.1 (2018-01-09) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.0...2.1.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Bei einer Gastbestellung wird nun nach dem Abschließen des Auftrags die Adresse aus der Session entfernt.
- Ein Fehler führte dazu, dass falsche Artikel-URLs generiert wurden, wenn im Webshop nur eine Sprache aktiv war. Dies wurde behoben.

## v2.1.0 (2018-01-04) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.3...2.1.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- URLs für Artikel und Kategorien können nun sprachabhängig generiert werden.

### Behoben

- Ein Fehler führte dazu, dass für neue Artikel keine lesbaren URLs erzeugt wurden. Dies wurde behoben.

## v2.0.3 (2017-12-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.2...2.0.3" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Übersetzbare Fehlermeldung bei der Registrierung für den Fall, dass die E-Mail-Adresse bereits existiert.
- Neue Gruppierungsmöglichkeit für Artikellisten.


### Geändert

- Die Logik zum Laden der Facetten wurde überarbeitet, sodass alle Facetten unabhängig von der Artikelgruppierung zurückgeliefert werden.

### Behoben

- Fehler behoben, bei dem die Lieferadresse nicht zurück auf "Lieferadresse gleich Rechnungsadresse" gesetzt werden konnte.
- Fehler behoben, durch den Artikel trotz Verknüpfung mit Kundenklassen sichtbar waren.


## v2.0.2 (2017-12-13) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.1...2.0.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Beim Laden von Zahlungsarten wird nun zusätzlich das Flag `isSelectable` mitgegeben.

### Behoben

- Auftragsherkünfte werden nun beim Laden von Artikeln und bei der Preisberechnung berücksichtigt.
- Diverse Fehler beim Behandeln von Gutschein-Codes wurden behoben.

## v2.0.1 (2017-12-06) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.0...2.0.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler wurde die Standard-Startseite nicht richtig angezeigt. Dies wurde behoben.

## v2.0.0 (2017-11-30) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.2...2.0.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Über die Twig-Funktionen `get_additional_styles()` und `get_additional_scripts()` können Skripte und Styles von externen Plugins angefordert werden, um diese an geeigneter Stelle auszugeben.
- Neue REST-Route `io/checkout/paymentId` zum Setzen der Zahlungsart wurde hinzugefügt.
- Neue REST-Route `io/checkout/shippingId` zum Setzen der Versandart wurde hinzugefügt.
- Ein **Account** wird in plentymarkets angelegt, wenn sich ein B2B-Kunde im Webshop registriert.
- Eine Middleware wurde hinzugefügt, um auf den Wechsel der Währung im Shop reagieren zu können.
- Preise werden nun umgerechnet, wenn die Währung geändert wird.
- Logik für die Berechnung der Auftragssummen (vorher wurde dies über ein Twig-Macro in Ceres realisiert).
- Zahlungsarten können nun auch bei Gastbestellungen auf der Auftragsbestätigungsseite geändert werden.
- Aufträge können nun auch bei Gastbestellungen nachträglich bezahlt werden, z.B. wenn die Zahlungsart geändert wird.
- Eine Fehlermeldung wurde hinzugefügt, die angezeigt wird, wenn beim Hinzufügen von Artikeln zum Warenkorb Fehler auftreten.

### Behoben

- Durch einen Fehler konnte der **Mein Konto**-Bereich nicht geladen werden, wenn die Aufträge eines Kunden geladen wurden. Dies wurde behoben.
- Durch einen Fehler war die Route für die Wunschliste `wishlist` nicht aktiv, obwohl die Route in der Konfiguration aktiviert wurde. Dies wurde behoben.
- Durch einen Fehler wurdnen Preise mit verschiedenen Mehrwertsteuersätzen nicht korrekt angezeigt. Dies wurde behoben.
- Nach dem Logout werden nun entsprechende Events ausgelöst, damit z.B. der Warenkorb auf die neuen Gegebenheiten reagieren kann.
- Ein Auftrag, für den keine Retoure möglich ist, kann nun nicht mehr bei direktem Aufruf der Route `/returns` aufgerufen werden.

## v1.7.2 (2017-11-22) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.1...1.7.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Durch einen Fehler wurden in der Detailansicht eines Auftrags oder auf der Auftragsbestätigungsseite die Versandkosten nicht korrekt angezeigt. Dies wurde behoben.
- Durch einen Fehler wurden zusätzliche Artikeldaten im Warenkorb nicht geladen, wenn sich mehr als 10 verschiedene Artikel im Warenkorb befanden. Dies wurde behoben.

## v1.7.1 (2017-11-17) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.0...1.7.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Die Position von Verkauspreisen wird jetzt berücksichtigt, um sicherzustellen, dass Preise im Webshop richtig dargestellt werden.
- Die Mindestbestellmenge, die an einer Kundenklasse hinterlegt ist, wird jetzt berücksichtigt.
- Varianten, die für die aktuelle Kundenklasse eines Kunden nicht verknüpft sind, werden in der Variantenauswahl der Artikelansicht ausgeblendet.

## v1.7.0 (2017-11-08) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.2...1.7.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Kundenklassen werden nun bei der Anzeige von Artikeldaten berücksichtigt.
- Plugins können nun die Artikelsortierung des Webshops durch eigene Einträge erweitern. Weitere Informationen dazu unter <a href="https://developers.plentymarkets.com/dev-doc/cookbook#item-sorting" target="_blank">plentyDevelopers</a>.

### Behoben

- Die Einstellung an der Variante für die Grundpreisangabe **Grundpreis anzeigen** wird nun berücksichtigt. Wenn diese Einstellung deaktiviert ist, wird der Grundpreis im Webshop nicht angezeigt.

## v1.6.2 (2017-10-25) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.1...1.6.2" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Adressen können als Packstationen oder Postfilialen gespeichert werden.
- Im Customer Service wurde die Funktion `hasReturns` hinzugefügt, um anzuzeigen, ob der Kunde Retouren hat.

## v1.6.1 (2017-10-19) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.0...1.6.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Geändert

- Die Einstellung **Retoure aktivieren** erfolgt nun über die Konfiguration des Plugins **Ceres**.

### Behoben

- Ein Fehler führte dazu, dass die Auftragsübersicht nicht geladen werden konnte, wenn ein Auftrag mit einem alten Versandprofil vorhanden war. Dies wurde behoben.


## v1.6.0 (2017-10-16) <a href="https://github.com/plentymarkets/plugin-io/compare/1.5.1...1.6.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Staffelpreise wurden integriert.

#### Behoben

- Durch einen Fehler wurde die falsche Zahlungsart am Auftrag gespeichert, wenn mit einer Zahlungsart mit Express-Checkout bezahlt wurde. Dies wurde behoben.
- Beim Aktualisieren einer Adresse wird nun auch das Event `FrontendCustomerAddressChanged`gefeuert.
- Beim Anlegen einer Retoure wird nun ein neues Datum erzeugt und nicht das Auftragsdatum für die Retoure verwendet.

## v1.5.1 (2017-10-05) <a href="https://github.com/plentymarkets/plugin-io/compare/1.5.0...1.5.1" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Die Kontaktformular-Route ist nun immer korrekt verfügbar, wenn sie in der IO-Konfiguration aktiviert wurde.

## v1.5.0 (2017-09-28) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.7...1.5.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Es wurde die Logik für die Abwicklung von Retouren hinzugefügt.
- Es wurde eine Methode im `RegisterController` hinzugefügt, um den Ceres-Checkout auch mit dem **Standard-Bestellvorgang** und dem **individuellen Warenkorb** von Callisto zu verwenden.

### Behoben

- Ein Fehler führte dazu, dass die Auftragsübersicht nicht geladen werden konnte, wenn ein Auftrag mit einer alten Zahlungsart vorhanden war. Dies wurde behoben.
- Durch einen sporadisch auftretenden Fehler wurde die Kaufabwicklung bei Gastbestellungen nicht aufgerufen. Dies wurde behoben.

## v1.4.7 (2017-09-13) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.6...1.4.7" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Behoben

- Ein Fehler führte dazu, dass die Grundpreisangabe nicht richtig funktionierte. Dies wurde behoben.
- Ein Fehler führte dazu, dass die Zahlungsart in der Kaufabwicklung manchmal nicht richtig ausgewählt wurde. Dies wurde behoben.
- Ein Fehler führte dazu, dass die Adressen in der Kaufabwicklung manchmal nicht richtig ausgewählt wurden. Dies wurde behoben.

## v1.4.6 (2017-09-13) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.5...1.4.6" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Die Suche nach Variantennummern wurde implementiert.
- Durch einen Fehler wurde der Webshop bei fehlender Datenbanktabelle für die Wunschliste nicht richtig geladen. Dies wurde behoben.

## v1.4.5 (2017-09-06)

### Behoben

- Durch einen Fehler wurde die Artikelanzahl in der Warenkorbvorschau manchmal nicht richtig anzeigt. Dies wurde behoben.

## v1.4.4 (2017-08-30)

### Hinzugefügt

- Eine Funktion wurde implementiert, um eine Mail zum Zurücksetzen des Kunden-Passworts zu versenden.
- Ein neues Passwort für den Kunden kann gespeichert werden.

### Behoben

- Die Variantenauswahl in der Einzelansicht eines Artikels zeigt nun auch die Attribute der Hauptvariante mit ein.

### TODO

- Die Route `password-reset` muss in IO aktiviert werden, um die Funktion **Passwort vergessen** in Ceres zu nutzen.

## v1.4.3 (2017-08-25)

### Entfernt

- Die ungenutzte Route `/guest` und der `GuestController` wurden entfernt.

## v1.4.2 (2017-08-23)

### Geändert

- Die Suche wurde optimiert so das nun auch Ergebnisse gefunden werden die nicht genau mit dem Suchstring übereinstimmen.

### Behoben

- Beim Aufruf der Auftragsübersichtsseite erscheint nun eine 404-Seite anstatt eines Twig-Fehlers, wenn die Session in der Zwischenzeit abgelaufen ist.

## v1.4.1 (2017-08-11)

### Hinzugefügt

- Der Kaufabwicklungslink aus der Auftragsübersicht im Backend kann jetzt interpretiert werden.
- Der `ContactMailService` kann nun auch einen Parameter entgegennehmen, um die Kontaktanfrage als Kopie an den Ersteller zu schicken.

### Behoben

- Aufgrund eines Fehlers wurden Preise für Cross-Selling-Artikel nicht angezeigt. Dies wurde behoben.
- Wenn der Link zur Kaufabwicklung ungültig ist, wird nun die 404-Seite angezeigt anstatt eines Twig-Fehlers.

## v1.4.0 (2017-08-09)

### Hinzugefügt

- Die Logik und die Route `/wish-list`, um eine Wunschliste im Webshop anzuzeigen, wurde hinzugefügt. **Hinweis:** Für die Migration der Datenbanktabelle muss der Standard-Mandant aktiviert sein und das Plugin bereitgestellt werden. Nach der Bereitstellung kann der Standard-Mandant deaktiviert werden.
- Die Logik und die Route `/contact`, um die Kontaktseite im Webshop anzuzeigen, wurde hinzugefügt.
- Der `ContactMailService` wurde hinzugefügt und steuert das Versenden von Kontaktanfragen über den Webshop.
- Im `BasketService` wurde eine Methode hinzugefügt, um die Anzahl der Artikel im Warenkorb auszugeben.
- Der `NotificationService` wurde erweitert, um Fehlermeldungen im Frontend richtig auszugeben.
- Der Link aus der Bestellbestätigung leitet nun auf die Bestellbestätigungsseite von Ceres weiter.

### Behoben

- Die Sprachauswahl im Header des Webshops zeigt nun wieder Sprachen an.

### Entfernt

- Im `ItemController` wurde die Logik für den Warenbestand entfernt. Dies wird nun über die `result fields` von ElasticSearch abgebildet.

## v1.3.2 (2017-07-26)

### Hinzugefügt

- Im `CustomerService` kann nun die Telefonnummer gespeichert werden.

### Behoben

- Die Performance der Bestellbestätigungsseite wurde verbessert.
- Die Artikelbilder in der Bestellbestätigungsseite werden nun korrekt ausgegeben.

## v1.3.1 (2017-07-21)

### Hinzugefügt

- Im `BasketService` und im `OrderItemBuilder` werden jetzt Bestellmerkmale vom Typ **Text** verarbeitet.
- Die Route `io/localization/language` wurde hinzugefügt, um die Sprache des Webshops zu setzen.

## v1.3.0 (2017-07-13)

### Hinzugefügt

- IO stellt nun Daten zu Tags und Cross-Selling für Artikellisten bereit.
- Templates können nun gecacht werden.
- Im `CustomerService` wurde das Speichern des akademischen Titels eingebaut.
- Ein neues Event `LocalizationChanged` wurde hinzugefügt.
- Es wurden Bedingungen für das Ändern der Zahlungsart im **Mein Konto**-Bereich hinzugefügt. In der Konfiguration von Ceres muss die Einstellung **Allow customer to change the payment method** aktiviert werden. Die Bestellung darf zudem noch nicht bezahlt sein. Der Status der Bestellung muss kleiner als 3.4 sein, bzw. wenn die Bestellung am gleichen Tag aufgegeben wurde, muss der Status gleich 5 oder kleiner als 3.4 sein.

### Geändert

- Die Suche wird nun mit einer **UND**-Logik ausgeführt und ersetzt damit die vorherige **ODER**-Suche.
- Im `CustomerService` wurde das Editieren zusätzlicher Adressfelder optimiert.

### Behoben

- Bei Artikeln werden nun nur noch die für den Mandanten aktivierten Bilder im Webshop angezeigt.

## v1.2.10 (2017-07-05)

### Hinzugefügt

- Im `CheckoutService` wurde die Methode `getCheckoutPaymentDataList` ergänzt, um die `sourceUrl` der Payment-Plugins zurückzugeben.
- Komplexe Sortierungen von Artikeln sind in Kategorieansicht und Suche über die Einstellung **Empfohlen** möglich.
- Das Ergebnis eines geladenen Artikel beinhaltet nun auch den formatierten Artikelpreis.

### Geändert

- Adressfelder, die in der Konfiguration von Ceres deaktiviert sind, für die aber die Validierung aktiviert ist, werden nun nicht mehr validiert.

## v1.2.9 (2017-06-30)

### Behoben

- Beim Klick auf **Zahlungsart ändern** in der Kaufabwicklung wurde die Liste der Zahlungsarten nicht übersetzt. Dies wurde behoben.
- Im `TemplateService` wurde die Methode `isCurrentTemplate` ergänzt, um dynamisch das aktuelle Template abzufragen.

## v1.2.8 (2017-06-29)

### Hinzugefügt

- Im **Mein Konto**-Bereich kann nun die Zahlungsart eines Auftrags geändert werden, wenn die Zahlungsart dies zulässt.

### Geändert

- Varianten ohne Bestand können nicht mehr in den Warenkorb gelegt werden.
- Varianten ohne Bestand werden beim Aufruf auf die nächste kaufbare Variante umgeleitet.

### Behoben

- Ein Fehler führte dazu, dass eine gelöschte Adresse nicht aus der Adressliste entfernt wurde. Dies wurde behoben.
- Ein Fehler führte dazu, dass eine Adresse bei einer Gastbestellung nicht bearbeitet werden konnte. Dies wurde behoben.

## v.1.2.7 (2017-06-21)

### Behoben

- Bei der Kundenregistrierung wird die eingegebene Adresse nicht mehr automatisch als Lieferadresse angelegt.
- In der Einzelansicht werden keine inaktiven Varianten und ohne Bestand im Dropdown angezeigt.

## v.1.2.6 (2017-06-14)

### Behoben

- Fehler beim Validieren von englischen Rechnungs- und Lieferadressen.

## v1.2.5 (2017-06-08)

### Hinzugefügt

- Lieferländer und Webshop-Einstellungen werden aus dem Cache geladen, um die Performance zu verbessern.

### Behoben

- Es wurde ein Fehler behoben, der dazu führte, dass manchmal keine Standardlieferland gesetzt wurde.

## v1.2.4 (2017-06-02)

### Hinzugefügt

- Es wurde ein Twig-Filter hinzugefügt, um Objekte anhand eines Keys zu sortieren.
- Validierung von Feldern des Adressformulars für das Lieferland **Vereinigtes Königreich**

## v1.2.3 (2017-05-19)

### Hinzugefügt

- Eingaben für Geburtsdatum und Umsatzsteuer-ID können jetzt an der Adresse gespeichert werden.
- Twig-Filter für Variantenbilder hinzugefügt.
- Konfigurationsmöglichkeit für zugehöriges Template-Plugin.
- Validierung von Adresseingaben anhand der Konfiguration des Template-Plugins.

### Behoben

- Artikel, für die keine Texte in der ausgewählten Shop-Sprache gespeichert wurden, werden nicht mehr zurückgeliefert.

## v1.2.2 (2017-05-11)

### Behoben

- Die Vorschläge der Autovervollständigung von Suchbegriffen berücksichtigen nun die Einstellung der Variantengruppierung.

## v1.2.1 (2017-05-08)

### Behoben

- Kleinere Bugfixes und Verbesserungen.

## v1.2.0 (2017-04-28)

### Behoben

- Registrierungen mit einer E-Mail-Adresse, für die bereits ein Konto existiert, sind nun nicht mehr möglich.
- Breadcrumbs funktionieren jetzt auch in der Einzelansicht eines Artikels.

## v1.1.1 (2017-04-24)

### Hinzugefügt

- Logik für die Artikelliste der zuletzt gesehenen Artikel

### Behoben

- Variantengruppierung in der Kategorieübersicht und Suchergebnisseite
- Sortierung nach Artikelnamen in der Kategorieübersicht und auf der Suchergebnisseite

## v1.1.0 (2017-04-12)

### Hinzugefügt

- TemplateService: `isCategoryView` Methode um zu überprüfen, ob die aktuelle Seite die Kategorie-Seite ist.
- Support für neue Kategorie-Logik in Ceres.

## v1.0.4 (2017-03-27)

### Behoben

- Fehler beim Aufruf der Auftragsbestätigungsseite

## v1.0.3 (2017-03-24)

### Hinzugefügt

- Filtermöglichkeit über Facetten
- Abruf von gerenderten Twig-Templates über REST
- Neue Twig-Funktionen: `trimNewLines` und `formatDateTime`
- Neue Funktion im **CategoryService**: `getChildren()`
um Unterkategorien zu erhalten

### Geändert

- Aktualisierung und Erweiterung des Routings: Alte Shop-URLs können verarbeitet und in **Ceres** angezeigt werden. Die URL-Struktur wurde zudem optimiert von `/{itemName}/{itemId}/{variationId}` zu `/{category}/{subcategory}/.../{itemName}-{itemId}-{variationId}`

## v1.0.2 (2017-03-06)

### Behoben

- Fehler beim Aufrufen der Kategorieansicht und der Artikel-Einzelansicht behoben.
- Fehler behoben, bei dem auch Artikel in einer Kategorie auftauchten, die mit dieser nicht verknüpft waren.
- Fehler behoben, bei welchem Routen von anderen Plugins von der 404-Route von IO überschrieben wurden.

## v1.0.1 (2017-02-22)

### Behoben

- Fehler beim Erweitern der Shop-Sprachen behoben. Wenn zusätzliche Sprachdateien im Ordner `resources/lang` [erstellt](https://developers.plentymarkets.com/dev-doc/template-plugins#design-lang) und per [Gulp kompiliert](https://developers.plentymarkets.com/dev-doc/template-plugins#gulp-ceres) wurden, wird das Template nun auch in der gewählten Sprache angezeigt.

## v1.0.0 (2017-02-20)

### Funktionen
**IO** bietet eine Vielzahl an Logikfunktionen für einen plentymarkets Webshop und dient als Schnittstelle zwischen plentymarkets und folgenden Webshop-Seiten:
- Startseite
- Kategorieansicht
- Artikelansicht
- Warenkorb
- Kasse (Checkout)
- Bestellbestätigung
- Login und Registrierung
- Gastbestellung
- **Mein Konto**-Bereich
- statische Seiten (z.B. AGB, Impressum etc.)

Außerdem bietet es eine Funktion um weiteren Content durch Layout-Container nachzuladen.
