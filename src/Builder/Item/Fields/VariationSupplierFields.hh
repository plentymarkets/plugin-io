<?hh //strict

namespace LayoutCore\Builder\Item\Fields;

enum VariationSupplierFields:string {
    CREATED_TIMESTAMP       = "createdTimestamp";
    DELIVERY_TIME_IN_DAYS   = "deliveryTimeInDays";
    DISCOUNT                = "discount";
    DISCOUNTABLE            = "discountable";
    ID                      = "id";
    ITEM_NUMBER             = "itemNumber";
    ITEM_VARIATION_ID       = "itemVariationId";
    LAST_PRICE_QUERY        = "lastPriceQuery";
    LAST_UPDATE_TIMESTAMP   = "lastUpdateTimestamp";
    MINIMUM_ORDER_VALUE     = "minimumOrderValue";
    PACKAGING_UNIT          = "packagingUnit";
    PURCHASE_PRICE          = "purchasePrice";
    SUPPLIER_ID             = "supplierId";
}
