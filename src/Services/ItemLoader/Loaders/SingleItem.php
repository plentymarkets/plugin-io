<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Services\SessionStorageService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\TemplateConfigService;
use IO\Services\PriceDetectService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;
use Plenty\Plugin\Application;

/**
 * Created by ptopczewski, 06.01.17 14:44
 * Class SingleItem
 * @package IO\Services\ItemLoader\Loaders
 */
class SingleItem implements ItemLoaderContract
{
    private $options = [];
    
	/**
	 * @return SearchInterface
	 */
	public function getSearch()
	{
        $sessionLang =  $this->options['lang'];
        if ( $sessionLang === null )
        {
            $sessionLang = pluginApp(SessionStorageService::class)->getLang();
        }
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [$sessionLang]]);
        $imageMutator = pluginApp(ImageMutator::class);
        $imageMutator->addClient(pluginApp(Application::class)->getPlentyId());
        
        $documentProcessor = pluginApp(DocumentProcessor::class);
        $documentProcessor->addMutator($languageMutator);
        $documentProcessor->addMutator($imageMutator);
        
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
        
        $sessionLang =  $options['lang'];
		if ( $sessionLang === null )
		{
		    $sessionLang = pluginApp(SessionStorageService::class)->getLang();
        }
        
        $langMap = [
            'de' => TextFilter::LANG_DE,
            'fr' => TextFilter::LANG_FR,
            'en' => TextFilter::LANG_EN,
        ];
        
        /**
         * @var TextFilter $textFilter
         */
        $textFilter = pluginApp(TextFilter::class);
        
        if(isset($langMap[$sessionLang]))
        {
            $textFilterLanguage = $langMap[$sessionLang];
            
            /**
             * @var TemplateConfigService $templateConfigService
             */
            $templateConfigService = pluginApp(TemplateConfigService::class);
            $usedItemName = $templateConfigService->get('item.name');
            
            $textFilterType = TextFilter::FILTER_ANY_NAME;
            if(strlen($usedItemName))
            {
                if($usedItemName == '0')
                {
                    $textFilterType = TextFilter::FILTER_NAME_1;
                }
                elseif($usedItemName == '1')
                {
                    $textFilterType = TextFilter::FILTER_NAME_2;
                }
                elseif($usedItemName == '2')
                {
                    $textFilterType = TextFilter::FILTER_NAME_3;
                }
            }
            
            $textFilter->hasNameInLanguage($textFilterLanguage, $textFilterType);
        }
        
        /**
         * @var PriceDetectService $priceDetectService
         */
        $priceDetectService = pluginApp(PriceDetectService::class);
        $priceIds = $priceDetectService->getPriceIdsForCustomer();
        
        /**
         * @var SalesPriceFilter $priceFilter
         */
        $priceFilter = pluginApp(SalesPriceFilter::class);
        $priceFilter->hasAtLeastOnePrice($priceIds);

		return [
			$clientFilter,
		    $variationFilter,
            $textFilter,
            $priceFilter
		];
	}
    
    public function setOptions($options = [])
    {
        $this->options = $options;
        return $options;
    }

    /**
     * @param array $defaultResultFields
     * @return array
     */
    public function getResultFields($defaultResultFields)
    {
        return $defaultResultFields;
    }
}