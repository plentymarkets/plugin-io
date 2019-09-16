<?php

namespace IO\Services\Order\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Translation\Translator;

class OrderFaker extends AbstractFaker
{
    public function fill($data)
    {
        $orderId =         $this->number(1, 10000);
        $billingAddress =  $this->makeAddress();
        $deliveryAddress = $billingAddress;
        $orderItems =      $this->makeOrderItems($orderId);
        
        $default = [
            'id'                => $orderId,
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
            'billingAddress'    => $billingAddress,
            'deliveryAddress'   => $deliveryAddress,
            'addresses'         => [$billingAddress, $deliveryAddress],
            'orderItems'        => $orderItems,
            'properties'        => $this->makeProperties(),
            'amounts'           => [],
            'comments'          => [],
            'location'          => null,
            'payments'          => [],
            'orderReferences'   => [],
            'documents'         => [],
            'dates'             => [],
            'originOrder'       => null,
            'parentOrder'       => null,
            'systemAmount'      => null,
            'amount'            => null
        ];
        
        $this->merge($data, $default);
        return $data;
    }
    
    private function makeAddress()
    {
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);
        
        return [
            'id'         => $this->number(0, 10000),
            'gender'     => 'male',
            'name1'      => '',
            'name2'      => $translator->trans('IO::Faker.addressName2'),
            'name3'      => $translator->trans('IO::Faker.addressName3'),
            'name4'      => '',
            'address1'   => $translator->trans('IO::Faker.addressAddress1'),
            'address2'   => $translator->trans('IO::Faker.addressAddress2'),
            'address3'   => '',
            'address4'   => '',
            'postalCode' => $translator->trans('IO::Faker.addressPostalCode'),
            'town'       => $translator->trans('IO::Faker.addressTown'),
            'countryId'  => 1,
            'stateId'    => null,
        ];
    }
    
    private function makeOrderItems($orderId)
    {
        $itemSearchOptions = [
            'page'         => 1,
            'itemsPerPage' => $this->number(1, 5),
            'sortingField' => 'item.random',
            'sortingOrder' => 'ASC'
        ];
        
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $itemResult = $itemSearchService->getResults(['items' => VariationList::getSearchFactory( $itemSearchOptions )]);
        
        $orderItems = [];
        
        foreach($itemResult['items']['documents'] as $itemData)
        {
            $item = $itemData['data'];
            
            $orderItems[] = [
                'id'                => $item['item']['id'],
                'orderId'           => $orderId,
                'typeId'            => 1,
                'referrerId'        => 1.00,
                'itemVariationId'   => $item['variation']['id'],
                'quantity'          => 1.00,
                'orderItemName'     => $item['texts']['name1'],
                'attributeValues'   => null,
                'shippingProfileId' => 1,
                'countryVatId'      => 1,
                'vatField'          => 0,
                'vatRate'           => 19.00,
                'position'          => 0,
                'warehouseId'       => 1,
                'bundleType'        => null,
                'bundleComponents'  => null,
                'images'            => [$item['variation']['id'] => $item['images']['all'][0]['path']],
                'amounts' => [
                    0 => [
                        'id' => $orderId,
                        'orderItemId'        => $item['item']['id'],
                        'isSystemCurrency'   => 1,
                        'currency'           => 'EUR',
                        'exchangeRate'       => 1.000000,
                        'purchasePrice'      => 0.0000,
                        'surcharge'          => 0.0000,
                        'discount'           => 0.0000,
                        'isPercentage'       => 1,
                        'priceOriginalNet'   => $item['prices']['default']['price']['value'],
                        'priceNet'           => $item['prices']['default']['price']['value'],
                        'priceOriginalGross' => $item['prices']['default']['price']['value'],
                        'priceGross'         => $item['prices']['default']['price']['value']
                    ]
                ]
            ];
        }
        
        return $orderItems;
    }
    
    private function makeProperties()
    {
        return [
            [
                'typeId' => 4,
                'value'  => 'unpaid'
            ]
        ];
    }
}
