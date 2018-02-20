<?php

namespace IO\Controllers;

use Plenty\Plugin\ConfigRepository;
use IO\Services\CustomerService;

/**
 * Class OrderReturnController
 * @package IO\Controllers
 */
class OrderReturnConfirmationController extends LayoutController
{
    /**
     * Render the order returns view
     * @return string
     */
    public function showOrderReturnConfirmation():string
    {
        $template = 'tpl.order.return.confirmation';
        
        /**
         * @var CustomerService $customerService
         */
        $customerService = pluginApp(CustomerService::class);
        
        /**
         * @var ConfigRepository $configRepo
         */
        $configRepo = pluginApp(ConfigRepository::class);
        
        $enabledRoutes = explode(", ",  $configRepo->get("IO.routing.enabled_routes") );
        if( (!in_array('order-return-confirmation', $enabledRoutes) && !in_array('all', $enabledRoutes)) || (int)$customerService->getContactId() <= 0 )
        {
            $template = 'tpl.page-not-found';
        }
        
        return $this->renderTemplate(
            $template,
            ['data' => '']
        );
    }
}