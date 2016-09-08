<?hh //strict

namespace LayoutCore\Builder\Order;

enum OrderItemType:int
{
    VARIATION               = 1;
    ITEM_BUNDLE             = 2;
    BUNDLE_COMPONENT        = 3;
    PROMOTIONAL_COUPON      = 4;
    GIFT_CARD               = 5;
    SHIPPING_COSTS          = 6;
    PAYMENT_SURCHARGE       = 7;
    GIFT_WRAP               = 8;
    UNASSIGNED_VARIATION    = 9;
    DEPOSIT                 = 10;
    ORDER                   = 11;
}
