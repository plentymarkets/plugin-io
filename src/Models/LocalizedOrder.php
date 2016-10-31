<?php

namespace LayoutCore\Models;

use LayoutCore\Helper\AbstractFactory;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Status\Models\OrderStatusName;

class LocalizedOrder extends ModelWrapper
{
    /**
     * @var Order
     */
    public $order = null;

    /**
     * @var OrderStatusName
     */
    public $status = null;

    /**
     * @param Order $order
     * @param array ...$data
     * @return LocalizedOrder
     */
    public static function wrap( $order, ...$data ):LocalizedOrder
    {
        list( $lang ) = $data;

        $statusRepository = AbstractFactory::create( \Plenty\Modules\Order\Status\Contracts\StatusRepositoryContract::class );

        $instance = AbstractFactory::create( self::class );
        $instance->order = $order;
        $instance->status = $statusRepository->findStatusNameById( $order->statusId, $lang );

        return $instance;
    }

    /**
     * @return array
     */
    public function toArray():array
    {
        return [
            "order" => $this->order->toArray(),
            "status" => $this->status->toArray()
        ];
    }
}