<?php

namespace IO\Services\UrlBuilder;

use IO\Constants\SessionStorageKeys;
use IO\Services\OrderTrackingService;
use IO\Services\SessionStorageService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;

class InternalUrlBuilder
{
    public function buildRetoureUrl($orderId)
    {
        $orderRepository = pluginApp(OrderRepositoryContract::class);
        $authHelper = pluginApp(AuthHelper::class);

        $accessKey = $authHelper->processUnguarded(
            function() use ($orderRepository, $orderId) {
                return $orderRepository->generateAccessKey($orderId);
            }
        );

        return "/returns/".$orderId."/".$accessKey."/";
    }

    public function buildTrackingUrl($orderId)
    {
        /**
         * @var OrderRepositoryContract $orderRepository
         */
        $orderRepository = pluginApp(OrderRepositoryContract::class);
        $authHelper = pluginApp(AuthHelper::class);

        $order = $authHelper->processUnguarded(
            function() use ($orderRepository, $orderId) {
                return $orderRepository->findOrderById($orderId);
            }
        );

        $orderTrackingService = pluginApp(OrderTrackingService::class);
        $sessionStorageService = pluginApp(SessionStorageService::class);
        $lang = $sessionStorageService->getLang();
        $trackingUrl = $orderTrackingService->getTrackingURL($order, $lang);

        return $trackingUrl;
    }
}
