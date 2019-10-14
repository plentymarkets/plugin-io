<?php //strict
namespace IO\Controllers;

use IO\Constants\SessionStorageKeys;
use IO\Extensions\Constants\ShopUrls;
use IO\Models\LocalizedOrder;
use IO\Services\CustomerService;
use IO\Services\OrderService;
use IO\Guards\AuthGuard;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class OrderReturnController
 * @package IO\Controllers
 */
class OrderReturnController extends LayoutController
{
    use Loggable;

    /**
     * Render the order returns view
     * @param int               $orderId
     * @param string            $orderAccessKey
     *
     * @return string|Response
     *
     * @throws \ErrorException
     */
    public function showOrderReturn($orderId, $orderAccessKey = null)
    {
        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);
        $sessionOrder = $sessionStorageService->getSessionValue(SessionStorageKeys::LAST_ACCESSED_ORDER);
        
        if((int)$sessionOrder['orderId'] == (int)$orderId)
        {
            $orderAccessKey = $sessionOrder['accessKey'];
        }
        
        if( pluginApp(CustomerService::class)->getContactId() <= 0 && !strlen($orderAccessKey))
        {
            AuthGuard::redirect(
                pluginApp(ShopUrls::class)->login,
                ["backlink" => AuthGuard::getUrl()]
            );
        }

        try
        {
            /** @var OrderService $orderService */
            $orderService   = pluginApp(OrderService::class);

            /** @var LocalizedOrder $returnOrder */
            $returnOrder    = $orderService->getReturnOrder($orderId, $orderAccessKey);

            if(!$returnOrder->isReturnable())
            {
                $this->getLogger(__CLASS__)->info(
                    "IO::Debug.OrderReturnController_orderNotReturnable",
                    [
                        "orderId"           => $orderId,
                        "returnOrderItems"  => $returnOrder->orderData['orderItems']
                    ]
                );
                return $this->notFound();
            }

        }
        catch (\Exception $e)
        {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.OrderReturnController_cannotPrepareReturn",
                [
                    "orderId" => $orderId,
                    "error" => [
                        "code" => $e->getCode(),
                        "message" => $e->getMessage()
                    ]
                ]
            );
            return $this->notFound();
        }

        return $this->renderTemplate(
            'tpl.order.return',
            [
                'orderData'         => $returnOrder,
                'orderAccessKey'    => $orderAccessKey
            ],
            false
		);
    }
}
