<?php

namespace IO\Controllers;

use IO\Services\ItemLastSeenService;
use IO\Services\ItemSearch\SearchPresets\CrossSellingItems;
use IO\Services\ItemSearch\SearchPresets\SingleItem;
use IO\Services\ItemSearch\Services\ItemSearchService;

/**
 * Class ItemController
 * @package IO\Controllers
 */
class ItemController extends LayoutController
{
    /**
     * Prepare and render the item data.
     * @param string $slug
     * @param int $itemId The itemId read from current request url. Will be null if item url does not contain a slug.
     * @param int $variationId
     * @return string
     */
	public function showItem(
		string $slug = "",
		int $itemId = 0,
		int $variationId = 0
	)
	{
//	    $templateContainer = $this->buildTemplateContainer('tpl.item');

	    $itemSearchOptions = [
	        'itemId'        => $itemId,
            'variationId'   => $variationId,
            'setCategory'   => true
        ];
	    /** @var ItemSearchService $itemSearchService */
	    $itemSearchService = pluginApp( ItemSearchService::class );
	    $result = $itemSearchService->getResults([
	        'item'              => SingleItem::getSearchFactory( $itemSearchOptions ),
            'crossSellingItems' => CrossSellingItems::getSearchFactory( $itemSearchOptions )
        ]);


	    $itemResult = $result['item'];
        $itemResult['CrossSellingItems'] = $result['crossSellingItems'];

        if(empty($itemResult['documents']))
        {
            return '';
        }
        else
        {
            $resultVariationId = $itemResult['documents'][0]['data']['variation']['id'];

            if((int)$resultVariationId <= 0)
            {
                $resultVariationId = $variationId;
            }

            if((int)$resultVariationId > 0)
            {
                /**
                 * @var ItemLastSeenService $itemLastSeenService
                 */
                $itemLastSeenService = pluginApp(ItemLastSeenService::class);
                $itemLastSeenService->setLastSeenItem( $variationId );
            }

            return $this->renderTemplate(
                'tpl.item',
                [
                    'item' => $itemResult
                ]
            );
        }
	}

	/**
	 * @param int $itemId
	 * @param int $variationId
	 * @return string
	 */
	public function showItemWithoutName(int $itemId, $variationId = 0):string
	{
		return $this->showItem("", $itemId, $variationId);
	}

	/**
	 * @param int $itemId
	 * @return string
	 */
	public function showItemFromAdmin(int $itemId):string
	{
		return $this->showItem("", $itemId, 0);
	}
    
    public function showItemOld($name = null, $itemId = null)
    {
        if(is_null($itemId))
        {
            $itemId = $name;
        }
        
        return $this->showItem("", (int)$itemId, 0);
    }
}
