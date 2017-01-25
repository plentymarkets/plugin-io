<?php
namespace IO\Services\ItemLoader\Services;

use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;

/**
 * Created by ptopczewski, 09.01.17 10:47
 * Class ItemLoaderService
 * @package IO\Services\ItemLoader\Services
 */
class ItemLoaderService
{
	use LoadResultFields;

	/**
	 * @var array
	 */
	private $loaderClassList = [];

	/**
	 * @var array
	 */
	private $resultFields = [];

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @param array $loaderClassList
	 * @return $this
	 */
	public function setLoaderClassList($loaderClassList)
	{
		$this->loaderClassList = $loaderClassList;
		return $this;
	}

	/**
	 * @param array $resultFields
	 * @return $this
	 */
	public function setResultFields($resultFields)
	{
		$this->resultFields = $resultFields;
		return $this;
	}


	/**
	 * @param array $options
	 * @return $this
	 */
	public function setOptions($options)
	{
		$this->options = $options;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function load()
	{
		/** @var ItemLoaderFactory $itemLoaderFactory */
		$itemLoaderFactory = pluginApp(ItemLoaderFactory::class);
		return $itemLoaderFactory->runSearch($this->loaderClassList, $this->resultFields, $this->options);
	}

	/**
	 * @param string $templateName
	 * @param array $loaderClassList
	 * @param array $options
	 * @return array
	 */
	public function loadForTemplate($templateName, $loaderClassList, $options = [])
	{
		$this->resultFields = $this->loadResultFields($templateName);
		$this->loaderClassList = $loaderClassList;
		$this->options = $options;
		return $this->load();
	}
}