<?php //strict

namespace IO\Services;

use IO\Services\ItemLoader\Loaders\BasketItems;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Models\BasketItem;
use Plenty\Modules\Frontend\Contracts\Checkout;
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
     * @var Checkout
     */
    private $checkout;
    
    private $template = '';

    /**
     * BasketService constructor.
     * @param BasketItemRepositoryContract $basketItemRepository
     * @param Checkout $checkout
     */
	public function __construct(BasketItemRepositoryContract $basketItemRepository, Checkout $checkout)
	{
		$this->basketItemRepository = $basketItemRepository;
        $this->checkout = $checkout;
	}
	
	public function setTemplate(string $template)
    {
        $this->template = $template;
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
	
	public function getBasketItemsForTemplate(string $template = ''):array
    {
        if(!strlen($template))
        {
            $template = $this->template;
        }
        
        $result = array();
    
        $basketItems = $this->basketItemRepository->all();
        $basketItemData = $this->getBasketItemData( $basketItems, $template );
    
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
		    $orderParam = [
                'propertyId'  => 1,
                'basketItemId' => 127,
                'type' => 'text',
                'name' => 'Personaliesierungstext',
                'value' => 'test test test'
            ];
		    
                $data['basketItemOrderParams'] = [$orderParam];
		    
			$data['id']       = $basketItem->id;
			$data['quantity'] = (int)$data['quantity'] + $basketItem->quantity;
			$this->basketItemRepository->updateBasketItem($basketItem->id, $data);
		}
		else
		{
			$this->basketItemRepository->addBasketItem($data);
		}

		return $this->getBasketItemsForTemplate();
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
		return $this->getBasketItemsForTemplate();
	}

    /**
     * Delete an item from the basket
     * @param int $basketItemId
     * @return array
     */
	public function deleteBasketItem(int $basketItemId):array
	{
		$this->basketItemRepository->removeBasketItem($basketItemId);
		return $this->getBasketItemsForTemplate();
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
	private function getBasketItemData($basketItems = array(), string $template = ''):array
	{
        if(!strlen($template))
        {
            $template = $this->template;
        }
        
		if(count($basketItems) <= 0)
		{
			return array();
		}

		$basketItemVariationIds = array();
        $basketVariationQuantities = array();
        
		foreach($basketItems as $basketItem)
		{
			array_push($basketItemVariationIds, $basketItem->variationId);
            $basketVariationQuantities[$basketItem->variationId] = $basketItem->quantity;
		}

        $items = pluginApp(ItemLoaderService::class)
            ->loadForTemplate($template, [BasketItems::class], ['variationIds' => $basketItemVariationIds, 'basketVariationQuantities' => $basketVariationQuantities]);
        
        $result = array();
        foreach($items['documents'] as $item)
        {
            $variationId          = $item['data']['variation']['id'];
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
    
    /**
     * Set the billing address id
     * @param int $billingAddressId
     */
    public function setBillingAddressId(int $billingAddressId)
    {
        $this->checkout->setCustomerInvoiceAddressId($billingAddressId);
    }
    
    /**
     * Return the billing address id
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->checkout->getCustomerInvoiceAddressId();
    }
    
    /**
     * Set the delivery address id
     * @param int $deliveryAddressId
     */
    public function setDeliveryAddressId(int $deliveryAddressId)
    {
        $this->checkout->setCustomerShippingAddressId($deliveryAddressId);
    }
    
    /**
     * Return the delivery address id
     * @return int
     */
    public function getDeliveryAddressId()
    {
        return $this->checkout->getCustomerShippingAddressId();
    }
}
