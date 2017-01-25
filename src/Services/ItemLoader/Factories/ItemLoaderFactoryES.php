<?php
namespace IO\Services\ItemLoader\Factories;

use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderSortingContract;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Item\Search\Contracts\ItemElasticSearchSearchRepositoryContract;

/**
 * Created by ptopczewski, 09.01.17 08:35
 * Class ItemLoaderFactoryES
 * @package IO\Services\ItemLoader\Factories
 */
class ItemLoaderFactoryES implements ItemLoaderFactory
{
	/**
	 * @param array $loaderClassList
	 * @param array $resultFields
	 * @param array $options
	 * @return array
	 */
	public function runSearch($loaderClassList, $resultFields,  $options = [])
	{
		/** @var ItemElasticSearchSearchRepositoryContract $elasticSearchRepo */
		$elasticSearchRepo = pluginApp(ItemElasticSearchSearchRepositoryContract::class);

		foreach($loaderClassList as $loaderClass)
		{
			/** @var ItemLoaderContract $loader */
			$loader = pluginApp($loaderClass);

			//search, filter
			$search = $loader->getSearch();
			foreach($loader->getFilterStack($options) as $filter)
			{
				$search->addFilter($filter);
			}

			//sorting
			if($loader instanceof ItemLoaderSortingContract)
			{
				/** @var ItemLoaderSortingContract $loader */
				$sorting = $loader->getSorting($options);
				if($sorting instanceof SortingInterface)
				{
					$search->setSorting($sorting);
				}
			}

			if($loader instanceof ItemLoaderPaginationContract)
			{
				if($search instanceof DocumentSearch)
				{
					/** @var ItemLoaderPaginationContract $loader */
					$search->setPage($loader->getCurrentPage($options), $loader->getItemsPerPage($options));
				}
			}

			/** @var IncludeSource $source */
			$source = pluginApp(IncludeSource::class);

			$currentFields = $resultFields;
			if(array_key_exists($loaderClass, $currentFields))
			{
				$currentFields = $currentFields[$loaderClass];
			}

			$fieldsFound = false;
			foreach($currentFields as $fieldName)
			{
				$source->activate($fieldName);
				$fieldsFound = true;
			}

			if(!$fieldsFound)
			{
				$source->activateAll();
			}

			$search->addSource($source);

			$elasticSearchRepo->addSearch($search);
		}

		return $elasticSearchRepo->execute();
	}
}