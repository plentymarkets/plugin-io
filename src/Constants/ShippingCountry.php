<?php
//strict

namespace IO\Constants;

/**
 * Class ShippingCountry
 *
 * Collection of shipping country id constants
 *
 * @package IO\Constants
 */
class ShippingCountry
{
    /**
     * @var string Identifier for the shipping country id of Germany.
     */
    const GERMANY = 1;

    /**
     * @var string Identifier for the shipping country id of Austria.
     */
    const AUSTRIA = 2;

    /**
     * @var string Identifier for the shipping country id of Belgium.
     */
    const BELGIUM = 3;

    /**
     * @var string Identifier for the shipping country id of Switzerland.
     */
    const SWITZERLAND = 4;

    /**
     * @var string Identifier for the shipping country id of Cyprus.
     */
    const CYPRUS = 5;

    /**
     * @var string Identifier for the shipping country id of Czech Republic.
     */
    const CZECH_REPUBLIC = 6;

    /**
     * @var string Identifier for the shipping country id of Denmark.
     */
    const DENMARK = 7;

    /**
     * @var string Identifier for the shipping country id of Spain.
     */
    const SPAIN = 8;

    /**
     * @var string Identifier for the shipping country id of Estonia.
     */
    const ESTONIA = 9;

    /**
     * @var string Identifier for the shipping country id of France.
     */
    const FRANCE = 10;

    /**
     * @var string Identifier for the shipping country id of Finland.
     */
    const FINLAND = 11;

    /**
     * @var string Identifier for the shipping country id of United Kingdom.
     */
    const UNITED_KINGDOM = 12;

    /**
     * @var string Identifier for the shipping country id of Greece.
     */
    const GREECE = 13;

    /**
     * @var string Identifier for the shipping country id of Hungary.
     */
    const HUNGARY = 14;

    /**
     * @var string Identifier for the shipping country id of Italy.
     */
    const ITALY = 15;

    /**
     * @var string Identifier for the shipping country id of Ireland.
     */
    const IRELAND = 16;

    /**
     * @var string Identifier for the shipping country id of Luxembourg.
     */
    const LUXEMBOURG = 17;

    /**
     * @var string Identifier for the shipping country id of Latvia.
     */
    const LATVIA = 18;

    /**
     * @var string Identifier for the shipping country id of Malta.
     */
    const MALTA = 19;

    /**
     * @var string Identifier for the shipping country id of Norway.
     */
    const NORWAY = 20;

    /**
     * @var string Identifier for the shipping country id of Netherlands.
     */
    const NETHERLANDS = 21;

    /**
     * @var string Identifier for the shipping country id of Portugal.
     */
    const PORTUGAL = 22;

    /**
     * @var string Identifier for the shipping country id of Poland.
     */
    const POLAND = 23;

    /**
     * @var string Identifier for the shipping country id of Sweden.
     */
    const SWEDEN = 24;

    /**
     * @var string Identifier for the shipping country id of Singapore.
     */
    const SINGAPORE = 25;

    /**
     * @var string Identifier for the address format in Germany.
     */
    const ADDRESS_FORMAT_DE = 'DE';

    /**
     * @var string Identifier for the address format in United Kingdom.
     */
    const ADDRESS_FORMAT_EN = 'EN';

    /**
     * Get the correct address format for a specific country id.
     *
     * @param $countryId The country id to determine the correct address format.
     * @return string
     */
    public static function getAddressFormat($countryId)
    {
        switch ($countryId) {
            case self::UNITED_KINGDOM:
                return self::ADDRESS_FORMAT_EN;
        }

        return self::ADDRESS_FORMAT_DE;
    }
}
