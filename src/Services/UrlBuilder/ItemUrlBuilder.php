<?php

namespace IO\Services\UrlBuilder;

use IO\Contracts\VariationSearchFactoryContract as VariationSearchFactory;
use IO\Services\ItemSearch\Services\ItemSearchService;
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
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory
            ->withLanguage( $lang )
            ->withUrls()
            ->hasItemId( $itemId );

        $itemSearchService->getResults([$searchFactory])[0];

        return 0;
    }
}
