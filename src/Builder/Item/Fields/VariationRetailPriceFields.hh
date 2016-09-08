<?hh //strict

namespace LayoutCore\Builder\Item\Fields;

enum VariationRetailPriceFields:string {
    PRICE_ID                = "priceId";
    PRICE                   = "price";
    RETAIL_PRICE            = "retailPrice";
    RETAIL_PRICE_NET        = "retailPriceNet";
    BASE_PRICE              = "basePrice";
    BASE_PRICE_NET          = "basePriceNet";
    UNIT_PRICE              = "unitPrice";
    UNIT_PRICE_NET          = "unitPriceNet";
    ORDER_PARAMS_MARKUP     = "orderParamsMarkup";
    ORDER_PARAMS_MARKUP_NET = "orderParamsMarkupNet";
    CLASS_REBATE_PERCENT    = "classRebatePercent";
    CLASS_REBATE            = "classRebate";
    CLASS_REBATE_NET        = "classRebateNet";
    CATEGORY_REBATE_PERCENT = "categoryRebatePercent";
    CATEGORY_REBATE         = "categoryRebate";
    CATEGORY_REBATE_NET     = "categoryRebateNet";
    VAT_ID                  = "vatId";
    VAT_VALUE               = "vatValue";
    CURRENCY                = "currency";
    EXCHANGE_RATION         = "exchangeRatio";
}
