<?php

namespace IO\Tests\Unit;

use Mockery;
use IO\Tests\TestCase;
use IO\Services\BasketService;
use Plenty\Modules\Basket\Exceptions\BasketItemCheckException;
use Plenty\Modules\Basket\Repositories\BasketItemRepository;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceTest extends TestCase
{

    /** @var BasketService $basketService  */
	protected $basketService;
	/** @var BasketItemRepository */
	protected $basketItemRepositoryMock;


    protected function setUp(): void
    {
        parent::setUp();

        $this->basketItemRepositoryMock = Mockery::mock(BasketItemRepository::class);
        $this->replaceInstanceByMock(BasketItemRepository::class, $this->basketItemRepositoryMock);

        $this->basketService = pluginApp(BasketService::class);
    }

    /** @test */
    public function it_throw_the_basket_item_check_exception()
    {
        //Fake Item
        $item1 = ['variationId' => 1, 'quantity' => 1, 'template' => 'test'];
        $errorCode = 111;
        $basketItemCheckException = new BasketItemCheckException(BasketItemCheckException::NOT_ENOUGH_STOCK_FOR_VARIATION);

        // allow method `all()` to be called but dont report an error, if it isn't
        $this->basketItemRepositoryMock->allows(['all' => []]);

        $this->basketItemRepositoryMock->shouldReceive('findExistingOneByData')
            ->once()
            ->andReturn(null);

        $this->basketItemRepositoryMock->shouldReceive('addBasketItem')
            ->andThrow($basketItemCheckException);

        $error = $this->basketService->addBasketItem($item1);

        $this->assertEquals($errorCode, $error['code']);
    }


    /** @test */
    public function it_throw_an_exception_with_sample_error_code()
    {
        //Fake Item
        $item1 = ['variationId' => 1, 'quantity' => 1, 'template' => 'test'];
        $errorCode = 404;
        $exception = new \Exception('', $errorCode);

        // allow method `all()` to be called but dont report an error, if it isn't
        $this->basketItemRepositoryMock->allows(['all' => []]);

        $this->basketItemRepositoryMock->shouldReceive('findExistingOneByData')
            ->once()
            ->andReturn(null);

        $this->basketItemRepositoryMock->shouldReceive('addBasketItem')
            ->andThrow($exception);

        $error = $this->basketService->addBasketItem($item1);

        $this->assertEquals($errorCode, $error['code']);
    }
}
