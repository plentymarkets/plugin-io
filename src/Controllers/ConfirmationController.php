<?php //strict

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Helper\RouteConfig;
use IO\Middlewares\CheckNotFound;
use IO\Services\CategoryService;
use IO\Services\CustomerService;
use IO\Services\OrderService;
use IO\Models\LocalizedOrder;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Date\Models\OrderDateType;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\ConfirmationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ConfirmationController
 * @package IO\Controllers
 */
class ConfirmationController extends LayoutController
{
    use Loggable;

    /**
     * Prepare and render the data for the order confirmation
     * @return string
     */
    public function showConfirmation(int $orderId = 0, $orderAccesskey = '', $category = null)
    {
        $order = null;

        /** @var SessionStorageRepositoryContract $sessionStorageRepository */
        $sessionStorageRepository = pluginApp(SessionStorageRepositoryContract::class);

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        $shopBuilderRequest->setMainContentType('checkout');

        /**
         * @var CustomerService $customerService
         */
        $customerService = pluginApp(CustomerService::class);

        /** @var ContactRepositoryContract $contactRepository */
        $contactRepository = pluginApp(ContactRepositoryContract::class);

        /**
         * @var OrderService $orderService
         */
        $orderService = pluginApp(OrderService::class);

        if ($shopBuilderRequest->isShopBuilder() && !is_null($category)) {
            return $this->renderTemplate(
                "tpl.confirmation",
                [
                    "category" => $category,
                    "data" => null,
                    "showAdditionalPaymentInformation" => true
                ],
                false
            );
        }

        if (strlen($orderAccesskey) && (int)$orderId > 0) {
            try {
                $order = $orderService->findOrderByAccessKey($orderId, $orderAccesskey);
            } catch (\Exception $e) {
                $this->getLogger(__CLASS__)->warning(
                    "IO::Debug.ConfirmationController_cannotFindOrderByAccessKey",
                    [
                        "orderId" => $orderId,
                        "orderAccessKey" => $orderAccesskey,
                        "error" => [
                            "code" => $e->getCode(),
                            "message" => $e->getMessage()
                        ]
                    ]
                );
            }

            if (!is_null($order) && $order instanceof LocalizedOrder) {
                $sessionStorageRepository->setSessionValue(
                    SessionStorageRepositoryContract::LAST_ACCESSED_ORDER,
                    ['orderId' => $orderId, 'accessKey' => $orderAccesskey]
                );
            }
        } else {
            try {
                if ($orderId > 0) {
                    $order = $orderService->findOrderById($orderId);
                } else {
                    $order = $customerService->getLatestOrder();
                }


                if ($order instanceof LocalizedOrder) {
                    /** @var OrderRepositoryContract $orderRepository */
                    $orderRepository = pluginApp(OrderRepositoryContract::class);
                    $orderAccessKey = $orderRepository->generateAccessKey($order->order->id);
                    $sessionStorageRepository->setSessionValue(
                        SessionStorageRepositoryContract::LAST_ACCESSED_ORDER,
                        ['orderId' => $order->order->id, 'accessKey' => $orderAccessKey]
                    );
                }
            } catch (\Exception $e) {
                $this->getLogger(__CLASS__)->warning(
                    "IO::Debug.ConfirmationController_cannotFindOrder",
                    [
                        "orderId" => $orderId,
                        "error" => [
                            "code" => $e->getCode(),
                            "message" => $e->getMessage()
                        ]
                    ]
                );
            }
        }

        if (is_null($order)) {
            $lastAccessedOrder = $sessionStorageRepository->getSessionValue(
                SessionStorageRepositoryContract::LAST_ACCESSED_ORDER
            );
            if (!is_null($lastAccessedOrder) && is_array($lastAccessedOrder)) {
                try {
                    $order = $orderService->findOrderByAccessKey(
                        $lastAccessedOrder['orderId'],
                        $lastAccessedOrder['accessKey']
                    );
                } catch (\Exception $e) {
                    $this->getLogger(__CLASS__)->warning(
                        "IO::Debug.ConfirmationController_cannotFindLastOrderByAccessKey",
                        [
                            "orderId" => $lastAccessedOrder['orderId'],
                            "orderAccessKey" => $lastAccessedOrder['accessKey'],
                            "error" => [
                                "code" => $e->getCode(),
                                "message" => $e->getMessage()
                            ]
                        ]
                    );
                }
            }
        }

        if ($contactRepository->getContactId() > 0) {
            $sessionStorageRepository->setSessionValue(SessionStorageRepositoryContract::LATEST_ORDER_ID, null);
        }

        if (!is_null($order) && $order instanceof LocalizedOrder) {
            if ($this->checkValidity($order->order)) {
                if ($category instanceof Category && $contactRepository->getContactId() <= 0) {
                    /** @var ConfigRepository $config */
                    $config = pluginApp(ConfigRepository::class);
                    $categoryGuestId = (int)$config->get('IO.routing.category_confirmation-guest', 0);
                    if ($categoryGuestId > 0) {
                        /** @var CategoryService $categoryService */
                        $categoryService = pluginApp(CategoryService::class);
                        $category = $categoryService->get($categoryGuestId);
                    }
                }

                return $this->renderTemplate(
                    "tpl.confirmation",
                    [
                        "category" => $category,
                        "data" => $order,
                        "showAdditionalPaymentInformation" => true
                    ],
                    false
                );
            } else {
                /** @var Response $response */
                $response = pluginApp(Response::class);
                $response->forceStatus(ResponseCode::NOT_FOUND);

                CheckNotFound::$FORCE_404 = true;

                return $response;
            }
        } elseif (!$order instanceof LocalizedOrder && !is_null($order)) {
            return $order;
        } else {
            $this->getLogger(__CLASS__)->warning("IO::Debug.ConfirmationController_orderNotFound");
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            CheckNotFound::$FORCE_404 = true;

            return $response;
        }
    }

    private function checkValidity(Order $order)
    {
        /** @var ConfirmationRepositoryContract $confirmationRepository */
        $confirmationRepository = pluginApp(ConfirmationRepositoryContract::class);
        return $confirmationRepository->checkValidity($order);
    }

    public function redirect($orderId = 0, $accessKey = '')
    {
        if (!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        $confirmationParams = [];

        if ((int)$orderId > 0 && strlen($accessKey)) {
            $confirmationParams['orderId'] = $orderId;
            $confirmationParams['accessKey'] = $accessKey;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);

        return $categoryController->redirectToCategory(
            RouteConfig::getCategoryId(RouteConfig::CONFIRMATION),
            '/confirmation',
            $confirmationParams
        );
    }
}
