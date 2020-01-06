<?php

namespace IO\Controllers;

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