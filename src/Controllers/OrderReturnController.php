<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
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
        $orderService = pluginApp(OrderService::class);
        $orderData = [];

        $enabledRoutes = explode(", ",  $configRepo->get("IO.routing.enabled_routes") );
        if( in_array('order-return', $enabledRoutes) || in_array("all", $enabledRoutes))
        {
            try
            {
                $orderData = $orderService->findOrderById($orderId);
                $template = 'tpl.order.return';
            }
            catch (\Exception $e)
            {
                $orderData = [];
                $template = 'tpl.page-not-found';
            }
            
        }
        
        return $this->renderTemplate(
            $template,
            ['orderData' => $orderData]
		);
    }
}
