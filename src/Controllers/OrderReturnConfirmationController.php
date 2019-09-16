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
        return $this->renderTemplate(
            'tpl.order.return.confirmation',
            ['data' => ''],
            false
        );
    }
}