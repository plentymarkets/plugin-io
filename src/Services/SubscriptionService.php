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
     * @param int $orderId
     * @return \Plenty\Modules\Order\Models\Order
     */
    public function cancelSubscription(int $orderId)
    {
        return $this->orderRepository->updateOrder([
            'dates' => [
                'date' => [
                    'typeId' => OrderDateType::ORDER_END_DATE,
                    'date'   => date('Y-m-d\TH:i:sP'),
                ],
            ],
        ], $orderId);
    }
}