<?php //strict

namespace IO\Builder\Item;

use IO\Builder\Item\Fields\ItemBaseFields;
use IO\Builder\Item\Fields\ItemCharacterFields;
use IO\Builder\Item\Fields\ItemCrossSellingFields;
use IO\Builder\Item\Fields\ItemDescriptionFields;
use IO\Builder\Item\Fields\VariationAttributeValueFields;
use IO\Builder\Item\Fields\VariationBarcodeFields;
use IO\Builder\Item\Fields\VariationBaseFields;
use IO\Builder\Item\Fields\VariationBundleComponentFields;
use IO\Builder\Item\Fields\VariationCategoryFields;
use IO\Builder\Item\Fields\VariationImageFields;
use IO\Builder\Item\Fields\VariationLinkMarketplaceFields;
use IO\Builder\Item\Fields\VariationLinkWebstoreFields;
use IO\Builder\Item\Fields\VariationMarketStatusFields;
use IO\Builder\Item\Fields\VariationRetailPriceFields;
use IO\Builder\Item\Fields\VariationStandardCategoryFields;
use IO\Builder\Item\Fields\VariationStockBufferFields;
use IO\Builder\Item\Fields\VariationStockFields;
use IO\Builder\Item\Fields\VariationSupplierFields;
use IO\Builder\Item\Fields\VariationWarehouseFields;

use IO\Builder\Item\Params\ItemColumnsParams;

/**
 * Build an array of ItemDataLayer columns to request from ItemDataLayerRepository::search
 * Class ItemColumnBuilder
 * @package IO\Builder\Item
 */
class ItemColumnBuilder
{
	/**
	 * @var array>
	 */
	private $columnFields = [];
	/**
	 * @var array>
	 */
	private $columnParams = [];

    /**
     * Get the item data that is used in most scenarios
     * @return ItemColumnBuilder
     */
	public function defaults():ItemColumnBuilder
	{
		return $this
			->withItemBase([
				               ItemBaseFields::ID,
				               ItemBaseFields::RATING,
				               ItemBaseFields::RATING_COUNT,
				               ItemBaseFields::STORE_SPECIAL,
				               ItemBaseFields::PRODUCER,
				               ItemBaseFields::PRODUCING_COUNTRY_ID,
				               ItemBaseFields::CONDITION,
				               ItemBaseFields::AGE_RESTRICTION,
				               ItemBaseFields::CUSTOMS_TARIFF_NUMBER
			               ])
			->withItemDescription([
				                      ItemDescriptionFields::NAME_1,
				                      ItemDescriptionFields::NAME_2,
				                      ItemDescriptionFields::NAME_3,
				                      ItemDescriptionFields::DESCRIPTION,
				                      ItemDescriptionFields::SHORT_DESCRIPTION,
				                      ItemDescriptionFields::TECHNICAL_DATA,
				                      ItemDescriptionFields::URL_CONTENT
			                      ])
			->withVariationBase([
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
			                    ])
			->withVariationImageList([
				                         VariationImageFields::IMAGE_ID,
				                         VariationImageFields::PATH
			                         ], [
				                         ItemColumnsParams::TYPE => 'item_variation'
			                         ])
			->withVariationRetailPrice([
				                           VariationRetailPriceFields::VAT_VALUE,
				                           VariationRetailPriceFields::PRICE,
				                           VariationRetailPriceFields::BASE_PRICE
			                           ])
			->withVariationRecommendedRetailPrice([
				                                      VariationRetailPriceFields::PRICE
			                                      ])
			->withVariationStandardCategory([
				                                VariationStandardCategoryFields::CATEGORY_ID
			                                ]);
	}

	/**
	 * Return generated columns to pass to ItemDataLayerRepository
	 * @return array
	 */
	public function build():array
	{
		$columns = [];
		foreach($this->columnFields as $columnName => $columnFields)
		{
			if(count($this->columnParams[$columnName]) > 0)
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

    /**
     * Add the column to get from ItemDataLayerRepository
     * @param string $columnKey
     * @param array $columnValues
     * @param null $columnParams
     * @return ItemColumnBuilder
     */
	private function withColumn(string $columnKey, array $columnValues, $columnParams = null):ItemColumnBuilder
	{

		$this->addColumnFields($columnKey, $columnValues);
		if($columnParams !== null)
		{
			$this->addColumnParams($columnKey, $columnParams);
		}

		return $this;
	}

    /**
     * Add column fields to get from ItemDataLayerRepository
     * @param string $columnKey
     * @param array $columnValues
     */
	private function addColumnFields(string $columnKey, array $columnValues)
	{
		$column = $this->columnFields[$columnKey];
		if(count($column) === 0)
		{
			$column = $columnValues;
		}
		else
		{
			$column = array_merge($this->columnFields[$columnKey], $columnValues);
		}
		$this->columnFields[$columnKey] = $column;
	}

    /**
     * Add column parameters to get from ItemDataLayerRepository
     * @param string $columnKey
     * @param array $columnParams
     */
	private function addColumnParams(string $columnKey, array $columnParams)
	{
		foreach($columnParams as $paramName => $paramValue)
		{
			$this->columnParams[$columnKey][$paramName] = $paramValue;
		}
	}

	/**
	 * Add fields in ItemBase to get from ItemDataLayerRepository
	 * @param array $itemBaseFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withItemBase(array $itemBaseFields):ItemColumnBuilder
	{
		return $this->withColumn("itemBase", $itemBaseFields);
	}

	/**
	 * Add fields in ItemCharacterList to get from ItemDataLayerRepository
	 * @param array $itemCharacterFields List of fields
	 * @param ?array additional params to use for ItemCharacterList
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withItemCharacterList(array $itemCharacterListFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("itemCharacterList", $itemCharacterListFields, $params);
	}

	/**
	 * Add fields in ItemCrossSellingList to get from ItemDataLayerRepository
	 * @param array $itemCrossSellingListFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withItemCrossSellingList(array $itemCrossSellingListFields):ItemColumnBuilder
	{
		return $this->withColumn("itemCrossSellingList", $itemCrossSellingListFields);
	}

	/**
	 * Add fields in ItemDescription to get from ItemDataLayerRepository
	 * @param array $itemDescriptionFields List of fields
	 * @param ?array additional params to use for ItemDescription
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withItemDescription(array $itemDescriptionFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("itemDescription", $itemDescriptionFields, $params);
	}

	/**
	 * Add fields in VariationAttributeValue to get from ItemDataLayerRepository
	 * @param array $variationAttributeValueListFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationAttributeValueList(array $variationAttributeValueListFields):ItemColumnBuilder
	{
		return $this->withColumn("variationAttributeValueList", $variationAttributeValueListFields);
	}

	/**
	 * Add fields in VariationBarcode to get from ItemDataLayerRepository
	 * @param array $variationBarcodeFields List of fields
	 * @param ?array additional params to use for VariationBarcode
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationBarcode(array $variationBarcodeFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationBarcode", $variationBarcodeFields, $params);
	}

	/**
	 * Add fields in VariationBarcodeList to get from ItemDataLayerRepository
	 * @param array $variationBarcodeFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationBarcodeList(array $variationBarcodeListFields):ItemColumnBuilder
	{
		return $this->withColumn("variationBarcodeList", $variationBarcodeListFields);
	}

	/**
	 * Add fields in VariationBase to get from ItemDataLayerRepository
	 * @param array $variationBaseFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationBase(array $variationBaseFields):ItemColumnBuilder
	{
		return $this->withColumn("variationBase", $variationBaseFields);
	}

	/**
	 * Add fields in VariationBundleComponentList to get from ItemDataLayerRepository
	 * @param array $variationBundleComponentListFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationBundleComponentList(array $variationBundleComponentListFields):ItemColumnBuilder
	{
		return $this->withColumn("variationBundleComponentList", $variationBundleComponentListFields);
	}

	/**
	 * Add fields in VariationCategory to get from ItemDataLayerRepository
	 * @param array $variationCategoryFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationCategoryList(array $variationCategoryListFields):ItemColumnBuilder
	{
		return $this->withColumn("variationCategoryList", $variationCategoryListFields);
	}

	/**
	 * Add fields in VariationImageList to get from ItemDataLayerRepository
	 * @param array $variationImageListFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationImageList(array $variationImageListFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationImageList", $variationImageListFields, $params);
	}

	/**
	 * Add fields in VariationLinkMarketplace to get from ItemDataLayerRepository
	 * @param array $variationLinkMarketplaceFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationLinkMarketplace(array $variationLinkMarketplaceField):ItemColumnBuilder
	{
		return $this->withColumn("variationLinkMarketplace", $variationLinkMarketplaceField);
	}

	/**
	 * Add fields in VariationLinkWebstore to get from ItemDataLayerRepository
	 * @param array $variationLinkWebstoreFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationLinkWebstore(array $variationLinkWebstoreFields):ItemColumnBuilder
	{
		return $this->withColumn("variationLinkWebstore", $variationLinkWebstoreFields);
	}

	/**
	 * Add fields in VariationMarketStatus to get from ItemDataLayerRepository
	 * @param array $variationMarketStatusFields List of fields
	 * @param ?array additional params to use for VariationMarketStatus
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationMarketStatus(array $variationMarketStatusFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationMarketStatus", $variationMarketStatusFields, $params);
	}

	/**
	 * Add fields in VariationRecommendedRetailPrice to get from ItemDataLayerRepository
	 * @param array $variationRecommendedPriceFields List of fields
	 * @param ?array additional params to use for VariationRecommendedRetailPrice
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationRecommendedRetailPrice(array $variationRecommendedRetailPriceFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationRecommendedRetailPrice", $variationRecommendedRetailPriceFields, $params);
	}

	/**
	 * Add fields in VariationRecommendedRetailPriceList to get from ItemDataLayerRepository
	 * @param array $variationRecommendedPriceListFields List of fields
	 * @param ?array additional params to use for VariationRecommendedRetailPriceList
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationRecommendedRetailPriceList(array $variationRecommendedRetailPriceListFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationRecommendedRetailPriceList", $variationRecommendedRetailPriceListFields, $params);
	}

	/**
	 * Add fields in VariationRetailPrice to get from ItemDataLayerRepository
	 * @param array $variationRetailPriceFields List of fields
	 * @param ?array additional params to use for VariationRetailPrice
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationRetailPrice(array $variationRetailPriceFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationRetailPrice", $variationRetailPriceFields, $params);
	}

	/**
	 * Add fields in VariationRetailPriceList to get from ItemDataLayerRepository
	 * @param array $variationRetailPriceListFields List of fields
	 * @param ?array additional params to use for VariationRetailPriceList
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationRetailPriceList(array $variationRetailPriceListFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationRetailPriceList", $variationRetailPriceListFields, $params);
	}

	/**
	 * Add fields in VariationSpecialOfferRetailPrice to get from ItemDataLayerRepository
	 * @param array $variationSpecialOfferRetailPriceFields List of fields
	 * @param ?array additional params to use for VariationSpecialOfferRetailPrice
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationSpecialOfferRetailPrice(array $variationSpecialOfferRetailPriceFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationSpecialOfferRetailPrice", $variationSpecialOfferRetailPriceFields, $params);
	}

	/**
	 * Add fields in VariationSpecialOfferRetailPriceList to get from ItemDataLayerRepository
	 * @param array $variationSpecialOfferRetailPriceListFields List of fields
	 * @param ?array additional params to use for VariationSpecialOfferRetailPriceList
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationSpecialOfferRetailPriceList(array $variationSpecialOfferRetailPriceListFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationSpecialOfferRetailPrice", $variationSpecialOfferRetailPriceListFields, $params);
	}

	/**
	 * Add fields in VariationStandardCategory to get from ItemDataLayerRepository
	 * @param array $variationStandardCategoryFields List of fields
	 * @param ?array additional params to use for VariationStandardCategory
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationStandardCategory(array $variationStandardCategoryFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationStandardCategory", $variationStandardCategoryFields, $params);
	}

	/**
	 * Add fields in VariationStock to get from ItemDataLayerRepository
	 * @param array $variationStockFields List of fields
	 * @param ?array additional params to use for VariationStock
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationStock(array $variationStockFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationStock", $variationStockFields, $params);
	}

	/**
	 * Add fields in VariationStockBuffer to get from ItemDataLayerRepository
	 * @param array $variationStockBufferFields List of fields
	 * @param ?array additional params to use for VariationStockBuffer
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationStockBuffer(array $variationStockBufferFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationStockBuffer", $variationStockBufferFields, $params);
	}

	/**
	 * Add fields in VariationStockList to get from ItemDataLayerRepository
	 * @param array $variationStockFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationStockList(array $variationStockListFields):ItemColumnBuilder
	{
		return $this->withColumn("variationStockList", $variationStockListFields);
	}

	/**
	 * Add fields in VariationSupplierList to get from ItemDataLayerRepository
	 * @param array $variationSupplierFields List of fields
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationSupplierList(array $variationSupplierListFields):ItemColumnBuilder
	{
		return $this->withColumn("variationSupplierList", $variationSupplierListFields);
	}

	/**
	 * Add fields in VariationWarehouse to get from ItemDataLayerRepository
	 * @param array $variationWarehouseFields List of fields
	 * @param ?array additional params to use for VariationWarehouse
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationWarehouse(array $variationWarehouseFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationWarehouse", $variationWarehouseFields, $params);
	}

	/**
	 * Add fields in VariationWarehouseList to get from ItemDataLayerRepository
	 * @param array $variationWarehouseFields List of fields
	 * @param ?array additional params to use for VariationWarehouseList
	 * @return ItemColumnBuilder current builder instance
	 */
	public function withVariationWarehouseList(array $variationWarehouseListFields, $params = null):ItemColumnBuilder
	{
		return $this->withColumn("variationWarehouseList", $variationWarehouseListFields, $params);
	}
}
