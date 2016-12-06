<?php //strict

namespace IO\Services;

use Plenty\Plugin\Application;
use IO\Services\SessionStorageService;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\Attribute\Contracts\AttributeNameRepositoryContract;
use Plenty\Modules\Item\Attribute\Contracts\AttributeValueNameRepositoryContract;
use IO\Builder\Item\ItemColumnBuilder;
use IO\Builder\Item\ItemFilterBuilder;
use IO\Builder\Item\ItemParamsBuilder;
use IO\Builder\Item\Params\ItemColumnsParams;
use IO\Builder\Item\Fields\ItemDescriptionFields;
use IO\Builder\Item\Fields\VariationBaseFields;
use IO\Builder\Item\Fields\VariationAttributeValueFields;
use IO\Builder\Item\Fields\ItemCrossSellingFields;
use IO\Constants\Language;
use Plenty\Repositories\Models\PaginatedResult;
use IO\Constants\CrossSellingType;
use IO\Builder\Category\CategoryParams;
use IO\Constants\ItemConditionTexts;

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
		$this->app                          = $app;
		$this->itemRepository               = $itemRepository;
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
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
		$columns = $columnBuilder
			->defaults()
			->build();

		// Filter the current item by item ID
        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
		$filter = $filterBuilder
			->hasId($itemIds)
            ->variationIsActive()
			->build();

		// Set the parameters
        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
		$params = $paramsBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->build();

		return $this->itemRepository->search(
			$columns,
			$filter,
			$params
		);
	}

	
	
    public function getItemImage( int $itemId = 0 ):string
    {
        $item = $this->getItem( $itemId );

        if( $item == null )
        {
            return "";
        }

        $imageList = $item->variationImageList;
        foreach( $imageList as $image )
        {
            if( $image->path !== "" )
            {
                return $image->path;
            }
        }

        return "";
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
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
		$columns = $columnBuilder
			->defaults()
			->build();

		// Filter the current variation by variation ID
        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
		$filter = $filterBuilder
			->variationHasId($variationIds)
            ->variationIsActive()
			->build();

		// Set the parameters
        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
		$params = $paramsBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->build();

		return $this->itemRepository->search(
			$columns,
			$filter,
			$params
		);
	}
    
    public function getVariationList( int $itemId, bool $withPrimary = false ):array
    {
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
        $columns = $columnBuilder
            ->withVariationBase([
                VariationBaseFields::ID
            ])
            ->build();

        // filter current item by item id
        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
        $filter = $filterBuilder
            ->hasId( [$itemId] );

        if($withPrimary)
        {
            $filter->variationIsChild();
        }
        
        $filter = $filter->build();
        
        // set params
        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
        $params = $paramsBuilder
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();
        $variations = $this->itemRepository->search(
            $columns,
            $filter,
            $params
        );
        
        $variationIds = [];
        foreach( $variations as $variation )
        {
            array_push( $variationIds, $variation->variationBase->id );
        }
        return $variationIds;
    }

    public function getVariationImage( int $variationId = 0 ):string
    {
        $variation = $this->getVariation( $variationId );

        if( $variation == null )
        {
            return "";
        }

        $imageList = $variation->variationImageList;

        foreach( $imageList as $image )
        {
            if( $image->path !== "" )
            {
                return $image->path;
            }
        }

        return "";
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
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
        $columns = $columnBuilder
            ->defaults()
            ->build();

        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
        if( $params->variationShowType == 2 )
        {
            $filterBuilder->variationIsPrimary();
        }

        if( $params->variationShowType == 3 )
        {
            $filterBuilder->variationIsChild();
        }

        $filter = $filterBuilder
            ->variationHasCategory( $catID )
            ->variationIsActive()
            ->build();

        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
        if( $params->orderBy != null && strlen( $params->orderBy ) > 0 )
        {
            $paramsBuilder->withParam( ItemColumnsParams::ORDER_BY, ["orderBy." . $params->orderBy => $params->orderByKey]);
        }

        $offset = ( $page - 1 ) * $params->itemsPerPage;
        $params = $paramsBuilder
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
	public function getVariationAttributeMap(int $itemId = 0):array
	{
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
		$columns = $columnBuilder
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

        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
		$filter = $filterBuilder
            ->hasId([$itemId])
            ->variationIsChild()
            ->variationIsActive()
            ->build();

        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
		$params = $paramsBuilder
			->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
			->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
			->build();

		$recordList = $this->itemRepository->search($columns, $filter, $params);
        
        $variations = [];
        foreach($recordList as $variation)
        {
            $data = [
                    "variationId" => $variation->variationBase->id,
                    "attributes" => $variation->variationAttributeValueList
                    ];
            array_push( $variations, $data );
        }
        
		return $variations;
	}
    
    public function getAttributeNameMap(int $itemId = 0):array
    {
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
        $columns = $columnBuilder
            ->withVariationBase(array(
                VariationBaseFields::ID,
                VariationBaseFields::ITEM_ID,
                VariationBaseFields::AVAILABILITY,
                VariationBaseFields::PACKING_UNITS,
                VariationBaseFields::CUSTOM_NUMBER
            ))
            ->withVariationAttributeValueList(array(
                VariationAttributeValueFields::ATTRIBUTE_ID,
                VariationAttributeValueFields::ATTRIBUTE_VALUE_ID
            ))->build();
    
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
        $filter = $filterBuilder
            ->hasId(array($itemId))
            ->variationIsChild()
            ->build();
    
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
        $params = $paramsBuilder
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();
        
        $recordList = $this->itemRepository->search( $columns, $filter, $params );
        
        $attributeList = [];
        
        foreach($recordList as $variation)
        {
            foreach($variation->variationAttributeValueList as $attribute)
            {
                $attributeId = $attribute->attributeId;
                $attributeValueId = $attribute->attributeValueId;
                $attributeList[$attributeId]["name"] = $this->getAttributeName($attributeId);
                if(!in_array($attributeValueId, $attributeList[$attributeId]["values"]))
                {
                    $attributeList[$attributeId]["values"][$attributeValueId] = $this->getAttributeValueName($attributeValueId);
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
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
		$columns = $columnBuilder
			->withItemDescription([
                  ItemDescriptionFields::URL_CONTENT
              ])
            ->build();

        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
		$filter = $filterBuilder
            ->hasId([$itemId])
            ->variationIsActive()
            ->build();

        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
		$params = $paramsBuilder
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
        $attributeNameRepository = pluginApp( AttributeNameRepositoryContract::class );

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
        $attributeValueNameRepository = pluginApp( AttributeValueNameRepositoryContract::class );

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
     * @return array
     */
	public function getItemCrossSellingList(int $itemId = 0, string $crossSellingType = CrossSellingType::SIMILAR):array
	{
		$crossSellingItems = [];

		if($itemId > 0)
		{
            /** @var ItemColumnBuilder $columnBuilder */
            $columnBuilder = pluginApp( ItemColumnBuilder::class );
			$columns = $columnBuilder
				->withItemCrossSellingList([
                    ItemCrossSellingFields::ITEM_ID,
                    ItemCrossSellingFields::CROSS_ITEM_ID,
                    ItemCrossSellingFields::RELATIONSHIP,
                    ItemCrossSellingFields::DYNAMIC
                ])
				->build();

            /** @var ItemFilterBuilder $filterBuilder */
            $filterBuilder = pluginApp( ItemFilterBuilder::class );
			$filter = $filterBuilder
				->hasId([$itemId])
                ->variationIsActive()
				->build();

            /** @var ItemParamsBuilder $paramsBuilder */
            $paramsBuilder = pluginApp( ItemParamsBuilder::class );
			$params = $paramsBuilder
				->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
				->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
				->build();

			$records = $this->itemRepository->search($columns, $filter, $params);
            
            if( $records->count() > 0 )
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

		return $crossSellingItems;
	}
	
	public function getItemConditionText(int $conditionId):string
    {
        return ItemConditionTexts::$itemConditionTexts[$conditionId];
    }

    public function getLatestItems( int $limit = 5, int $categoryId = 0 )
    {
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp( ItemColumnBuilder::class );

        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );

        /** @var ItemParamsBuilder $paramBuilder */
        $paramBuilder = pluginApp( ItemParamsBuilder::class );

        $columns = $columnBuilder
            ->defaults()
            ->build();


        $filterBuilder
            ->variationIsActive()
            ->variationIsPrimary();

        if( $categoryId > 0 )
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

        return $this->itemRepository->search( $columns, $filter, $params );

    }
    
    public function searchItems(string $searchString, CategoryParams $params, int $page = 1)
    {
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp( ItemColumnBuilder::class );
    
        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp( ItemFilterBuilder::class );
    
        /** @var ItemParamsBuilder $paramBuilder */
        $paramsBuilder = pluginApp( ItemParamsBuilder::class );
    
        $columns = $columnBuilder
            ->defaults()
            ->build();
        
        $filter = $filterBuilder
            ->descriptionContains($searchString, true)
            ->build();
    
        $offset = ( $page - 1 ) * $params->itemsPerPage;
        
        $params = $paramsBuilder
            ->withParam( ItemColumnsParams::LIMIT, $params->itemsPerPage )
            ->withParam( ItemColumnsParams::OFFSET, $offset )
            ->withParam( ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang() )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();
        
        return $this->itemRepository->searchWithPagination( $columns, $filter, $params );
    }
}
