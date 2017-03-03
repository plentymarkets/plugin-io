<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Builder\Facet\FacetBuilder;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Item\Search\Aggregations\FacetAggregation;
use Plenty\Modules\Item\Search\Aggregations\FacetAggregationProcessor;
use Plenty\Modules\Item\Search\Filter\FacetFilter;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;

/**
 * Created by ptopczewski, 06.01.17 14:44
 * Class SingleItemAttributes
 * @package IO\Services\ItemLoader\Loaders
 */
class Facets implements ItemLoaderContract
{
    
    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        $documentProcessor = pluginApp(DocumentProcessor::class);
        return pluginApp(DocumentSearch::class, [$documentProcessor]);
    }
    
    /**
     * @return array
     */
    public function getAggregations()
    {
        $facetProcessor = pluginApp(FacetAggregationProcessor::class);
        $facetSearch = pluginApp(FacetAggregation::class, [$facetProcessor]);
        
        return [
            $facetSearch
        ];
    }
    
    /**
     * @param array $options
     * @return TypeInterface[]
     */
    public function getFilterStack($options = [])
    {
        $facetValues = [];
        
        if(array_key_exists('facets', $options) && count($options['facets']))
        {
            $facetValues = FacetBuilder::buildFacetValues($options['facets']);
        }
        else
        {
            /**
             * @var Request $request
             */
            $request = pluginApp(Request::class);
            $facetValues = FacetBuilder::buildFacetValues($request->get('facets', ''));
        }
        
        if(count($facetValues))
        {
            /**
             * @var FacetFilter $facetFilter
             */
            $facetFilter = pluginApp(FacetFilter::class);
            $facetFilter->hasEachFacet($facetValues);
            
            return [
                $facetFilter
            ];
        }
        
        return [];
    }
}