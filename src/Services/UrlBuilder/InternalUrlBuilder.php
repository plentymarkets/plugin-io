<?php

namespace IO\Services\UrlBuilder;

use IO\Extensions\Constants\ShopUrls;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use IO\Helper\MemoryCache;
use Plenty\Plugin\Events\Dispatcher;

class InternalUrlBuilder
{
    use MemoryCache;

    /**
     * @var OrderRepositoryContract $orderRepository;
     */
    private $orderRepository;

    /**
     * @var AuthHelper $authHelper
     */
    private $authHelper;

    /**
     * @var ShopUrls $shopUrls
     */
    private $shopUrls;

    public function __construct(
        OrderRepositoryContract $orderRepository,
        AuthHelper $authHelper,
        ShopUrls $shopUrls,
        Dispatcher $dispatcher
    )
    {
        $this->orderRepository = $orderRepository;
        $this->authHelper = $authHelper;
        $this->shopUrls = $shopUrls;

        $this->resetMemoryCache();

        $dispatcher->listen(FrontendLanguageChanged::class, function(FrontendLanguageChanged $event)
        {
            $this->resetMemoryCache();
        });
    }

    public function buildRetoureUrl($orderId)
    {
        $orderRepository = $this->orderRepository;
        $authHelper = $this->authHelper;
        $shopUrls = $this->shopUrls;

        return $this->fromMemoryCache("returns", function() use ($orderId, $orderRepository, $authHelper, $shopUrls)
        {
            $accessKey = $authHelper->processUnguarded(
                function() use ($orderRepository, $orderId) {
                    return $orderRepository->generateAccessKey($orderId);
                }
            );

            return $shopUrls->returns($orderId, $accessKey);
        });
    }

    public function buildTrackingUrl($orderId)
    {
        $orderRepository = $this->orderRepository;
        $authHelper = $this->authHelper;
        $shopUrls = $this->shopUrls;

        return $this->fromMemoryCache("tracking", function() use ($orderId, $orderRepository, $authHelper, $shopUrls) {
            $order = $authHelper->processUnguarded(
                function() use ($orderRepository, $orderId) {
                    return $orderRepository->findOrderById($orderId);
                }
            );

            return $shopUrls->tracking($order);
        });
    }
}
