<?php //strict

namespace IO\Builder\Order;

/**
 * Class OrderItemType
 * @package IO\Builder\Order
 */
class OrderItemType
{
	const VARIATION            = 1;
	const ITEM_BUNDLE          = 2;
	const BUNDLE_COMPONENT     = 3;
	const PROMOTIONAL_COUPON   = 4;
	const GIFT_CARD            = 5;
	const SHIPPING_COSTS       = 6;
	const PAYMENT_SURCHARGE    = 7;
	const GIFT_WRAP            = 8;
	const UNASSIGNED_VARIATION = 9;
	const DEPOSIT              = 10;
	const ORDER                = 11;
}
