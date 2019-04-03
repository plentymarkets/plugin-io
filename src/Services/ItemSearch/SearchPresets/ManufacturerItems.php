<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Helper\SortingHelper;

class ManufacturerItems implements SearchPreset
{
    public static function getSearchFactory($options)
    {
        $page = 1;
        if ( array_key_exists( 'page', $options ) )
        {
            $page = (int) $options['page'];
        }
       
        $sorting = '';
        if ( array_key_exists( 'sorting', $options ) )
        {
            $sorting =  $options['sorting'];
        }

        $itemsPerPage = 20;
        if ( array_key_exists( 'itemsPerPage', $options ) )
        {
            $itemsPerPage = (int) $options['itemsPerPage'];
        }

        $manufacturerId = 1;
        if (array_key_exists( 'manufacturerId', $options))
        {
            $manufacturerId = (int) $options['manufacturerId'];
        }


        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );

        $searchFactory
            ->withLanguage()
            ->withImages()
            ->withUrls()
            ->withPrices()
            ->withDefaultImage()
            ->isVisibleForClient()
            ->isActive()
            ->isHiddenInCategoryList( false )
            ->hasNameInLanguage()
            ->hasPriceForCustomer()
            ->sortByMultiple( $sorting )
            ->hasManufacturer( $manufacturerId )
            ->setPage( $page, $itemsPerPage )
            ->groupByTemplateConfig()
            ->withLinkToContent()
            ->withReducedResults();

        return $searchFactory;
    }
}

