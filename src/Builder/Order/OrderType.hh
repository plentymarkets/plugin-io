<?hh // strict

namespace LayoutCore\Builder\Order;

enum OrderType:int
{
    ORDER               = 1;
    DELIVERY            = 2;
    RETURNS             = 3;
    CREDIT_NOTE         = 4;
    WARRANTY            = 5;
    REPAIR              = 6;
    OFFER               = 7;
    PRE_ORDER           = 8;
    MULTI_ORDER         = 9;
    MULTI_CREDIT_ORDER  = 10;
    REORDER             = 11;
    PARTIAL_DELIVERY    = 12;
}
