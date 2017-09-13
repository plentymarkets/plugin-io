<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use Plenty\Plugin\ConfigRepository;

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
    public function showOrderReturn($orderReturn):string
    {
        $configRepo = pluginApp(ConfigRepository::class);
        $enabledRoutes = explode(", ",  $configRepo->get("IO.routing.enabled_routes") );
        if(in_array('order-returns', $enabledRoutes)) {}
        
        return $this->renderTemplate(
			"tpl.order.return"
		);
    }
}
