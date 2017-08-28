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
    public function showConfirmation(int $orderId = 0, $orderAccesskey = '')
    {
        $order = null;
        $showAdditionalPaymentInformation = false;
        
        if(strlen($orderAccesskey) && (int)$orderId > 0)
        {
            $showAdditionalPaymentInformation = true;
            
            /**
             * @var OrderService $orderService
             */
            $orderService = pluginApp(OrderService::class);
            try
            {
                $order = $orderService->findOrderByAccessKey($orderId, $orderAccesskey);
            }
            catch(\Exception $e)
            {
                $order = null;
            }
        }
        else
        {
            /**
             * @var CustomerService $customerService
             */
            $customerService = pluginApp(CustomerService::class);
            try
            {
                if($orderId > 0)
                {
                    /**
                     * @var OrderService $orderService
                     */
                    $orderService = pluginApp(OrderService::class);
                    $order = $orderService->findOrderById($orderId);
                }
                else
                {
                    $order = $customerService->getLatestOrder();
                }
            }
            catch(\Exception $e)
            {
                $order = null;
            }
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
            return $this->renderTemplate(
                "tpl.page-not-found",
                [
                    "data" => ""
                ]
            );
        }
    }
}
