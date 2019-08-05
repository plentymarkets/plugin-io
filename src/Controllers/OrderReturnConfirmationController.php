<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Middlewares\Middleware;
use IO\Services\CustomerService;
use Plenty\Plugin\Http\Response;

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
    public function showOrderReturnConfirmation()
    {
        /**
         * @var CustomerService $customerService
         */
        $customerService = pluginApp(CustomerService::class);

        // TODO: is check for login state required in here?
        if( (int)$customerService->getContactId() <= 0 )
        {
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);
            Middleware::$FORCE_404 = true;

            return $response;
        }

        return $this->renderTemplate(
            'tpl.order.return.confirmation',
            ['data' => ''],
            false
        );
    }
}