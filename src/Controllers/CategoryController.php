<?php //strict

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Extensions\Constants\ShopUrls;
use IO\Helper\RouteConfig;
use IO\Guards\AuthGuard;
use IO\Helper\Utils;
use IO\Services\UrlService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\ContactRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;

/**
 * Class CategoryController
 * @package IO\Controllers
 */
class CategoryController extends LayoutController
{
    use Loggable;
    static $LANGUAGE_FROM_URL = null;
    /**
     * Prepare and render the data for categories
     * @param string $lvl1 Level 1 of category url. Will be null at root page
     * @param string $lvl2 Level 2 of category url.
     * @param string $lvl3 Level 3 of category url.
     * @param string $lvl4 Level 4 of category url.
     * @param string $lvl5 Level 5 of category url.
     * @param string $lvl6 Level 6 of category url.
     * @return string
     */
    public function showCategory(
        $lvl1 = null,
        $lvl2 = null,
        $lvl3 = null,
        $lvl4 = null,
        $lvl5 = null,
        $lvl6 = null
    ) {
        $lang = Utils::getLang();
        $webstoreId = Utils::getWebstoreId();
        $category = $this->categoryRepo->findCategoryByUrl($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $lvl6, $webstoreId,
            $lang);

        if ($category === null) {
            $category = $this->categoryRepo->findCategoryByUrl($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $lvl6, $webstoreId,
               self::$LANGUAGE_FROM_URL);
        }

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
        if ($shopBuilderRequest->isShopBuilder() && ($shopBuilderRequest->getPreviewContentType() === 'singleitem' || $shopBuilderRequest->getPreviewContentType() === 'itemset')) {
            /** @var ItemController $itemController */
            $itemController = pluginApp(ItemController::class);
            return $itemController->showItemForCategory($category);
        }

        return $this->renderCategory(
            $category
        );
    }

    public function showCategoryById($categoryId, $params = [], $webstoreId = null)
    {
        $lang = Utils::getLang();

        if(is_null($webstoreId)) {
            $webstoreId = Utils::getWebstoreId();
        }

        return $this->renderCategory(
            $this->categoryRepo->get($categoryId, $lang, $webstoreId),
            $params
        );
    }

    public function redirectToCategory($categoryId, $defaultUrl = '', $params = [])
    {
        $lang = Utils::getLang();
        $webstoreId = Utils::getWebstoreId();

        /** @var UrlService $urlService */
        $urlService = pluginApp(UrlService::class);
        $categoryUrl = $urlService->getCategoryURL((int)$categoryId, $lang, $webstoreId);
        if ($categoryUrl->equals($defaultUrl)) {
            // category url equals legacy route name
            return $this->showCategoryById($categoryId, $params, $webstoreId);
        }

        $category = $this->categoryRepo->get($categoryId, $lang, $webstoreId);

        if (is_null($category)) {
            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }

        $urlParams = http_build_query($params);
        return $urlService->redirectTo(
            $categoryUrl->toRelativeUrl() . (strlen($urlParams) ? '?' . $urlParams : '')
        );
    }

    public function redirectRoute($route, $params = [])
    {
        return $this->redirectToCategory(
            RouteConfig::getCategoryId($route),
            "/" . $route,
            $params
        );
    }

    private function renderCategory($category, $params = [])
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        if ($category === null || (($category->clients->count() == 0 || $category->details->count() == 0) && !$this->app->isAdminPreview())) {
            $this->getLogger(__CLASS__)->warning(
                "IO::Debug.CategoryController_cannotDisplayCategory",
                [
                    "category" => $category,
                    "clientCount" => ($category !== null ? $category->clients->count() : 0),
                    "detailCount" => ($category !== null ? $category->details->count() : 0),
                    "isAdminPreview" => $this->app->isAdminPreview()
                ]
            );

            /** @var Response $response */
            $response = pluginApp(Response::class);
            $response->forceStatus(ResponseCode::NOT_FOUND);

            return $response;
        }

        $this->categoryService->setCurrentCategory($category);
        if ($this->categoryService->isHidden($category->id)) {
            /** @var ShopUrls $shopUrls */
            $shopUrls = pluginApp(ShopUrls::class);
            /** @var AuthGuard $guard */
            $guard = pluginApp(AuthGuard::class);
            $guard->assertOrRedirect(true, $shopUrls->login);
        }

        /** @var ShopBuilderRequest $shopBuilderRequest */
        $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

        if ($category->type === 'item') {
            // the shopbuilder type for item categories is 'categoryitem'
            $shopBuilderRequest->setMainContentType('categoryitem');
        }
        else {
            $shopBuilderRequest->setMainContentType($category->type);
        }
        $shopBuilderRequest->setMainCategory($category->id);

        if (RouteConfig::getCategoryId(RouteConfig::CHECKOUT) === $category->id || $shopBuilderRequest->getPreviewContentType() === 'checkout') {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.CategoryController_showCheckoutCategory",
                [
                    "category" => $category,
                    "previewContentType" => $shopBuilderRequest->getPreviewContentType()
                ]
            );
            RouteConfig::overrideCategoryId(RouteConfig::CHECKOUT, $category->id);

            /** @var CheckoutController $checkoutController */
            $checkoutController = pluginApp(CheckoutController::class);
            return $checkoutController->showCheckout($category);
        }

        if (RouteConfig::getCategoryId(RouteConfig::MY_ACCOUNT) === $category->id || $shopBuilderRequest->getPreviewContentType() === 'myaccount') {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.CategoryController_showMyAccountCategory",
                [
                    "category" => $category,
                    "previewContentType" => $shopBuilderRequest->getPreviewContentType()
                ]
            );
            RouteConfig::overrideCategoryId(RouteConfig::MY_ACCOUNT, $category->id);

            /** @var MyAccountController $myAccountController */
            $myAccountController = pluginApp(MyAccountController::class);
            return $myAccountController->showMyAccount($category);
        }

        if (RouteConfig::getCategoryId(RouteConfig::SEARCH) === $category->id || $shopBuilderRequest->getPreviewContentType() === 'itemsearch') {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.CategoryController_showMyAccountCategory",
                [
                    "category" => $category,
                    "previewContentType" => $shopBuilderRequest->getPreviewContentType()
                ]
            );
            RouteConfig::overrideCategoryId(RouteConfig::SEARCH, $category->id);

            /** @var ItemSearchController $itemSearchController */
            $itemSearchController = pluginApp(ItemSearchController::class);

            return $itemSearchController->showSearch($category);
        }

        if (RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) === $category->id) {
            $this->getLogger(__CLASS__)->info(
                "IO::Debug.CategoryController_showConfirmationCategory",
                [
                    "category" => $category,
                    "previewContentType" => $shopBuilderRequest->getPreviewContentType()
                ]
            );
            RouteConfig::overrideCategoryId(RouteConfig::CONFIRMATION, $category->id);

            /** @var ContactRepositoryContract $contactRepository */
            $contactRepository = pluginApp(ContactRepositoryContract::class);

            if ($request->get('contentLinkId', false) && $contactRepository->getContactId() <= 0) {
                /** @var ShopUrls $shopUrls */
                $shopUrls = pluginApp(ShopUrls::class);
                /** @var AuthGuard $guard */
                $guard = pluginApp(AuthGuard::class);
                $guard->assertOrRedirect(true, $shopUrls->login);
            }

            /** @var ConfirmationController $confirmationController */
            $confirmationController = pluginApp(ConfirmationController::class);
            return $confirmationController->showConfirmation(
                $params['orderId'] ?? $request->get('orderId', 0),
                $params['accessKey'] ?? $request->get('accessKey', ''),
                $category
            );
        }

        if (RouteConfig::getCategoryId(RouteConfig::LOGIN) === $category->id
            || RouteConfig::getCategoryId(RouteConfig::REGISTER) === $category->id) {
            /** @var ContactRepositoryContract $contactRepository */
            $contactRepository = pluginApp(ContactRepositoryContract::class);

            if ($contactRepository->getContactId() > 0 && !$shopBuilderRequest->isShopBuilder()) {
                /** @var ShopUrls $shopUrls */
                $shopUrls = pluginApp(ShopUrls::class);
                AuthGuard::redirect($shopUrls->home);
            }
        }

        if (RouteConfig::getCategoryId(RouteConfig::ORDER_RETURN) === $category->id) {
            /** @var OrderReturnController $orderReturnController */
            $orderReturnController = pluginApp(OrderReturnController::class);

            $orderId = $request->get('orderId', 0);
            if ($orderId > 0) {
                return $orderReturnController->showOrderReturn(
                    $orderId,
                    $request->get('orderAccessKey', null),
                    $category
                );
            } elseif (!$shopBuilderRequest->isShopBuilder()) {
                /** @var Response $response */
                $response = pluginApp(Response::class);
                $response->forceStatus(ResponseCode::NOT_FOUND);

                return $response;
            }
        }

        if (RouteConfig::getCategoryId(RouteConfig::CHANGE_MAIL) === $category->id) {
            /** @var CustomerChangeMailController $customerChangeMailController */
            $customerChangeMailController = pluginApp(CustomerChangeMailController::class);
            return $customerChangeMailController->show(
                $request->get('contactId'),
                $request->get('hash'),
                $category
            );
        }

        if (RouteConfig::getCategoryId(RouteConfig::PASSWORD_RESET) === $category->id) {

            /** @var CustomerPasswordResetController $customerPasswordResetController */
            $customerPasswordResetController = pluginApp(CustomerPasswordResetController::class);
            return $customerPasswordResetController->showReset(
                $request->get('contactId'),
                $request->get('hash'),
                $category
            );
        }

        return $this->renderTemplate(
            "tpl.category." . $category->type,
            [
                'category' => $category,
                'sorting' => $request->get('sorting', null),
                'itemsPerPage' => $request->get('items', null),
                'page' => $request->get('page', null),
                'facets' => $request->get('facets', '')
            ],
            RouteConfig::getCategoryId(RouteConfig::BASKET) !== $category->id
        );
    }
}
