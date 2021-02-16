<?php

namespace IO\Constants;

/**
 * Class OrderPaymentStatus
 *
 * Collection of order payment status constants
 *
 * @package IO\Constants
 */
class OrderPaymentStatus
{
    /**
     * @var string Identifier for payment status "unpaid".
     */
    const UNPAID = 'unpaid';

    /**
     * @var string Identifier for payment status "prepaid".
     */
    const PREPAID = 'prepaid';

    /**
     * @var string Identifier for payment status "partlyPaid".
     */
    const PARTLY_PAID = 'partlyPaid';

    /**
     * @var string Identifier for payment status "fullyPaid".
     */
    const FULLY_PAID = 'fullyPaid';

    /**
     * @var string Identifier for payment status "overpaid".
     */
    const OVERPAID = 'overpaid';
}
