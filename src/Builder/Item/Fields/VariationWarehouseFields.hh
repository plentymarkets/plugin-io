<?hh //strict

namespace LayoutCore\Builder\Item\Fields;

enum VariationWarehouseFields:string {
    VARIATION_ID            = "variationId";
    WAREHOUSE_ID            = "warehouseId";
    WAREHOUSE_ZONE_ID       = "warehouseZoneId";
    STORAGE_LOCATION_TYPE   = "storageLocationType";
    REORDER_LEVEL           = "reorderLevel";
    MAXIMUM_STOCK           = "maximumStock";
    STOCK_BUFFER            = "stockBuffer";
    STOCK_TURNOVER_IN_DAYS  = "stockTurnoverInDays";
    STORAGE_LOCATION_ID     = "storageLocationId";
    LAST_UPDATE_TIMESTAMP   = "lastUpdateTimestamp";
    CREATED_TIMESTAMP       = "createdTimestamp";
}
