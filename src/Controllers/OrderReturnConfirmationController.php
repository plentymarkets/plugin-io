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
        /**
         * @var CustomerService $customerService
         */
        $customerService = pluginApp(CustomerService::class);
        
        /**
         * @var ConfigRepository $configRepo
         */
        $configRepo = pluginApp(ConfigRepository::class);
        
        if( (int)$customerService->getContactId() <= 0 )
        {
            return '';
        }
        
        return $this->renderTemplate(
            'tpl.order.return.confirmation',
            ['data' => '']
        );
    }
}