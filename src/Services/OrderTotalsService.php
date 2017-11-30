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
        $amountId = $this->getCustomerAmountId($order->amounts);
        $totalNet = $order->amounts[$amountId]->netTotal;
        $totalGross = $order->amounts[$amountId]->grossTotal;
        $currency = $order->amounts[$amountId]->currency;

        $orderItems = $order->orderItems;
        foreach ($orderItems as $item) {
            $itemAmountId = $this->getCustomerAmountId($item->amounts);
            /** @var OrderItem $item */
            $firstAmount = $item->amounts[$itemAmountId];

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

    private function getCustomerAmountId( $amounts )
    {
        foreach( $amounts as $index => $amount )
        {
            if ( !$amount->isSystemCurrency )
            {
                return $index;
            }
        }

        return 0;
    }
}