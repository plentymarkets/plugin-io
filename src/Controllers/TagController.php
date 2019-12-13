<?php

namespace IO\Controllers;

use IO\Helper\RouteConfig;
use IO\Services\CategoryService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;

/**
 * Class TagController
 * @package IO\Controllers
 */
class TagController extends LayoutController
{
    /**
     * @param string $tagName
     * @param int $tagId
     */
    public function showItemByTag(string $tagName = "", int $tagId = null)
    {
        $category = null;
        if(RouteConfig::getCategoryId(RouteConfig::SEARCH) > 0)
        {
            /** @var CategoryService $categoryService */
            $categoryService = pluginApp(CategoryService::class);
            $category = $categoryService->get(RouteConfig::getCategoryId(RouteConfig::SEARCH));
            $categoryService->setCurrentCategory($category);

            /** @var ShopBuilderRequest $shopBuilderRequest */
            $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
            $shopBuilderRequest->setMainContentType($category->type);
            $shopBuilderRequest->setMainCategory($category->id);
        }

        return $this->renderTemplate(
            'tpl.tags',
            [
                "category" => $category,
                "tagId" => $tagId,
                "tagName" => $tagName
            ]
        );
    }
}
