<?php
namespace IO\Services;

use IO\Builder\Item\Fields\ItemCrossSellingFields;
use IO\Builder\Item\Fields\ItemDescriptionFields;
use IO\Builder\Item\Fields\VariationAttributeValueFields;
use IO\Builder\Item\Fields\VariationBaseFields;
use IO\Builder\Item\Fields\VariationRetailPriceFields;
use IO\Builder\Item\Fields\VariationStockFields;
use IO\Builder\Item\ItemColumnBuilder;
use IO\Builder\Item\ItemFilterBuilder;
use IO\Builder\Item\ItemParamsBuilder;
use IO\Builder\Item\Params\ItemColumnsParams;
use IO\Constants\CrossSellingType;
use IO\Constants\ItemConditionTexts;
use IO\Constants\Language;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\Items;
use IO\Extensions\Filters\ItemImagesFilter;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Item\Attribute\Contracts\AttributeNameRepositoryContract;
use Plenty\Modules\Item\Attribute\Contracts\AttributeValueNameRepositoryContract;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregation;
use Plenty\Modules\Item\Search\Aggregations\AttributeValueListAggregationProcessor;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchSearchRepositoryContract;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Plugin\Application;
use IO\Services\TemplateConfigService;
use Plenty\Plugin\Events\Dispatcher;


/**
 * Class ItemService
 * @package IO\Services
 */
class ItemService
{
	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var ItemDataLayerRepositoryContract
	 */
	private $itemRepository;

	/**
	 * SessionStorageService
	 */
	private $sessionStorage;

    /**
     * @var array
     */
	private $additionalItemSortingMap = [];

	/**
	 * ItemService constructor.
	 * @param Application $app
	 * @param ItemDataLayerRepositoryContract $itemRepository
	 * @param SessionStorageService $sessionStorage
	 */
	public function __construct(
		Application $app,
		ItemDataLayerRepositoryContract $itemRepository,
		SessionStorageService $sessionStorage
	)
	{
		$this->app            = $app;
		$this->itemRepository = $itemRepository;
		$this->sessionStorage = $sessionStorage;
	}

	/**
	 * Get an item by ID
	 * @param int $itemId
	 * @return array
	 */
	public function getItem(int $itemId = 0):array
	{
		//$languageMutator = pluginApp(LanguageMutator::class);
		//$documentProcessor->addMutator($languageMutator);
		//$attributeProcessor->addMutator($languageMutator);

		$documentProcessor = pluginApp(DocumentProcessor::class);
		/** @var DocumentSearch $documentSearch */
		$documentSearch = pluginApp(DocumentSearch::class, [$documentProcessor]);

		//$attributeProcessor = pluginApp(AttributeValueListAggregationProcessor::class);
		//$attributeSearch    = pluginApp(AttributeValueListAggregation::class, [$attributeProcessor]);

		/** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
		$elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
		$elasticSearchRepo->addSearch($documentSearch);
		//$elasticSearchRepo->addSearch($attributeSearch);

		/** @var ClientFilter $clientFilter */
		$clientFilter = pluginApp(ClientFilter::class);
		$clientFilter->isVisibleForClient($this->app->getPlentyId());

		/** @var VariationBaseFilter $variationFilter */
		$variationFilter = pluginApp(VariationBaseFilter::class);
		$variationFilter->isActive();
		$variationFilter->hasItemId($itemId);

		$documentSearch
			->addFilter($clientFilter)
			->addFilter($variationFilter);

		return $elasticSearchRepo->execute();
	}

	/**
	 * Get a list of items with the specified item IDs
	 * @param array $itemIds
	 * @return array
	 */
	public function getItems(array $itemIds):array
	{
		$documentProcessor = pluginApp(DocumentProcessor::class);
		$documentSearch    = pluginApp(DocumentSearch::class, [$documentProcessor]);

		/** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
		$elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
		$elasticSearchRepo->addSearch($documentSearch);

		/** @var ClientFilter $clientFilter */
		$clientFilter = pluginApp(ClientFilter::class);
		$clientFilter->isVisibleForClient($this->app->getPlentyId());

		/** @var VariationBaseFilter $variationFilter */
		$variationFilter = pluginApp(VariationBaseFilter::class);
		$variationFilter->isActive();
		$variationFilter->hasItemIds($itemIds);

		$documentSearch
			->addFilter($clientFilter)
			->addFilter($variationFilter);

		return $elasticSearchRepo->execute();
	}

    /**
     * @param int $itemId
     * @return string
     */
    public function getItemImage(int $itemId = 0):string
    {
        $item = $this->getItem($itemId);

        if(is_array($item) && strlen($item['documents'][0]['data']['images']['item'][0]['path']))
        {
            return $item['documents'][0]['data']['images']['item'][0]['path'];
        }

        return '';
    }

	/**
	 * Get an item variation by ID
	 * @param int $variationId
	 * @return array
	 */
	public function getVariation(int $variationId = 0):array
	{
		$documentProcessor = pluginApp(DocumentProcessor::class);
		$documentSearch    = pluginApp(DocumentSearch::class, [$documentProcessor]);

		//$attributeProcessor = pluginApp(AttributeValueListAggregationProcessor::class);
		//$attributeSearch    = pluginApp(AttributeValueListAggregation::class, [$attributeProcessor]);


		/** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
		$elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
		$elasticSearchRepo->addSearch($documentSearch);
		//$elasticSearchRepo->addSearch($attributeSearch);

		/** @var ClientFilter $clientFilter */
		$clientFilter = pluginApp(ClientFilter::class);
		$clientFilter->isVisibleForClient($this->app->getPlentyId());

		/** @var VariationBaseFilter $variationFilter */
		$variationFilter = pluginApp(VariationBaseFilter::class);
		$variationFilter->isActive();
		$variationFilter->hasId($variationId);

		$documentSearch
			->addFilter($clientFilter)
			->addFilter($variationFilter);

		return $elasticSearchRepo->execute();
	}

	/**
	 * Get a list of item variations with the specified variation IDs
	 * @param array $variationIds
	 * @return array
	 */
	public function getVariations(array $variationIds):array
	{
		$documentProcessor = pluginApp(DocumentProcessor::class);
		$documentSearch    = pluginApp(DocumentSearch::class, [$documentProcessor]);

		/** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
		$elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
		$elasticSearchRepo->addSearch($documentSearch);

		/** @var ClientFilter $clientFilter */
		$clientFilter = pluginApp(ClientFilter::class);
		$clientFilter->isVisibleForClient($this->app->getPlentyId());

		/** @var VariationBaseFilter $variationFilter */
		$variationFilter = pluginApp(VariationBaseFilter::class);
		$variationFilter->isActive();
		$variationFilter->hasIds($variationIds);

		$documentSearch
			->addFilter($clientFilter)
			->addFilter($variationFilter);

		return $elasticSearchRepo->execute();
	}

    /**
     * @param $itemId
     * @return array
     */
    public function getVariationIds($itemId):array
    {
        $variationIds = [];

        if((int)$itemId > 0)
        {
            /** @var ItemColumnBuilder $columnBuilder */
            $columnBuilder = pluginApp(ItemColumnBuilder::class);
            $columns       = $columnBuilder
                ->withVariationBase([
                    VariationBaseFields::ID
                ])
                ->build();

            // filter current item by item id
            /** @var ItemFilterBuilder $filterBuilder */
            $filterBuilder = pluginApp(ItemFilterBuilder::class);
            $filter        = $filterBuilder
                ->hasId([$itemId])
                ->variationIsActive()
                ->variationStockIsSalable();

            $filter = $filter->build();

            // set params
            /** @var ItemParamsBuilder $paramsBuilder */
            $paramsBuilder = pluginApp(ItemParamsBuilder::class);
            $params        = $paramsBuilder
                ->withParam(ItemColumnsParams::LANGUAGE,  $this->sessionStorage->getLang())
                ->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
                ->build();
            $variations    = $this->itemRepository->search($columns, $filter, $params);

            foreach($variations as $variation)
            {
                array_push($variationIds, $variation->variationBase->id);
            }
        }

        return $variationIds;
    }

	/**
	 * @param int $itemId
	 * @param bool $withPrimary
	 * @return array
	 */
	public function getVariationList($itemId, bool $withPrimary = false):array
	{
		$variationIds = [];

		if((int)$itemId > 0)
		{
			/** @var ItemColumnBuilder $columnBuilder */
			$columnBuilder = pluginApp(ItemColumnBuilder::class);
			$columns       = $columnBuilder
				->withVariationBase([
					                    VariationBaseFields::ID
				                    ])
				->build();

			// filter current item by item id
			/** @var ItemFilterBuilder $filterBuilder */
			$filterBuilder = pluginApp(ItemFilterBuilder::class);
			$filter        = $filterBuilder
				->hasId([$itemId]);

			if($withPrimary)
			{
				$filter->variationIsChild();
			}

			$filter = $filter->build();

			// set params
			/** @var ItemParamsBuilder $paramsBuilder */
			$paramsBuilder = pluginApp(ItemParamsBuilder::class);
			$params        = $paramsBuilder
				->withParam(ItemColumnsParams::LANGUAGE, Language::DE)
				->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
				->build();
			$variations    = $this->itemRepository->search(
				$columns,
				$filter,
				$params
			);

			foreach($variations as $variation)
			{
				array_push($variationIds, $variation->variationBase->id);
			}
		}

		return $variationIds;
	}

    /**
     * @param int $variationId
     * @param string $imageAccessor
     * @return string
     */
    public function getVariationImage(int $variationId = 0, string $imageAccessor = 'urlPreview'):string
    {
        /**
         * @var ItemLoaderService $itemLoaderService
         */
        $itemLoaderService = pluginApp(ItemLoaderService::class);
        
        $itemLoaderService
            ->setLoaderClassList([Items::class])
            ->setOptions(['variationIds' => [$variationId]])
            ->setResultFields(['images']);
        
        $variation = $itemLoaderService->load();

        if(is_array($variation) && count($variation['documents']))
        {
            $itemImageFilter = pluginApp(ItemImagesFilter::class);
            $variationImages = $itemImageFilter->getItemImages($variation['documents'][0]['data']['images'], $imageAccessor);
            $variationImage = [];

            foreach ($variationImages as $image)
            {
                if(!count($variationImage) || $variationImage['position'] > $image['position'])
                {
                    $variationImage = $image;
                }
            }

            if(!is_null($variationImage['url']))
            {
                return $variationImage['url'];
            }

            return '';
        }

        return '';
    }

	/**
	 * Get all items for a specific category
	 * @param int $catID
	 * @param array $params
	 * @param int $page
	 * @return array
	 */
	public function getItemForCategory(int $catID, $params = [], int $page = 1):array
	{
		$documentProcessor = pluginApp(DocumentProcessor::class);
		$documentSearch    = pluginApp(DocumentSearch::class, [$documentProcessor]);

		/** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
		$elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
		$elasticSearchRepo->addSearch($documentSearch);

		/** @var ClientFilter $clientFilter */
		$clientFilter = pluginApp(ClientFilter::class);
		$clientFilter->isVisibleForClient($this->app->getPlentyId());

		/** @var VariationBaseFilter $variationFilter */
		$variationFilter = pluginApp(VariationBaseFilter::class);
		$variationFilter->isActive();

		/** @var CategoryFilter $categoryFilter */
		$categoryFilter = pluginApp(CategoryFilter::class);
		$categoryFilter->isInCategory($catID);

		$documentSearch
			->addFilter($clientFilter)
			->addFilter($variationFilter)
			->addFilter($categoryFilter)
			->setPage($page, $params['itemsPerPage']);

		return $elasticSearchRepo->execute();
	}

	/**
	 * List the attributes of an item variation
	 * @param int $itemId
	 * @return array
	 */
	public function getVariationAttributeMap($itemId = 0):array
	{
		$variations = [];

		if((int)$itemId > 0)
		{
			/** @var ItemColumnBuilder $columnBuilder */
			$columnBuilder = pluginApp(ItemColumnBuilder::class);
			$columns       = $columnBuilder
				->withVariationBase([
					                    VariationBaseFields::ID,
					                    VariationBaseFields::ITEM_ID
				                    ])
                ->withItemDescription([
                                        ItemDescriptionFields::URL_CONTENT
                ])
				->withVariationAttributeValueList([
					                                  VariationAttributeValueFields::ATTRIBUTE_ID,
					                                  VariationAttributeValueFields::ATTRIBUTE_VALUE_ID
				                                  ])->build();

			/** @var ItemFilterBuilder $filterBuilder */
			$filterBuilder = pluginApp(ItemFilterBuilder::class);

            if(pluginApp(TemplateConfigService::class)->get('item.show_variation_over_dropdown') != 'true')
            {
                $filterBuilder->variationStockIsSalable();
            }

			$filter = $filterBuilder
				->hasId([$itemId])
				->variationIsActive()
                ->build();

			/** @var ItemParamsBuilder $paramsBuilder */
			$paramsBuilder = pluginApp(ItemParamsBuilder::class);
			$params        = $paramsBuilder
				->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
				->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
                ->withParam(ItemColumnsParams::CUSTOMER_CLASS, pluginApp(CustomerService::class)->getContact()->classId)
				->build();

			$recordList = $this->itemRepository->search($columns, $filter, $params);

			foreach($recordList as $variation)
			{
                if($variation->itemDescription->urlContent !== "" )
                {
                    $url = $variation->itemDescription->urlContent  ."_". $itemId;
                }
                else
                {
                    $url = $itemId;
                }

                $data = [
                    "variationId" => $variation->variationBase->id,
                    "attributes"  => $variation->variationAttributeValueList,
                    "url"         => $url
                ];
				array_push($variations, $data);
			}
		}

		return $variations;
	}

    /**
     * @param int $variationId
     * @return bool
     */
    public function getVariationIsSalable($variationId = 0):Bool
    {
        $isSalable = false;

        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp(ItemColumnBuilder::class);
        $columns       = $columnBuilder
            ->withVariationStock([
                VariationStockFields::STOCK_PHYSICAL
            ])
            ->withVariationBase([
                VariationBaseFields::LIMIT_ORDER_BY_STOCK_SELECT
            ])
            ->withVariationRetailPrice([
                VariationRetailPriceFields::BASE_PRICE
            ])
            ->build();

        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp(ItemFilterBuilder::class);
        $filter        = $filterBuilder
            ->variationHasId([$variationId])
            ->variationHasRetailPrice()
            ->build();

        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp(ItemParamsBuilder::class);
        $params        = $paramsBuilder
            ->withParam(ItemColumnsParams::TYPE, 'virtual')
            ->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
            ->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
            ->withParam(ItemColumnsParams::CUSTOMER_CLASS, pluginApp(CustomerService::class)->getContactClassId())
            ->build();

        $record = $this->itemRepository->search($columns, $filter, $params)->current();

        $isSalable = $record['variationBase']['limitOrderByStockSelect'] == 1 && $record['variationStock']['stockPhysical'] <= 0;

        return $isSalable;
    }

	/**
	 * @param int $itemId
	 * @return array
	 */
	public function getAttributeNameMap($itemId = 0):array
	{
		$attributeList = [];

		if((int)$itemId > 0)
		{
			/** @var ItemColumnBuilder $columnBuilder */
			$columnBuilder = pluginApp(ItemColumnBuilder::class);
			$columns       = $columnBuilder
				->withVariationBase([
					                    VariationBaseFields::ID,
					                    VariationBaseFields::ITEM_ID,
					                    VariationBaseFields::AVAILABILITY,
					                    VariationBaseFields::PACKING_UNITS,
					                    VariationBaseFields::CUSTOM_NUMBER
				                    ])
                ->withVariationRetailPrice([
                    VariationRetailPriceFields::BASE_PRICE
                ])
				->withVariationAttributeValueList([
					                                  VariationAttributeValueFields::ATTRIBUTE_ID,
					                                  VariationAttributeValueFields::ATTRIBUTE_VALUE_ID
				                                  ])->build();

			/** @var ItemFilterBuilder $filterBuilder */
			$filterBuilder = pluginApp(ItemFilterBuilder::class);
			$filter        = $filterBuilder
				->hasId([$itemId])
                ->variationHasRetailPrice()
                ->variationIsActive()
                ->build();

			/** @var ItemParamsBuilder $paramsBuilder */
			$paramsBuilder = pluginApp(ItemParamsBuilder::class);
			$params        = $paramsBuilder
				->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
				->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
                ->withParam(ItemColumnsParams::CUSTOMER_CLASS, pluginApp(CustomerService::class)->getContactClassId())
				->build();

			$recordList = $this->itemRepository->search($columns, $filter, $params);

			foreach($recordList as $variation)
			{
				foreach($variation->variationAttributeValueList as $attribute)
				{
					$attributeId                         = $attribute->attributeId;
					$attributeValueId                    = $attribute->attributeValueId;
					$attributeList[$attributeId]["name"] = $this->getAttributeName($attributeId);
					if(!in_array($attributeValueId, $attributeList[$attributeId]["values"]))
					{
						$attributeList[$attributeId]["values"][$attributeValueId] = $this->getAttributeValueName($attributeValueId);
					}
				}
			}
		}

		return $attributeList;
	}

	/**
	 * Get the item URL
	 * @param int $itemId
	 * @return Record
	 */
	public function getItemURL(int $itemId):Record
	{
		/** @var ItemColumnBuilder $columnBuilder */
		$columnBuilder = pluginApp(ItemColumnBuilder::class);
		$columns       = $columnBuilder
			->withItemDescription([
				                      ItemDescriptionFields::URL_CONTENT
			                      ])
			->build();

		/** @var ItemFilterBuilder $filterBuilder */
		$filterBuilder = pluginApp(ItemFilterBuilder::class);
		$filter        = $filterBuilder
			->hasId([$itemId])
			->variationIsActive()
			->build();

		/** @var ItemParamsBuilder $paramsBuilder */
		$paramsBuilder = pluginApp(ItemParamsBuilder::class);
		$params        = $paramsBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->build();
   
		$record = $this->itemRepository->search($columns, $filter, $params)->current();
		return $record;
	}

	/**
	 * Get the name of an attribute by ID
	 * @param int $attributeId
	 * @return string
	 */
	public function getAttributeName(int $attributeId = 0):string
	{
		/** @var AttributeNameRepositoryContract $attributeNameRepository */
		$attributeNameRepository = pluginApp(AttributeNameRepositoryContract::class);

		$name      = '';
		$attribute = $attributeNameRepository->findOne($attributeId, $this->sessionStorage->getLang());

		if(!is_null($attribute))
		{
			$name = $attribute->name;
		}

		return $name;
	}

	/**
	 * Get the name of an attribute value by ID
	 * @param int $attributeValueId
	 * @return string
	 */
	public function getAttributeValueName(int $attributeValueId = 0):string
	{
		/** @var AttributeValueNameRepositoryContract $attributeValueNameRepository */
		$attributeValueNameRepository = pluginApp(AttributeValueNameRepositoryContract::class);

		$name           = '';
		$attributeValue = $attributeValueNameRepository->findOne($attributeValueId, $this->sessionStorage->getLang());
		if(!is_null($attributeValue))
		{
			$name = $attributeValue->name;
		}

		return $name;
	}
	
	/**
	 * Get a list of cross-selling items for the specified item ID
	 * @param int $itemId
	 * @param string $crossSellingType
	 * @return array
	 */
	public function getItemCrossSellingList($itemId = 0, string $crossSellingType = 'similar'):array
	{
		$crossSellingItems = [];

		if((int)$itemId > 0)
		{
			if($itemId > 0)
			{
				/** @var ItemColumnBuilder $columnBuilder */
				$columnBuilder = pluginApp(ItemColumnBuilder::class);
				$columns       = $columnBuilder
					->withItemCrossSellingList([
						                           ItemCrossSellingFields::ITEM_ID,
						                           ItemCrossSellingFields::CROSS_ITEM_ID,
						                           ItemCrossSellingFields::RELATIONSHIP,
						                           ItemCrossSellingFields::DYNAMIC
					                           ])
					->build();

				/** @var ItemFilterBuilder $filterBuilder */
				$filterBuilder = pluginApp(ItemFilterBuilder::class);
				$filter        = $filterBuilder
					->hasId([$itemId])
					->variationIsActive()
					->build();

				/** @var ItemParamsBuilder $paramsBuilder */
				$paramsBuilder = pluginApp(ItemParamsBuilder::class);
				$params        = $paramsBuilder
					->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
					->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
					->build();

				$records = $this->itemRepository->search($columns, $filter, $params);

				if($records->count() > 0)
				{
					$currentItem = $records->current();
					foreach($currentItem->itemCrossSellingList as $crossSellingItem)
					{
						if($crossSellingItem['relationship'] == $crossSellingType)
						{
							$crossSellingItems[] = $crossSellingItem;
						}
					}
				}
			}
		}


		return $crossSellingItems;
	}
	
	/**
	 * @param int $conditionId
	 * @return string
	 */
	public function getItemConditionText(int $conditionId):string
	{
		return ItemConditionTexts::$itemConditionTexts[$conditionId];
	}
	
	/**
	 * @param int $limit
	 * @param int $categoryId
	 * @return RecordList
	 */
	public function getLatestItems(int $limit = 5, int $categoryId = 0)
	{
		/** @var ItemColumnBuilder $columnBuilder */
		$columnBuilder = pluginApp(ItemColumnBuilder::class);

		/** @var ItemFilterBuilder $filterBuilder */
		$filterBuilder = pluginApp(ItemFilterBuilder::class);

		/** @var ItemParamsBuilder $paramBuilder */
		$paramBuilder = pluginApp(ItemParamsBuilder::class);

		$columns = $columnBuilder
			->defaults()
			->build();


		$filterBuilder
			->variationIsActive()
			->variationIsPrimary();

		if($categoryId > 0)
		{
			$filterBuilder->variationHasCategory([$categoryId]);
		}

		$filter = $filterBuilder->build();

		$params = $paramBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->withParam(ItemColumnsParams::ORDER_BY, ["orderBy.variationCreateTimestamp" => "desc"])
			->withParam(ItemColumnsParams::LIMIT, $limit)
			->build();

		return $this->itemRepository->search($columns, $filter, $params);

	}
	
	/**
	 * @param string $searchString
	 * @param array $params
	 * @param int $page
	 * @return array
	 */
	public function searchItems(string $searchString, $params = [], int $page = 1):array
	{
        /**
         * @var SessionStorageService $sessionStorage
         */
        $sessionStorage = pluginApp(SessionStorageService::class);
        $lang = $sessionStorage->getLang();
        
		$documentProcessor = pluginApp(DocumentProcessor::class);
		$documentSearch    = pluginApp(DocumentSearch::class, [$documentProcessor]);

		/** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
		$elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
		$elasticSearchRepo->addSearch($documentSearch);

		/** @var VariationBaseFilter $variationFilter */
		$variationFilter = pluginApp(VariationBaseFilter::class);
		$variationFilter->isActive();

		/** @var ClientFilter $clientFilter */
		$clientFilter = pluginApp(ClientFilter::class);
		$clientFilter->isVisibleForClient($this->app->getPlentyId());

		/** @var SearchFilter $searchFilter */
		$searchFilter = pluginApp(SearchFilter::class);
		$searchFilter->setSearchString($searchString, $lang, ElasticSearch::SEARCH_TYPE_FUZZY);

		$documentSearch
			->addFilter($clientFilter)
			->addFilter($variationFilter)
			->addFilter($searchFilter)
			->setPage($page, $params['itemsPerPage']);

		return $elasticSearchRepo->execute();
	}

    /**
     *
     */
	public function getAdditionalItemSorting(){
	    /** @var Dispatcher $dispatcher */
	    $dispatcher = pluginApp(Dispatcher::class);
	    $dispatcher->fire('IO.initAdditionalSorting', [$this]);
	    return $this->additionalItemSortingMap;
    }

    /**
     * @param string $key
     * @param string $translationKey
     */
    public function addAdditionalItemSorting($key, $translationKey){
        $this->additionalItemSortingMap[$key] = $translationKey;
    }
    
    /**
     * @param string $searchString
     * @return array
     */
    /*public function searchItemsAutocomplete(string $searchString):array
    {
        /** @var IncludeSource $includeSource */
        /*$includeSource = pluginApp(IncludeSource::class);
        $includeSource->activate('test', 'test');
    
        $documentProcessor = pluginApp(DocumentProcessor::class);
        $documentSearch    = pluginApp(DocumentSearch::class, [$documentProcessor]);
    
        /** @var VariationElasticSearchSearchRepositoryContract $elasticSearchRepo */
        /*$elasticSearchRepo = pluginApp(VariationElasticSearchSearchRepositoryContract::class);
        $elasticSearchRepo->addSearch($documentSearch);
    
        /** @var VariationBaseFilter $variationFilter */
        /*$variationFilter = pluginApp(VariationBaseFilter::class);
        $variationFilter->isActive();
    
        /** @var ClientFilter $clientFilter */
        /*$clientFilter = pluginApp(ClientFilter::class);
        $clientFilter->isVisibleForClient($this->app->getPlentyId());
    
        /** @var SearchFilter $searchFilter */
        /*$searchFilter = pluginApp(SearchFilter::class);
        $searchFilter->setSearchString($searchString, ElasticSearch::SEARCH_TYPE_AUTOCOMPLETE);
    
        $documentSearch
            ->addFilter($clientFilter)
            ->addFilter($variationFilter)
            ->addFilter($searchFilter)
            ->addSource($includeSource);
    
        return $elasticSearchRepo->execute();
    }*/
}
