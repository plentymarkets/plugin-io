<?php

namespace IO\Services\Basket\Factories;

use IO\Services\Basket\Factories\Faker\BasketFaker;
use IO\Services\Basket\Factories\Faker\BasketItemFaker;
use Plenty\Modules\Webshop\ItemSearch\Helper\ResultFieldTemplate;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\VariationList;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

class BasketResultFactory
{
    /** @var BasketFaker $basketFaker */
    private $basketFaker;

    /** @var BasketItemFaker $basketItemFaker */
    private $basketItemFaker;

    public function __construct()
    {
        // Fetch random items for the faker to use
        $rawBasketItems = $this->makeRawBasketItems();

        $this->basketFaker = pluginApp(BasketFaker::class);
        $this->basketFaker->setRawBasketItems($rawBasketItems);

        $this->basketItemFaker = pluginApp(BasketItemFaker::class);
        $this->basketItemFaker->setRawBasketItems($rawBasketItems);
    }

    /**
     * Faker function for the basket view
     * @return array
     */
    public function fillBasketResult()
    {
        $fakeBasket = $this->basketFaker->fill([]);
        $fakeBasketItems = $this->basketItemFaker->fill([]);

        return [
            'basket' => $fakeBasket,
            'basketItems' => $fakeBasketItems
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

        $searchFactory = VariationList::getSearchFactory( $itemSearchOptions );
        $searchFactory
            ->withResultFields(
                ResultFieldTemplate::load( ResultFieldTemplate::TEMPLATE_BASKET_ITEM )
            );

        $itemResult = $itemSearchService->getResults(['items' => $searchFactory]);

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
