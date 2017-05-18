<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Services\SessionStorageService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Plugin\Application;

/**
 * Created by ptopczewski, 06.01.17 14:44
 * Class SingleItem
 * @package IO\Services\ItemLoader\Loaders
 */
class SingleItem implements ItemLoaderContract
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

		if(array_key_exists('itemId', $options) && $options['itemId'] != 0)
		{
			$variationFilter->hasItemId($options['itemId']);
		}

		if(array_key_exists('variationId', $options) && $options['variationId'] != 0)
		{
			$variationFilter->hasId($options['variationId']);
		}
        
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $usedItemName = $templateConfigService->get('item.name');
        
        $textFilterType = TextFilter::FILTER_ANY_NAME;
        if(strlen($usedItemName))
        {
            if($usedItemName == 'name1')
            {
                $textFilterType = TextFilter::FILTER_NAME_1;
            }
            elseif($usedItemName == 'name2')
            {
                $textFilterType = TextFilter::FILTER_NAME_2;
            }
            elseif($usedItemName == 'name3')
            {
                $textFilterType = TextFilter::FILTER_NAME_3;
            }
        }
        
        /**
         * @var TextFilter $textFilter
         */
        $textFilter = pluginApp(TextFilter::class);
        $textFilter->hasNameInLanguage(pluginApp(SessionStorageService::class)->getLang(), $textFilterType);

		return [
			$clientFilter,
		    $variationFilter
		];
	}
}