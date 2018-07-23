<?php //strict
namespace IO\Controllers;

use IO\Constants\SessionStorageKeys;
use IO\Services\BasketService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use IO\Services\UrlBuilder\UrlQuery;
use IO\Services\WebstoreConfigurationService;
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
     * @param WebstoreConfigurationService $webstoreConfigurationService;
     * @return string
     */
    public function showCheckout(BasketService $basketService,  CustomerService $customerService, BasketItemRepositoryContract $basketItemRepository, WebstoreConfigurationService $webstoreConfigurationService): string
    {
        $basketItems = $basketItemRepository->all();
        /**
         * @var SessionStorageService $sessionStorage
         */
        $sessionStorage = pluginApp(SessionStorageService::class);

        $url = $this->urlService->getHomepageURL();
        if( $sessionStorage->getSessionValue(SessionStorageKeys::GUEST_EMAIL) == null &&
            $customerService->getContactId() <= 0)
        {
            if(substr($url, -1) !== '/')
            {
                $url .= '/';
            }
            $url .= 'login';
            $url .= UrlQuery::shouldAppendTrailingSlash() ? '/' : '';

            AuthGuard::redirect($url, ["backlink" => AuthGuard::getUrl()]);
        }
        else if(!count($basketItems))
        {
            AuthGuard::redirect($url, []);
        }

        $basket = $basketService->getBasketForTemplate();

        return $this->renderTemplate(
            "tpl.checkout",
            [
                "basket" => $basket
            ],
            false
        );
    }
}
