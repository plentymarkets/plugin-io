<?php //strict

namespace IO\Services;

use Plenty\Modules\Frontend\Contracts\Checkout;

/**
 * Class ShippingService
 * @package IO\Services
 */
class ShippingService
{
	/**
	 * @var Checkout
	 */
	private $checkout;

    /**
     * ShippingService constructor.
     * @param Checkout $checkout
     */
	public function __construct(Checkout $checkout)
	{
		$this->checkout = $checkout;
	}

    /**
     * Set the ID of the current shipping profile
     * @param int $shippingProfileId
     */
	public function setShippingProfileId(int $shippingProfileId)
	{
		$this->checkout->setShippingProfileId($shippingProfileId);
	}
}
