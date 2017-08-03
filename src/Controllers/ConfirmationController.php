<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use IO\Services\CustomerService;
use IO\Services\OrderService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Models\LocalizedOrder;

/**
 * Class ConfirmationController
 * @package IO\Controllers
 */
class ConfirmationController extends LayoutController
{
    /**
     * Prepare and render the data for the order confirmation
     * @return string
     */
    public function showConfirmation(int $orderId = 0, $orderHash = '')
    {
        $order = null;
        $showAdditionalPaymentInformation = false;
        
        if(strlen($orderHash) && (int)$orderId > 0)
        {
            $showAdditionalPaymentInformation = true;
            
            /**
             * @var OrderService $orderService
             */
            $orderService = pluginApp(OrderService::class);
            $order = $orderService->findOrderByIdUnguarded($orderId, $orderHash);
        }
        else
        {
            /**
             * @var CustomerService $customerService
             */
            $customerService = pluginApp(CustomerService::class);
            $order = $customerService->getLatestOrder();
        }
        
        if(!is_null($order) && $order instanceof LocalizedOrder)
        {
            return $this->renderTemplate(
                "tpl.confirmation",
                [
                    "data" => $order,
                    "showAdditionalPaymentInformation" => $showAdditionalPaymentInformation
                ]
            );
        }
        else
        {
            return $order;
        }
    }
}
