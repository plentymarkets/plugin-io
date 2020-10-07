<?php //strict

namespace IO\Controllers;

use IO\Helper\RouteConfig;
use Plenty\Plugin\Http\Request;

class ItemSearchController extends LayoutController
{
    public function showSearch($category = null): string
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        return $this->renderTemplate(
            "tpl.search",
            [
                'category' => $category,
                'page' => $request->get('page', null),
                'itemsPerPage' => $request->get('items', null),
                'query' => $request->get('query', null),
                'sorting' => $request->get('sorting', null),
                'facets' => $request->get('facets', '')
            ],
            false
        );
    }

    /**
     * Redirect to new search url from category when search route
     * is enabled and called.
     *
     * @param string $tagName tagName from route /tag/tagName convert to search string
     */
    public function redirectToSearch($tagName = null)
    {
        if (!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var Request $request */
        $request = pluginApp(Request::class);

        $params = [];
        if(!is_null($tagName)) {
            $params['query'] = $tagName;
        } else {
            $params = $request->query();
            unset($params['plentyMarkets']);
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);

        return $categoryController->redirectRoute(RouteConfig::SEARCH, $params);
    }
}
