<?php //strict

namespace IO\Services;

use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use IO\Services\ItemService;

/**
 * Class BasketService
 * @package IO\Services
 */
class BasketService
{
	/**
	 * @var BasketItemRepositoryContract
	 */
	private $basketItemRepository;

    /**
     * BasketService constructor.
     * @param BasketItemRepositoryContract $basketItemRepository
     */
	public function __construct(BasketItemRepositoryContract $basketItemRepository)
	{
		$this->basketItemRepository = $basketItemRepository;
	}

	/**
	 * Return the basket as an array
	 * @return Basket
	 */
	public function getBasket():Basket
	{
		return pluginApp(BasketRepositoryContract::class)->load();
	}

    /**
     * List the basket items
     * @return array
     */
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

    /**
     * Get a basket item
     * @param int $basketItemId
     * @return array
     */
	public function getBasketItem(int $basketItemId):array
	{
		$basketItem = $this->basketItemRepository->findOneById( $basketItemId );
        if( $basketItem === null )
        {
            return array();
        }
        $basketItemData = $this->getBasketItemData( $basketItem->toArray() );
        return $this->addVariationData( $basketItem, $basketItemData[$basketItem->variationId] );
    }

    /**
     * Load the variation data for the basket item
     * @param BasketItem $basketItem
     * @param $variationData
     * @return array
     */
	private function addVariationData( BasketItem $basketItem, $variationData ):array
    {
        $arr = $basketItem->toArray();
        $arr["variation"] = $variationData;
        return $arr;
    }

    /**
     * Add an item to the basket or update the basket
     * @param array $data
     * @return array
     */
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

    /**
     * Update a basket item
     * @param int $basketItemId
     * @param array $data
     * @return array
     */
	public function updateBasketItem(int $basketItemId, array $data):array
	{
		$data['id'] = $basketItemId;
		$this->basketItemRepository->updateBasketItem($basketItemId, $data);
		return $this->getBasketItems();
	}

    /**
     * Delete an item from the basket
     * @param int $basketItemId
     * @return array
     */
	public function deleteBasketItem(int $basketItemId):array
	{
		$this->basketItemRepository->removeBasketItem($basketItemId);
		return $this->getBasketItems();
	}

    /**
     * Check whether the item is already in the basket
     * @param array $data
     * @return null|BasketItem
     */
	public function findExistingOneByData(array $data)
	{
		return $this->basketItemRepository->findExistingOneByData($data);
	}

    /**
     * Get the data of the basket items
     * @param array $basketItems
     * @return array
     */
	private function getBasketItemData($basketItems = array()):array
	{
		if(count($basketItems) <= 0)
		{
			return array();
		}

		$basketItemVariationIds = array();
		foreach($basketItems as $basketItem)
		{
			array_push($basketItemVariationIds, $basketItem->variationId);
		}

		$items  = pluginApp(ItemService::class)->getVariations($basketItemVariationIds);
		$result = array();
		foreach($items as $item)
		{
			$variationId          = $item->variationBase->id;
			$result[$variationId] = $item;
		}

		return $result;
	}

    public function resetBasket()
    {
        $basketItems = $this->basketItemRepository->all();
        foreach( $basketItems as $basketItem )
        {
            $this->basketItemRepository->removeBasketItem( $basketItem->id );
        }
    }


}
