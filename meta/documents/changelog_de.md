# Release Notes für IO

## v1.4.2 (2017-08-16)

### Behoben

- Beim Aufruf der Auftragsübersichtsseite erscheint nun eine 404 Seite anstatt eines Twig Fehlers wenn die Session in der Zwischenzeit abgelaufen ist.

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
