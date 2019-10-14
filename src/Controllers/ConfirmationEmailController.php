<?php //strict
namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;

/**
 * Class ConfirmationEmailController
 * @package IO\Controllers
 */
class ConfirmationEmailController extends LayoutController
{
    /**
     * Prepare and render the data for the order confirmation
     * @return string
     */
    public function showConfirmation($orderAccessKey = '', int $orderId = 0)
    {
        if(strlen($orderAccessKey) && (int)$orderId > 0)
        {
            /** @var ShopUrls $shopUrls */
            $shopUrls = pluginApp(ShopUrls::class);
            $confirmationUrl = $shopUrls->confirmation . ($shopUrls->appendTrailingSlash ? '' : '/');

            return $this->urlService->redirectTo($confirmationUrl.$orderId.'/'.$orderAccessKey);
        }

        return $this->renderTemplate(
            "tpl.confirmation",
            [
                "data" => ''
            ],
            false
        );
    }

    public function redirect($accessKey, $orderId)
    {
        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);

        return $categoryController->redirectToCategory(
            RouteConfig::getCategoryId(RouteConfig::CONFIRMATION),
            '/confirmation',
            [
                'orderId' => $orderId,
                'accessKey' => $accessKey
            ]
        );
    }
}
