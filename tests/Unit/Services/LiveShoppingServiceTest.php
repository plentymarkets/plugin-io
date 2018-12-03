<?php

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\SearchPresets\LiveShoppingItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\LiveShoppingService;
use Plenty\Modules\Cloud\ElasticSearch\Factories\ElasticSearchResultFactory;
use Plenty\Modules\LiveShopping\Contracts\LiveShoppingRepositoryContract;
use Plenty\Modules\LiveShopping\Models\LiveShopping;
use PluginTests\SimpleTestCase;

/**
 * Created by IntelliJ IDEA.
 * User: manueldierkes
 * Date: 27.11.18
 * Time: 13:16
 */
class LiveShoppingServiceTest extends SimpleTestCase {

    /** @var LiveShoppingService $liveShoppingService */
    protected $liveShoppingService;

    /** @var ElasticSearchResultFactory $elasticSearchRepository */
    protected $elasticSearchRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->elasticSearchRepository = pluginApp(ElasticSearchResultFactory::class);
        $this->liveShoppingService = pluginApp(LiveShoppingService::class);

    }

    /**
     * @test
     * @dataProvider dataProviderLiveShopping
     * @param LiveShopping $factory
     */
    public function should_get_the_right_live_shopping($factory, $prices, $assert) {

        $liveShoppingContractRepositoryMock = Mockery::mock(LiveShoppingRepositoryContract::class);
        $liveShoppingContractRepositoryMock->shouldReceive('getLiveShopping')->andReturn($factory);

        $this->replaceInstanceByMock(LiveShoppingRepositoryContract::class, $liveShoppingContractRepositoryMock);

        /** @var ElasticSearchResultFactory $esFactory */
        $esFactory = pluginApp(ElasticSearchResultFactory::class);
        $esMockData = $this->attachSpecialOfferPrices($esFactory->makeWrapped(), $prices);


        $liveShoppingItemsMock = Mockery::mock('alias:'.LiveShoppingItems::class);
        $liveShoppingItemsMock->shouldReceive('getSearchFactory')->withAnyArgs()->andReturn('');

        $this->replaceInstanceByMock(LiveShoppingItems::class, $liveShoppingItemsMock);




        $itemSearchServiceMock = Mockery::mock(ItemSearchService::class);

        $itemSearchServiceMock
            ->shouldReceive('getResults')
            ->withArgs([Mockery::any()])
            ->andReturn([$esMockData]);

        $this->replaceInstanceByMock(ItemSearchService::class, $itemSearchServiceMock);



        /**
         * @var ResultFieldTemplate $resultFieldTemplate
         */
        $resultFieldTemplate = pluginApp(ResultFieldTemplate::class);
        $resultFieldTemplate->setTemplates([ResultFieldTemplate::TEMPLATE_LIST_ITEM   => 'Ceres::ResultFields.ListItem']);
        $this->replaceInstanceByMock(ResultFieldTemplate::class, $resultFieldTemplate);



        $liveShopping = $this->liveShoppingService->getLiveShoppingData(1);


        $this->assertNotNull($liveShopping);


        if(!empty($liveShopping['item'] && !empty($liveShopping['liveShopping']))) {
            $this->assertEquals($factory->liveShoppingId, $liveShopping['liveShopping']['liveShoppingId']);
        }

    }


    /**
     * @test
     * @dataProvider dataProviderSpecialOfferPrices
     */
    public function should_filter_the_right_variations($prices , $numberOfVariations, $assertVariationSorting) {

        /** @var ElasticSearchResultFactory $elasticSearchResultFactory */
        $elasticSearchResultFactory = pluginApp(ElasticSearchResultFactory::class);

        $esResult = $this->attachSpecialOfferPrices($elasticSearchResultFactory->makeWrapped([], $numberOfVariations), $prices);

        $result = $this->liveShoppingService->filterLiveShoppingVariations([$esResult]);

        $this->assertNotNull($result);

        $i=0;
        foreach ($result[0]['documents'] as $variation) {
            $this->assertEquals($assertVariationSorting[$i], $variation['data']['prices']['specialOffer']);
            $i++;
        }



    }

    public function dataProviderLiveShopping() {

        $this->refreshApplication();

        /** @var LiveShopping $liveShopping  */
        $liveShopping = factory(LiveShopping::class)->make([
            'liveShoppingId' => 10
        ]);


        return [
            [
                null,
                [
                    null,
                    1.0
                ],
                [
                    [],
                    []
                ]
            ],
            [
                $liveShopping,
                [
                    null,
                    1.0
                ],
                [
                    [],
                    [
                        'liveShoppingId' => 10
                    ]
                ]
            ]
        ];

    }

    public function dataProviderSpecialOfferPrices() {
        return [
            [
                [
                    null,
                    1.0
                ],
                2,
                [
                    1.0,
                    null
                ]

            ],
            [
                [
                    null,
                    null,
                    2.0
                ],
                3,
                [
                    2.0,
                    null,
                    null
                ]
            ]
        ];
    }


    private function attachSpecialOfferPrices($result, $prices) {

        $variations = $result['documents'];

        foreach ($variations as $key => $variation) {
            $result['documents'][$key]['data']['prices']['specialOffer'] = $prices[$key];
        }

        return $result;
    }






}