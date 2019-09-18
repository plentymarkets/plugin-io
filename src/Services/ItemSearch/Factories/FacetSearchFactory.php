<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Helper\Utils;
use IO\Services\ItemSearch\Extensions\FacetFilterExtension;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Item\Search\Helper\SearchHelper;

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
        if ( is_array( $facets ) )
        {
            $instance->facetValues = (array)$facets;
        }
        else
        {
            $instance->facetValues = explode(",", (string)$facets );
        }

        return $instance;
    }

    /**
     * Build facet search classes
     *
     * @param IncludeSource $source
     * @return DocumentSearch
     */
    protected function prepareSearch($source)
    {
        $plentyId   = Utils::getPlentyId();
        $lang       = Utils::getLang();

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
