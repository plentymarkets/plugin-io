<?php
//strict

namespace IO\Constants;

/**
 * Class ShippingCountry
 *
 * Collection of shipping country ID constants
 *
 * @package IO\Constants
 */
class ShippingCountry
{
    /**
     * @var string Identifier for the shipping country ID of Germany.
     */
    const GERMANY = 1;

    /**
     * @var string Identifier for the shipping country ID of Austria.
     */
    const AUSTRIA = 2;

    /**
     * @var string Identifier for the shipping country ID of Belgium.
     */
    const BELGIUM = 3;

    /**
     * @var string Identifier for the shipping country ID of Switzerland.
     */
    const SWITZERLAND = 4;

    /**
     * @var string Identifier for the shipping country ID of Cyprus.
     */
    const CYPRUS = 5;

    /**
     * @var string Identifier for the shipping country ID of Czech Republic.
     */
    const CZECH_REPUBLIC = 6;

    /**
     * @var string Identifier for the shipping country ID of Denmark.
     */
    const DENMARK = 7;

    /**
     * @var string Identifier for the shipping country ID of Spain.
     */
    const SPAIN = 8;

    /**
     * @var string Identifier for the shipping country ID of Estonia.
     */
    const ESTONIA = 9;

    /**
     * @var string Identifier for the shipping country ID of France.
     */
    const FRANCE = 10;

    /**
     * @var string Identifier for the shipping country ID of Finland.
     */
    const FINLAND = 11;

    /**
     * @var string Identifier for the shipping country ID of United Kingdom.
     */
    const UNITED_KINGDOM = 12;

    /**
     * @var string Identifier for the shipping country ID of Greece.
     */
    const GREECE = 13;

    /**
     * @var string Identifier for the shipping country ID of Hungary.
     */
    const HUNGARY = 14;

    /**
     * @var string Identifier for the shipping country ID of Italy.
     */
    const ITALY = 15;

    /**
     * @var string Identifier for the shipping country ID of Ireland.
     */
    const IRELAND = 16;

    /**
     * @var string Identifier for the shipping country ID of Luxembourg.
     */
    const LUXEMBOURG = 17;

    /**
     * @var string Identifier for the shipping country ID of Latvia.
     */
    const LATVIA = 18;

    /**
     * @var string Identifier for the shipping country ID of Malta.
     */
    const MALTA = 19;

    /**
     * @var string Identifier for the shipping country ID of Norway.
     */
    const NORWAY = 20;

    /**
     * @var string Identifier for the shipping country ID of Netherlands.
     */
    const NETHERLANDS = 21;

    /**
     * @var string Identifier for the shipping country ID of Portugal.
     */
    const PORTUGAL = 22;

    /**
     * @var string Identifier for the shipping country ID of Poland.
     */
    const POLAND = 23;

    /**
     * @var string Identifier for the shipping country ID of Sweden.
     */
    const SWEDEN = 24;

    /**
     * @var string Identifier for the shipping country ID of Singapore.
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
     * Get the correct address format for a specific country ID.
     *
     * @param $countryId The country ID to determine the correct address format.
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
