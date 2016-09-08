<?hh //strict

namespace LayoutCore\Builder\Order;

enum OrderOptionType:int
{
    WAREHOUSE          = 1;
    SHIPPING_PROFIL    = 2;
    CATEGORY           = 3;
    WEIGHT             = 4;
    WIDTH              = 5;
    LENGTH             = 6;
    HEIGHT             = 7;
    QUANTITY           = 8;
    MARKET             = 9;
    VARIANT            = 10;
    POSITION           = 11;
    TOKEN              = 12;
    METHOD_OF_PAYMENT  = 13;
    IDENTIFIER         = 14;
    DUNNING            = 15;
    ORDER	           = 16;
    DOCUMENT           = 17;
    PROPERTY           = 18;
}

enum OrderOptionSubType:int
{
    MAIN_VALUE     = 1;
	ORIGINAL_VALUE = 2;
	STATUS         = 3;
	SURCHARGE      = 4;
	TYPE           = 5;
	EXTERNAL       = 6;
	NAME           = 7;
	CONTENT        = 8;
	ACCOUNT        = 9;
	FLAG           = 10;
	LANGUAGE       = 11;
	CONSUMER       = 12;
}
