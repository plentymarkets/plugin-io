<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregation;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregationProcessor;

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
		$attributeProcessor = pluginApp(AttributeValueListAggregationProcessor::class);
		return pluginApp(AttributeValueListAggregation::class, [$attributeProcessor]);
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