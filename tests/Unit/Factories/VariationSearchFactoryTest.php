<?php

namespace IO\Tests\Unit;

use IO\Providers\IOServiceProvider;
use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\ItemService;
use IO\Tests\TestCase;
use Mockery;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Item\Search\Filter\PropertyFilter;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Upg\Library\Mns\ProcessorInterface;


class VariationSearchFactoryTest extends TestCase
{

    /** @var VariationSearchFactory $variationSearchFactory  */
    protected $variationSearchFactory;

    /** @var ItemSearchService $itemSearchService */
    protected $itemSearchService;


    protected function setUp()
    {
        parent::setUp();

        $this->variationSearchFactory = pluginApp(VariationSearchFactory::class);

    }

    /**
     * @test
     */
    public function should_have_the_right_property_filter()
    {
        /**
         * @var PropertyFilter $propertyFilter
         */
        $expectedIds = [10,11,12];

        $propertyFilter = $this->variationSearchFactory->createFilter(PropertyFilter::class);

        $propertyFilter->hasEachProperty($expectedIds);


        $settedFilters = $propertyFilter->toArray()['bool']['must'][0]['nested'];

        $properties = $settedFilters['query']['bool']['must'];

        foreach ($properties as $key => $property)
        {
            $this->assertEquals($expectedIds[$key], $property['term']['properties.property.id']);
        }
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
