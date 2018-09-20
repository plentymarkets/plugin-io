<?php

namespace IO\Services;

use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Date\Models\OrderDateType;


/**
 * Class SubscriptionService.php
 * @package IO\Services
 */
class SubscriptionService
{
    /**
     * @var OrderRepositoryContract
     */
    private $orderRepository;

    /**
     * OrderService constructor.
     * @param OrderRepositoryContract $orderRepository
     */
    public function __construct(
        OrderRepositoryContract $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param int $orderId The orderId
     * @param string $date Date must be valid w3c formated date time string
     * @return \Plenty\Modules\Order\Models\Order
     */
    public function cancelSubscription(int $orderId, string $date = null)
    {
        return $this->orderRepository->updateOrder([
            'dates' => [
                'date' => [
                    'typeId' => OrderDateType::SUBSCRIPTION_CANCELLED_ON,
                    'date'   => $date == null ? date('Y-m-d\TH:i:sP') : $date,
                ],
            ],
        ], $orderId);
    }

    /**
     * @param int    $orderId
     * @param string $date Date must be valid w3c formated date time string
     * @return \Plenty\Modules\Order\Models\Order
     */
    public function setEndDate(int $orderId, string $date = null)
    {
        return $this->orderRepository->updateOrder([
            'dates' => [
                'date' => [
                    'typeId' => OrderDateType::ORDER_END_DATE,
                    'date'   => $date == null ? date('Y-m-d\TH:i:sP') : $date,
                ],
            ],
        ], $orderId);
    }
}