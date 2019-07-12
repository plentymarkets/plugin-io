<?php //strict
namespace IO\Controllers;

use IO\Helper\RouteConfig;
use IO\Services\CategoryService;

/**
 * Class HomepageController
 * @package IO\Controllers
 */
class HomepageController extends LayoutController
{
    /**
     * Prepare and render the data for the homepage
     * @return string
     */
    public function showHomepage()
    {
        return $this->renderTemplate(
            "tpl.home",
            [
                "object" => ""
            ]
        );
    }
    
    public function showHomepageCategory()
    {
        /** @var CategoryService $categoryService */
        $categoryService = pluginApp(CategoryService::class);
        $homepageCategory = $categoryService->get(RouteConfig::getCategoryId(RouteConfig::HOME));
        
        return $this->renderTemplate(
            "tpl.home.category",
            [
                "category" => $homepageCategory
            ]
        );
    }
}
