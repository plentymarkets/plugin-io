<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Services\SessionStorageService;
use IO\Services\PriceDetectService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregation;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregationProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Plugin\Application;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;

/**
 * Created by ptopczewski, 06.01.17 14:44
 * Class SingleItemAttributes
 * @package IO\Services\ItemLoader\Loaders
 */
class SingleItemAttributes implements ItemLoaderContract
{
    private $options = [];
    
	/**
	 * @return SearchInterface
	 */
	public function getSearch()
	{
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [pluginApp(SessionStorageService::class)->getLang()]]);
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
        $attributeProcessor = pluginApp(AttributeValueListAggregationProcessor::class);
        $attributeSearch = pluginApp(AttributeValueListAggregation::class, [$attributeProcessor]);
        
        return [
            $attributeSearch
        ];
    }
    
    /**
	 * @param array $options
	 * @return TypeInterface[]
	 */
	public function getFilterStack($options = [])
	{
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