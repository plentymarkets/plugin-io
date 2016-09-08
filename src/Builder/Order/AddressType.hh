<?hh //strict

namespace LayoutCore\Builder\Order;

enum AddressType:int as int
{
    BILLING     = 1;
    DELIVERY    = 2;
    SENDER      = 3;
    RETURN      = 4;
    CLIENT      = 5;
    CONTRACTOR  = 6;
    WAREHOUSE   = 7;
}
