<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\ItemLoader\Helper\FacetFilterBuilder;
use IO\Services\ItemLoader\Helper\WebshopFilterBuilder;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\SessionStorageService;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderSortingContract;
use IO\Builder\Sorting\SortingBuilder;
use IO\Services\TemplateConfigService;
use IO\Services\PriceDetectService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;
use Plenty\Modules\Item\Search\Helper\FacetHelper;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Plugin\Application;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\BaseCollapse;

/**
 * Created by ptopczewski, 09.01.17 11:15
 * Class CategoryItems
 * @package IO\Services\ItemLoader\Loaders
 */
class CategoryItems implements ItemLoaderContract, ItemLoaderPaginationContract, ItemLoaderSortingContract
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
        
        $collapse =  pluginApp(BaseCollapse::class, ['ids.itemId']);
        
        $documentProcessor = pluginApp(DocumentProcessor::class);
        $documentProcessor->addMutator($languageMutator);
        $documentProcessor->addMutator($imageMutator);
        
        $documentSearch = pluginApp(DocumentSearch::class, [$documentProcessor]);
        $documentSearch->setCollapse($collapse);
        $documentSearch->setName('search');
        
        return $documentSearch;
	}
    
    /**
     * @return array
     */
    /**
     * @return array
     */
    public function getAggregations()
    {
        /** @var FacetExtensionContainer $facetExtensionContainer */
        $facetExtensionContainer = pluginApp(FacetExtensionContainer::class);
        
        $aggregations = [];
        foreach ($facetExtensionContainer->getFacetExtensions() as $facetExtension) {
            if ($facetExtension instanceof FacetExtension) {
                $aggregations[] = $facetExtension->getAggregation();
            }
        }
        
        return $aggregations;
    }

	/**
	 * @param array $options
	 * @return TypeInterface[]
	 */
	public function getFilterStack($options = [])
	{
		/*if(isset($options['variationShowType']) && $options['variationShowType'] == 'main')
        {
            $variationFilter->isMain();
        }
        elseif(isset($options['variationShowType']) && $options['variationShowType'] == 'child')
        {
            $variationFilter->isChild();
        }*/
        
		$filters = [];
		
		/** @var CategoryFilter $categoryFilter */
		$categoryFilter = pluginApp(CategoryFilter::class);
		$categoryFilter->isInCategory($options['categoryId']);
		$filters[] = $categoryFilter;
        
        /** @var WebshopFilterBuilder $webshopFilterBuilder */
        $webshopFilterBuilder = pluginApp(WebshopFilterBuilder::class);
        $defaultFilters = $webshopFilterBuilder->getFilters($options);
        $filters = array_merge( $filters, $defaultFilters );
		
        /** @var FacetFilterBuilder $facetHelper */
        $facetHelper = pluginApp(FacetFilterBuilder::class);
        $facetFilters = $facetHelper->getFilters($options);
        $filters = array_merge( $filters, $facetFilters );
        
        return $filters;
    }
	
	/**
	 * @param array $options
	 * @return int
	 */
	public function getCurrentPage($options = [])
	{
		return (INT)$options['page'];
	}

	/**
	 * @param array $options
	 * @return int
	 */
	public function getItemsPerPage($options = [])
	{
		return (INT)$options['items'];
	}
	
	public function getSorting($options = [])
    {
        $sortingInterface = null;

        if(isset($options['sorting']) && strlen($options['sorting']))
        {

            if($options['sorting'] == 'default.recommended_sorting')
            {
                $sortingInterface = SortingBuilder::buildDefaultSortingCategory();
            }
            else
            {
                $sortingInterface = SortingBuilder::buildSorting($options['sorting']);
            }

        }
       
        return $sortingInterface;
    }
    
    public function setOptions($options = [])
    {
        $this->options = $options;
    }
}