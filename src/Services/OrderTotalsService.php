<?php

namespace IO\Services;

use IO\Builder\Order\OrderItemType;
use Plenty\Modules\Accounting\Contracts\AccountingLocationRepositoryContract;
use Plenty\Modules\Frontend\Services\VatService;
use Plenty\Modules\Order\Date\Models\OrderDateType;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Models\OrderItem;
use Plenty\Modules\Order\Shipping\Contracts\EUCountryCodesServiceContract;
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

        $orderItems = $order->orderItems;

        $accountRepo = pluginApp(AccountingLocationRepositoryContract::class);
        $vatService = pluginApp(VatService::class);
        /** @var NumberFormatter $numberFormatter */
        $numberFormatter = pluginApp(NumberFormatter::class);
        /**
         * @var EUCountryCodesServiceContract $euCountryCodesServiceContract
         */
        $euCountryCodesServiceContract = pluginApp(EUCountryCodesServiceContract::class);
        foreach ($orderItems as $item) {
            $itemAmountId = $this->getCustomerAmountId($item->amounts);
            /** @var OrderItem $item */
            $firstAmount = $item->amounts[$itemAmountId];

            switch ($item->typeId) {
                case OrderItemType::VARIATION:
                case OrderItemType::ITEM_BUNDLE:
                    $itemSumGross += $firstAmount->priceGross * $item->quantity;
                    $itemSumNet += $firstAmount->priceNet * $item->quantity;
                    break;
                case OrderItemType::SHIPPING_COSTS:
                    $locationId = $vatService->getLocationId($item->countryVatId);
                    $accountSettings = $accountRepo->getSettings($locationId);

                    $shippingGross += $firstAmount->priceGross;
                    $shippingNet += $firstAmount->priceNet;
                    $entryDate = $order->dates->where('typeId', OrderDateType::ORDER_ENTRY_AT)->first();

                    if ((bool)$accountSettings->showShippingVat && $euCountryCodesServiceContract->isExportDelivery(
                            $order->deliveryAddress->countryId,
                            $item->countryVatId,
                            isset($entryDate) ? $entryDate->date->toDateString() : $order->createdAt->toDateString()
                        )) {
                        $shippingNet = $shippingGross;
                    }
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
                    $itemSumRebateGross += round($item->quantity * $firstAmount->priceOriginalGross * $firstAmount->discount / 100, 2);
                    $itemSumRebateNet += round($item->quantity * $firstAmount->priceOriginalNet * $firstAmount->discount / 100, 2);
                } else {
                    $itemSumRebateGross += $item->quantity * $firstAmount->discount;
                }
            }
        }

        $itemSumGross += $itemSumRebateGross;
        $itemSumNet += $itemSumRebateNet;

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
            'additionalCosts'
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
}
