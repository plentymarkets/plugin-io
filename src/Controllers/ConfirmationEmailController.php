<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;
use IO\Services\OrderService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;

/**
 * Class ConfirmationEmailController
 * @package IO\Controllers
 */
class ConfirmationEmailController extends LayoutController
{
    /**
     * Prepare and render the data for the order confirmation
     * @return string
     */
    public function showConfirmation($orderHash = '', int $orderId = 0)
    {
        if(strlen($orderHash) && (int)$orderId > 0)
        {
            return pluginApp(Response::class)->redirectTo('confirmation/'.$orderId.'/'.$orderHash);
        }
        
        return $this->renderTemplate(
            "tpl.confirmation",
            [
                "data" => ''
            ]
        );
    }
}
