<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Plugin\ConfigRepository;
use IO\Services\CustomerService;
use IO\Services\OrderService;
use IO\Guards\AuthGuard;
use IO\Services\UrlBuilder\UrlQuery;

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
            $url = $this->urlService->getHomepageURL();
            if(substr($url, -1) !== '/')
            {
                $url .= '/';
            }
            $url .= 'login';
            $url .= UrlQuery::shouldAppendTrailingSlash() ? '/' : '';

            AuthGuard::redirect($url, ["backlink" => AuthGuard::getUrl()]);
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

                $newOrderItems = [];

                foreach($returnOrder->orderData['orderItems'] as $orderItem)
                {
                    if($orderItem['bundleType'] !== 'bundle_item' && count($orderItem['references']) === 0)
                    {
                        $newOrderItems[] = $orderItem;
                    }
                }

                if(count($newOrderItems) > 0)
                {
                    $returnOrder->orderData['orderItems'] = $newOrderItems;
                }
                
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
            ['orderData' => $returnOrder],
            false
		);
    }
}
