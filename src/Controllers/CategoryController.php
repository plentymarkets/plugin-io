<?php //strict

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Helper\RouteConfig;
use IO\Guards\AuthGuard;
use IO\Services\SessionStorageService;
use IO\Services\UrlService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Application;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

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
        $lvl6 = null)
    {
        /** @var SessionStorageService $sessionService */
        $sessionService  = pluginApp(SessionStorageService::class);
        $lang = $sessionService->getLang();
        $webstoreId = pluginApp(Application::class)->getWebstoreId();

        return $this->renderCategory(
            $this->categoryRepo->findCategoryByUrl($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $lvl6, $webstoreId, $lang)
        );
	}

	public function showCategoryById($categoryId)
    {
        /** @var SessionStorageService $sessionService */
        $sessionService  = pluginApp(SessionStorageService::class);
        $lang = $sessionService->getLang();

        return $this->renderCategory(
            $this->categoryRepo->get( $categoryId, $lang )
        );
    }

    public function redirectToCategory( $categoryUrlLevels, $redirectUrl )
    {
        // Check if category can be displayed
        $categoryResponse = $this->showCategory(
            $categoryUrlLevels[0],
            $categoryUrlLevels[1],
            $categoryUrlLevels[2],
            $categoryUrlLevels[3],
            $categoryUrlLevels[4],
            $categoryUrlLevels[5]
        );
        if (!($categoryResponse instanceof Response && $categoryResponse->status() == ResponseCode::NOT_FOUND))
        {
            // category cannot be displayed. Return 404
            return $categoryResponse;
        }

        /** @var UrlService $urlService */
        $urlService = pluginApp(UrlService::class);
        return $urlService->redirectTo($redirectUrl);
    }

	private function renderCategory($category)
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        if ($category === null || (($category->clients->count() == 0 || $category->details->count() == 0) && !$this->app->isAdminPreview()))
        {
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }

        $this->categoryService->setCurrentCategory($category);
        if ($this->categoryService->isHidden($category->id)) {
            $guard = pluginApp(AuthGuard::class);
            $guard->assertOrRedirect( true, '/login');
        }

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if ( RouteConfig::getCategoryId( RouteConfig::CHECKOUT ) === $category->id || $shopBuilderRequest->getPreviewContentType() === 'checkout')
        {
            RouteConfig::overrideCategoryId(RouteConfig::CHECKOUT, $category->id);

            /** @var CheckoutController $checkoutController */
            $checkoutController = pluginApp(CheckoutController::class);
            return $checkoutController->showCheckout( $category );
        }

        if ( RouteConfig::getCategoryId( RouteConfig::MY_ACCOUNT ) === $category->id || $shopBuilderRequest->getPreviewContentType() === 'myaccount')
        {
            RouteConfig::overrideCategoryId(RouteConfig::MY_ACCOUNT, $category->id);

            /** @var MyAccountController $myAccountController */
            $myAccountController = pluginApp(MyAccountController::class);
            return $myAccountController->showMyAccount( $category );
        }

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
