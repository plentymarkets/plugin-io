<?php //strict

namespace IO\Builder\Item;

use IO\Constants\Language;

/**
 * Build an array of ItemDataLayer filters to pass to ItemDataLayerRepository:search
 * Class ItemFilterBuilder
 * @package IO\Builder\Item
 */
class ItemFilterBuilder
{
	/**
	 * @var array
	 */
	private $filter = null;

	/**
	 * Return the generated filters to pass to ItemDataLayerRepository::search
	 * @return array
	 */
	public function build():array
	{
		return $this->filter;
	}

    /**
     * Filter items by specific filter key
     * @param string $filterKey
     * @param array $filterValue
     * @return ItemFilterBuilder
     */
	private function hasFilter(string $filterKey, array $filterValue = []):ItemFilterBuilder
	{
		if($this->filter === null)
		{
			$this->filter = [];
		}
		$this->filter[$filterKey] = $filterValue;
		return $this;
	}

    /**
     * Filter items by propertyId
     * @param array $propertyId
     * @return ItemFilterBuilder
     */
    public function hasProperty(array $propertyId):ItemFilterBuilder
    {
        return $this->hasFilter("variationBase.hasProperty", ["propertyId" => $propertyId]);
    }

	/**
	 * Filter items by amazon product genre
	 * @param string $productGenre The amazon genre
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasAmazonTypeProductGenre(string $productGenre):ItemFilterBuilder
	{
		return $this->hasFilter("itemAmazonType.hasProductGenre", [
			"genre" => $productGenre
		]);
	}

	/**
	 * Deny items not having defined attributes
	 * @param array $attributeIDs allowed attribute IDs
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasAttribute(array $attributeIDs):ItemFilterBuilder
	{
		return $this->hasFilter("itemAttribute.hasAttribute", [
			"attributeId" => $attributeIDs
		]);
	}

	/**
	 * Deny items having one of the defined attributes
	 * @param array $attributeIDs The attribute IDs to Deny
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notHasAttribute(array $attributeIDs):ItemFilterBuilder
	{
		return $this->hasFilter("itemAttribute.doesNotHaveAttribute", [
			"attributeId" => $attributeIDs
		]);
	}

	/**
	 * Filter items by age restriction
	 * @param array List of allowed age restrictions
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasAgeRestriction(array $ageRestrictions):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasAgeRestriction", [
			"ageRestriction" => $ageRestrictions
		]);
	}

	/**
	 * Filter items by amazon product type. Do not pass any type to filter
	 * items having any amazon product type.
	 * @param array $productTypes The amazon product types to filter
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasAmazonProductType($productTypes = null):ItemFilterBuilder
	{
		if($productTypes === null)
		{
			return $this->hasFilter("itemBase.hasAmazonProductType?");
		}
		else
		{
			return $this->hasFilter("itemBase.hasAmazonProductType", [
				"amazonProductType" => $productTypes
			]);
		}
	}

	/**
	 * Deny items having any amazon product type
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notHasAmazonProductType():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasNoAmazonProductType?");
	}

	/**
	 * Filter items having a barcode
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasBarcode():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasBarcode?");
	}

	/**
	 * Filter items having no barcode
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notHasBarcode():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasNoBarcode?");
	}

	/**
	 * Filter items by cross selling items
	 * @param array $itemIDs The linked item IDs
	 * @param bool $dynamic Filter dynamic entries -> true|false
	 *                       or all entries -> null
	 * @param array $relationship The relationship between the linked items
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasCrossItemId(array $itemIDs, bool $dynamic, array $relationship):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasCrossItemId", [
			"itemId"       => $itemIDs,
			"dynamic"      => $dynamic,
			"relationship" => $relationship
		]);
	}

	/**
	 * Filter item by IDs
	 * @param array $itemIDs The item IDs to filter
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasId(array $itemIDs):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasId", [
			"itemId" => $itemIDs
		]);
	}

	/**
	 * Filter items having an image
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasImage():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasImage?");
	}

	/**
	 * Filter items not having any image
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notHasImage():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasNoImage?");
	}

	/**
	 * Filter items by flag 1
	 * @param array $markingOneIDs IDs of the markings to filter
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasMarkinOne(array $markingOneIDs):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasMarkingOne", [
			"markingOne" => $markingOneIDs
		]);
	}

	/**
	 * Filter items by flag 2
	 * @param array $markingTwoIDs IDs of the markings to filter
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasMarginTwo(array $markingTwoIDs):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasMarkingTwo", [
			"markingTwo" => $markingTwoIDs
		]);
	}

	/**
	 * Filter items by manufacturers. Do not pass the manufacturer ID to filter items
	 * not having a manufacturer.
	 * @param array $producerIDs The producers to filter
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasProducer( $producerIDs = null):ItemFilterBuilder
	{
		if($producerIDs === null)
		{
			return $this->hasFilter("itemBase.hasProducer?");
		}
		else
		{
			return $this->hasFilter("itemBase.hasProducer", [
				"producer" => $producerIDs
			]);
		}
	}

	/**
	 * Filter items not having a manufacturer
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notHasProducer():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.hasNoProducer?");
	}

	/**
	 * Filter bundle items having components
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isBundle():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isBundle?");
	}

	/**
	 * Deny bundle items
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notIsBundle():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isNotABundle?");
	}

	/**
	 * Filter bundle components
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isBundleItem():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isBundleItem?");
	}

	/**
	 * Filter items with type 'ColliItem'
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isColli():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isColli?");
	}

	/**
	 * Filter items with type 'ProductionItem'
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isProductionItem():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isProductionItem?");
	}

	/**
	 * Filter items with type 'DeliveryItem'
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isSpecialOrderItem():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isSpecialOrderItem?");
	}

	/**
	 * Filter items with type 'StockedItem'
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isStockedItem():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isStockedItem?");
	}

	/**
	 * Filter items by type
	 * @param array $typeIDs the type IDs
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isType(array $typeIDs):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isType", [
			"type" => $typeIDs
		]);
	}

	/**
	 * Filter items by store special
	 * @param array $shopActions IDs of allowed shop actions
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function isStoreSpecial(array $shopActions):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.isStoreSpecial", [
			"shopActions" => $shopActions
		]);
	}

	/**
	 * Filter items which can be shipped via Amazon FBA
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function mayBeShippedWithAmazonFBA():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.mayBeShippedWithAmazonFBA?");
	}

	/**
	 * Filter items which cannot be shipped via Amazon FBA
	 */
	public function notMayBeShippedWithAmazonFBA():ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.mayNotBeShippedWithAmazonFBA?");
	}

	/**
	 * Filter items by last changes on item data
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function wasUpdatedBetween(string $from, string $to):ItemFilterBuilder
	{
		return $this->hasFilter("itemBase.wasUpdatedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to
		]);
	}

	/**
	 * Filter itmes containing search string in description
	 * @param string $search string to search in item description
	 * @param bool $browse use extended search index
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function descriptionContains(string $search, bool $browse):ItemFilterBuilder
	{
		return $this->hasFilter("itemDescription.contains", [
			"searchString"      => $search,
			"browseDescription" => $browse
		]);
	}

	/**
	 * Filter items having a description in a specific language
	 * @param Language $lang the language to use
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasDescription(Language $lang):ItemFilterBuilder
	{
		return $this->hasFilter("itemDescription.hasDescription", [
			"language" => $lang
		]);
	}

	/**
	 * Filter items not having a description in a specific language
	 * @param Language $lang the language to use
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notHasDescription(Language $lang):ItemFilterBuilder
	{
		return $this->hasFilter("itemDescription.doesNotHaveDescription", [
			"language" => $lang
		]);
	}

	/**
	 * Filter items linked to a specific listing
	 * @param string $auctionType Type of listing
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasListing(string $auctionType):ItemFilterBuilder
	{
		return $this->hasFilter("itemListing.hasListing", [
			"auctionType" => $auctionType
		]);
	}

	/**
	 * Filter items not linked to a listing
	 * @param string $auctionType Tyoe of listing
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function notHasListing(string $auctionType):ItemFilterBuilder
	{
		return $this->hasFilter("itemListing.hasNoListing", [
			"auctionType" => $auctionType
		]);
	}

	/**
	 * Filter variations with attributes
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasAttributes():ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasAttributes?");
	}

	/**
	 * Filter variations not having an attribute
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationNotHasAttributes():ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasNoAttributes?");
	}

	/**
	 * Filter variations linked to specific attribute values
	 * @param array $attributes A Map of attributes: attributeID => attributeValue
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasAttributeValue(array $attributes):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasAttributeValue", [
			"attributes" => $attributes
		]);
	}

	/**
	 * Filter variations by attribute value sets
	 * @param array $attributeValueSetIDs IDs of attribute value sets
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationhasAttributeValueSets(array $attributeValueSetIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasAttributeValueSetId", [
			"attributeValueSetId" => $attributeValueSetIDs
		]);
	}

	/**
	 * Filter variations by availability
	 * @param array $availabilityIDs The availability IDs
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasAvailability(array $availabilityIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasAvailability", [
			"availability" => $availabilityIDs
		]);
	}

	public function variationHasBarcode(string $barcode):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasBarcode", [
			"barcode" => $barcode
		]);
	}

	public function variationHasCustomNumber(string $customNumber, bool $fuzzy = false):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasCustomNumber", [
			"customNumber" => $customNumber,
			"fuzzy"        => $fuzzy
		]);
	}

	/**
	 * Filter variations by external variation ID.
	 * @param string $externalId The external ID to filter.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasExternalId(string $externalId):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasExternalId", [
			"externalId" => $externalId
		]);
	}

	/**
	 * Filter variations by variation ID
	 * @param array $variationIDs The variation IDs
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasId(array $variationIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasId", [
			"id" => $variationIDs
		]);
	}

	/**
	 * Filter variations by default warehouse ID
	 * @param array $warehouseIDs The warehouse IDs
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasMainWarehouse(array $warehouseIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasMainWarehouse", [
			"warehouseId" => $warehouseIDs
		]);
	}

	/**
	 * Filter variations by SKU
	 * @param string $itemId The Item ID of the SKU
	 * @param string $priceId The price ID of the SKU
	 * @param string $attributeValueSetId The attribute value set id of the SKU
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasSKU(string $itemId, string $priceId, string $attributeValueSetId):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasSKU", [
			"itemId"              => $itemId,
			"priceId"             => $priceId,
			"attributeValueSetId" => $attributeValueSetId
		]);
	}

	/**
	 * Filter variations by stock limitations
	 * @param array $stockLimitation Allowed stock limitations:
	 *                   0 -> No stock limitation
	 *                   1 -> limit net stock
	 *                   2 -> allow oversold
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasStockLimitation(array $stockLimitation):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasStockLimitation", [
			"stockLimitation" => $stockLimitation
		]);
	}

	/**
	 * Filter variations by unit combination
	 * @param int $unitCombinationId The unit combination id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function hasUnitCombinationId(int $unitCombinationId):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.hasUnitCombinationId", [
			"unitCombinationId" => $unitCombinationId
		]);
	}

	/**
	 * Filter active variations
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationIsActive():ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.isActive?");
	}

	/**
	 * Filter inactive variations
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationNotIsActive():ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.isNotActive?");
	}

	/**
	 * Exclude child variations
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationIsPrimary():ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.isPrimary?");
	}

	/**
	 * Exclude the primary variation
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationIsChild():ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.isChild?");
	}

	/**
	 * If an item has variations with attributes, exclude variations without attributes.
	 * Otherwise, return all variations without attributes.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function onlyBasicVariations():ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.onlyBasicVariations?");
	}

	/**
	 * Filter variation by date 'available until'
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationWasAvailableUntilBetween(string $from, string $to):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.wasAvailableUntilBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to
		]);
	}

	/**
	 * Filter variation by creation date
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationWasCreatedBetween(string $from, string $to):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.wasCreatedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to
		]);
	}

	/**
	 * Filter variation by date of last changes on variation information, e.g. availability
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationWasUpdatedBetween(string $from, string $to):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.wasUpdatedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to
		]);
	}

	/**
	 * Filter variation by date of last changes on related data, e.g. category, bundle etc.
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationWasRelatedUpdatedBetween(string $from, string $to):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.wasRelatedUpdatedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to
		]);
	}

	/**
	 * Filter variations by release date
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationWasReleasedBetween(string $from, string $to):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.wasReleasedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to
		]);
	}

	/**
	 * Filter variations by gross weight
	 * @param int $weightG Weight in gramm
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasWeight(int $weightG):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.weightG", [
			"weightG" => $weightG
		]);
	}

	/**
	 * Filter variations by net weight
	 * @param int $weightG Weight in gramm
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasWeighNet(int $weight):ItemFilterBuilder
	{
		return $this->hasFilter("variationBase.weightNetG", [
			"weightG" => $weight
		]);
	}

	/**
	 * Filter variations linked to a specific category. Do not pass any category
	 * to filter variations linked to any categories.
	 * @param int $categoryID The category ID
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasCategory($categoryId = null):ItemFilterBuilder
	{
		if($categoryId === null)
		{
			return $this->hasFilter("variationCategory.hasCategory?");
		}
		else
		{
			return $this->hasFilter("variationCategory.hasCategory", [
				"categoryId" => $categoryId
			]);
		}
	}

	/**
	 * Filter variations not linked to a category
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationNotHasCategory():ItemFilterBuilder
	{
		return $this->hasFilter("variationCategory.hasNoCategory?");
	}

	/**
	 * Filter variations linked to a specific category level
	 * @param int $lvl1 Category ID at first layer
	 * @param int ?lvl2 Category ID at second layer
	 * @param int ?lvl3 Category ID at third layer
	 * @param int ?lvl4 Category ID at 4th layer
	 * @param int ?lvl5 Category ID at 5th layer
	 * @param int ?lvl6 Category ID at 6th layer
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasCategoryBranch(int $lvl1, int $lvl2 = 0, int $lvl3 = 0, int $lvl4 = 0, int $lvl5 = 0, int $lvl6 = 0):ItemFilterBuilder
	{
		return $this->hasFilter("variationCategory.hasCategoryBranch", [
			"category1" => $lvl1,
			"category2" => $lvl2,
			"category3" => $lvl3,
			"category4" => $lvl4,
			"category5" => $lvl5,
			"category6" => $lvl6
		]);
	}

	/**
	 * Filter variations by additional content for specific markets
	 * @param string $additionalInformation
	 * @param float $marketplaceId Referred marketplace id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationMarketHasAdditionalInformation(string $additionalInformation, float $marketplaceId):ItemFilterBuilder
	{
		return $this->hasFilter("variationMarketStatus.hasAdditionalInformation", [
			"additionalInformation" => $additionalInformation,
			"marketplace"           => $marketplaceId
		]);
	}

	/**
	 * Filter variations by date of first export to specific markets
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @param float $marketplaceId Referred marketplace id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationMarketWasFirstExportedBetween(string $from, string $to, float $marketplaceId):ItemFilterBuilder
	{
		return $this->hasFilter("variationMarketStatus.wasFirstExportedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to,
			"marketplace"   => $marketplaceId
		]);
	}

	/**
	 * Filter variations by date of last export to specific markets
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @param float $marketplaceId Referred marketplace idv
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationMarketWasLastExportedBetween(string $from, string $to, float $marketplaceId):ItemFilterBuilder
	{
		return $this->hasFilter("variationMarketStatus.wasLastExportedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to,
			"marketplace"   => $marketplaceId
		]);
	}

	/**
	 * Filter variations by specific shipping profiles
	 * @param array $shippingProfileIDs List of shipping profile IDs to filter.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasShippingProfile(array $shippingProfileIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationShipping.hasShippingProfile", [
			"parcelServicePresetId" => $shippingProfileIDs
		]);
	}

	/**
	 * Filter variations without specific shipping profiles
	 * @param array $shippingProfileIDs List of shipping profile IDs to deny.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationNotHasShippingProfile(array $shippingProfileIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationShipping.doesNotHaveShippingProfile", [
			"parcelServicePresetId" => $shippingProfileIDs
		]);
	}

	/**
	 * Filter variations by warehouse
	 * @param mixed $warehouseIDs "primary", "virtual" or the warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasWarehouse($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.hasWarehouse", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations which are for sale because of stock
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockIsSalable():ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.isSalable?");
	}

	/**
	 * Filter variations by net stock
	 * @param mixed $warehouseId "primary, "virtual" or the warehouse id
	 * @param float $stockConditionOperand stock value to filters
	 * @param string $stockConditionOperator the operator to compare stock value
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasNet($warehouseId, float $stockConditionOperand, string $stockConditionOperator):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.net", [
			"warehouse"              => $warehouseId,
			"stockConditionOperand"  => $stockConditionOperand,
			"stockConditionOperator" => $stockConditionOperator
		]);
	}

	/**
	 * Filter variations with negative net stock
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasNetNegative($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.netNegative", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations with positive net stock
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasNetPositive($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.netPositive", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations with net stock value of 0
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasNetEqualZero($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.netNegativeEqual0", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations with phyiscal stock
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @param float $stockConditionOperand stock value to filters
	 * @param string $stockConditionOperator the operator to compare stock value
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasPhysical($warehouseId, float $stockConditionOperand, string $stockConditionOperator):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.physical", [
			"warehouse"              => $warehouseId,
			"stockConditionOperand"  => $stockConditionOperand,
			"stockConditionOperator" => $stockConditionOperator
		]);
	}

	/**
	 * Filter variations with negative physical stock
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasPhysicalNegative($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.physicalNegative", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations with positive physical stock
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasPhysicalPositive($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.physicalPositive", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations with physical stock value of 0
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasPhysicalEqualZero($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.physicalNegativeEqual0", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations by reorder level
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @param float $stockConditionOperand stock value to filters
	 * @param string $stockConditionOperator the operator to compare stock value
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockHasReorderLevel($warehouseId, float $stockConditionOperand, string $stockConditionOperator):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.reorderLevel", [
			"warehouse"              => $warehouseId,
			"stockConditionOperand"  => $stockConditionOperand,
			"stockConditionOperator" => $stockConditionOperator
		]);
	}

	/**
	 * Filter variations fallen below reorder level
	 * @param mixed $warehouseId "primary", "virtual" or warehouse id
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockIsReorderLevelReached($warehouseId):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.reorderLevelReached", [
			"warehouse" => $warehouseId
		]);
	}

	/**
	 * Filter variations by date of last changes to stock
	 * @param string $from The start datetime of the period in unix format
	 * @param string $to The end datetime of the period in unix format
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationStockWasUpdatedBetween(string $from, string $to):ItemFilterBuilder
	{
		return $this->hasFilter("variationStock.wasUpdatedBetween", [
			"timestampFrom" => $from,
			"timestampTo"   => $to
		]);
	}

	/**
	 * Filter variations by specific supplier
	 * @param array $supplierIDs Supplier IDs to filter.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasSupplier(array $supplierIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationSupplier.hasSupplier", [
			"supplierId" => $supplierIDs
		]);
	}

	/**
	 * Filter variations not having a specific supplier
	 * @param array $supplierIDs Supplier IDs to deny.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationNotHasSupplier(array $supplierIDs):ItemFilterBuilder
	{
		return $this->hasFilter("variationSupplier.doesNotHaveSupplier", [
			"supplierId" => $supplierIDs
		]);
	}

	/**
	 * Filter variations by supplier number
	 * @param int $supplierNumber the supplier number
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationHasSupplierNumber(int $supplierNumber):ItemFilterBuilder
	{
		return $this->hasFilter("variationSupplier.hasSupplierNumber", [
			"supplierNumber" => $supplierNumber
		]);
	}

	/**
	 * Filter items which are visible for specific markets
	 * @param array $mandatoryAllMarketplace All listed marketplaces have to be linked with filtered item.
	 * @param array $mandatoryOneMarketplace At least one marketplace have to be linked with filtered item.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationIsVisibleForMarketplace(array $mandatoryAllMarketplace, array $mandatoryOneMarketplace):ItemFilterBuilder
	{
		return $this->hasFilter("variationVisibility.isVisibleForMarketplace", [
			"mandatoryAllMarketplace" => $mandatoryAllMarketplace,
			"mandatoryOneMarketplace" => $mandatoryOneMarketplace
		]);
	}

	/**
	 * Filter items which are not visible for specific markets
	 * @param array $mandatoryAllMarketplace All listed marketplaces have to be linked with filtered item.
	 * @param array $mandatoryOneMarketplace At least one marketplace have to be linked with filtered item.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationNotIsVisibleForMarketplace(array $mandatoryAllMarketplace, array $mandatoryOneMarketplace):ItemFilterBuilder
	{
		return $this->hasFilter("variationVisibility.isNotVisibleForMarketplace", [
			"mandatoryAllMarketplace" => $mandatoryAllMarketplace,
			"mandatoryOneMarketplace" => $mandatoryOneMarketplace
		]);
	}

	/**
	 * Filter items which are visible for specific client (store)
	 * @param array $mandatoryAllPlentyId All listed shops have to be linked with filtered item.
	 * @param array $mandatoryOnePlentyId At least one shop has to be linked with filtered item.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationIsVisibleForPlentyId(array $mandatoryAllPlentyId, array $mandatoryOnePlentyId):ItemFilterBuilder
	{
		return $this->hasFilter("variationVisibility.isVisibleForPlentyId", [
			"mandatoryAllPlentyId" => $mandatoryAllPlentyId,
			"mandatoryOnePlentyId" => $mandatoryOnePlentyId
		]);
	}

	/**
	 * Filter items which are not visible for specific client (store)
	 * @param array $mandatoryAllPlentyId All listed shops have to be linked with filtered item.
	 * @param array $mandatoryOnePlentyId At least one shop has to be linked with filtered item.
	 * @return ItemFilterBuilder the current instance of the builder
	 */
	public function variationNotIsVisibleForPlentyId(array $mandatoryAllPlentyId, array $mandatoryOnePlentyId):ItemFilterBuilder
	{
		return $this->hasFilter("variationVisibility.isNotVisibleForPlentyId", [
			"mandatoryAllPlentyId" => $mandatoryAllPlentyId,
			"mandatoryOnePlentyId" => $mandatoryOnePlentyId
		]);
	}
}
