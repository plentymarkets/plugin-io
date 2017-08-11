<?php //strict
namespace IO\Controllers;

use IO\Helper\TemplateContainer;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class HomepageController
 * @package IO\Controllers
 */
class HomepageController extends LayoutController
{
    /**
     * Prepare and render the data for the homepage
     * @return string
     */
    public function showHomepage(Request $request, Response $response)
    {
        $orderId = $request->get('id', 0);
        $orderAccessKey = $request->get('ak', '');
        
        if(strlen($orderAccessKey) && (int)$orderId > 0)
        {
            return $response->redirectTo('confirmation/'.$orderId.'/'.$orderAccessKey);
        }
        
        return $this->renderTemplate(
            "tpl.home",
            [
                "object" => ""
            ]
        );
    }
}
