<?php //strict
namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Guards\AuthGuard;
use IO\Helper\RouteConfig;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;

/**
 * Class MyAccountController
 * @package IO\Controllers
 */
class MyAccountController extends LayoutController
{
    /**
     * Prepare and render the data for the my account page
     * @param Category $category
     * @return string
     * @throws \ErrorException
     */
	public function showMyAccount($category = null): string
	{
        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        $shopBuilderRequest->setMainContentType('myaccount');
        if ( !$shopBuilderRequest->isShopBuilder() )
        {
            /** @var ShopUrls $shopUrls */
            $shopUrls = pluginApp(ShopUrls::class);
            /** @var AuthGuard $guard */
            $guard = pluginApp(AuthGuard::class);
            $guard->assertOrRedirect( true, $shopUrls->login );
        }

		return $this->renderTemplate(
		    "tpl.my-account",
            [
                "category" => $category
            ],
            false );
	}

    public function redirect()
    {
        if(!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::MY_ACCOUNT);
    }
}
