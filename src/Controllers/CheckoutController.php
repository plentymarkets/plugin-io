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
use Plenty\Plugin\Log\Loggable;

/**
 * Class CheckoutController
 * @package IO\Controllers
 */
class CheckoutController extends LayoutController
{
    use Loggable;

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
        $shopBuilderRequest->setMainContentType('checkout');

        if ( !$shopBuilderRequest->isShopBuilder() )
        {
            if( $sessionStorage->getSessionValue(SessionStorageKeys::GUEST_EMAIL) == null
                && $customerService->getContactId() <= 0 )
            {
                $this->getLogger(__CLASS__)->info("IO::Debug.CheckoutController_notLoggedIn");
                AuthGuard::redirect(
                    pluginApp(ShopUrls::class)->login,
                    ["backlink" => AuthGuard::getUrl()]
                );
            }
            else if(!count($basketItemRepository->all()))
            {
                $this->getLogger(__CLASS__)->info("IO::Debug.CheckoutController_emptyBasket");
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
}
