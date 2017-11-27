<?php
/**
 * Created by IntelliJ IDEA.
 * User: chensink
 * Date: 27.11.17
 * Time: 11:11
 */

namespace IO\Services;

use IO\Builder\Order\OrderItemType;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderItem;

/**
 * Calculate order totals
 * @package IO\Services
 */
class OrderTotalsService
{
    /**
     * Get all order totals which are relevant for the OrderDetails-modal
     *
     * @param Order $order
     * @return array
     */
    public function getAllTotals(Order $order)
    {
        $itemSumGross = 0;
        $itemSumNet = 0;
        $shippingGross = 0;
        $shippingNet = 0;
        $vats = [];
        $couponValue = 0;
        $totalNet = $order->amounts[0]->netTotal;
        $totalGross = $order->amounts[0]->grossTotal;
        $currency = $order->amounts[0]->currency;

        $orderItems = $order->orderItems;
        foreach ($orderItems as $item) {
            /** @var OrderItem $item */
            $firstAmount = $item->amounts[0];

            switch ($item->typeId) {
                case OrderItemType::VARIATION:
                    $itemSumGross += $firstAmount->priceGross * $item->quantity;
                    $itemSumNet += $firstAmount->priceNet * $item->quantity;
                    break;
                case OrderItemType::SHIPPING_COSTS:
                    $shippingGross += $firstAmount->priceGross;
                    $shippingNet += $firstAmount->priceNet;
                    break;
                case OrderItemType::PROMOTIONAL_COUPON:
                case OrderItemType::GIFT_CARD:
                    $couponValue += $firstAmount->priceGross;
                    break;
                default:
                    // noop
            }
        }

        foreach ($order->amounts[0]->vats as $vat) {
            $vats[] = [
                'rate' => $vat->vatRate,
                'value' => $vat->value
            ];
        }

        return compact(
            'itemSumGross',
            'itemSumNet',
            'shippingGross',
            'shippingNet',
            'vats',
            'couponValue',
            'totalGross',
            'totalNet',
            'currency'
        );
    }
}