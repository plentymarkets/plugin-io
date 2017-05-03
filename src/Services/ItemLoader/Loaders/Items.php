<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Services\SessionStorageService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;

/**
 * Class Items
 * @package IO\Services\ItemLoader\Loaders
 */
class Items implements ItemLoaderContract
{
    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [pluginApp(SessionStorageService::class)->getLang()]]);
        
        $documentProcessor = pluginApp(DocumentProcessor::class);
        $documentProcessor->addMutator($languageMutator);
        
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
        
        if(isset($options['itemIds']) && count($options['itemIds']))
        {
            $variationFilter->hasItemIds($options['itemIds']);
        }
        
        if(isset($options['variationIds']) && count($options['variationIds']))
        {
            $variationFilter->hasIds($options['variationIds']);
        }
        
        return [
            $clientFilter,
            $variationFilter
        ];
    }
}