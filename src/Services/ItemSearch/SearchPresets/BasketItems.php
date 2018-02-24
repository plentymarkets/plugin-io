<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;

class BasketItems implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        $variationIds   = $options['variationIds'];
        $quantities     = $options['variationQuantities'];

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory
            ->withLanguage()
            ->withUrls()
            ->withImages()
            ->withPrices(['quantities' => $quantities])
            ->isVisibleForClient()
            ->isActive()
            ->hasVariationIds( $variationIds )
            ->setPage( 1, count( $variationIds ) );

        return $searchFactory;
    }
}