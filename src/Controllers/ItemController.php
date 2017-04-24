<?php
namespace IO\Controllers;

use IO\Helper\CategoryKey;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\ItemLoader\Loaders\SingleItemAttributes;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use Plenty\Modules\Category\Models\Category;

/**
 * Class ItemController
 * @package IO\Controllers
 */
class ItemController extends ItemLoaderController
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
	):string
	{
		$loaderOptions = [];

		if((int)$variationId > 0)
		{
			$loaderOptions['variationId'] = $variationId;
		}
		elseif($itemId > 0)
		{
			$loaderOptions['itemId'] = $itemId;
		}

		$templateContainer = $this->buildTemplateContainer("tpl.item", $loaderOptions);
		
		/** @var ItemLoaderService $loaderService */
		$loaderService = $templateContainer->getTemplateData()['itemLoader'];
		$loaderService->setLoaderClassList([SingleItem::class, SingleItemAttributes::class]);

		$itemResult = $loaderService->load();

		if(empty($itemResult))
		{
			// If item not found, render the error category
			$itemNotFoundCategory = $this->categoryRepo->get(
				$this->categoryMap->getID(CategoryKey::ITEM_NOT_FOUND)
			);

			if($itemNotFoundCategory instanceof Category)
			{
				return $this->renderCategory($itemNotFoundCategory);
			}
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
                $itemLastSeenService->setLastSeenItem($itemResult['documents'][0]['data']['variation']['id']);
            }
            
			$templateContainer->setTemplateData(
				array_merge(['item' => $itemResult], $templateContainer->getTemplateData(), ['http_host' => $_SERVER['HTTP_HOST']])
			);

			return $this->renderTemplateContainer($templateContainer);
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
