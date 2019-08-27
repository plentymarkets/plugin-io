<?php

namespace IO\Services\Order\Factories;

use IO\Services\Order\Factories\Faker\LocalizedOrderFaker;
use IO\Services\Order\Factories\Faker\OrderFaker;
use IO\Services\Order\Factories\Faker\TotalsFaker;

class OrderResultFactory
{
    const ORDER_STRUCTURE = [
        'order' => [
            'id'                => 0,
            'typeId'            => 0,
            'methodOfPaymentId' => 0,
            'shippingProfileId' => 0,
            'paymentStatus'     => 'unpaid',
            'statusId'          => 0,
            'statusName'        => '',
            'ownerId'           => 0,
            'referrerId'        => 1.0,
            'createdAt'         => '',
            'updatedAt'         => '',
            'plentyId'          => 0,
            'locationId'        => 0,
            'roundTotalsOnly'   => false,
            'numberOfDecimals'  => 2,
            'lockStatus'        => 'unlocked',
            'owner'             => null,
            'billingAddress'    => [],
            'deliveryAddress'   =>[],
            'addresses'         => [],
            'orderItems'        => [],
            'properties'        => [],
            'amounts'           => [],
            'comments'          => [],
            'location'          => null,
            'payments'          => [],
            'orderReferences'   => [],
            'documents'         => [],
            'dates'             => [],
            'originOrder' => null,
            'parentOrder' => null,
            'systemAmount' => null,
            'amount' => null,
        ],
        'totals' => [
            'itemSumGross'       => 0.0,
            'itemSumNet'         => 0.0,
            'itemSumRebateGross' => 0.0,
            'itemSumRebateNet'   => 0.0,
            'shippingGross'      => 0.0,
            'shippingNet'        => 0.0,
            'couponValue'        => 0,
            'openAmount'         => 0,
            'couponType'         => '',
            'couponCode'         => '',
            'totalGross'         => 0.0,
            'totalNet'           => 0.0,
            'currency'           => 'EUR',
            'isNet' =>           false,
            'vats' => [
                [
                    'rate' => 0.0,
                    'value' => 0.0
                ]
            ]
        ],
        'status'                       => null,
        'shippingProvider'             => null,
        'shippingProfileName'          => null,
        'shippingProfileId'            => 0,
        'trackingURL'                  => '',
        'paymentMethodName'            => null,
        'paymentMethodIcon'            => null,
        'paymentStatus'                => 'unpaid',
        'itemURLs'                     => [],
        'itemImages'                   =>[],
        'isReturnable'                 => true,
        'highlightNetPrices'           => true,
        'allowPaymentMethodSwitchFrom' => true,
        'paymentMethodListForSwitch'   => []
    ];
    
    const FAKER_MAP = [
        'order' => OrderFaker::class,
        'totals' => TotalsFaker::class,
    ];
    
    public function fillOrderResult()
    {
        $orderResult = [];
        
        foreach(self::ORDER_STRUCTURE as $key => $value)
        {
            if(array_key_exists($key, self::FAKER_MAP))
            {
                $faker = pluginApp(self::FAKER_MAP[$key]);
                $orderResult[$key] = $faker->fill($orderResult[$key]);
            }
            else
            {
                $orderResult[$key] = $value;
            }
        }
        
        /** @var LocalizedOrderFaker $localizedOrderFaker */
        $localizedOrderFaker = pluginApp(LocalizedOrderFaker::class);
        $orderResult = $localizedOrderFaker->fill($orderResult);
        
        return $orderResult;
    }
}
