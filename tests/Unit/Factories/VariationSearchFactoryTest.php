<?php

namespace IO\Tests\Unit;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Tests\TestCase;
use Plenty\Modules\Item\Search\Filter\CategoryFilter;
use Plenty\Modules\Item\Search\Filter\ClientFilter;
use Plenty\Modules\Item\Search\Filter\PriceFilter;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;
use Plenty\Modules\Item\Search\Filter\TagFilter;
use Plenty\Modules\Item\Search\Filter\TextFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;


class VariationSearchFactoryTest extends TestCase
{

    /** @var VariationSearchFactory $variationSearchFactory  */
    protected $variationSearchFactory;

    /** @var ItemSearchService $itemSearchService */
    protected $itemSearchService;


    protected function setUp(): void
    {
        parent::setUp();

        $this->variationSearchFactory = pluginApp(VariationSearchFactory::class);

    }

    /**
     * @test
     */
    public function should_have_the_right_client_filter()
    {
        /**
         * @var ClientFilter $clientFilter
         */
        $expectedClientId = 105;

        $clientFilter = $this->variationSearchFactory->createFilter(ClientFilter::class);

        $clientFilter->isVisibleForClient($expectedClientId);


        $settedFilters = $clientFilter->toArray()['bool']['must'];


        $this->assertEquals($settedFilters[0]['term']['ids.clients'], $expectedClientId);
    }

    /**
     * @test
     */
    public function should_have_the_right_text_filter()
    {
        /**
         * @var TextFilter $textFilter
         */


        $textFilter = $this->variationSearchFactory->createFilter(TextFilter::class);

        $textFilter->hasNameInLanguage(TextFilter::LANG_DE, TextFilter::FILTER_NAME_1);


        $settedFilters = $textFilter->toArray()['bool']['must'];


        $this->assertEquals(key($settedFilters[0]['term']), 'filter.names.'.TextFilter::LANG_DE.'.'.TextFilter::FILTER_NAME_1);
        $this->assertEquals($settedFilters[0]['term']['filter.names.'.TextFilter::LANG_DE.'.'.TextFilter::FILTER_NAME_1], true);
    }

    /**
     * @test
     */
    public function should_have_the_right_category_filter()
    {
        /**
         * @var CategoryFilter $categoryFilter
         */
        $expectedCategoryId = 100;

        $categoryFilter = $this->variationSearchFactory->createFilter(CategoryFilter::class);

        $categoryFilter->isInCategory($expectedCategoryId);


        $settedFilters = $categoryFilter->toArray()['bool']['must'];


        $this->assertEquals($settedFilters[0]['term']['ids.categories.all'], $expectedCategoryId);
    }

    /**
     * @test
     */
    public function should_have_the_right_sales_price_filter()
    {
        /**
         * @var SalesPriceFilter $salesPriceFilter
         */
        $expectedPriceIds = [100, 101];

        $salesPriceFilter = $this->variationSearchFactory->createFilter(SalesPriceFilter::class);

        $salesPriceFilter->hasAtLeastOnePrice($expectedPriceIds);


        $settedFilters = $salesPriceFilter->toArray()['bool']['must'];


        $this->assertEquals($settedFilters[0]['terms']['ids.salesPrices'][0], $expectedPriceIds[0]);
        $this->assertEquals($settedFilters[0]['terms']['ids.salesPrices'][1], $expectedPriceIds[1]);
    }

    /**
     * @test
     * @dataProvider dataProviderPriceRangeFilter
     */
    public function should_have_the_right_price_filter($min, $max)
    {
        /**
         * @var PriceFilter $priceRangeFilter
         */
        $expectedClientId = 101;

        $priceRangeFilter = $this->variationSearchFactory->createFilter(PriceFilter::class);

        $priceRangeFilter->betweenByClient($min, $max, $expectedClientId);


        $settedFilters = $priceRangeFilter->toArray()['bool']['filter'];


        $this->assertEquals(key($settedFilters['range']), 'sorting.priceByClientDynamic.'.$expectedClientId.'.avg');

        if($min != null && $max != null)
        {
            $this->assertEquals($settedFilters['range']['sorting.priceByClientDynamic.'.$expectedClientId.'.avg']['gte'], $min);
            $this->assertEquals($settedFilters['range']['sorting.priceByClientDynamic.'.$expectedClientId.'.avg']['lte'], $max);
        }
    }

    public function dataProviderPriceRangeFilter()
    {
        return [
            [
                null,
                null
            ],
            [
                10,
                450
            ]
        ];
    }


    /**
     * @test
     * @dataProvider dataProviderTagFilter
     */
    public function should_have_the_right_tag_filter($method, $params)
    {
        /**
         * @var TagFilter $tagFilter
         */
        $tagFilter = $this->variationSearchFactory->createFilter(TagFilter::class);

        $tagFilter->{$method}($params);

        $settedFilters = $tagFilter->toArray()['bool']['must'];

        if(!is_array($params))
        {
            $this->assertEquals($settedFilters[0]['term']['ids.tags'], $params);
        } else {
            $this->assertEquals($settedFilters[0]['terms']['ids.tags'][0], $params[0]);
            $this->assertEquals($settedFilters[0]['terms']['ids.tags'][1], $params[1]);
        }

    }

    public function dataProviderTagFilter()
    {
        return [
            [
                'hasTag',
                1
            ],
            [
                'hasAnyTag',
                [10,12]
            ]
        ];
    }




    /**
     * @test
     * @dataProvider dataProviderRightVariationBaseFilter
     */
    public function should_have_the_right_variation_base_filter($filterClass, $method, $params, $filter, $filterValue)
    {

        /** @var VariationBaseFilter $variationFilter */
        $variationFilter = $this->variationSearchFactory->createFilter($filterClass);


        if($params != null)
        {
            $variationFilter->{$method}($params);
        } else {
            $variationFilter->{$method}();
        }



        $settedFilters = $variationFilter->toArray()['bool']['must'];


        $this->assertEquals(key($settedFilters[0]), $filter);

        if(is_array($params))
        {
            foreach ($params as $key => $param)
            {
                $this->assertEquals($settedFilters[0][$filter][$filterValue[0]][$key], $filterValue[1][$key]);
            }

        } else {

            $this->assertEquals($settedFilters[0][$filter][$filterValue[0]], $filterValue[1]);

        }


    }


    public function dataProviderRightVariationBaseFilter()
    {

        /*
         *  1. Filter
         *  2. Parameter
         *  3. Kind of Filter
         *  4. Filter (Key)
         *  5. Filter (Value)
         */
        return [
            [
                VariationBaseFilter::class,
                'isActive',
                null,
                'term',
                [
                    'variation.isActive',
                    true
                ]
            ],
            [
                VariationBaseFilter::class,
                'isInactive',
                null,
                'term',
                [
                    'variation.isActive',
                    false
                ]
            ],
            [
                VariationBaseFilter::class,
                'hasItemId',
                110,
                'term',
                [
                    'variation.itemId',
                    110
                ]
            ],
            [
                VariationBaseFilter::class,
                'hasItemIds',
                [90,110,23],
                'terms',
                [
                    'variation.itemId',
                    [90,110,23]
                ]
            ],
            [
                VariationBaseFilter::class,
                'hasId',
                110,
                'term',
                [
                    'variation.id',
                    110
                ]
            ],
            [
                VariationBaseFilter::class,
                'hasIds',
                [90,110,23],
                'terms',
                [
                    'variation.id',
                    [90,110,23]
                ]
            ],
            [
                VariationBaseFilter::class,
                'hasAtLeastOneAvailability',
                [90,110,23],
                'terms',
                [
                    'variation.availability.id',
                    [90,110,23]
                ]
            ],
            [
                VariationBaseFilter::class,
                'hasSupplier',
                1,
                'term',
                [
                    'ids.suppliers',
                    1
                ]
            ],
            [
                VariationBaseFilter::class,
                'isMain',
                null,
                'term',
                [
                    'variation.isMain',
                    true
                ]
            ],
            [
                VariationBaseFilter::class,
                'isChild',
                null,
                'term',
                [
                    'variation.isMain',
                    false
                ]
            ],
            [
                VariationBaseFilter::class,
                'isHiddenInCategoryList',
                true,
                'term',
                [
                    'variation.isHiddenInCategoryList',
                    true
                ]
            ]
        ];
    }


}
