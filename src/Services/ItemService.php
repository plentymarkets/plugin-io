<?php //strict

namespace LayoutCore\Services;

use Plenty\Plugin\Application;
use LayoutCore\Services\SessionStorageService;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\Attribute\Contracts\AttributeNameRepositoryContract;
use Plenty\Modules\Item\Attribute\Contracts\AttributeValueNameRepositoryContract;
use LayoutCore\Builder\Item\ItemColumnBuilder;
use LayoutCore\Builder\Item\ItemFilterBuilder;
use LayoutCore\Builder\Item\ItemParamsBuilder;
use LayoutCore\Builder\Item\Params\ItemColumnsParams;
use LayoutCore\Builder\Item\Fields\ItemDescriptionFields;
use LayoutCore\Builder\Item\Fields\VariationBaseFields;
use LayoutCore\Builder\Item\Fields\VariationRetailPriceFields;
use LayoutCore\Builder\Item\Fields\VariationImageFields;
use LayoutCore\Builder\Item\Fields\ItemBaseFields;
use LayoutCore\Builder\Item\Fields\VariationStandardCategoryFields;
use LayoutCore\Builder\Item\Fields\VariationAttributeValueFields;
use LayoutCore\Builder\Item\Fields\ItemCrossSellingFields;
use LayoutCore\Constants\Language;
use Plenty\Plugin\Http\Request;
use Plenty\Repositories\Models\PaginatedResult;
use LayoutCore\Constants\CrossSellingType;
use LayoutCore\Builder\Category\CategoryParams;
use LayoutCore\Constants\ItemConditionTexts;

/**
 * Class ItemService
 * @package LayoutCore\Services
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
	 * @var AttributeNameRepositoryContract
	 */
	private $attributeNameRepository;
	/**
	 * @var AttributeValueNameRepositoryContract
	 */
	private $attributeValueNameRepository;
	/**
	 * @var ItemColumnBuilder
	 */
	private $columnBuilder;
	/**
	 * @var ItemFilterBuilder
	 */
	private $filterBuilder;
	/**
	 * @var ItemParamsBuilder
	 */
	private $paramsBuilder;
	/**
	 * @var Request
	 */
	private $request;
	/**
	 * SessionStorageService
	 */
	private $sessionStorage;

    /**
     * ItemService constructor.
     * @param Application $app
     * @param ItemDataLayerRepositoryContract $itemRepository
     * @param AttributeNameRepositoryContract $attributeNameRepository
     * @param AttributeValueNameRepositoryContract $attributeValueNameRepository
     * @param ItemColumnBuilder $columnBuilder
     * @param ItemFilterBuilder $filterBuilder
     * @param ItemParamsBuilder $paramsBuilder
     * @param Request $request
     * @param SessionStorageService $sessionStorage
     */
	public function __construct(
		Application $app,
		ItemDataLayerRepositoryContract $itemRepository,
		AttributeNameRepositoryContract $attributeNameRepository,
		AttributeValueNameRepositoryContract $attributeValueNameRepository,
		ItemColumnBuilder $columnBuilder,
		ItemFilterBuilder $filterBuilder,
		ItemParamsBuilder $paramsBuilder,
		Request $request,
		SessionStorageService $sessionStorage
	)
	{
		$this->app                          = $app;
		$this->itemRepository               = $itemRepository;
		$this->attributeNameRepository      = $attributeNameRepository;
		$this->attributeValueNameRepository = $attributeValueNameRepository;
		$this->columnBuilder                = $columnBuilder;
		$this->filterBuilder                = $filterBuilder;
		$this->paramsBuilder                = $paramsBuilder;
		$this->request                      = $request;
		$this->sessionStorage				= $sessionStorage;
	}

    /**
     * Get an item by ID
     * @param int $itemId
     * @return Record
     */
	public function getItem(int $itemId = 0) : Record
	{
		return $this->getItems([$itemId])->current();
	}

    /**
     * Get a list of items with the specified item IDs
     * @param array $itemIds
     * @return RecordList
     */
	public function getItems(array $itemIds):RecordList
	{
		$columns = $this->columnBuilder
			->defaults()
			->build();

		// Filter the current item by item ID
		$filter = $this->filterBuilder
			->hasId($itemIds)
			->build();

		// Set the parameters
		// TODO: make current language global
		$params = $this->paramsBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->build();

		return $this->itemRepository->search(
			$columns,
			$filter,
			$params
		);
	}

    /**
     * Get an item variation by ID
     * @param int $variationId
     * @return Record
     */
	public function getVariation(int $variationId = 0):Record
	{
		return $this->getVariations([$variationId])->current();
	}

    /**
     * Get a list of item variations with the specified variation IDs
     * @param array $variationIds
     * @return RecordList
     */
	public function getVariations(array $variationIds):RecordList
	{
		$columns = $this->columnBuilder
			->defaults()
			->build();
		// Filter the current item by item ID
		$filter = $this->filterBuilder
			->variationHasId($variationIds)
			->build();

		// Set the parameters
		// TODO: make current language global
		$params = $this->paramsBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->build();

		return $this->itemRepository->search(
			$columns,
			$filter,
			$params
		);
	}


    /**
     * Get all items for a specific category
     * @param int $catID
     * @param CategoryParams $params
     * @param int $page
     * @return PaginatedResult
     */
    public function getItemForCategory( int $catID, CategoryParams $params, int $page = 1 )
    {
        $columns = $this->columnBuilder
            ->defaults()
            ->build();

        if( $params->variationShowType == 2 )
        {
            $this->filterBuilder->variationIsPrimary();
        }

        if( $params->variationShowType == 3 )
        {
            $this->filterBuilder->variationIsChild();
        }

        $filter = $this->filterBuilder
            ->variationHasCategory( $catID )
            ->build();

        if( $params->orderBy != null && strlen( $params->orderBy ) > 0 )
        {
            $this->paramsBuilder->withParam( ItemColumnsParams::ORDER_BY, ["orderBy." . $params->orderBy => $params->orderByKey]);
        }

        $offset = ( $page - 1 ) * $params->itemsPerPage;
        $params = $this->paramsBuilder
            ->withParam( ItemColumnsParams::LIMIT, $params->itemsPerPage )
            ->withParam( ItemColumnsParams::OFFSET, $offset )
            ->withParam( ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang() )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();

        return $this->itemRepository->searchWithPagination( $columns, $filter, $params );
    }

    /**
     * List the attributes of an item variation
     * @param int $itemId
     * @return array
     */
	public function getItemVariationAttributes(int $itemId = 0):array
	{
		$columns = $this->columnBuilder
			->withVariationBase([
				                    VariationBaseFields::ID,
				                    VariationBaseFields::ITEM_ID,
				                    VariationBaseFields::AVAILABILITY,
				                    VariationBaseFields::PACKING_UNITS,
				                    VariationBaseFields::CUSTOM_NUMBER
			                    ])
			->withVariationAttributeValueList([
				                                  VariationAttributeValueFields::ATTRIBUTE_ID,
				                                  VariationAttributeValueFields::ATTRIBUTE_VALUE_ID
			                                  ])->build();

		$filter = $this->filterBuilder->hasId([$itemId])->build();

		$params = $this->paramsBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->build();

		$recordList = $this->itemRepository->search($columns, $filter, $params);

		$attributeList                    = [];
		$attributeList['selectionValues'] = [];
		$attributeList['variations']      = [];
		$attributeList['attributeNames']  = [];

		$foo = 1;

		foreach($recordList as $variation)
		{
			foreach($variation->variationAttributeValueList as $attribute)
			{


				$attributeId      = $attribute->attributeId;
				$attributeValueId = $attribute->attributeValueId;

				$attributeList['attributeNames'][$attributeId] = $this->getAttributeName($attributeId);

				if(!in_array($attributeValueId, $attributeList['selectionValues'][$attributeId]))
				{
					$attributeList['selectionValues'][$attributeId][$attributeValueId] = $this->getAttributeValueName($attributeValueId);
				}
			}

			$variationId                               = $variation->variationBase->id;
			$attributeList['variations'][$variationId] = $variation->variationAttributeValueList;
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
		$columns = $this->columnBuilder
			->withItemDescription([
				                      ItemDescriptionFields::URL_CONTENT
			                      ])->build();

		$filter = $this->filterBuilder->hasId([$itemId])->build();

		$params = $this->paramsBuilder
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
		$name      = '';
		$attribute = $this->attributeNameRepository->findOne($attributeId, $this->sessionStorage->getLang());

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
		$name           = '';
		$attributeValue = $this->attributeValueNameRepository->findOne($attributeValueId, $this->sessionStorage->getLang());
		if(!is_null($attributeValue))
		{
			$name = $attributeValue->name;
		}

		return $name;
	}

    /**
     * Get a list of cross-selling items for the specified item ID
     * @param int $itemId
     * @return array
     */
	public function getItemCrossSellingList(int $itemId = 0, string $crossSellingType = CrossSellingType::SIMILAR):array
	{
		$crossSellingItems = [];

		if($itemId > 0)
		{
			$columns = $this->columnBuilder
				->withItemCrossSellingList([
					                           ItemCrossSellingFields::ITEM_ID,
					                           ItemCrossSellingFields::CROSS_ITEM_ID,
					                           ItemCrossSellingFields::RELATIONSHIP,
					                           ItemCrossSellingFields::DYNAMIC
				                           ])
				->build();

			$filter = $this->filterBuilder
				->hasId([$itemId])
				->build();

			$params = $this->paramsBuilder
				->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
				->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
				->build();

			$currentItem = $this->itemRepository->search($columns, $filter, $params)->current();

			foreach($currentItem->itemCrossSellingList as $crossSellingItem)
			{
                //TODO filter through datalayer - performance
                if($crossSellingItem['relationship'] == $crossSellingType)
                {
                    $crossSellingItems[] = $crossSellingItem;
                }
			}
		}

		return $crossSellingItems;
	}
	
	public function getItemConditionText(int $conditionId):string
    {
        return ItemConditionTexts::$itemConditionTexts[$conditionId];
    }
}
