<?php

namespace IO\Helper;

use IO\Services\SessionStorageService;

class LanguageMap
{
    private static $locales = array(
        'de' => 'de_DE',
        'en' => 'en_GB',
        'bg' => 'bg_BG',
        'fr' => 'fr_FR',
        'it' => 'it_IT',
        'es' => 'es_ES',
        'tr' => 'tr_TR',
        'nl' => 'nl_NL',
        'pl' => 'pl_PL',
        'pt' => 'pt_PT',
        'nn' => 'en_GB', // ??
        'da' => 'en_GB', // ??
        'se' => 'en_GB', // ??
        'cz' => 'cs_CZ',
        'ro' => 'ro_RO',
        'ru' => 'ru_RU',
        'sk' => 'sk_SK',
        'cn' => 'zh_CN',
        'vn' => 'vi_VN'
    );

    private static $languages =  array(
        'de' => 'de',
        'en' => 'en',
        'bg' => 'bg',
        'fr' => 'fr',
        'it' => 'it',
        'es' => 'es',
        'tr' => 'tr',
        'nl' => 'nl',
        'pl' => 'pl',
        'pt' => 'pt',
        'nn' => 'no',
        'ro' => 'ro',
        'da' => 'da',
        'se' => 'sv',
        'cz' => 'cs',
        'ru' => 'ru',
        'sk' => 'sk',
        'cn' => 'zh',
        'vn' => 'vi'
    );

    public static function getLocale(): string
    {
        $lang = Utils::getLang();
        if ( array_key_exists( $lang, LanguageMap::$locales ) )
        {
            return LanguageMap::$locales[$lang];
        }

        return $lang . '_' . strtoupper( $lang );
    }

    public static function getLanguageCode($countryCode = null)
    {
        if ( is_null( $countryCode ) )
        {
            $countryCode = Utils::getLang();
        }

        return LanguageMap::$languages[$countryCode];
    }

    public static function getCountryCode($language = null)
    {
        if ( is_null( $language ) )
        {
            return Utils::getLang();
        }

        foreach( LanguageMap::$languages as $countryCode => $languageCode )
        {
            if ( $languageCode === $language )
            {
                return $countryCode;
            }
        }

        return Utils::getLang();
    }
}