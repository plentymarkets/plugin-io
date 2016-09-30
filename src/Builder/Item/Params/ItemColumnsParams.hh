<?hh //strict

namespace LayoutCore\Builder\Item\Params;

enum ItemColumnsParams:string {
    TYPE            = "type";
    LANGUAGE        = "language";
    BARCODE_TYPE    = "barcodeType";
    BARCODE_ID      = "barcodeId";
    MARKET_ID       = "marketId";
    QUANTITY        = "quantity";
    PLENTY_ID       = "plentyId";
    WAREHOUSE_ID    = "warehouseId";
    ORDER_BY        = "order_by";
    LIMIT           = "limit";
    OFFSET          = "offset";
}
