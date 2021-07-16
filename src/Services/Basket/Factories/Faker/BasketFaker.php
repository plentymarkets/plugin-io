<?php

namespace IO\Services\Basket\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use Plenty\Modules\Frontend\Services\VatService;

/**
 * Class BasketFaker
 * Factory to generate random basket data to be used in the ShopBuilder preview.
 *
 * @package IO\Services\Basket\Factories\Faker
 */
class BasketFaker extends AbstractFaker
{
    private $rawBasketItems = [];

    /**
     * Fill existing basket data with random values.
     *
     * @param array $default Existing basket data to be filled with random values.
     *
     * @return mixed
     */
    public function fill($default)
    {
        $data = [
            'basketAmount'                  => 0.0,
            'basketAmountNet'               => 0.0,
            'basketRebate'                  => 0,
            'basketRebatetype'              => '0',
            'couponCode'                    => '',
            'couponDiscount'                => 0,
            'createdAt'                     => '',
            'currency'                      => 'EUR',
            'customerId'                    => null,
            'customerInvoiceAddressId'      => null,
            'customerShippingAddressId'     => null,
            'id'                            => 0,
            'isExportDelivery'              => false,
            'itemQuantity'                  => 0,
            'itemSum'                       => 0.0,
            'itemSumNet'                    => 0.0,
            'maxFsk'                        => 0,
            'methodOfPaymentId'             => 0,
            'orderId'                       => null,
            'orderTimestamp'                => null,
            'paymentAmount'                 => 0,
            'referrerId'                    => 0,
            'sessionId'                     => '',
            'shippingAmount'                => 0.0,
            'shippingAmountNet'             => 0.0,
            'shippingCountryId'             => 0,
            'shippingDeleteByCoupon'        => 0,
            'shippingProfileId'             => 0,
            'shippingProviderId'            => 0,
            'shopCountryId'                 => 0,
            'totalVats'                     => [
                []
            ],
            'updatedAt'                     => '',
        ];

        $data = $this->getTotals($data);

        return $data;
    }

    /**
     * Set the list of basket item data to be considered while generating the totals of a random basket object.
     *
     * @param array $rawBasketItems List of raw basket item data.
     *
     * @see BasketItemFaker to generate list of random basket items.
     */
    public function setRawBasketItems($rawBasketItems)
    {
        $this->rawBasketItems = $rawBasketItems;
    }

    private function getTotals($data)
    {
        foreach($this->rawBasketItems as $rawBasketItem )
        {
            $itemData = $rawBasketItem['itemData'];
            $quantity = $rawBasketItem['quantity'];

            $priceData = $itemData['data']['prices']['default']['data'];
            $data['itemSum'] += $priceData['basePrice'] * $quantity;
            $data['itemSumNet'] += $priceData['basePriceNet'] * $quantity;
        }

        /** @var VatService $vatService */
        $vatService = pluginApp(VatService::class);
        $vatData = $vatService->getCurrentTotalVats();

        // calculate vatAmount, because vatService uses current basket
        $data['totalVats'][0]['vatValue'] = $vatData[0]['vatValue'];
        $data['totalVats'][0]['vatAmount'] = $data['itemSum'] - $data['itemSumNet'];

        // fake shipping costs
        $data['shippingAmountNet'] = $this->number(1, $data['itemSumNet'] / 50);
        $data['shippingAmount'] = $data['shippingAmountNet'] + ($data['shippingAmountNet'] * $vatData[0]['vatValue']) / 100;

        // basket totals
        $data['basketAmount'] = $data['itemSum'] + $data['shippingAmount'];
        $data['basketAmountNet'] = $data['itemSumNet'] + $data['shippingAmountNet'];

        return $data;
    }
}
