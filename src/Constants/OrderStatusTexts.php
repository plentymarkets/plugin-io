<?php //strict

namespace LayoutCore\Constants;

/**
 * Class OrderStatusTexts
 * @package LayoutCore\Constants
 */
class OrderStatusTexts
{
    /**
     * @var array
     */
    public static $orderStatusTexts = [
        '1'    =>   '[1] Unvollständige Daten',
        '1.1'  =>   '[1.1] Warten auf Zahlung & Freischaltung',
        '1.2'  =>   '[1.2] Freigeschaltet, warten auf Zahlung',
        '2'    =>   '[2] Warten auf Freischaltung',
        '3'    =>   '[3] Warten auf Zahlung',
        '3.1'  =>   '[3.1] Start PayPal-Zahlungsprozess',
        '3.2'  =>   '[3.2] In Warteposition',
        '3.3'  =>   '[3.3] Versandfertig; warten auf Zahlung',
        '3.4'  =>   '[3.4] Mahnung versendet',
        '4'    =>   '[4] In Versandvorbereitung',
        '5'    =>   '[5] Freigabe Versand',
        '5.1'  =>   '[5.1] Abwicklung extern',
        '5.2'  =>   '[5.2] Bereit zur Abholung',
        '6'    =>   '[6] Gerade im Versand',
        '6.1'  =>   '[6.1] Gerade im Versand GLS',
        '6.2'  =>   '[6.2] Gerade im Versand Hermes',
        '6.3'  =>   '[6.3] Gerade im Versand DHL UK',
        '6.9'  =>   '[6.9] Teillieferung',
        '7'    =>   '[7] Warenausgang gebucht',
        '7.1'  =>   '[7.1] Auftrag exportiert',
        '8'    =>   '[8] Storniert',
        '8.1'  =>   '[8.1] Storniert durch Kunden',
        '9'    =>   '[9] Retoure',
        '9.1'  =>   '[9.1] Ware wird geprüft',
        '9.2'  =>   '[9.2] Warten auf Retoure von Großhändler',
        '9.3'  =>   '[9.3] Gewährleistung eingeleitet',
        '9.4'  =>   '[9.4] Umtausch eingeleitet',
        '9.5'  =>   '[9.5] Gutschrift angelegt',
        '10'   =>   '[10] Gewährleistung',
        '11'   =>   '[11] Gutschrift',
        '11.1' =>   '[11.1] Gutschrift ausgezahlt',
        '12'   =>   '[12] Reparatur',
        '13'   =>   '[13] Sammelauftrag',
        '14'   =>   '[14] Sammelgutschrift'
    ];
}

