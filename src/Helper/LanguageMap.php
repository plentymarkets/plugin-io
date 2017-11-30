<?php

namespace IO\Helper;

use IO\Services\SessionStorageService;

class LanguageMap
{
    private static $language_map = array(
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

    public static function getLocale(): string
    {
        $lang = pluginApp(SessionStorageService::class)->getLang();
        if ( array_key_exists( $lang, LanguageMap::$language_map ) )
        {
            return LanguageMap::$language_map[$lang];
        }

        return $lang . '_' . strtoupper( $lang );
    }


}