<?hh //strict

namespace LayoutCore\Builder\Item;

use LayoutCore\Builder\Item\Fields\ItemBaseFields;
use LayoutCore\Builder\Item\Fields\ItemCharacterFields;
use LayoutCore\Builder\Item\Fields\ItemCrossSellingFields;
use LayoutCore\Builder\Item\Fields\ItemDescriptionFields;
use LayoutCore\Builder\Item\Fields\VariationAttributeValueFields;
use LayoutCore\Builder\Item\Fields\VariationBarcodeFields;
use LayoutCore\Builder\Item\Fields\VariationBaseFields;
use LayoutCore\Builder\Item\Fields\VariationBundleComponentFields;
use LayoutCore\Builder\Item\Fields\VariationCategoryFields;
use LayoutCore\Builder\Item\Fields\VariationImageFields;
use LayoutCore\Builder\Item\Fields\VariationLinkMarketplaceFields;
use LayoutCore\Builder\Item\Fields\VariationLinkWebstoreFields;
use LayoutCore\Builder\Item\Fields\VariationMarketStatusFields;
use LayoutCore\Builder\Item\Fields\VariationRetailPriceFields;
use LayoutCore\Builder\Item\Fields\VariationStandardCategoryFields;
use LayoutCore\Builder\Item\Fields\VariationStockBufferFields;
use LayoutCore\Builder\Item\Fields\VariationStockFields;
use LayoutCore\Builder\Item\Fields\VariationSupplierFields;
use LayoutCore\Builder\Item\Fields\VariationWarehouseFields;

use LayoutCore\Builder\Item\Params\ItemColumnsParams;

/**
 * Builds array of ItemDataLayer columns to request from ItemDataLayerRepository::search
 */
class ItemColumnBuilder
{
    private array<string, array<mixed>> $columnFields = array();
    private array<string, array<ItemColumnsParams, string>> $columnParams = array();

    public function defaults():ItemColumnBuilder
    {
        return $this
            ->withItemBase(array(
                ItemBaseFields::ID,
                ItemBaseFields::RATING,
                ItemBaseFields::RATING_COUNT,
                ItemBaseFields::STORE_SPECIAL,
                ItemBaseFields::PRODUCER,
                ItemBaseFields::PRODUCING_COUNTRY_ID,
                ItemBaseFields::CONDITION,
                ItemBaseFields::AGE_RESTRICTION,
                ItemBaseFields::CUSTOMS_TARIFF_NUMBER
            ))
            ->withItemDescription(array(
                ItemDescriptionFields::NAME_1,
                ItemDescriptionFields::NAME_2,
                ItemDescriptionFields::NAME_3,
                ItemDescriptionFields::DESCRIPTION,
                ItemDescriptionFields::SHORT_DESCRIPTION,
                ItemDescriptionFields::TECHNICAL_DATA,
                ItemDescriptionFields::URL_CONTENT
            ))
            ->withVariationBase(array(
                VariationBaseFields::ID,
                VariationBaseFields::AVAILABILITY,
                VariationBaseFields::PACKING_UNITS,
                VariationBaseFields::CONTENT,
                VariationBaseFields::UNIT_ID,
                VariationBaseFields::MODEL,
                VariationBaseFields::VARIATION_NAME,
                VariationBaseFields::CUSTOM_NUMBER,
                VariationBaseFields::EXTERNAL_ID,
                VariationBaseFields::WEIGHT_G,
                VariationBaseFields::WEIGHT_NET_G,
                VariationBaseFields::WIDTH_MM,
                VariationBaseFields::HEIGHT_MM,
                VariationBaseFields::LENGTH_MM,
                VariationBaseFields::UNIT_COMBINATION_ID
            ))
            ->withVariationImageList(array(
                VariationImageFields::IMAGE_ID,
                VariationImageFields::PATH
            ), [
                ItemColumnsParams::TYPE => 'item_variation'
            ])
            ->withVariationRetailPrice(array(
                VariationRetailPriceFields::VAT_VALUE,
                VariationRetailPriceFields::PRICE,
                VariationRetailPriceFields::BASE_PRICE
            ))
            ->withVariationRecommendedRetailPrice(array(
              VariationRetailPriceFields::PRICE
            ))
            ->withVariationStandardCategory(array(
                VariationStandardCategoryFields::CATEGORY_ID
            ));
    }

    /**
     * Returns generated columns to pass to ItemDataLayerRepository
     * @return array<string, mixed>
     */
    public function build():array<string, mixed> {
        $columns = array();
        foreach( $this->columnFields as $columnName => $columnFields )
        {
            if( count( $this->columnParams[$columnName] ) > 0 )
            {
                // column has params
                $columns[$columnName] = [
                    "fields" => $columnFields,
                    "params" => $this->columnParams[$columnName]
                ];
            }
            else
            {
                $columns[$columnName] = $columnFields;
            }
        }
        return $columns;
    }

    private function withColumn( string $columnKey, array<mixed> $columnValues, ?array<ItemColumnsParams, string> $columnParams = null ):ItemColumnBuilder {

        $this->addColumnFields( $columnKey, $columnValues );
        if( $columnParams !== null )
        {
            $this->addColumnParams( $columnKey, $columnParams );
        }

        return $this;
    }

    private function addColumnFields( string $columnKey, array<mixed> $columnValues ):void
    {
        $column = $this->columnFields[$columnKey];
        if( count( $column ) === 0 )
        {
            $column = $columnValues;
        }
        else
        {
            $column = array_merge( $this->columnFields[$columnKey], $columnValues );
        }
        $this->columnFields[$columnKey] = $column;
    }

    private function addColumnParams( string $columnKey, array<ItemColumnsParams, string> $columnParams ):void
    {
        foreach( $columnParams as $paramName => $paramValue )
        {
            $this->columnParams[$columnKey][$paramName] = $paramValue;
        }
    }

    /**
     * Adds fields in ItemBase to get from ItemDataLayerRepository
     * @param array<ItemBaseFields> $itemBaseFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withItemBase( array<ItemBaseFields> $itemBaseFields ):ItemColumnBuilder {
        return $this->withColumn("itemBase", $itemBaseFields);
    }

    /**
     * Adds fields int ItemCharacterList to get from ItemDataLayerRepository
     * @param array<ItemCharacterFields> $itemCharacterFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for ItemCharacterList
     * @return ItemColumnBuilder current builder instance
     */
    public function withItemCharacterList( array<ItemCharacterFields> $itemCharacterListFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("itemCharacterList", $itemCharacterListFields, $params);
    }

    /**
     * Adds fields in ItemCrossSellingList to get from ItemDataLayerRepository
     * @param array<ItemCrossSellingFields> $itemCrossSellingListFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withItemCrossSellingList( array<ItemCrossSellingFields> $itemCrossSellingListFields ):ItemColumnBuilder {
        return $this->withColumn("itemCrossSellingList", $itemCrossSellingListFields);
    }

    /**
     * Adds fields in ItemDescription to get from ItemDataLayerRepository
     * @param array<ItemDescriptionFields> $itemDescriptionFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for ItemDescription
     * @return ItemColumnBuilder current builder instance
     */
    public function withItemDescription( array<ItemDescriptionFields> $itemDescriptionFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("itemDescription", $itemDescriptionFields, $params);
    }

    /**
     * Adds fields in VariationAttributeValue to get from ItemDataLayerRepository
     * @param array<ItemBaseFields> $variationAttributeValueListFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationAttributeValueList( array<VariationAttributeValueFields> $variationAttributeValueListFields ):ItemColumnBuilder {
        return $this->withColumn("variationAttributeValueList", $variationAttributeValueListFields);
    }

    /**
     * Adds fields in VariationBarcode to get from ItemDataLayerRepository
     * @param array<VariationBarcodeFields> $variationBarcodeFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationBarcode
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationBarcode( array<VariationBarcodeFields> $variationBarcodeFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationBarcode", $variationBarcodeFields, $params);
    }

    /**
     * Adds fields in VariationBarcodeList to get from ItemDataLayerRepository
     * @param array<VariationBarcodeFields> $variationBarcodeFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationBarcodeList( array<VariationBarcodeFields> $variationBarcodeListFields ):ItemColumnBuilder {
        return $this->withColumn("variationBarcodeList", $variationBarcodeListFields);
    }

    /**
     * Adds fields in VariationBase to get from ItemDataLayerRepository
     * @param array<VariationBaseFields> $variationBaseFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationBase( array<VariationBaseFields> $variationBaseFields ):ItemColumnBuilder {
        return $this->withColumn("variationBase", $variationBaseFields);
    }

    /**
     * Adds fields in VariationBundleComponentList to get from ItemDataLayerRepository
     * @param array<VariationBundleComponentFields> $variationBundleComponentListFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationBundleComponentList( array<VariationBundleComponentFields> $variationBundleComponentListFields ):ItemColumnBuilder {
        return $this->withColumn("variationBundleComponentList", $variationBundleComponentListFields);
    }

    /**
     * Adds fields in VariationCategory to get from ItemDataLayerRepository
     * @param array<VariationCategoryFields> $variationCategoryFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationCategoryList( array<VariationCategoryFields> $variationCategoryListFields ):ItemColumnBuilder {
        return $this->withColumn("variationCategoryList", $variationCategoryListFields);
    }

    /**
     * Adds fields in VariationImageList to get from ItemDataLayerRepository
     * @param array<VariationImageFields> $variationImageListFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationImageList( array<VariationImageFields> $variationImageListFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationImageList", $variationImageListFields, $params );
    }

    /**
     * Adds fields in VariationLinkMarketplace to get from ItemDataLayerRepository
     * @param array<VariationLinkMarketplaceFields> $variationLinkMarketplaceFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationLinkMarketplace( array<VariationLinkMarketplaceFields> $variationLinkMarketplaceField ):ItemColumnBuilder {
        return $this->withColumn("variationLinkMarketplace", $variationLinkMarketplaceField);
    }

    /**
     * Adds fields in VariationLinkWebstore to get from ItemDataLayerRepository
     * @param array<VariationLinkWebstoreFields> $variationLinkWebstoreFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationLinkWebstore( array<VariationLinkWebstoreFields> $variationLinkWebstoreFields ):ItemColumnBuilder {
        return $this->withColumn("variationLinkWebstore", $variationLinkWebstoreFields);
    }

    /**
     * Adds fields in VariationMarketStatus to get from ItemDataLayerRepository
     * @param array<VariationMarketStatusFields> $variationMarketStatusFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationMarketStatus
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationMarketStatus( array<VariationMarketStatusFields> $variationMarketStatusFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationMarketStatus", $variationMarketStatusFields, $params);
    }

    /**
     * Adds fields in VariationRecommendedRetailPrice to get from ItemDataLayerRepository
     * @param array<VariationRetailPriceFields> $variationRecommendedPriceFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationRecommendedRetailPrice
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationRecommendedRetailPrice( array<VariationRetailPriceFields> $variationRecommendedRetailPriceFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationRecommendedRetailPrice", $variationRecommendedRetailPriceFields, $params);
    }

    /**
     * Adds fields in VariationRecommendedRetailPriceList to get from ItemDataLayerRepository
     * @param array<VariationRetailPriceFields> $variationRecommendedPriceListFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationRecommendedRetailPriceList
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationRecommendedRetailPriceList( array<VariationRetailPriceFields> $variationRecommendedRetailPriceListFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationRecommendedRetailPriceList", $variationRecommendedRetailPriceListFields, $params);
    }

    /**
     * Adds fields in VariationRetailPrice to get from ItemDataLayerRepository
     * @param array<VariationRetailPriceFields> $variationRetailPriceFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationRetailPrice
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationRetailPrice( array<VariationRetailPriceFields> $variationRetailPriceFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationRetailPrice", $variationRetailPriceFields, $params);
    }

    /**
     * Adds fields in VariationRetailPriceList to get from ItemDataLayerRepository
     * @param array<VariationRetailPriceFields> $variationRetailPriceListFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationRetailPriceList
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationRetailPriceList( array<VariationRetailPriceFields> $variationRetailPriceListFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationRetailPriceList", $variationRetailPriceListFields, $params);
    }

    /**
     * Adds fields in VariationSpecialOfferRetailPrice to get from ItemDataLayerRepository
     * @param array<VariationRetailPriceFields> $variationSpecialOfferRetailPriceFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationSpecialOfferRetailPrice
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationSpecialOfferRetailPrice( array<VariationRetailPriceFields> $variationSpecialOfferRetailPriceFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationSpecialOfferRetailPrice", $variationSpecialOfferRetailPriceFields, $params);
    }

    /**
     * Adds fields in VariationSpecialOfferRetailPriceList to get from ItemDataLayerRepository
     * @param array<VariationRetailPriceFields> $variationSpecialOfferRetailPriceListFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationSpecialOfferRetailPriceList
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationSpecialOfferRetailPriceList( array<VariationRetailPriceFields> $variationSpecialOfferRetailPriceListFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationSpecialOfferRetailPrice", $variationSpecialOfferRetailPriceListFields, $params);
    }

    /**
     * Adds fields in VariationStandardCategory to get from ItemDataLayerRepository
     * @param array<VariationStandardCategoryFields> $variationStandardCategoryFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationStandardCategory
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationStandardCategory( array<VariationStandardCategoryFields> $variationStandardCategoryFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationStandardCategory", $variationStandardCategoryFields, $params);
    }

    /**
     * Adds fields in VariationStock to get from ItemDataLayerRepository
     * @param array<VariationStockFields> $variationStockFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationStock
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationStock( array<VariationStockFields> $variationStockFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationStock", $variationStockFields, $params);
    }

    /**
     * Adds fields in VariationStockBuffer to get from ItemDataLayerRepository
     * @param array<VariationStockBufferFields> $variationStockBufferFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationStockBuffer
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationStockBuffer( array<VariationStockBufferFields> $variationStockBufferFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationStockBuffer", $variationStockBufferFields, $params);
    }

    /**
     * Adds fields in VariationStockList to get from ItemDataLayerRepository
     * @param array<VariationStockFields> $variationStockFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationStockList( array<VariationStockFields> $variationStockListFields ):ItemColumnBuilder {
        return $this->withColumn("variationStockList", $variationStockListFields);
    }

    /**
     * Adds fields in VariationSupplierList to get from ItemDataLayerRepository
     * @param array<VariationSupplierFields> $variationSupplierFields List of fields
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationSupplierList( array<VariationSupplierFields> $variationSupplierListFields ):ItemColumnBuilder {
        return $this->withColumn("variationSupplierList", $variationSupplierListFields);
    }

    /**
     * Adds fields in VariationWarehouse to get from ItemDataLayerRepository
     * @param array<VariationWarehouseFields> $variationWarehouseFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationWarehouse
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationWarehouse( array<VariationWarehouseFields> $variationWarehouseFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationWarehouse", $variationWarehouseFields, $params);
    }

    /**
     * Adds fields in VariationWarehouseList to get from ItemDataLayerRepository
     * @param array<VariationWarehouseFields> $variationWarehouseFields List of fields
     * @param ?array<ItemColumnsParams, string> additional params to use for VariationWarehouseList
     * @return ItemColumnBuilder current builder instance
     */
    public function withVariationWarehouseList( array<VariationWarehouseFields> $variationWarehouseListFields, ?array<ItemColumnsParams, string> $params = null ):ItemColumnBuilder {
        return $this->withColumn("variationWarehouseList", $variationWarehouseListFields, $params);
    }
}
