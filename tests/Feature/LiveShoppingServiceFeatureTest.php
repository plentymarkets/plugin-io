<?php

use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\SearchPresets\LiveShoppingItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\LiveShoppingService;
use IO\Tests\TestCase;
use Plenty\Modules\Cloud\ElasticSearch\Factories\ElasticSearchResultFactory;
use Plenty\Modules\LiveShopping\Contracts\LiveShoppingRepositoryContract;
use Plenty\Modules\LiveShopping\Models\LiveShopping;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Created by IntelliJ IDEA.
 * User: manueldierkes
 * Date: 27.11.18
 * Time: 13:16
 */

class LiveShoppingServiceFeatureTest extends TestCase {

    use RefreshDatabase;

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
    public function should_get_the_right_live_shopping_from_db($factory, $prices) {

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
        }else{
            $this->assertEmpty($liveShopping['liveShopping']);
            $this->assertEmpty($liveShopping['item']);
        }

    }

    public function dataProviderLiveShopping() {

        $this->refreshApplication();

        /** @var LiveShopping $liveShopping  */
        $liveShopping = factory(LiveShopping::class)->create([
            'liveShoppingId' => 10
        ]);


        return [
            [
                null,
                [
                    null,
                    1.0
                ]
            ],
            [
                $liveShopping,
                [
                    null,
                    1.0
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