<?php

namespace IO\Services;

use IO\Builder\Order\OrderItemType;
use Plenty\Modules\Core\Data\Factories\LazyLoaderFactory;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderItem;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Property\V2\Models\Property;
use Plenty\Modules\Webshop\Helpers\NumberFormatter;

/**
 * Service Class OrderTotalsService
 *
 * This service class contains functions related to the order totals calculation.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class OrderTotalsService
{
    /**
     * Get all order totals which are relevant for the OrderDetails-modal
     * @param Order $order The order to get order totals for
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
        $couponCode = '';
        $openAmount = 0;
        $couponType = '';
        $amountId = $this->getCustomerAmountId($order->amounts);
        $totalNet = $order->amounts[$amountId]->netTotal;
        $totalGross = $order->amounts[$amountId]->grossTotal;
        $currency = $order->amounts[$amountId]->currency;
        $isNet = $order->amounts[$amountId]->isNet;
        $itemSumRebateGross = 0;
        $itemSumRebateNet = 0;
        $additionalCosts = [];
        $additionalCostsWithTax = [];
        $subAmount = 0;
        $taxlessAmount = 0;


        $orderItems = $order->orderItems;

        /** @var NumberFormatter $numberFormatter */
        $numberFormatter = pluginApp(NumberFormatter::class);
        foreach ($orderItems as $item) {
            $itemAmountId = $this->getCustomerAmountId($item->amounts);
            /** @var OrderItem $item */
            $firstAmount = $item->amounts[$itemAmountId];

            switch ($item->typeId) {
                case OrderItemType::VARIATION:
                case OrderItemType::ITEM_BUNDLE:
                case OrderItemType::TYPE_ORDER_PROPERTY:
                    $itemSumGross += $firstAmount->priceGross * $item->quantity;
                    $itemSumNet += $firstAmount->priceNet * $item->quantity;

                    $this->addAdditionalCost(
                        $item,
                        $numberFormatter,
                        $additionalCosts,
                        $additionalCostsWithTax,
                        $taxlessAmount
                    );
                break;
                case OrderItemType::SHIPPING_COSTS:
                    $shippingGross += $firstAmount->priceGross;
                    $shippingNet += $firstAmount->priceNet;
                    break;
                case OrderItemType::PROMOTIONAL_COUPON:
                case OrderItemType::GIFT_CARD:
                    $couponType = $item->typeId;
                    $couponValue += $firstAmount->priceGross;
                    $itemNameArray = explode(' ', rtrim($item->orderItemName));
                    $couponCode = end($itemNameArray);
                    break;
                case OrderItemType::DEPOSIT;
                    if (!empty($item->amounts)) {
                        $price = $item->amounts[0]->priceGross;
                        $currency = $item->amounts[0]->currency;
                        $additionalCosts[] = [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'name' => $item->orderItemName,
                            'price' => $price,
                            'currency' => $currency,
                            'formattedTotalPrice'
                            => $numberFormatter->formatMonetary($price * $item->quantity, $currency)
                        ];
                    }
                    break;
                default:
                    // noop
            }

            if ($firstAmount->discount > 0) {
                if ($firstAmount->isPercentage) {
                    $itemSumRebateGross += round(
                        $item->quantity * $firstAmount->priceOriginalGross * $firstAmount->discount / 100,
                        2
                    );
                    $itemSumRebateNet += round(
                        $item->quantity * $firstAmount->priceOriginalNet * $firstAmount->discount / 100,
                        2
                    );
                } else {
                    $itemSumRebateGross += $item->quantity * $firstAmount->discount;
                }
            }
        }

        $itemSumGross += $itemSumRebateGross;
        $itemSumNet += $itemSumRebateNet;
        $itemSumNet -= $taxlessAmount;
        $itemSumGross -= $taxlessAmount;
        $subAmount = $totalNet - $taxlessAmount;

        foreach ($order->amounts[$amountId]->vats as $vat) {
            $vats[] = [
                'rate' => $vat->vatRate,
                'value' => $vat->value
            ];
        }

        if ($isNet) {
            $itemSumGross = $itemSumNet;
            $totalGross = $totalNet;
        }

        if ($couponType == OrderItemType::GIFT_CARD) {
            $couponType = 'sales';
            $openAmount = $totalGross + $couponValue;
        } elseif ($couponType == OrderItemType::PROMOTIONAL_COUPON) {
            $couponType = 'promotional';
        }

        return compact(
            'itemSumGross',
            'itemSumNet',
            'itemSumRebateGross',
            'itemSumRebateNet',
            'shippingGross',
            'shippingNet',
            'vats',
            'couponValue',
            'openAmount',
            'couponType',
            'couponCode',
            'totalGross',
            'totalNet',
            'currency',
            'isNet',
            'additionalCosts',
            'additionalCostsWithTax',
            'subAmount'
        );
    }

    /**
     * @param $amounts
     * @return int|string
     */
    private function getCustomerAmountId($amounts)
    {
        foreach ($amounts as $index => $amount) {
            if (!$amount->isSystemCurrency) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * Check if the order has only net amounts or only net amounts should be shown
     * @param Order $order
     * @return bool
     */
    public function highlightNetPrices(Order $order): bool
    {
        $isOrderNet = $order->amounts[0]->isNet;

        $orderContactId = 0;
        foreach ($order->relations as $relation) {
            if ($relation['referenceType'] === 'contact' && (int)$relation['referenceId'] > 0) {
                $orderContactId = $relation['referenceId'];
            }
        }

        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);

        $showNet = $customerService->showNetPricesByContactId($orderContactId);

        return $showNet || $isOrderNet;
    }

    /**
     * @param OrderItem $item
     * @param NumberFormatter $numberFormatter
     * @param array $additionalCosts
     * @param array $additionalCostsWithTax
     * @throws \Plenty\Modules\Core\Data\Exceptions\ModelFlattenerException
     */
    private function addAdditionalCost(
        OrderItem $item,
        NumberFormatter $numberFormatter,
        array &$additionalCosts,
        array &$additionalCostsWithTax,
        &$taxlessAmount
    ) {
        $propertyId = null;
        foreach ($item->properties as $property) {
            if ($property->typeId === OrderPropertyType::ORDER_PROPERTY_ID) {
                $propertyId = $property->value;
            }
        }
        if (isset($propertyId)) {
            $ll = LazyLoaderFactory::getLazyLoaderFor(Property::class);
            $property = $ll->getById($propertyId);
            $isAdditionalCost = false;
            $hasTax = false;
            foreach ($property['options'] as $option) {
                if ($option['type'] === 'vatId' && ($option['value'] !== 'none' || $option['value'] !== null)) {
                    $hasTax = true;
                }
                if ($option['value'] === 'displayAsAdditionalCosts') {
                    $isAdditionalCost = true;
                }
            }
            $price = $item->amounts[0]->priceGross;
            $currency = $item->amounts[0]->currency;

            $newProperty = [
                'id' => $item->id,
                'quantity' => $item->quantity,
                'name' => $item->orderItemName,
                'price' => $price,
                'currency' => $currency,
                'formattedTotalPrice'
                => $numberFormatter->formatMonetary($price * $item->quantity, $currency)
            ];

            if (!$hasTax) {
                $additionalCosts[] = $newProperty;
                $taxlessAmount += $price * $item->quantity;
            }
            elseif($isAdditionalCost) {
                $additionalCostsWithTax[] = $newProperty;
            }
        }
    }
}
