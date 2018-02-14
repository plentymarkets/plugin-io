<?php

namespace IO\Controllers;

use IO\Services\CategoryService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use IO\Services\ItemLoader\Services\LoadResultFields;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\SessionStorageService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Plugin\Application;

/**
 * Class ItemController
 * @package IO\Controllers
 */
class ItemController extends LayoutController
{
    use LoadResultFields;

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
	    $templateContainer = $this->buildTemplateContainer('tpl.item');
	    
        $loaderOptions = [];

        if((int)$variationId > 0)
        {
            $loaderOptions['variationId'] = $variationId;
        }
        elseif($itemId > 0)
        {
            $loaderOptions['itemId'] = $itemId;
        }
        
        $loaderOptions['crossSellingItemId'] = $itemId;

        /** @var TwigLoaderPresets $loaderPresets */
        $loaderPresets = pluginApp(TwigLoaderPresets::class);
        $presets = $loaderPresets->getGlobals();
        
        /** @var ItemLoaderService $loaderService */
        $loaderService = pluginApp(ItemLoaderService::class);
        
        $itemResult = $loaderService
            ->setLoaderClassList($presets['itemLoaderPresets']['singleItem'])
            ->setOptions($loaderOptions)
            ->setResultFields($this->loadResultFields($templateContainer->getTemplate()))
            ->load();

        if(empty($itemResult['documents']))
        {
            return '';
        }
        else
        {
            $this->setCategory($itemResult['ItemURLs']['documents'][0]['data']);

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
    
    private function setCategory($item)
    {
        $defaultCategories = $item['defaultCategories'];

        if(count($defaultCategories))
        {
            $currentCategoryId = 0;
            foreach($defaultCategories as $defaultCategory)
            {
                if((int)$defaultCategory['plentyId'] == pluginApp(Application::class)->getPlentyId())
                {
                    $currentCategoryId = $defaultCategory['id'];
                }
            }
            if((int)$currentCategoryId > 0)
            {
                /**
                 * @var CategoryRepositoryContract $categoryRepo
                 */
                $categoryRepo = pluginApp(CategoryRepositoryContract::class);
                $currentCategory = $categoryRepo->get($currentCategoryId, pluginApp(SessionStorageService::class)->getLang());
            
                /**
                 * @var CategoryService $categoryService
                 */
                $categoryService = pluginApp(CategoryService::class);
                $categoryService->setCurrentCategory($currentCategory);
                $categoryService->setCurrentItem($item);
            }
        }
    }
}
