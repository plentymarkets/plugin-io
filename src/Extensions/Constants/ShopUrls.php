<?php

namespace IO\Extensions\Constants;

use IO\Helper\MemoryCache;
use IO\Helper\RouteConfig;
use IO\Helper\Utils;
use IO\Services\CategoryService;
use IO\Services\OrderTrackingService;
use IO\Services\UrlBuilder\UrlQuery;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Frontend\Events\FrontendLanguageChanged;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;

class ShopUrls
{
    use MemoryCache;

    private $urlMap = [
        RouteConfig::ORDER_RETURN => 'returns',
        RouteConfig::ORDER_RETURN_CONFIRMATION => 'return-confirmation',
        RouteConfig::NEWSLETTER_OPT_OUT => 'newsletter/unsubscribe',
        RouteConfig::ORDER_DOCUMENT => 'order-document'
    ];

    public $appendTrailingSlash = false;
    public $trailingSlashSuffix = '';
    public $includeLanguage = false;

    public $basket = '';
    public $cancellationForm = '';
    public $cancellationRights = '';
    public $checkout = '';
    public $confirmation = '';
    public $contact = '';
    public $gtc = '';
    public $home = '';
    public $legalDisclosure = '';
    public $login = '';
    public $myAccount = '';
    public $passwordReset = '';
    public $privacyPolicy = '';
    public $registration = '';
    public $search = '';
    public $termsConditions = '';
    public $wishList = '';
    public $returns = '';
    public $returnConfirmation = '';
    public $changeMail = '';
    public $newsletterOptOut = '';
    public $orderDocument = '';

    public function __construct(Dispatcher $dispatcher)
    {
        $this->init(Utils::getLang());
        $dispatcher->listen(
            FrontendLanguageChanged::class,
            function (FrontendLanguageChanged $event) {
                $this->init($event->getLanguage());
            }
        );
    }

    private function init($lang)
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        $this->resetMemoryCache();
        $this->appendTrailingSlash = UrlQuery::shouldAppendTrailingSlash();
        $this->trailingSlashSuffix = $this->appendTrailingSlash ? '/' : '';
        $this->includeLanguage = $lang !== $webstoreConfigurationRepository->getWebstoreConfiguration(
            )->defaultLanguage;

        $this->basket = $this->getShopUrl(RouteConfig::BASKET);
        $this->cancellationForm = $this->getShopUrl(RouteConfig::CANCELLATION_FORM);
        $this->cancellationRights = $this->getShopUrl(RouteConfig::CANCELLATION_RIGHTS);
        $this->checkout = $this->getShopUrl(RouteConfig::CHECKOUT);
        $this->confirmation = $this->getShopUrl(RouteConfig::CONFIRMATION);
        $this->contact = $this->getShopUrl(RouteConfig::CONTACT);
        $this->gtc = $this->getShopUrl(RouteConfig::TERMS_CONDITIONS);

        // Homepage URL may not be used from category. Even if linked to category, the homepage url should be '/'
        $this->home = Utils::makeRelativeUrl('/', $this->includeLanguage);
        $this->legalDisclosure = $this->getShopUrl(RouteConfig::LEGAL_DISCLOSURE);
        $this->login = $this->getShopUrl(RouteConfig::LOGIN);
        $this->myAccount = $this->getShopUrl(RouteConfig::MY_ACCOUNT);
        $this->passwordReset = $this->getShopUrl(RouteConfig::PASSWORD_RESET);
        $this->privacyPolicy = $this->getShopUrl(RouteConfig::PRIVACY_POLICY);
        $this->registration = $this->getShopUrl(RouteConfig::REGISTER);
        $this->search = $this->getShopUrl(RouteConfig::SEARCH);
        $this->termsConditions = $this->getShopUrl(RouteConfig::TERMS_CONDITIONS);
        $this->wishList = $this->getShopUrl(RouteConfig::WISH_LIST);
        $this->returns = $this->getShopUrl(RouteConfig::ORDER_RETURN);
        $this->returnConfirmation = $this->getShopUrl(RouteConfig::ORDER_RETURN_CONFIRMATION);
        $this->changeMail = $this->getShopUrl(RouteConfig::CHANGE_MAIL);
        $this->newsletterOptOut = $this->getShopUrl(RouteConfig::NEWSLETTER_OPT_OUT);
        $this->orderDocument = $this->getShopUrl(RouteConfig::ORDER_DOCUMENT);
    }

    public function returns($orderId, $orderAccessKey = null)
    {
        if ($orderAccessKey == null) {
            $request = pluginApp(Request::class);
            $orderAccessKey = $request->get('accessKey');
        }

        $categoryId = RouteConfig::getCategoryId(RouteConfig::ORDER_RETURN);
        if ($categoryId > 0) {
            $params = [
                'orderId' => $orderId,
                'orderAccessKey' => $orderAccessKey
            ];

            return $this->getShopUrl(RouteConfig::ORDER_RETURN, null, $params);
        }

        return $this->getShopUrl(RouteConfig::ORDER_RETURN, [$orderId, $orderAccessKey]);
    }

    public function orderPropertyFile($path)
    {
        return $this->getShopUrl(RouteConfig::ORDER_PROPERTY_FILE, null, $path);
    }

    public function orderDocumentPreview($documentId, $orderId, $orderAccessKey = null)
    {
        if ($orderAccessKey == null) {
            /** @var Request $request */
            $request = pluginApp(Request::class);
            $orderAccessKey = $request->get('accessKey');
        }

        $url = $this->getShopUrl(
            RouteConfig::ORDER_DOCUMENT,
            ['documentId' => $documentId],
            ['orderId' => $orderId, 'accessKey' => $orderAccessKey],
            'order-document/preview'
        );
        return $url;
    }

    public function tracking($orderId)
    {
        $lang = Utils::getLang();
        return $this->fromMemoryCache(
            "tracking.{$orderId}",
            function () use ($orderId, $lang) {
                $authHelper = pluginApp(AuthHelper::class);
                $trackingURL = $authHelper->processUnguarded(
                    function () use ($orderId, $lang) {
                        $orderRepository = pluginApp(OrderRepositoryContract::class);
                        $orderTrackingService = pluginApp(OrderTrackingService::class);

                        $order = $orderRepository->findOrderById($orderId);
                        return $orderTrackingService->getTrackingURL($order, $lang);
                    }
                );

                return $trackingURL;
            }
        );
    }

    public function orderConfirmation($orderId)
    {
        if (RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0) {
            $suffix = '?orderId=' . $orderId;
        } else {
            // if there is no trailing slash we must add a slash before the orderID to divide the suffix
            // from the given url path else we have to add ad slasg after the orderID to show a correct url
            $suffix = $this->appendTrailingSlash ? $orderId . '/' : '/' . $orderId;
        }
        return $this->confirmation . $suffix;
    }

    private function getShopUrl($route, $routeParams = [], $urlParams = [], $overrideUrl = null)
    {
        $key = $route;

        if (count($routeParams) || count($urlParams)) {
            $key .= '.' . implode('.', $routeParams) . '.' . json_encode($urlParams);
        }

        if (strlen($overrideUrl)) {
            $key .= '.' . $overrideUrl;
        }

        return $this->fromMemoryCache(
            $key,
            function () use ($route, $routeParams, $urlParams, $overrideUrl) {
                $categoryId = RouteConfig::getCategoryId($route);
                if ($categoryId > 0) {
                    /** @var CategoryService $categoryService */
                    $categoryService = pluginApp(CategoryService::class);
                    $category = $categoryService->get($categoryId);

                    if ($category !== null) {
                        /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
                        $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);

                        return $this->applyParams(
                            $urlBuilderRepository->buildCategoryUrl($category->id),
                            $routeParams,
                            $urlParams
                        );
                    }
                }

                $url = $overrideUrl ?? $this->urlMap[$route] ?? null;
                return $this->applyParams(
                    pluginApp(UrlQuery::class, ['path' => ($url ?? $route)]),
                    $routeParams,
                    $urlParams
                );
            }
        );
    }

    private function applyParams($url, $routeParams, $urlParams)
    {
        $routeParam = array_shift($routeParams);
        while (!is_null($routeParam) && strlen($routeParam)) {
            $url->join($routeParam);
            $routeParam = array_shift($routeParams);
        }

        $queryParameters = http_build_query($urlParams);
        $relativeUrl = $url->toRelativeUrl($this->includeLanguage);

        return $relativeUrl . (strlen($queryParameters) > 0 ? '?' . $queryParameters : '');
    }

    public function equals($routeUrl, $url)
    {
        return $routeUrl === $url || $routeUrl === $url . '/';
    }

    public function getTemplateType()
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        if ($request->has('templateType')) {
            // template type is explicitly set via request param
            return $request->get('templateType');
        }

        // detect template type from request uri
        $url = Utils::makeRelativeUrl(
            explode('?', $request->getRequestUri())[0],
            $this->includeLanguage
        );

        if (!strlen($url) || $url === '/') {
            return RouteConfig::HOME;
        }

        foreach (RouteConfig::ALL as $routeKey) {
            if ($this->equals($url, $this->getShopUrl($routeKey))) {
                // current page is a special linked page
                return $routeKey;
            }
        }

        // match url pattern
        if (preg_match('/(?:a\-\d+|_\d+|_\d+_\d+)\/?$/m', $url) === 1) {
            return RouteConfig::ITEM;
        } elseif (preg_match('/_t\d+\/?$/m', $url) === 1) {
            return RouteConfig::TAGS;
        } elseif (preg_match('/confirmation\/\d+\/([A-Za-z]|\d)+\/?/m', $url) === 1) {
            return RouteConfig::CONFIRMATION;
        }

        // template type cannot be determined
        return RouteConfig::CATEGORY;
    }

    public function is($routeKey)
    {
        return $this->getTemplateType() === $routeKey;
    }
}
