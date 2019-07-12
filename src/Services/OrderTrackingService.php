<?php

namespace IO\Services;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelService;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderTrackingService
 * @package IO\Services
 */
class OrderTrackingService
{
    use Loggable;
    
    /** @var OrderRepositoryContract */
    private $orderRepo;

    /** @var ParcelServicePresetRepositoryContract */
    private $parcelServicePresetRepo;

    /**
     * OrderTrackingService constructor.
     * @param OrderRepositoryContract $orderRepo
     * @param ParcelServicePresetRepositoryContract $parcelServicePresetRepo
     */
    public function __construct(OrderRepositoryContract $orderRepo, ParcelServicePresetRepositoryContract $parcelServicePresetRepo)
    {
        $this->orderRepo = $orderRepo;
        $this->parcelServicePresetRepo = $parcelServicePresetRepo;
    }
    
    /**
     * @param Order $order
     * @param string $lang
     * @return string
     */
    public function getTrackingURL(Order $order, $lang)
    {
        $trackingURL = '';

        try
        {
            $shippingProfile = $this->parcelServicePresetRepo->getPresetById( $order->shippingProfileId );
            $parcelService = $shippingProfile->parcelService;
            if($parcelService instanceof ParcelService)
            {
                /** @var OrderRepositoryContract $orderRepo */
                $orderRepo = pluginApp(OrderRepositoryContract::class);
                $packageNumber = implode(',', $orderRepo->getPackageNumbers($order->id));

                if(strlen($packageNumber))
                {
                    $trackingURL = $parcelService->trackingUrl;


                    $zip = $order->deliveryAddress->postalCode;

                    if(strlen($trackingURL) && strlen($packageNumber))
                    {
                        $trackingURL = str_replace('[PaketNr]',
                                                   $packageNumber,
                                                   str_replace('[PLZ]',
                                                               $zip,
                                                               str_replace('[Lang]',
                                                                           $lang,
                                                                           $trackingURL)));

                        $trackingURL = str_replace('$PaketNr',
                                                   $packageNumber,
                                                   str_replace('$PLZ',
                                                               $zip,
                                                               str_replace('$Lang',
                                                                           $lang,
                                                                           $trackingURL)));
                    }
                }
            }
        }
        catch (\Exception $e)
        {
            $this->getLogger(__CLASS__)->error("IO::Debug.OrderTrackingService_getTrackingURL", [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }

        return $trackingURL;
    }
}
