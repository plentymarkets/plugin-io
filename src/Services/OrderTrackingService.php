<?php

namespace IO\Services;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Modules\Order\Shipping\ParcelService\Models\ParcelService;
use Plenty\Plugin\Log\Loggable;

/**
 * Service Class OrderTrackingService
 *
 * This service class contains functionality related to order tracking.
 * All public functions are available in the Twig template renderer.
 *
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
     * Get the tracking URL for a specific order
     * @param Order $order The order to get the tracking URL for
     * @param string $lang The desired language of the tracking URL (ISO-639-1)
     * @return string
     */
    public function getTrackingURL(Order $order, $lang)
    {
        $trackingURL = '';

        try {
            $shippingProfile = $this->parcelServicePresetRepo->getPresetById($order->shippingProfileId);
            $parcelService = $shippingProfile->parcelService;
            if ($parcelService instanceof ParcelService) {
                /** @var OrderRepositoryContract $orderRepo */
                $orderRepo = pluginApp(OrderRepositoryContract::class);
                $packageNumber = implode(',', $orderRepo->getPackageNumbers($order->id));

                $trackingURL = $this->buildTrackingUrl($order, $parcelService->trackingUrl, $packageNumber, $lang);
            }
        } catch (\Exception $e) {
            $this->getLogger(__CLASS__)->error("IO::Debug.OrderTrackingService_getTrackingURL", [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }

        return $trackingURL;
    }

    /**
     * Get list of tracking URLs for a specific order
     * @param Order $order The order to get the tracking URL for
     * @param string $lang The desired language of the tracking URL (ISO-639-1)
     * @return array
     */
    public function getTrackingURLs(Order $order, $lang): array
    {
        $trackingURLs = [];

        try {
            $shippingProfile = $this->parcelServicePresetRepo->getPresetById($order->shippingProfileId);
            $parcelService = $shippingProfile->parcelService;
            if ($parcelService instanceof ParcelService) {
                /** @var OrderRepositoryContract $orderRepo */
                $orderRepo = pluginApp(OrderRepositoryContract::class);
                $packageNumbers = $orderRepo->getPackageNumbers($order->id);

                $splitUrls = $parcelService->toArray()['splitTrackingUrl'] ?? false; // Direct property access doesn't work for some reason
                $delimiter = $parcelService->toArray()['splitDelimiter'] ?? null; // Direct property access doesn't work for some reason
                if (!$delimiter) {
                    $delimiter = ',';
                }

                $trackingURL = $parcelService->trackingUrl;

                if ($splitUrls) {
                    foreach ($packageNumbers as $packageNo) {
                        $trackingURLs[] = $this->buildTrackingUrl($order, $trackingURL, $packageNo, $lang);
                    }
                } else {
                    $packageNumber = implode($delimiter, $packageNumbers);

                    $trackingURLs[] = $this->buildTrackingUrl($order, $trackingURL, $packageNumber, $lang);
                }
            }
        } catch (\Exception $e) {
            $this->getLogger(__CLASS__)->error("IO::Debug.OrderTrackingService_getTrackingURL", [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
        }

        return $trackingURLs;
    }

    /**
     * @param Order $order
     * @param string $trackingURL
     * @param string $packageNumber
     * @param string $lang
     * @return string
     */
    private function buildTrackingUrl(Order $order, string $trackingURL, string $packageNumber, string $lang): string
    {
        $zip = $order->deliveryAddress->postalCode;

        if (!strlen($trackingURL) || !strlen($packageNumber)) {
            return '';
        }

        return str_replace(
            ['[PaketNr]', '$PaketNr', '[PLZ]', '$PLZ', '[Lang]', '$Lang'],
            [urlencode($packageNumber), urlencode($packageNumber), urlencode($zip), urlencode($zip), $lang, $lang],
            $trackingURL
        );
    }
}
