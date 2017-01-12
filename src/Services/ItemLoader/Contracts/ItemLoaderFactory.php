<?php
namespace IO\Services\ItemLoader\Contracts;

/**
 * Created by ptopczewski, 11.01.17 16:42
 * Interface ItemLoaderFactory
 * @package IO\Services\ItemLoader\Contracts
 */
interface ItemLoaderFactory
{
	/**
	 * @param array $loaderClassList
	 * @param array $resultFields
	 * @param array $options
	 * @return array
	 */
	public function runSearch($loaderClassList, $resultFields,  $options = []);

}