<?hh //strict

namespace LayoutCore\Services;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Models\Record;

use LayoutCore\Services\ItemService;

class BasketService
{
    private BasketRepositoryContract $basketRepository;
    private BasketItemRepositoryContract $basketItemRepository;
    private ItemService $itemService;


    public function __construct(
        BasketRepositoryContract $basketRepository,
        BasketItemRepositoryContract $basketItemRepository,
        ItemService $itemService
    )
    {
        $this->basketRepository = $basketRepository;
        $this->basketItemRepository = $basketItemRepository;
        $this->itemService = $itemService;
    }

    /**
     * Returns basket as array
     * @return array<string, mixed>
     */
    public function getBasket():array<string, mixed>
    {
        $basket = $this->basketRepository->load();
        $basketItems = $this->basketItemRepository->all();
        $basketItemData = $this->getBasketItemData( $basketItems );
        return [
            "basket" => $basket,
            "basketItems" => $basketItems,
            "items" => $basketItemData
        ];
    }

    public function getBasketItems():array<string, mixed>
    {
        $basketItems = $this->basketItemRepository->all();
        $basketItemData = $this->getBasketItemData( $basketItems );
        return [
            "basketItems" => $basketItems,
            "items" => $basketItemData
        ];
    }

    public function getBasketItem( int $basketItemId ):array<string, mixed>
    {
        $basketItem = $this->basketItemRepository->findOneById( $basketItemId );
        if( $basketItem === null )
        {
            return array();
        }
        $basketItemData = $this->getBasketItemData( [$basketItem] );
        return [
            "basketItem" => $basketItem,
            "item" => $basketItemData
        ];
    }

    public function addBasketItem( array<string, mixed> $data ):array<string, mixed>
    {
        $basketItem = $this->findExistingOneByData($data);
        if( $basketItem instanceof BasketItem )
        {
          $data['id']        = $basketItem->id;
          $data['quantity']  = (int)$data['quantity'] + $basketItem->quantity;
          $this->basketItemRepository->updateBasketItem( $basketItem->id, $data );
        }
        else
        {
          $this->basketItemRepository->addBasketItem( $data );
        }

        return $this->getBasket();
    }

    public function updateBasketItem( int $basketItemId, array<string, mixed> $data ):array<string, mixed>
    {
        $data['id'] = $basketItemId;
        $this->basketItemRepository->updateBasketItem( $basketItemId, $data );
        return $this->getBasket();
    }

    public function deleteBasketItem( int $basketItemId ):array<string, mixed>
    {
        $this->basketItemRepository->removeBasketItem( $basketItemId );
        return $this->getBasket();
    }

    public function findExistingOneByData( array<string, mixed> $data ):?BasketItem
    {
        return $this->basketItemRepository->findExistingOneByData( $data );
    }

    private function getBasketItemData( array<BasketItem> $basketItems = array() ):array<int, Record>
    {
        if( count( $basketItems ) <= 0 )
        {
            return array();
        }

        $basketItemVariationIds = array();
        foreach( $basketItems as $basketItem )
        {
            array_push( $basketItemVariationIds, $basketItem->variationId );
        }

        $items = $this->itemService->getVariations( $basketItemVariationIds );
        $result = array();
        foreach( $items as $item )
        {
            $variationId = $item->variationBase->id;
            $result[$variationId] = $item;
        }

        return $result;
    }


}
