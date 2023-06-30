<?php

namespace IO\Services;

use IO\Builder\Order\OrderItemType;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderItem;
use Plenty\Modules\Order\Property\Models\OrderPropertyType;
use Plenty\Modules\Webshop\Helpers\NumberFormatter;
use Plenty\Modules\Webshop\Helpers\PropertyHelper;

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
        $paidAmount = $order->amounts[$amountId]->paidAmount;
        $currency = $order->amounts[$amountId]->currency;
        $isNet = $order->amounts[$amountId]->isNet;
        $itemSumRebateGross = 0;
        $itemSumRebateNet = 0;
        $additionalCosts = [];
        $additionalCostsWithTax = [];
        $subAmount = 0;
        $taxlessAmount = 0;
        $promotionalCouponsValue = 0;
        $giftCardsValue = 0;

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
                case OrderItemType::TYPE_ITEM_SET:
                case OrderItemType::TYPE_SET_COMPONENT:
                    $itemSumGross += $firstAmount->priceGross * $item->quantity;
                    $itemSumNet += $firstAmount->priceNet * $item->quantity;

                    $this->addAdditionalCost(
                        $item,
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
                    if ($item->typeId == OrderItemType::GIFT_CARD) {
                        $giftCardsValue += $firstAmount->priceGross;
                    } else {
                        $promotionalCouponsValue += $firstAmount->priceGross;
                    }
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
        } elseif ($couponType == OrderItemType::PROMOTIONAL_COUPON) {
            $couponType = 'promotional';
        }

        $openAmount = $totalGross - $paidAmount + $giftCardsValue;

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
            'subAmount',
            'promotionalCouponsValue',
            'giftCardsValue',
            'paidAmount'
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
     * @param array $additionalCosts
     * @param array $additionalCostsWithTax
     * @param Number $taxlessAmount
     * @throws \Plenty\Modules\Core\Data\Exceptions\ModelFlattenerException
     */
    private function addAdditionalCost(
        OrderItem $item,
        array &$additionalCosts,
        array &$additionalCostsWithTax,
        &$taxlessAmount
    ) {
        /** @var NumberFormatter $numberFormatter */
        $numberFormatter = pluginApp(NumberFormatter::class);
        $propertyId = null;
        foreach ($item->properties as $property) {
            if ($property->typeId === OrderPropertyType::ORDER_PROPERTY_ID) {
                $propertyId = $property->value;
            }
        }
        if (isset($propertyId)) {
            if (isset($additionalCosts[$propertyId])) {
                $tempProperty =& $additionalCosts[$propertyId];
                $additionalCosts[$propertyId]['quantity'] += $item->quantity;
                $additionalCosts[$propertyId]['formattedTotalPrice'] = $numberFormatter->formatMonetary(
                    $tempProperty['price'] * $tempProperty['quantity'],
                    $tempProperty['currency']
                );
                $taxlessAmount += $item->amounts[0]->priceGross * $item->quantity;
            } elseif (isset($additionalCostsWithTax[$propertyId])) {
                $tempProperty =& $additionalCostsWithTax[$propertyId];
                $additionalCostsWithTax[$propertyId]['quantity'] += $item->quantity;
                $additionalCostsWithTax[$propertyId]['formattedTotalPrice'] = $numberFormatter->formatMonetary(
                    $tempProperty['price'] * $tempProperty['quantity'],
                    $tempProperty['currency']
                );
            } else {
                list($isAdditionalCost, $hasTax, $newProperty) = $this->getPropertyWithMoreDetails($propertyId, $item);
                if (!$hasTax) {
                    $additionalCosts[$propertyId] = $newProperty;
                    $taxlessAmount += $item->amounts[0]->priceGross * $item->quantity;
                } elseif ($isAdditionalCost) {
                    $additionalCostsWithTax[$propertyId] = $newProperty;
                }
            }
        }
    }

    /**
     * @param string $propertyId
     * @param OrderItem $item
     * @return array
     * @throws \Plenty\Modules\Core\Data\Exceptions\ModelFlattenerException
     */
    private function getPropertyWithMoreDetails(
        string $propertyId,
        OrderItem $item
    ): array {
        /** @var NumberFormatter $numberFormatter */
        $numberFormatter = pluginApp(NumberFormatter::class);

        $property = PropertyHelper::getPropertyById($propertyId);
        $isAdditionalCost = false;
        $hasTax = false;

        foreach ($property['options'] as $option) {
            if ($option['type'] === 'vatId' && ($option['value'] !== 'none' && $option['value'] !== null)) {
                $hasTax = true;
            }
            if ($option['value'] === 'displayAsAdditionalCosts') {
                $isAdditionalCost = true;
            }
        }

        $newProperty = [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'name' => $item->orderItemName,
            'price' => $item->amounts[0]->priceGross,
            'currency' => $item->amounts[0]->currency,
            'formattedTotalPrice' => $numberFormatter->formatMonetary(
                $item->amounts[0]->priceGross * $item->quantity,
                $item->amounts[0]->currency
            )
        ];

        return array($isAdditionalCost, $hasTax, $newProperty);
    }
}