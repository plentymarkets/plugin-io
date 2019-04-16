<?php //strict

namespace IO\Services;

use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelServicePreset;

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
    
    /**
     * Return an array of shipping profile ids as keys and max delivery days as values
     * @return array
     */
	public function getMaxDeliveryDays()
    {
        $maxDeliveryDays = [];
        
        /** @var CheckoutService $checkoutService */
        $checkoutService = pluginApp(CheckoutService::class);
        $shippingProfileList = $checkoutService->getShippingProfileList();
        
        if(count($shippingProfileList))
        {
            $currentShippingCountryId = $checkoutService->getShippingCountryId();
            /** @var CountryRepositoryContract $countryRepo */
            $countryRepo = pluginApp(CountryRepositoryContract::class);
            /** @var Country $country */
            $country = $countryRepo->getCountryById($currentShippingCountryId);
    
            /** @var ParcelServicePresetRepositoryContract $parcelServicepresetRepo */
            $parcelServicepresetRepo = pluginApp(ParcelServicePresetRepositoryContract::class);
    
            foreach($shippingProfileList as $shippingProfile)
            {
                $parcelServicePreset = $parcelServicepresetRepo->getPresetById($shippingProfile['parcelServicePresetId']);
        
                if($parcelServicePreset instanceof ParcelServicePreset)
                {
                    $generalSettings = $parcelServicePreset->parcelServiceRegionConstraint->where('shippingRegionId', $country->shippingDestinationId)->first()->constraint->first()->generalSettings;
                    if(isset($generalSettings['termOfDelivery']) && !is_null($generalSettings['termOfDelivery']) && $generalSettings['termOfDelivery'] !== '')
                    {
                        $maxDeliveryDays[$shippingProfile['parcelServicePresetId']] = $generalSettings['termOfDelivery'];
                    }
                }
            }
            
            if(count($maxDeliveryDays))
            {
                /** @var BasketService $basketService */
                $basketService = pluginApp(BasketService::class);
                $basketItems = $basketService->getBasketItems();
    
                $maxItemAvailability = 0;
                if(count($basketItems))
                {
                    foreach($basketItems as $basketItem)
                    {
                        $itemAvailability = $basketItem['variation']['data']['variation']['availability']['averageDays'];
                        if((int)$itemAvailability > 0 && (int)$itemAvailability > $maxItemAvailability)
                        {
                            $maxItemAvailability = $itemAvailability;
                        }
                    }
                }
                
                if($maxItemAvailability > 0)
                {
                    $maxDeliveryDays = array_map(function($days) use ($maxItemAvailability) {
                        return (int)$days + (int)$maxItemAvailability;
                    }, $maxDeliveryDays);
                }
            }
        }
        
        return $maxDeliveryDays;
    }
}
