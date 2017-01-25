<?php
namespace IO\Services\ItemLoader\Contracts;

/**
 * Created by ptopczewski, 09.01.17 11:19
 * Interface ItemLoaderPaginationContract
 * @package IO\Services\ItemLoader\Contracts
 */
interface ItemLoaderPaginationContract
{
	/**
	 * @param array $options
	 * @return int
	 */
	public function getCurrentPage($options = []);

	/**
	 * @param array $options
	 * @return int
	 */
	public function getItemsPerPage($options = []);
}