<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use Plenty\Plugin\ConfigRepository;
use IO\Services\OrderService;

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
    public function showOrderReturn($orderId):string
    {
        $configRepo = pluginApp(ConfigRepository::class);
        $orderService = pluginApp(OrderService::class);
        $orderData = [];

        $enabledRoutes = explode(", ",  $configRepo->get("IO.routing.enabled_routes") );
        if(in_array('order-return', $enabledRoutes))
        {
            $orderData = $orderService->findOrderById(orderReturn);
        }
        
        return $this->renderTemplate(
            "tpl.order.return",
            ['orderData' => $orderData]
		);
    }
}
