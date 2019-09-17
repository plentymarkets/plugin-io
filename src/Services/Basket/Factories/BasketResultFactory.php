<?php

namespace IO\Services\Basket\Factories;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\Basket\Factories\Faker\BasketFaker;
use IO\Services\Basket\Factories\Faker\BasketItemFaker;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Modules\Basket\Models\Basket;

class BasketResultFactory
{
    const BASKET_STRUCTURE = [
        'basket' => [
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
            'isExportDelivery'              => true,
            'itemQuantity'                  => 0,
            'itemSum'                       => 0.0,
            'itemSumNet'                    => 0.0,
            'maxFsk'                        => 0,
            'methodOfPaymentId'             => 0,
            'orderId'                       => null,
            'orderTimestamp'                => null,
            'paymentAmount'                 => 0,
            'reffererId'                    => 0,
            'sessionId'                     => '',
            'shippingAmount'                => 0.0,
            'shippingAmountNet'             => 0.0,
            'shippingCountryId'             => 0,
            'shippingDeleteByCoupon'        => 0,
            'shippingProfileId'             => 0,
            'shippingProviderId'            => 0,
            'shopCountryId'                 => 0,
            'totalVats'                     => [],
            'updatedAt'                     => '',
        ],
        'basketItems' => []
    ];

    const FAKER_MAP = [
        'basket' => BasketFaker::class,
        'basketItems' => BasketItemFaker::class,
    ];

    /**
     * Faker function for the basket view
     * @return array
     */
    public function fillBasketResult()
    {
        // Fetch random items for the faker to use
        $rawBasketItems = $this->makeRawBasketItems();

        // Fill in fake data into objects
        $basketResult = [];

        foreach(self::BASKET_STRUCTURE as $key => $value)
        {
            if(array_key_exists($key, self::FAKER_MAP))
            {
                $faker = pluginApp(self::FAKER_MAP[$key]);
                if($faker instanceof BasketFaker || $faker instanceof BasketItemFaker)
                {
                    $faker->setRawBasketItems($rawBasketItems);
                    $basketResult[$key] = $faker->fill(self::BASKET_STRUCTURE[$key]);
                }
            }
            else
            {
                $basketResult[$key] = $value;
            }
        }

        return [
            'basket' => $basketResult['basket'],
            'basketItems' => $basketResult['basketItems']
        ];
    }

    private function makeRawBasketItems()
    {
        $itemSearchOptions = [
            'page'         => 1,
            'itemsPerPage' => rand(1, 5),
            'sortingField' => 'item.random',
            'sortingOrder' => 'ASC'
        ];

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $itemResult = $itemSearchService->getResults(['items' => VariationList::getSearchFactory( $itemSearchOptions )]);

        $flatItemResult = [];

        foreach ($itemResult['items']['documents'] as $itemData)
        {
            $flatItemResult[] = $itemData;
        }

        return $flatItemResult;
    }
}
