<?php //strict

namespace IO\Builder\Order;

/**
 * Class AddressType
 * @package IO\Builder\Order
 */
class AddressType
{
	const BILLING    = 1;
	const DELIVERY   = 2;
	const SENDER     = 3;
	const RETURN     = 4;
	const CLIENT     = 5;
	const CONTRACTOR = 6;
	const WAREHOUSE  = 7;
}
