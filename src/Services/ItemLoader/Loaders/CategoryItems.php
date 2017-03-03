<?php
namespace IO\Services\ItemLoader\Loaders;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;

/**
 * Created by ptopczewski, 09.01.17 11:15
 * Class CategoryItems
 * @package IO\Services\ItemLoader\Loaders
 */
class CategoryItems implements ItemLoaderContract, ItemLoaderPaginationContract
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

		/** @var CategoryFilter $categoryFilter */
		$categoryFilter = pluginApp(CategoryFilter::class);
		$categoryFilter->isInCategory($options['categoryId']);
        
        return [
            $clientFilter,
            $variationFilter,
            $categoryFilter
        ];
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
		return (INT)$options['itemsPerPage'];
	}
}