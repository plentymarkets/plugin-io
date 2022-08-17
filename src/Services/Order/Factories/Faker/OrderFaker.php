<?php

namespace IO\Services\Order\Factories\Faker;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationList;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;
use Plenty\Plugin\Translation\Translator;

/**
 * Class OrderFaker
 *
 * This is a faker class for orders.
 * Faker classes are used for creating preview objects for use in the shopBuilder.
 *
 * @package IO\Services\Order\Factories\Faker
 */
class OrderFaker extends AbstractFaker
{
    public $variations = [];

    /**
     * Fill the order array $data with faked order data.
     * @param array $data An order array.
     * @return mixed
     */
    public function fill($data)
    {
        $orderId            = $this->number(1, 10000);
        $billingAddress     = $this->makeAddress();
        $deliveryAddress    = $billingAddress;
        $orderItems         = $this->makeOrderItems($orderId);
        $amount             = $this->makeAmount($orderId, $orderItems);
        $default            = [
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
            'amounts'           => [$amount],
            'comments'          => [],
            'location'          => null,
            'payments'          => [],
            'orderReferences'   => [],
            'documents'         => [],
            'dates'             => [],
            'originOrder'       => null,
            'parentOrder'       => null,
            'systemAmount'      => $amount,
            'amount'            => $amount
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
            'itemsPerPage' => 5,
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

            $price = is_string($item['prices']['default']['price']['value']) ? $this->float(1, 200): $item['prices']['default']['price']['value'];

            $variationId = $item['variation']['id'];
            $this->variations[$variationId] = $item;
            $orderItems[] = [
                'id'                => $item['item']['id'],
                'orderId'           => $orderId,
                'typeId'            => 1,
                'referrerId'        => 1.00,
                'itemVariationId'   => $variationId,
                'quantity'          => $this->number(1, 10),
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
                        'priceOriginalNet'   => $price,
                        'priceNet'           => $price,
                        'priceOriginalGross' => $price,
                        'priceGross'         => $price
                    ]
                ]
            ];
        }

        return $orderItems;
    }

    private function makeAmount($orderId, $orderItems)
    {
        $totalsGross = 0;
        $totalsNet   = 0;
        $shipping    = $this->float(0, 10);

        foreach($orderItems as $orderItem)
        {
            $totalsNet += $orderItem['amounts'][0]['priceNet'];
            $totalsGross += $orderItem['amounts'][0]['priceGross'];
        }

        return [
            'id'                => $this->number(),
            'orderId'           => $orderId,
            'isSystemCurrency'  => true,
            'isNet'             => $this->boolean(),
            'currency'          => 'EUR',
            'exchangeRate'      => 1.0,
            'netTotal'          => $totalsNet,
            'grossTotal'        => $totalsGross,
            'vatTotal'          => $totalsGross - $totalsNet,
            'invoiceTotal'      => $totalsGross,
            'paidAmount'        => 0,
            'prepaidAmount'     => 0,
            'giftCardAmount'    => 0,
            'shippingCostsGross'=> $shipping,
            'shippingCostsNet'  => $shipping,
        ];
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
