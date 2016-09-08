<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Frontend\Contracts\Checkout;

class ShippingService
{
   private Checkout $checkout;

    public function __construct( Checkout $checkout )
    {
        $this->checkout = $checkout;
    }

    public function setShippingProfileId ( int $shippingProfileId ):void
    {
        $this->checkout->setShippingProfileId( $shippingProfileId );
    }
}
