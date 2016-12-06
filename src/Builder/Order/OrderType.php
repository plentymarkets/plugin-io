<?php // strict

namespace IO\Builder\Order;

/**
 * Class OrderType
 * @package IO\Builder\Order
 */
class OrderType
{
	const ORDER              = 1;
	const DELIVERY           = 2;
	const RETURNS            = 3;
	const CREDIT_NOTE        = 4;
	const WARRANTY           = 5;
	const REPAIR             = 6;
	const OFFER              = 7;
	const PRE_ORDER          = 8;
	const MULTI_ORDER        = 9;
	const MULTI_CREDIT_ORDER = 10;
	const REORDER            = 11;
	const PARTIAL_DELIVERY   = 12;
}
