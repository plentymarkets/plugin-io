<?hh //strict

namespace LayoutCore\Builder\Item\Fields;

enum VariationStockFields:string {
    WAREHOUSE_ID                = "warehouseId";
    STOCK_PHYSICAL              = "stockPhysical";
    RESERVED_STOCK              = "reservedStock";
    RESERVED_EBAY               = "reservedEbay";
    REORDER_DELTA               = "reorderDelta";
    STOCK_NET                   = "stockNet";
    UPDATE_AMAZON               = "updateAmazon";
    UPDATE_PIXMANIA             = "updatePixmania";
    WAREHOUSE_TYPE              = "warehouseType";
    REORDERED                   = "reordered";
    RESERVED_BUNDLE             = "reservedBundle";
    AVERAGE_PURCHASE_PRICE      = "averagePurchasePrice";
    WAREHOUSE_PRIORITY          = "warehousePriority";
    LAST_UPDATE_TIMESTAMP       = "lastUpdateTimestamp";
    LAST_CALCULATE_TIMESTAMP    = "lastCalculateTimestamp";
    RESERVED_OUT_OF_STOCK       = "reservedOutOfStock";
    RESERVED_BASKET             = "reservedBasket";
}
