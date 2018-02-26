<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Plugin\ConfigRepository;
use IO\Services\CustomerService;
use IO\Services\OrderService;
use IO\Guards\AuthGuard;

/**
 * Class OrderReturnController
 * @package IO\Controllers
 */
class OrderReturnController extends LayoutController
{
    /**
     * Render the order returns view
     * @return string
     */
    public function showOrderReturn($orderId, CustomerService $customerService):string
    {
        if($customerService->getContactId() <= 0)
        {
            AuthGuard::redirect("/login", ["backlink" => AuthGuard::getUrl()]);
        }

        $configRepo = pluginApp(ConfigRepository::class);
    
        /**
         * @var OrderService $orderService
         */
        $orderService = pluginApp(OrderService::class);
        
        $returnOrder = [];
        $template = 'tpl.order.return';
        
        $enabledRoutes = explode(", ",  $configRepo->get("IO.routing.enabled_routes") );
        if( (in_array('order-return', $enabledRoutes) || in_array("all", $enabledRoutes)) && $orderService->isReturnActive() )
        {
            try
            {
                $order = $orderService->findOrderById($orderId, false, true);
                $returnOrder = $orderService->getReturnOrder($order);
    
                /** @var OrderRepositoryContract $orderRepo */
                $orderRepo = pluginApp(OrderRepositoryContract::class);
                
                if(!count($returnOrder->orderData['orderItems']) || !$orderService->isOrderReturnable($orderRepo->findOrderById($orderId)))
                {
                    return '';
                }
                
            }
            catch (\Exception $e)
            {
                return '';
            }
        }
        else
        {
            return '';
        }
        
        return $this->renderTemplate(
            'tpl.order.return',
            ['orderData' => $returnOrder]
		);
    }
}
