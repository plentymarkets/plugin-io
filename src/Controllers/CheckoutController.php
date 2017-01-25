<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\BasketService;
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
     * @return string
     */
    public function showCheckout(BasketService $basketService, BasketItemRepositoryContract $basketItemRepository): string
    {
        $basketItems = $basketItemRepository->all();

        if(!count($basketItems))
        {
            AuthGuard::redirect("/", []);
        }

        $basket = $basketService->getBasket();

        return $this->renderTemplate(
            "tpl.checkout",
            [
                "basket" => $basket
            ]
        );
    }
}
