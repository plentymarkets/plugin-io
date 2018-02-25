<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemSearch\Extensions\FacetFilterExtension;
use IO\Services\SessionStorageService;
use Plenty\Modules\Item\Search\Helper\SearchHelper;
use Plenty\Plugin\Application;

/**
 * Class FacetSearchFactory
 *
 * Concrete factory to build facet searches.
 *
 * @package IO\Services\ItemSearch\Factories
 */
class FacetSearchFactory extends VariationSearchFactory
{
    private $facetValues = [];

    /**
     * Create a factory instance depending on a given set of facet values.
     * @param string|array      $facets     List of active facet values. If string is given, it will be exploded by ',' to a list of values.
     *
     * @return FacetSearchFactory
     */
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

    /**
     * Build facet search classes
     *
     * @inheritdoc
     */
    protected function prepareSearch()
    {
        $plentyId   = pluginApp( Application::class )->getPlentyId();
        $lang       = pluginApp( SessionStorageService::class )->getLang();

        /** @var SearchHelper $searchHelper */
        $searchHelper = pluginApp( SearchHelper::class, [$this->facetValues, $plentyId, 'item', $lang]);
        return $searchHelper->getFacetSearch();
    }

    /**
     * Register extension to filter facets by minimum hit count.
     *
     * @return $this
     */
    public function withMinimumCount()
    {
        return $this->withExtension( FacetFilterExtension::class );
    }
}