<?php //strict
namespace IO\Controllers;

use IO\Constants\SessionStorageKeys;
use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use IO\Guards\AuthGuard;

/**
 * Class CheckoutController
 * @package IO\Controllers
 */
class CheckoutController extends LayoutController
{
    /**
     * Prepare and render the data for the checkout
     * @param BasketService $basketService
     * @param CustomerService $customerService
     * @param BasketItemRepositoryContract $basketItemRepository
     * @return string
     */
    public function showCheckout(BasketService $basketService,  CustomerService $customerService, BasketItemRepositoryContract $basketItemRepository): string
    {
        $basketItems = $basketItemRepository->all();
        $sessionStorage = pluginApp(SessionStorageService::class);

        if( $sessionStorage->getSessionValue(SessionStorageKeys::GUEST_EMAIL) == null &&
            $customerService->getContactId() <= 0)
        {
            AuthGuard::redirect("/login", ["backlink" => AuthGuard::getUrl()]);
        }
        else if(!count($basketItems))
        {
            AuthGuard::redirect("/", []);
        }

        $basket = $basketService->getBasketForTemplate();

        return $this->renderTemplate(
            "tpl.checkout",
            [
                "basket" => $basket
            ]
        );
    }
}
