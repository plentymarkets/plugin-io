<?hh //strict
namespace LayoutCore\Controllers;

use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Services\BasketService;


class CheckoutController extends LayoutController
{
    public function showCheckout( BasketService $basketService ): string
    {
        $basket = $basketService->getBasket();

        return $this->renderTemplate(
            "tpl.checkout",
            array(
                "basket" => $basket
            )
        );
    }
}
