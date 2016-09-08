<?hh //strict

namespace LayoutCore\Services;

use Plenty\Plugin\Application;
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

class ItemService
{
    private Application $app;
    private ItemDataLayerRepositoryContract $itemRepository;
    private AttributeNameRepositoryContract $attributeNameRepository;
    private AttributeValueNameRepositoryContract $attributeValueNameRepository;
    private ItemColumnBuilder $columnBuilder;
    private ItemFilterBuilder $filterBuilder;
    private ItemParamsBuilder $paramsBuilder;
    private Request $request;

    public function __construct(
        Application $app,
        ItemDataLayerRepositoryContract $itemRepository,
        AttributeNameRepositoryContract $attributeNameRepository,
        AttributeValueNameRepositoryContract $attributeValueNameRepository,
        ItemColumnBuilder $columnBuilder,
        ItemFilterBuilder $filterBuilder,
        ItemParamsBuilder $paramsBuilder,
        Request $request
    )
    {
        $this->app = $app;
        $this->itemRepository = $itemRepository;
        $this->attributeNameRepository = $attributeNameRepository;
        $this->attributeValueNameRepository = $attributeValueNameRepository;
        $this->columnBuilder = $columnBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->paramsBuilder = $paramsBuilder;
        $this->request = $request;
    }

    public function getItem(int $itemId = 0) : Record
    {
        return $this->getItems( array($itemId) )->current();
    }

    public function getItems( array<int> $itemIds ):RecordList
    {
        $columns = $this->columnBuilder

            ->defaults()
            ->build();

        // filter current item by item id
        $filter = $this->filterBuilder
            ->hasId($itemIds)
            ->build();

        // set params
        // TODO: make current language global
        $params = $this->paramsBuilder
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();

        return $this->itemRepository->search(
            $columns,
            $filter,
            $params
        );
    }

    public function getVariation( int $variationId = 0):Record
    {
        return $this->getVariations( array($variationId) )->current();
    }

    public function getVariations( array<int> $variationIds ):RecordList
    {
        $columns = $this->columnBuilder
            ->defaults()
            ->build();
        // filter current item by item id
        $filter = $this->filterBuilder
            ->variationHasId( $variationIds )
            ->build();

        // set params
        // TODO: make current language global
        $params = $this->paramsBuilder
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();

        return $this->itemRepository->search(
            $columns,
            $filter,
            $params
        );
    }

    public function getItemForCategory( int $catID, int $variationShowType = 1 ):PaginatedResult
    {
        $limit = $this->request->get('limit', 20);
        $offset = $this->request->get('offset', 0);
        $currentPage = $this->request->get('page', 0);
        $itemsPerPage = $this->request->get('items_per_page', 20);

        if((int)$currentPage > 0)
        {
            $limit = $itemsPerPage;
            $offset = ((int)$currentPage * (int)$itemsPerPage) - (int)$itemsPerPage;
        }

        $columns = $this->columnBuilder
            ->defaults()
            ->build();



        if($variationShowType == 2)
        {
            $filter = $this->filterBuilder#
                ->variationHasCategory( $catID )
                ->variationIsPrimary()
                ->build();
        }
        elseif($variationShowType == 3)
        {
            $filter = $this->filterBuilder#
                ->variationHasCategory( $catID )
                ->variationIsChild()
                ->build();
        }
        else
        {
            $filter = $this->filterBuilder#
                ->variationHasCategory( $catID )
                ->build();
        }

        /*$filter = $this->filterBuilder
            ->variationHasCategory( $catID )
            ->build();*/

        $params = $this->paramsBuilder
            ->withParam( ItemColumnsParams::ORDER_BY, array('orderBy.itemPrice', 'ASC') )
            ->withParam( ItemColumnsParams::LIMIT, $limit )
            ->withParam( ItemColumnsParams::OFFSET, $offset )
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();

        return $this->itemRepository->searchWithPagination( $columns, $filter, $params );
    }

    public function getItemVariationAttributes(int $itemId = 0):array<string, mixed>
    {
        $columns = $this->columnBuilder
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

        $filter = $this->filterBuilder->hasId(array($itemId))->build();

        $params = $this->paramsBuilder
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();

        $recordList = $this->itemRepository->search( $columns, $filter, $params );

        $attributeList = [];
        $attributeList['selectionValues'] = [];
        $attributeList['variations'] = [];
        $attributeList['attributeNames'] = [];

        $foo = 1;

        foreach($recordList as $variation)
        {
            foreach($variation->variationAttributeValueList as $attribute)
            {


                $attributeId = $attribute->attributeId;
                $attributeValueId = $attribute->attributeValueId;

                $attributeList['attributeNames'][$attributeId] = $this->getAttributeName($attributeId);

                if(!in_array($attributeValueId, $attributeList['selectionValues'][$attributeId]))
                {
                    $attributeList['selectionValues'][$attributeId][$attributeValueId] = $this->getAttributeValueName($attributeValueId);
                }
            }

            $variationId = $variation->variationBase->id;
            $attributeList['variations'][$variationId] = $variation->variationAttributeValueList;
        }

        return $attributeList;
    }

    public function getItemURL(int $itemId):Record
    {
        $columns = $this->columnBuilder
            ->withItemDescription(array(
                ItemDescriptionFields::URL_CONTENT
            ))->build();

        $filter = $this->filterBuilder->hasId(array($itemId))->build();

        $params = $this->paramsBuilder
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();

        $record = $this->itemRepository->search( $columns, $filter, $params )->current();
        return $record;
    }

    public function getAttributeName(int $attributeId = 0):string
    {
        $name = '';
        $attribute = $this->attributeNameRepository->findOne($attributeId, 'de');

        if(!is_null($attribute))
        {
            $name = $attribute->name;
        }

        return $name;
    }

    public function getAttributeValueName(int $attributeValueId = 0):string
    {
        $name = '';
        $attributeValue = $this->attributeValueNameRepository->findOne($attributeValueId, 'de');
        if(!is_null($attributeValue))
        {
            $name = $attributeValue->name;
        }

        return $name;
    }

    public function getItemCrossSellingList(int $itemId = 0):array<int, mixed>
    {
        $crossSellingItems = [];

        if($itemId > 0)
        {
            $columns = $this->columnBuilder
                ->withItemCrossSellingList(array(
                    ItemCrossSellingFields::ITEM_ID,
                    ItemCrossSellingFields::CROSS_ITEM_ID,
                    ItemCrossSellingFields::RELATIONSHIP,
                    ItemCrossSellingFields::DYNAMIC
                ))
                ->build();

            $filter = $this->filterBuilder
                ->hasId(array($itemId))
                ->build();

            $params = $this->paramsBuilder
                ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
                ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
                ->build();

            $currentItem = $this->itemRepository->search( $columns, $filter, $params )->current();

            foreach($currentItem->itemCrossSellingList as $crossSellingItem)
            {
                $crossSellingItems[] = $crossSellingItem;
            }
        }

        return $crossSellingItems;
    }
}
