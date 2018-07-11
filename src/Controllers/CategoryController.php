<?php //strict

namespace IO\Controllers;

use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Category\Models\CategoryDetails;
use Plenty\Modules\Category\Models\CategoryClient;
use Plenty\Plugin\Http\Request;

/**
 * Class CategoryController
 * @package IO\Controllers
 */
class CategoryController extends LayoutController
{
	/**
	 * Prepare and render the data for categories
	 * @param string $lvl1 Level 1 of category url. Will be null at root page
	 * @param string $lvl2 Level 2 of category url.
	 * @param string $lvl3 Level 3 of category url.
	 * @param string $lvl4 Level 4 of category url.
	 * @param string $lvl5 Level 5 of category url.
	 * @param string $lvl6 Level 6 of category url.
	 * @return string
	 */
	public function showCategory(
		$lvl1 = null,
		$lvl2 = null,
		$lvl3 = null,
		$lvl4 = null,
		$lvl5 = null,
		$lvl6 = null):string
	{
	    /** @var Request $request */
	    $request = pluginApp(Request::class);

	    /** @var SessionStorageService $sessionService */
        $sessionService  = pluginApp(SessionStorageService::class);

        /** @var WebstoreConfigurationService $webstoreService */
        $webstoreService = pluginApp(WebstoreConfigurationService::class);

        $category = $this->categoryRepo->findCategoryByUrl($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $lvl6);

        if($category === null
            || !($category->clients->where('plenty_webstore_category_link_webstore_id', $webstoreService->getWebstoreConfig()->webstoreId)->first() instanceof CategoryClient)
            || !($category->details->where('lang', $sessionService->getLang())->first() instanceof CategoryDetails))
        {
            return '';
        }
        
        $this->categoryService->setCurrentCategory($category);
        
        return $this->renderTemplate(
            "tpl.category." . $category->type,
            [
                'category'      => $category,
                'sorting'       => $request->get('sorting', null),
                'itemsPerPage'  => $request->get('items', null),
                'page'          => $request->get('page', null),
                'facets'        => $request->get('facets', '')
            ]
        );
	}

}
