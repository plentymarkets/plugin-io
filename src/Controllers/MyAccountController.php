<?php //strict
namespace IO\Controllers;

use IO\Guards\AuthGuard;
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

        if ( !$shopBuilderRequest->isShopBuilder() )
        {
            /** @var AuthGuard $guard */
            $guard = pluginApp(AuthGuard::class);
            $guard->assertOrRedirect( true, "/login" );
        }

		return $this->renderTemplate(
		    "tpl.my-account", [
                "category" => $category
            ],
            false );
	}
}
