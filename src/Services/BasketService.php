<?php //strict

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
	/**
	 * @var BasketRepositoryContract
	 */
	private $basketRepository;
	/**
	 * @var BasketItemRepositoryContract
	 */
	private $basketItemRepository;
	/**
	 * @var ItemService
	 */
	private $itemService;


	public function __construct(
		BasketRepositoryContract $basketRepository,
		BasketItemRepositoryContract $basketItemRepository,
		ItemService $itemService
	)
	{
		$this->basketRepository     = $basketRepository;
		$this->basketItemRepository = $basketItemRepository;
		$this->itemService          = $itemService;
	}

	/**
	 * Returns basket as array
	 * @return Basket
	 */
	public function getBasket():Basket
	{
		return $this->basketRepository->load();
	}

	public function getBasketItems():array
	{
		$result = array();
        $basketItems = $this->basketItemRepository->all();
        $basketItemData = $this->getBasketItemData( $basketItems );
        foreach( $basketItems as $basketItem )
        {
            array_push(
                $result,
                $this->addVariationData($basketItem, $basketItemData[$basketItem->variationId])
            );
        }
        return $result;
	}

	public function getBasketItem(int $basketItemId):array
	{
		$basketItem = $this->basketItemRepository->findOneById( $basketItemId );
        if( $basketItem === null )
        {
            return array();
        }
        $basketItemData = $this->getBasketItemData( [$basketItem] );
        return $this->addVariationData( $basketItem, $basketItemData[$basketItem->variationId] );
    }

	private function addVariationData( BasketItem $basketItem, mixed $variationData ):array
    {
        $arr = $basketItem->toArray();
        $arr["variation"] = $variationData;
        return $arr;
    }

	public function addBasketItem(array $data):array
	{
		$basketItem = $this->findExistingOneByData($data);
		if($basketItem instanceof BasketItem)
		{
			$data['id']       = $basketItem->id;
			$data['quantity'] = (int)$data['quantity'] + $basketItem->quantity;
			$this->basketItemRepository->updateBasketItem($basketItem->id, $data);
		}
		else
		{
			$this->basketItemRepository->addBasketItem($data);
		}

		return $this->getBasketItems();
	}

	public function updateBasketItem(int $basketItemId, array $data):array
	{
		$data['id'] = $basketItemId;
		$this->basketItemRepository->updateBasketItem($basketItemId, $data);
		return $this->getBasketItems();
	}

	public function deleteBasketItem(int $basketItemId):array
	{
		$this->basketItemRepository->removeBasketItem($basketItemId);
		return $this->getBasketItems();
	}

	public function findExistingOneByData(array $data):BasketItem
	{
		return $this->basketItemRepository->findExistingOneByData($data);
	}

	private function getBasketItemData(array $basketItems = []):array
	{
		if(count($basketItems) <= 0)
		{
			return [];
		}

		$basketItemVariationIds = [];
		foreach($basketItems as $basketItem)
		{
			array_push($basketItemVariationIds, $basketItem->variationId);
		}

		$items  = $this->itemService->getVariations($basketItemVariationIds);
		$result = [];
		foreach($items as $item)
		{
			$variationId          = $item->variationBase->id;
			$result[$variationId] = $item;
		}

		return $result;
	}


}
