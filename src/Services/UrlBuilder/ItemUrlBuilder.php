<?php

namespace IO\Services\UrlBuilder;

use IO\Services\ItemLoader\Loaders\ItemURLs;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\SessionStorageService;

class ItemUrlBuilder
{
    public function buildUrl(int $itemId, string $lang = null )
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        $variationId = 0;
        if ( count( VariationUrlBuilder::$urlPathMap[$itemId] ) )
        {
            $variationIds = array_keys( VariationUrlBuilder::$urlPathMap[$itemId] );
            $variationId = $variationIds[0];
        }
        else
        {
            $variationId = $this->searchItemVariationId( $itemId, $lang );
        }

        if ( $variationId > 0 )
        {
            /** @var VariationUrlBuilder $variationUrlBuilder */
            $variationUrlBuilder = pluginApp( VariationUrlBuilder::class );
            return $variationUrlBuilder->buildUrl( $itemId, $variationId, $lang );
        }

        return pluginApp( UrlQuery::class, ['path' => $itemId, 'lang' => $lang]);
    }

    private function searchItemVariationId( $itemId, $lang )
    {
        /** @var ItemLoaderService $itemLoader */
        $itemLoader = pluginApp( ItemLoaderService::class );
        $result = $itemLoader
            ->setLoaderClassList([ItemURLs::class])
            ->setOptions([
                'itemId' => $itemId,
                'lang' => $lang
            ])
            ->load();

        if ( count($result['documents']) )
        {
            VariationUrlBuilder::fillItemUrl( $result['documents'][0]['data'] );
            return $result['documents'][0]['data']['variation']['id'];
        }

        return 0;
    }
}