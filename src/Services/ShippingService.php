<?php //strict

namespace IO\Services;

use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelServicePreset;
use Plenty\Modules\Webshop\Contracts\ShippingRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Service Class ShippingService
 *
 * This service class contains functions related to shipping profiles.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ShippingService
{
    use Loggable;

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
     * Set the id of the current shipping profile
     * @param int $shippingProfileId A shipping profile id
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

        if (count($shippingProfileList)) {
            $currentShippingCountryId = $checkoutService->getShippingCountryId();
            /** @var CountryRepositoryContract $countryRepo */
            $countryRepo = pluginApp(CountryRepositoryContract::class);
            /** @var Country $country */
            $country = $countryRepo->getCountryById($currentShippingCountryId);

            /** @var ParcelServicePresetRepositoryContract $parcelServicepresetRepo */
            $parcelServicepresetRepo = pluginApp(ParcelServicePresetRepositoryContract::class);

            foreach ($shippingProfileList as $shippingProfile) {
                $parcelServicePreset = $parcelServicepresetRepo->getPresetById(
                    $shippingProfile['parcelServicePresetId']
                );

                if ($parcelServicePreset instanceof ParcelServicePreset) {
                    $generalSettings = [];

                    $regionConstraint = $parcelServicePreset->parcelServiceRegionConstraint->where(
                        'shippingRegionId',
                        $country->shippingDestinationId
                    )->first();
                    if (!is_null($regionConstraint)) {
                        $generalSettings = $regionConstraint->constraint->first()->generalSettings;
                    } else {
                        $this->getLogger(__CLASS__)->warning(
                            "IO::Debug.ShippingService_noShippingContraintFound",
                            [
                                'country' => $country->toArray(),
                                'shippingProfile' => $shippingProfile,
                                'parcelServicePreset' => $parcelServicePreset->toArray()
                            ]
                        );
                    }

                    if (isset($generalSettings['termOfDelivery']) && !is_null(
                            $generalSettings['termOfDelivery']
                        ) && $generalSettings['termOfDelivery'] !== '') {
                        $maxDeliveryDays[$shippingProfile['parcelServicePresetId']] = $generalSettings['termOfDelivery'];
                    }
                }
            }

            if (count($maxDeliveryDays)) {
                /** @var BasketService $basketService */
                $basketService = pluginApp(BasketService::class);
                $basketItems = $basketService->getBasketItems();

                $maxItemAvailability = 0;
                if (count($basketItems)) {
                    foreach ($basketItems as $basketItem) {
                        $itemAvailability = $basketItem['variation']['data']['variation']['availability']['averageDays'];
                        if ((int)$itemAvailability > 0 && (int)$itemAvailability > $maxItemAvailability) {
                            $maxItemAvailability = $itemAvailability;
                        }
                    }
                }

                if ($maxItemAvailability > 0) {
                    $maxDeliveryDays = array_map(
                        function ($days) use ($maxItemAvailability) {
                            return (int)$days + (int)$maxItemAvailability;
                        },
                        $maxDeliveryDays
                    );
                }
            }
        }

        return $maxDeliveryDays;
    }

    /**
     * Check if any parcel service preset exists for the current user that allows sending to a post office.
     *
     * @return boolean
     */
    public function hasAnyPostOfficePreset()
    {
        /** @var ShippingRepositoryContract $shippingRepository */
        $shippingRepository = pluginApp(ShippingRepositoryContract::class);
        return $shippingRepository->hasAnyPostOfficePreset();
    }

    /**
     * Check if any parcel service preset exists for the current user that allows sending to a parcel box.
     *
     * @return boolean
     */
    public function hasAnyParcelBoxPreset()
    {
        /** @var ShippingRepositoryContract $shippingRepository */
        $shippingRepository = pluginApp(ShippingRepositoryContract::class);
        return $shippingRepository->hasAnyParcelBoxPreset();
    }
}
