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

                $bundleComponentsToAdd = [];
                $bundleMainComponents = [];

                $newOrderItems = [];

                foreach($returnOrder->orderData['orderItems'] as $orderItem)
                {
                    if($orderItem['bundleType'] === 'bundle_item' && count($orderItem['references']) > 0)
                    {
                        $bundleComponentsToAdd[$orderItem['references'][0]['referenceOrderItemId']][] = $orderItem;
                    }
                    elseif(strpos($orderItem['orderItemName'], 'BUNDLE'))
                    {
                        $bundleMainComponents[$orderItem['id']] = $orderItem;
                    }
                    else
                    {
                        $newOrderItems[] = $orderItem;
                    }
                }

                foreach($bundleComponentsToAdd as $key => $bundleComponent)
                {
                    $refOrderItem = $bundleMainComponents[$key];
                    $refOrderItem['bundleType'] = "bundle";

                    foreach($bundleComponent as $bundleChild)
                    {
                        $refOrderItem['bundleComponents'][] = $bundleChild;
                    }

                    $newOrderItems[] = $refOrderItem;
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
            ['orderData' => $returnOrder]
		);
    }
}
