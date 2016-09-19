<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Frontend\Contracts\Checkout;

class ShippingService
{
	/**
	 * @var Checkout
	 */
	private $checkout;
	
	public function __construct(Checkout $checkout)
	{
		$this->checkout = $checkout;
	}
	
	public function setShippingProfileId(int $shippingProfileId)
	{
		$this->checkout->setShippingProfileId($shippingProfileId);
	}
}
