<?php

namespace IO\Controllers;

use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use Plenty\Modules\Webshop\Helpers\UrlQuery;

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

            /** @var UrlQuery $urlQuery */
            $urlQuery = pluginApp(UrlQuery::class, ['path' => $shopUrls->confirmation]);
            if(RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0)
            {
                $params = '?'.http_build_query(['orderId' => $orderId, 'accessKey' => $orderAccessKey]);
            }
            else
            {
                $params = '';
                $urlQuery->join($orderId.'/'.$orderAccessKey);
            }

            return $this->urlService->redirectTo($urlQuery->toRelativeUrl().$params);
        }

        return $this->renderTemplate(
            "tpl.confirmation",
            [
                "data" => ''
            ],
            false
        );
    }
}
