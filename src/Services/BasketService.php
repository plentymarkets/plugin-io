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
	 * @return array
	 */
	public function getBasket():array
	{
		$basket         = $this->basketRepository->load();
		$basketItems    = $this->basketItemRepository->all();

		if($basketItems instanceof BasketService)
		{
			$basketItemData = $this->getBasketItemData($basketItems);
		}
		else
		{
			$basketItemData = null;
		}

		return [
			"basket"      => $basket,
			"basketItems" => $basketItems,
			"items"       => $basketItemData
		];
	}
	
	public function getBasketItems():array
	{
		$basketItems    = $this->basketItemRepository->all();
		$basketItemData = $this->getBasketItemData($basketItems);
		return [
			"basketItems" => $basketItems,
			"items"       => $basketItemData
		];
	}
	
	public function getBasketItem(int $basketItemId):array
	{
		$basketItem = $this->basketItemRepository->findOneById($basketItemId);
		if($basketItem === null)
		{
			return [];
		}
		$basketItemData = $this->getBasketItemData([$basketItem]);
		return [
			"basketItem" => $basketItem,
			"item"       => $basketItemData
		];
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
		
		return $this->getBasket();
	}
	
	public function updateBasketItem(int $basketItemId, array $data):array
	{
		$data['id'] = $basketItemId;
		$this->basketItemRepository->updateBasketItem($basketItemId, $data);
		return $this->getBasket();
	}
	
	public function deleteBasketItem(int $basketItemId):array
	{
		$this->basketItemRepository->removeBasketItem($basketItemId);
		return $this->getBasket();
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
