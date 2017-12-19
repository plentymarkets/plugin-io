<?php

namespace IO\Services\UrlBuilder;

use IO\Services\SessionStorageService;

class ItemUrlBuilder
{
    public function buildUrl(int $itemId, string $lang = null )
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
            $variationId = 0;
            if ( count( Variation::$urlPathMap[$itemId] ) )
            {
                $variationIds = array_keys( Variation::$urlPathMap[$itemId] );
                $variationId = $variationIds[0];
            }
        }
    }
}