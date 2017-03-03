<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;

class BasketItems implements ItemLoaderContract
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
        return [];
    }
    
    /**
     * @param array $options
     *
     * @return TypeInterface[]
     */
    public function getFilterStack($options = [])
    {
        /** @var ClientFilter $clientFilter */
        $clientFilter = pluginApp(ClientFilter::class);
        $clientFilter->isVisibleForClient(pluginApp(Application::class)->getPlentyId());
        
        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = pluginApp(VariationBaseFilter::class);
        $variationFilter->isActive();
        
        if(array_key_exists('variationIds', $options) && count($options['variationIds']))
        {
            $variationFilter->hasIds($options['variationIds']);
        }
        
        return [
            $clientFilter,
            $variationFilter
        ];
    }
}