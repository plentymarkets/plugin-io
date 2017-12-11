<?php //strict

namespace IO\Services;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Frontend\Coupon\Contracts\FrontendCouponRepositoryContract;

class CouponService
{
    public function __construct()
    {
        
    }
    
    public function setCoupon(string $couponCode)
    {
        /**
         * @var BasketRepositoryContract $basket
         */
        $basket = pluginApp(BasketRepositoryContract::class);
        return $basket->setCouponCode($couponCode);
    }
    
    public function removeCoupon()
    {
        /**
         * @var BasketRepositoryContract $basket
         */
        $basket = pluginApp(BasketRepositoryContract::class);
        return $basket->removeCouponCode();
    }
}