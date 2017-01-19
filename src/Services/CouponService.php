<?php //strict

namespace IO\Services;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

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
        return $basket->save(['couponCode' => $couponCode]);
    }
    
    public function removeCoupon()
    {
        //TODO
    }
}