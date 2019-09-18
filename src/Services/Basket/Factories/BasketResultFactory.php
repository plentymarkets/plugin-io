<?php

namespace IO\Services\Basket\Factories;

use IO\Services\Basket\Factories\Faker\BasketFaker;
use IO\Services\Basket\Factories\Faker\BasketItemFaker;
use IO\Services\ItemSearch\SearchPresets\VariationList;
use IO\Services\ItemSearch\Services\ItemSearchService;

class BasketResultFactory
{
    const BASKET_STRUCTURE = [
        'basket' => [],
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
                    $basketResult[$key] = $faker->fill([]);
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

    /**
     * Get random amount of items between 1 and 5 with random quantities between 1 and 3
     * Format of array is [item, quantity]
     * @return array
     */
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
            // Add urlPreview image to display in basket
            $itemData['data']['images']['all'][0]['urlPreview'] = $itemData['data']['images']['all'][0]['urlMiddle'];

            $quantity = rand(1, 3);
            $flatItemResult[] = [
                'itemData' => $itemData,
                'quantity' => $quantity
            ];
        }

        return $flatItemResult;
    }
}
