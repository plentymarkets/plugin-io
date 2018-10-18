<?php //strict

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Services\CustomerService;
use IO\Services\OrderService;
use IO\Services\OrderTotalsService;
use IO\Services\SessionStorageService;
use IO\Constants\SessionStorageKeys;
use IO\Models\LocalizedOrder;
use Plenty\Plugin\Http\Response;

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
    
        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);
    
        /**
         * @var OrderService $orderService
         */
        $orderService = pluginApp(OrderService::class);
        
        if(strlen($orderAccesskey) && (int)$orderId > 0)
        {
            try
            {
                $order = $orderService->findOrderByAccessKey($orderId, $orderAccesskey);
            }
            catch(\Exception $e)
            {}
            
            if(!is_null($order) && $order instanceof LocalizedOrder)
            {
                $sessionStorageService->setSessionValue(SessionStorageKeys::LAST_ACCESSED_ORDER, ['orderId' => $orderId, 'accessKey' => $orderAccesskey]);
            }
        }
        else
        {
            try
            {
                if($orderId > 0)
                {
                    $order = $orderService->findOrderById($orderId);
                }
                else
                {
                    /**
                     * @var CustomerService $customerService
                     */
                    $customerService = pluginApp(CustomerService::class);
                    $order = $customerService->getLatestOrder();
                }
            }
            catch(\Exception $e) {}
        }
        
        if(is_null($order))
        {
            $lastAccessedOrder = $sessionStorageService->getSessionValue(SessionStorageKeys::LAST_ACCESSED_ORDER);
            if(!is_null($lastAccessedOrder) && is_array($lastAccessedOrder))
            {
                try
                {
                    $order = $orderService->findOrderByAccessKey($lastAccessedOrder['orderId'], $lastAccessedOrder['accessKey']);
                }
                catch(\Exception $e) {}
            }
        }
        
        if(!is_null($order) && $order instanceof LocalizedOrder)
        {
            return $this->renderTemplate(
                "tpl.confirmation",
                [
                    "data" => $order,
                    "totals" => pluginApp(OrderTotalsService::class)->getAllTotals($order->order),
                    "showAdditionalPaymentInformation" => true
                ],
                false
            );
        }
        elseif(!$order instanceof LocalizedOrder && !is_null($order))
        {
            return $order;
        }
        else
        {
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }
    }
}
