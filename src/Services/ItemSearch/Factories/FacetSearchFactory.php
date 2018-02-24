<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemSearch\Extensions\FacetFilterExtension;
use IO\Services\SessionStorageService;
use Plenty\Modules\Item\Search\Helper\SearchHelper;
use Plenty\Plugin\Application;

class FacetSearchFactory extends VariationSearchFactory
{
    private $facetValues = [];
    public static function create( $facets )
    {
        /** @var FacetSearchFactory $instance */
        $instance = pluginApp( FacetSearchFactory::class );
        if ( is_string( $facets ) )
        {
            $instance->facetValues = explode(",", $facets );
        }
        else
        {
            $instance->facetValues = $facets;
        }

        return $instance;
    }

    public function prepareSearch()
    {
        $plentyId   = pluginApp( Application::class )->getPlentyId();
        $lang       = pluginApp( SessionStorageService::class )->getLang();

        /** @var SearchHelper $searchHelper */
        $searchHelper = pluginApp( SearchHelper::class, [$this->facetValues, $plentyId, 'item', $lang]);
        return $searchHelper->getFacetSearch();
    }

    public function withMinimumCount()
    {
        return $this->withExtension( FacetFilterExtension::class );
    }
}