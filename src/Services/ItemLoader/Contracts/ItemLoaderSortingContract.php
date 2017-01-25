<?php
namespace IO\Services\ItemLoader\Contracts;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;

/**
 * Created by ptopczewski, 09.01.17 11:19
 * Interface ItemLoaderSortingContract
 * @package IO\Services\ItemLoader\Contracts
 */
interface ItemLoaderSortingContract
{
	/**
	 * @param array $options
	 * @return SortingInterface
	 */
	public function getSorting($options = []);
}