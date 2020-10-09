<?php

namespace IO\Controllers;

use IO\Helper\RouteConfig;
use IO\Services\CategoryService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Http\Request;

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
        $request = pluginApp(Request::class);
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
                "tagName" => $tagName,
                'sorting' => $request->get('sorting', null),
                'itemsPerPage' => $request->get('items', null),
                'page' => $request->get('page', null),
                'facets' => $request->get('facets', '')
            ]
        );
    }
}
