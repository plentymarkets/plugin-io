# Release Notes für IO

## v2.7.0 (2018-04-13) <a href="https://github.com/plentymarkets/plugin-io/compare/2.6.0...2.7.0" target="_blank"><b>Übersicht aller Änderungen</b></a>

### Hinzugefügt

- Bei Artikeln ohne Bilder wird jetzt das konfigurierte Platzhalter-Bild im Webshop dargestellt.
- Bestellmerkmale vom Typ **Datei** können jetzt verarbeitet werden.

### Behoben

- Durch einen Fehler wurden im Warenkorb keine Staffelpreise angezeigt. Dies wurde behoben.
- Bei Retouren wurde der Sperr-Status nicht vom ursprünglichen Auftrag übernommen. Dies wurde behoben.
- Durch einen Fehler wurden die Daten beim Speichern und Editieren von Adressen nicht serverseitig validiert. Dies wurde behoben.
- Durch einen Fehler wurden in der Bestellbestätigung der Auftragsstatus, der Versanddienstleister und die Zahlungsart immer in der Systemsprache angezeigt. Dies wurde behoben.
- Durch einen Fehler wurde die Rabattstaffel auf den Netto-Warenwert der Kundenklasse bei der Auftragsanlage nicht berücksichtigt. Dies wurde behoben.

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
