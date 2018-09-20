<?php

namespace IO\Api\Resources;

use IO\Services\SubscriptionService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\OrderService;


/**
 * Class SubscriptionResource.php
 * @package IO\Api\Resources
 */
class SubscriptionResource extends ApiResource
{
    /**
     * OrderResource constructor.
     * @param Request     $request
     * @param ApiResponse $response
     */
    public function __construct(
        Request $request,
        ApiResponse $response
    ) {
        parent::__construct($request, $response);
    }

    /**
     * Set the end date for a subscription order
     *
     * @param string $orderId
     * @return \Plenty\Plugin\Http\Response
     */
    public function update(string $orderId): Response
    {
        $orderId = (int) $orderId;
        $order   = pluginApp(SubscriptionService::class)->setEndDate($orderId);
        return $this->response->create($order, ResponseCode::OK);
    }

    /**
     * Set the cancelled on date for a subscription order
     *
     * @param string $orderId
     * @return \Plenty\Plugin\Http\Response
     */
    public function destroy(string $orderId): Response
    {
        $orderId = (int) $orderId;
        $order   = pluginApp(SubscriptionService::class)->cancelSubscription($orderId);
        return $this->response->create($order, ResponseCode::OK);
    }
}