<?php //strict
namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Constants\SessionStorageKeys;
use IO\Extensions\Constants\ShopUrls;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use IO\Services\UrlService;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use IO\Guards\AuthGuard;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Plugin\Http\Response;

/**
 * Class CheckoutController
 * @package IO\Controllers
 */
class CheckoutController extends LayoutController
{
    /**
     * Prepare and render the data for the checkout
     * @param Category $category
     * @return string
     */
    public function showCheckout($category = null)
    {
        /** @var BasketItemRepositoryContract $basketItemRepository */
        $basketItemRepository = pluginApp(BasketItemRepositoryContract::class);

        /** @var SessionStorageService $sessionStorage */
        $sessionStorage = pluginApp(SessionStorageService::class);

        /** @var CustomerService $customerService */
        $customerService = pluginApp(CustomerService::class);

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if ( !$shopBuilderRequest->isShopBuilder() )
        {
            if( $sessionStorage->getSessionValue(SessionStorageKeys::GUEST_EMAIL) == null
                && $customerService->getContactId() <= 0 )
            {
                AuthGuard::redirect(
                    pluginApp(ShopUrls::class)->login,
                    ["backlink" => AuthGuard::getUrl()]
                );
            }
            else if(!count($basketItemRepository->all()))
            {
                AuthGuard::redirect(pluginApp(ShopUrls::class)->home, []);
            }
        }
        else if ( is_null($category) )
        {
            $categoryController = pluginApp(CategoryController::class);
            return $categoryController->showCategory("checkout");
        }
        

        return $this->renderTemplate(
            "tpl.checkout",
            [
                'category' => $category
            ],
            false
        );
    }

    public function redirectCheckoutCategory()
    {
        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        $categoryResponse = $categoryController->showCategory("checkout");
        if (!($categoryResponse instanceof Response && $categoryResponse->status() == ResponseCode::NOT_FOUND))
        {
            return $categoryResponse;
        }

        /** @var UrlService $urlService */
        $urlService = pluginApp(UrlService::class);
        return $urlService->redirectTo(
            pluginApp(ShopUrls::class)->checkout
        );
    }
}
