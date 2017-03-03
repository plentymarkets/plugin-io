<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregation;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregationProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;

/**
 * Created by ptopczewski, 06.01.17 14:44
 * Class SingleItemAttributes
 * @package IO\Services\ItemLoader\Loaders
 */
class SingleItemAttributes implements ItemLoaderContract
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
		return [];
	}
}