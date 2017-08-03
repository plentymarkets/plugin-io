<?php
namespace IO\Controllers;

use IO\Helper\CategoryKey;
use IO\Services\CategoryService;
use IO\Services\ItemLastSeenService;
use IO\Services\ItemLoader\Loaders\CrossSellingItems;
use IO\Services\ItemService;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\ItemLoader\Loaders\SingleItemAttributes;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\SessionStorageService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Plugin\Application;
use Plenty\Plugin\Http\Response;

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
	)
	{
        $loaderOptions = [];
        $itemService = pluginApp(ItemService::class);

        if((int)$variationId > 0)
        {
            $loaderOptions['variationId'] = $variationId;
        }
        elseif($itemId > 0)
        {
            $loaderOptions['itemId'] = $itemId;
        }
        
        $loaderOptions['crossSellingItemId'] = $itemId;

        $attributeMap = $itemService->getVariationAttributeMap($itemId);
        $attributeNameMap = $itemService->getAttributeNameMap($itemId);

        $templateContainer = $this->buildTemplateContainer("tpl.item", $loaderOptions);

        /** @var ItemLoaderService $loaderService */
        $loaderService = $templateContainer->getTemplateData()['itemLoader'];
        $loaderService->setLoaderClassList(["single" => [SingleItem::class, SingleItemAttributes::class], "multi" => [CrossSellingItems::class]]);

        $itemResult = $loaderService->load();

        if(empty($itemResult['documents']))
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
            $itemNames = [
                'name1' => $itemResult['documents'][0]['data']['texts']['name1'],
                'name2' => $itemResult['documents'][0]['data']['texts']['name2'],
                'name3' => $itemResult['documents'][0]['data']['texts']['name3']
            ];

            $this->setCategory($itemResult['documents'][0]['data']['defaultCategories'], $itemNames);

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
                array_merge(['item' => $itemResult, 'attributeNameMap' => $attributeNameMap, 'variations' => $attributeMap], $templateContainer->getTemplateData(), ['http_host' => $_SERVER['HTTP_HOST']])
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
    
    private function setCategory($defaultCategories, $itemNames)
    {
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
                $categoryService->setCurrentItem($itemNames);
            }
        }
    }
}
