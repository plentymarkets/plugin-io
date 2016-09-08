<?hh //strict

namespace LayoutCore\Builder\Item\Fields;

enum VariationBaseFields:string {
    ACTIVE                          = "active";
    ATTRIBUTE_VALUE_SET_ID          = "attributeValueSetId";
    AUTO_STOCK_INVISIBLE            = "autoStockInvisible";
    BUNDLE_TYPE                     = "bundleType";
    AUTO_STOCK_NO_STOCK_ICON        = "autoStockNoStockIcon";
    AUTO_STOCK_POSITIVE_STOCK_ICON  = "autoStockPositiveStockIcon";
    AUTO_STOCK_VISIBLE              = "autoStockVisible";
    AVAILABILITY                    = "availability";
    AVERAGE_ORDER_QUANTITY          = "averageOrderQuantity";
    CONTENT                         = "content";
    UNIT_COMBINATION_ID             = "unitCombinationId";
    CREATED_TIMESTAMP               = "createdTimestamp";
    CUSTOM_NUMBER                   = "customNumber";
    ESTIMATED_AVAILABILITY          = "estimatedAvailability";
    EXTERNAL_ID                     = "externalId";
    EXTRA_SHIPPING_CHARGE_1         = "extraShippingCharge1";
    EXTRA_SHIPPING_CHARGE_2         = "extraShippingCharge2";
    HEIGHT_MM                       = "heightMm";
    ID                              = "id";
    ITEM_ID                         = "itemId";
    LAST_UPDATE_TIMESTAMP           = "lastUpdateTimestamp";
    LIMIT_ORDER_BY_STOCK_SELECT     = "limitOrderByStockSelect";
    MAIN_WAREHOUSE                  = "mainWarehouse";
    MODEL                           = "model";
    OPERATING_COSTS_PERCENT         = "operatingCostsPercent";
    PACKING_UNITS                   = "packingUnits";
    PACKING_UNIT_TYPE               = "packingUnitType";
    PARENT_VARIATION_ID             = "parentVariationId";
    PARENT_ITEM_VARIATION_QUANTITY  = "parentItemVariationQuantity";
    PICKING                         = "picking";
    CUSTOMS_PERCENT                 = "customsPercent";
    POSITION                        = "position";
    PRICE_CALCULATION_ID            = "priceCalculationId";
    PRIMARY_VARIATION               = "primaryVariation";
    PRIMARY_VARIATION_ID            = "primaryVariationId";
    PURCHASE_PRICE                  = "purchasePrice";
    STORAGE_COSTS                   = "storageCosts";
    TRANSPORTATION_COSTS            = "transportationCosts";
    UNIT_ID                         = "unitId";
    UNIT_LOAD_DEVICE                = "unitLoadDevice";
    UNITS_CONTAINED                 = "unitsContained";
    VAT_ID                          = "vatId";
    VARIATION_NAME                  = "variationName";
    WEIGHT_G                        = "weightG";
    WEIGHT_NET_G                    = "weightNetG";
    MAXIMUM_ORDER_QUANTITY          = "maximumOrderQuantity";
    MINIMUM_ORDER_QUANTITY          = "minimumOrderQuantity";
    INTERVAL_ORDER_QUANTITY         = "intervalOrderQuantity";
    AVAILABLE_UNTIL                 = "availableUnti";
    RELEASE_DATE                    = "releaseDate";
    WIDTH_MM                        = "widthMm";
    LENGTH_MM                       = "lengthMm";
}
