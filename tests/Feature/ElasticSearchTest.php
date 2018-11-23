<?php

use IO\Models\LocalizedOrder;
use IO\Services\ItemSearch\Helper\ResultFieldTemplate;
use IO\Services\ItemSearch\SearchPresets\SingleItem;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\ItemService;
use IO\Tests\TestCase;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Cloud\ElasticSearch\Factories\ElasticSearchResultFactory;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\OrderType;
use Plenty\Modules\Order\Models\OrderItemType;

/**
 * Created by PhpStorm.
 * User: lukasmatzen
 * Date: 02.11.18
 * Time: 15:18
 */
class ElasticSearchTest extends TestCase
{
    /** @var ElasticSearchResultFactory $esFactory */
    protected $esFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->esFactory = pluginApp(ElasticSearchResultFactory::class);
    }

    /** @test */
    public function it_runs_es_mapping()
    {
        $result = $this->esFactory->makeWrapped();
        $this->assertNotNull($result);
    }

    /** @test */
    public function it_checks_the_properties_of_variation_image()
    {
        $variationId = 100;

        $itemSearchServiceMock = Mockery::mock(ItemSearchService::class);
        $this->replaceInstanceByMock(ItemSearchService::class, $itemSearchServiceMock);

        $singleItemMock = Mockery::mock(SingleItem::class);
        $singleItemMock->shouldReceive('getSearchFactory')->with([])->andReturn([]);
        $this->replaceInstanceByMock(SingleItem::class, $singleItemMock);

        /**
         * @var ResultFieldTemplate $resultFieldTemplate
         */
        $resultFieldTemplate = pluginApp(ResultFieldTemplate::class);
        $resultFieldTemplate->setTemplates([ResultFieldTemplate::TEMPLATE_SINGLE_ITEM => 'Ceres::ResultFields.SingleItem']);
        $this->replaceInstanceByMock(ResultFieldTemplate::class, $resultFieldTemplate);

        $result = $this->esFactory->makeWrapped([
            "variation.id" => $variationId
        ]);
        $itemSearchServiceMock->shouldReceive('getResult')->with(Mockery::any())->andReturn($result);

        /** @var ItemService $itemService */
        $itemService = pluginApp(ItemService::class);

        $itemImageUrl = $itemService->getVariationImage($variationId);

        $this->assertNotNull($itemImageUrl);
    }

    /** @test */
    public function it_runs_localized_order()
    {
        $itemSearchServiceMock = Mockery::mock(ItemSearchService::class);
        $this->replaceInstanceByMock(ItemSearchService::class, $itemSearchServiceMock);
        $itemSearchServiceMock->shouldReceive('getResults')->andReturn($this->esFactory->makeWrapped());
        $itemSearchServiceMock->shouldReceive('getResult')->andReturn($this->esFactory->makeWrapped());

        $this->actingAs($user = $this->createUser(false, ['order.create']));
        /** @var Variation $variation */
        $variation = Variation::query()->inRandomOrder()->first();
        /** @var Address $address */
        $address = factory(Address::class)->create();

        /** @var OrderRepositoryContract $orders */
        $orders = app()->make(OrderRepositoryContract::class);
        $createdOrder = $orders->createOrder([
            'typeId' => OrderType::TYPE_SALES_ORDER,
            'plentyId' => config('plentyId'),
            'ownerId' => $user->id,
            'orderItems' => [
                [
                    'typeId' => OrderItemType::TYPE_VARIATION,
                    'itemVariationId' => $variation->id,
                    'quantity' => 1,
                    'orderItemName' => empty($variation->name) ? 'The name of the variation is empty, so here is another one.' : $variation->name,
                    'amounts' => [
                        [
                            'currency' => 'EUR',
                            'priceOriginalGross' => 14.99
                        ]
                    ]
                ]
            ],
            'addressRelations' => [
                [
                    'typeId' => AddressRelationType::BILLING_ADDRESS,
                    'addressId' => $address->id,
                ],
                [
                    'typeId' => AddressRelationType::DELIVERY_ADDRESS,
                    'addressId' => $address->id
                ]
            ]
        ]);

        $localizedOrder = LocalizedOrder::wrap($createdOrder);

        $this->assertNotNull($localizedOrder);
    }
}