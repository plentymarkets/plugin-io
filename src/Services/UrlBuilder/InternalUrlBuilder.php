<?php

namespace IO\Services\UrlBuilder;

use IO\Constants\SessionStorageKeys;
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
        return "#";
    }
}
