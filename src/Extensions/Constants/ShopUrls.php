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
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;

/**
 * Class ShopUrls
 *
 * Helper to get configured URLs to be used in the webshop.
 * Generated URLs consider configured categories for several webshop pages and settings for trailing slashes or item URL patterns.
 *
 * @package IO\Extensions\Constants
 */
class ShopUrls
{
    use MemoryCache;

    private $urlMap = [
        RouteConfig::ORDER_RETURN => 'returns',
        RouteConfig::ORDER_RETURN_CONFIRMATION => 'return-confirmation',
        RouteConfig::NEWSLETTER_OPT_OUT => 'newsletter/unsubscribe',
        RouteConfig::ORDER_DOCUMENT => 'order-document'
    ];

    /**
     * @var array Data array with values for each language if already initialized.
     */
    private static $shopUrls = [];

    /**
     * @var bool Define if a trailing slash should be appended to URLS or not.
     *           Consider this option to avoid unnecessary 301 redirects.
     */
    public $appendTrailingSlash = false;

    /**
     * @var string Suffix to append to URLs containing a trailing slash if required.
     */
    public $trailingSlashSuffix = '';

    /**
     * @var bool Indicate if the language should be included in URLs.
     *           This is false if the current language equals the default language of the webstore.
     *           Otherwise the language should be included in the URLs to be detected correctly.
     */
    public $includeLanguage = false;

    /**
     * @var string Relative URL of the basket view.
     */
    public $basket = '';

    /**
     * @var string Relative URL of the cancellation form view.
     */
    public $cancellationForm = '';

    /**
     * @var string Relative URL of the cancellation rights view.
     */
    public $cancellationRights = '';

    /**
     * @var string Relative URL of the checkout view.
     */
    public $checkout = '';

    /**
     * @var string Relative URL of the order confirmation view of the most recent order.
     *             Use orderConfirmation() to get the URL for the order confirmation view of a specific order.
     */
    public $confirmation = '';

    /**
     * @var string Relative URL of the contact view.
     */
    public $contact = '';

    /**
     * @var string Relative URL of the general terms and conditions view.
     */
    public $gtc = '';

    /**
     * @var string Relative URL of the home page.
     */
    public $home = '';

    /**
     * @var string Relative URL of the legal disclosure view.
     */
    public $legalDisclosure = '';

    /**
     * @var string Relative URL of the login page.
     */
    public $login = '';

    /**
     * @var string Relative URL of the my-account view.
     */
    public $myAccount = '';

    /**
     * @var string Relative URL of the view displaying the form to reset a password.
     */
    public $passwordReset = '';

    /**
     * @var string Relative URL of the privacy policy.
     */
    public $privacyPolicy = '';

    /**
     * @var string Relative URL of the registration form.
     */
    public $registration = '';

    /**
     * @var string Relative URL of the item search view.
     */
    public $search = '';

    /**
     * @var string Relative URL of the general terms and conditions view.
     * @deprecated since 5.0.12. Use $gtc instead.
     */
    public $termsConditions = '';

    /**
     * @var string Relative URL of the wish list view.
     */
    public $wishList = '';

    /**
     * @var string  Relative URL of the returns form for the most recent order.
     *              Use returns() to get the URL for the returns form for a specific order.
     */
    public $returns = '';

    /**
     * @var string Relative URL of the order return confirmation.
     * @deprecated since 5.0.12. This is not in use anymore since only a success message will be displayed after submitting a return.
     */
    public $returnConfirmation = '';

    /**
     * @var string Relative URL of the form to change a customer's mail.
     */
    public $changeMail = '';

    /**
     * @var string Relative URL of the form to unsubscribe from a newsletter.
     */
    public $newsletterOptOut = '';

    /**
     * @var string Get a preview URL for an order document.
     * @deprecated since 5.0.12. Not in use anymore. Use orderDocumentPreview() instead.
     */
    public $orderDocument = '';

    private $templateType = null;

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
        if (isset(self::$shopUrls[$lang])) {
            $shopUrls = self::$shopUrls[$lang];
        } else {
            $shopUrls = Utils::getCacheKey('shopUrls_' . $lang, null);
            self::$shopUrls[$lang] = $shopUrls;
        }

        if (!is_null($shopUrls)) {
            $this->initByCache($shopUrls);
        } else {
            /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
            $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
            $this->resetMemoryCache();
            $dataForCache = [];
            $this->appendTrailingSlash = $dataForCache['appendTrailingSlash'] = UrlQuery::shouldAppendTrailingSlash();
            $this->trailingSlashSuffix = $dataForCache['trailingSlashSuffix'] = $this->appendTrailingSlash ? '/' : '';
            $this->includeLanguage = $dataForCache['includeLanguage'] = $lang !== $webstoreConfigurationRepository->getWebstoreConfiguration(
                )->defaultLanguage;

            $this->basket = $dataForCache['basket'] = $this->getShopUrl(RouteConfig::BASKET, $lang);
            $this->cancellationForm = $dataForCache['cancellationForm'] = $this->getShopUrl(RouteConfig::CANCELLATION_FORM, $lang);
            $this->cancellationRights = $dataForCache['cancellationRights'] = $this->getShopUrl(RouteConfig::CANCELLATION_RIGHTS, $lang);
            $this->checkout = $dataForCache['checkout'] = $this->getShopUrl(RouteConfig::CHECKOUT, $lang);
            $this->confirmation = $dataForCache['confirmation'] = $this->getShopUrl(RouteConfig::CONFIRMATION, $lang);
            $this->contact = $dataForCache['contact'] = $this->getShopUrl(RouteConfig::CONTACT, $lang);
            $this->gtc = $dataForCache['gtc'] = $this->getShopUrl(RouteConfig::TERMS_CONDITIONS, $lang);

            // Homepage URL may not be used from category. Even if linked to category, the homepage URL should be '/'
            $this->home = $dataForCache['home'] = Utils::makeRelativeUrl('/', $this->includeLanguage, $lang);
            $this->legalDisclosure = $dataForCache['legalDisclosure'] = $this->getShopUrl(RouteConfig::LEGAL_DISCLOSURE, $lang);
            $this->login = $dataForCache['login'] = $this->getShopUrl(RouteConfig::LOGIN, $lang);
            $this->myAccount = $dataForCache['myAccount'] = $this->getShopUrl(RouteConfig::MY_ACCOUNT, $lang);
            $this->passwordReset = $dataForCache['passwordReset'] = $this->getShopUrl(RouteConfig::PASSWORD_RESET, $lang);
            $this->privacyPolicy = $dataForCache['privacyPolicy'] = $this->getShopUrl(RouteConfig::PRIVACY_POLICY, $lang);
            $this->registration = $dataForCache['registration'] = $this->getShopUrl(RouteConfig::REGISTER, $lang);
            $this->search = $dataForCache['search'] = $this->getShopUrl(RouteConfig::SEARCH, $lang);
            $this->termsConditions = $dataForCache['termsConditions'] = $this->getShopUrl(RouteConfig::TERMS_CONDITIONS, $lang);
            $this->wishList = $dataForCache['wishList'] = $this->getShopUrl(RouteConfig::WISH_LIST, $lang);
            $this->returns = $dataForCache['returns'] = $this->getShopUrl(RouteConfig::ORDER_RETURN, $lang);
            $this->returnConfirmation = $dataForCache['returnConfirmation'] = $this->getShopUrl(RouteConfig::ORDER_RETURN_CONFIRMATION, $lang);
            $this->changeMail = $dataForCache['changeMail'] = $this->getShopUrl(RouteConfig::CHANGE_MAIL, $lang);
            $this->newsletterOptOut = $dataForCache['newsletterOptOut'] = $this->getShopUrl(RouteConfig::NEWSLETTER_OPT_OUT, $lang);
            $this->orderDocument = $dataForCache['orderDocument'] = $this->getShopUrl(RouteConfig::ORDER_DOCUMENT);

            Utils::putCacheKey('shopUrls_' . $lang, $dataForCache, 10);
        }
    }

    private function initByCache(array $dataFromCache)
    {
        $this->appendTrailingSlash = $dataFromCache['appendTrailingSlash'];
        $this->trailingSlashSuffix = $dataFromCache['trailingSlashSuffix'];
        $this->includeLanguage = $dataFromCache['includeLanguage'];
        $this->basket = $dataFromCache['basket'];
        $this->cancellationForm = $dataFromCache['cancellationForm'];
        $this->cancellationRights = $dataFromCache['cancellationRights'];
        $this->checkout = $dataFromCache['checkout'];
        $this->confirmation = $dataFromCache['confirmation'];
        $this->contact = $dataFromCache['contact'];
        $this->gtc = $dataFromCache['gtc'];
        $this->home = $dataFromCache['home'];
        $this->legalDisclosure = $dataFromCache['legalDisclosure'];
        $this->login = $dataFromCache['login'];
        $this->myAccount = $dataFromCache['myAccount'];
        $this->passwordReset = $dataFromCache['passwordReset'];
        $this->privacyPolicy = $dataFromCache['privacyPolicy'];
        $this->registration = $dataFromCache['registration'];
        $this->search = $dataFromCache['search'];
        $this->termsConditions = $dataFromCache['termsConditions'];
        $this->wishList = $dataFromCache['wishList'];
        $this->returns = $dataFromCache['returns'];
        $this->returnConfirmation = $dataFromCache['returnConfirmation'];
        $this->changeMail = $dataFromCache['changeMail'];
        $this->newsletterOptOut = $dataFromCache['newsletterOptOut'];
        $this->orderDocument = $dataFromCache['orderDocument'];
    }

    /**
     * Get the URL of the return form for a specific order.
     *
     * @param string|int $orderId The id of the order to return items for.
     * @param string $orderAccessKey Access key to authorize accessing the order. Required for guest accounts.
     * @return string
     */
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

            return $this->getShopUrl(RouteConfig::ORDER_RETURN, Utils::getLang(),null, $params);
        }

        return $this->getShopUrl(RouteConfig::ORDER_RETURN, Utils::getLang(), [$orderId, $orderAccessKey]);
    }

    /**
     * Get the URL of a file stored in an order property.
     *
     * @param string $path The path to the file read from the value of the order property.
     * @return string
     */
    public function orderPropertyFile($path)
    {
        return $this->getShopUrl(RouteConfig::ORDER_PROPERTY_FILE, Utils::getLang(), [$path]);
    }

    /**
     * Get a preview URL for an order document.
     *
     * @param string|int $documentId Id of the order document to get order.
     * @param string|int $orderId Id of the order the document belongs to.
     * @param string $orderAccessKey Access key to authorize accessing the order. Required for guest accounts.
     * @return string
     */
    public function orderDocumentPreview($documentId, $orderId, $orderAccessKey = null)
    {
        if ($orderAccessKey == null) {
            /** @var Request $request */
            $request = pluginApp(Request::class);
            $orderAccessKey = $request->get('accessKey');
        }

        $url = $this->getShopUrl(
            RouteConfig::ORDER_DOCUMENT,
            Utils::getLang(),
            ['documentId' => $documentId],
            ['orderId' => $orderId, 'accessKey' => $orderAccessKey],
            'order-document/preview'
        );
        return $url;
    }

    /**
     * Get tracking URL for a specific order id.
     *
     * @param string|int $orderId Id of the order to get the tracking URL for.
     * @return string
     */
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

    /**
     * Get the URL of the order confirmation page for a specific order id.
     *
     * @param string|int $orderId Id of the order to get the confirmation URL for.
     * @return string
     */
    public function orderConfirmation($orderId)
    {
        if (RouteConfig::getCategoryId(RouteConfig::CONFIRMATION) > 0) {
            $suffix = '?orderId=' . $orderId;
        } else {
            // if there is no trailing slash we must add a slash before the orderID to divide the suffix
            // from the given URL path else we have to add a slash after the orderID to show a correct URL
            $suffix = $this->appendTrailingSlash ? $orderId . '/' : '/' . $orderId;
        }
        return $this->confirmation . $suffix;
    }

    private function getShopUrl($route, $language = null, $routeParams = [], $urlParams = [], $overrideUrl = null)
    {
        $key = $route;

        // Sanitize inputs
        $routeParams = $routeParams ?? [];
        $urlParams = $urlParams ?? [];

        if (count($routeParams) || count($urlParams)) {
            $key .= '.' . implode('.', $routeParams) . '.' . json_encode($urlParams);
        }

        if (strlen($overrideUrl)) {
            $key .= '.' . $overrideUrl;
        }

        return $this->fromMemoryCache(
            $key,
            function () use ($route, $routeParams, $urlParams, $overrideUrl, $language) {
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
                    pluginApp(UrlQuery::class, ['path' => ($url ?? $route), 'lang' => $language]),
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

    /**
     * Check if two routes are equal but ignore trailing slashes.
     *
     * @param string $urlA First URL to compare.
     * @param string $urlB Second URL to compare.
     * @return bool True if the two URLs are equal.
     */
    public function equals($urlA, $urlB)
    {
        if (substr($urlA, 0, 1) !== '/') {
            $urlA = '/' . $urlA;
        }
        if (substr($urlA, -1, 1) !== '/') {
            $urlA = $urlA . '/';
        }
        if (substr($urlB, 0, 1) !== '/') {
            $urlB = '/' . $urlB;
        }
        if (substr($urlB, -1, 1) !== '/') {
            $urlB = $urlB . '/';
        }
        return $urlA === $urlB;
    }

    /**
     * Get type of the currently displayed page.
     *
     * @return string Type of the current page. @see RouteConfig
     */
    public function getTemplateType()
    {
        return $this->templateType ?? $this->fromMemoryCache(
                'templateType',
                function () {
                    /** @var Request $request */
                    $request = pluginApp(Request::class);

                    /** @var ShopBuilderRequest $shopBuilderRequest */
                    $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);

                    if ($request->has('templateType')) {
                        // template type is explicitly set via request param
                        return $request->get('templateType');
                    }

                    // detect template type from request uri
                    $url = Utils::makeRelativeUrl(
                        explode('?', $request->getRequestUri())[0],
                        $this->includeLanguage
                    );

                    if ($shopBuilderRequest->isShopBuilder() && ($previewUri = $shopBuilderRequest->getPreviewUri(
                        )) !== null) {
                        $url = Utils::makeRelativeUrl($previewUri, $this->includeLanguage);
                    }

                    if (!strlen($url) || $url === '/') {
                        return RouteConfig::HOME;
                    }

                    // match url pattern
                    if (preg_match('/(?:a\-\d+|_\d+|_\d+_\d+|^\/\d+)\/?$/m', $url) === 1) {
                        return RouteConfig::ITEM;
                    } elseif (preg_match('/_t\d+\/?$/m', $url) === 1) {
                        return RouteConfig::TAGS;
                    } elseif (preg_match('/confirmation\/\d+\/([A-Za-z]|\d)+\/?/m', $url) === 1) {
                        return RouteConfig::CONFIRMATION;
                    }

                    foreach (RouteConfig::ALL as $routeKey) {
                        if ($this->equals($url, $this->getUrl($routeKey))) {
                            // current page is a special linked page
                            return $routeKey;
                        }
                    }

                    if ($shopBuilderRequest->isShopBuilder(
                        ) && ($previewType = $shopBuilderRequest->getPreviewContentType()) !== null) {
                        $previewTypeMap = [
                            'content' => RouteConfig::CATEGORY,
                            'checkout' => RouteConfig::CHECKOUT,
                            'myaccount' => RouteConfig::MY_ACCOUNT,
                            'singleitem' => RouteConfig::ITEM,
                            'categoryitem' => RouteConfig::CATEGORY,
                            'itemsearch' => RouteConfig::SEARCH,
                            'itemset' => RouteConfig::ITEM,
                        ];

                        return $previewTypeMap[$previewType] ?? RouteConfig::CATEGORY;
                    }

                    // template type cannot be determined
                    return RouteConfig::CATEGORY;
                }
            );
    }

    /**
     * Set the template type from a custom controller. If not defined the template type
     * will fallback to {@see RouteConfig::CATEGORY} on custom routes.
     *
     * @param string $type The type of the template.
     */
    public function setTemplateType($type)
    {
        $this->templateType = $type;
    }

    /**
     * Check if current page is of a given type. @see RouteConfig for available type values.
     *
     * @param string $routeKey Type to check current page against.
     *
     * @return bool True if current page matches the given type.
     *
     */
    public function is($routeKey)
    {
        return $this->getTemplateType() === $routeKey;
    }

    /**
     * Check if current page is in the list of legal pages.
     * These contain cancellation rights, cancellation form,
     * legal disclosure, terms and conditions and privacy
     * policy.
     */
    public function isLegalPage()
    {
        $currentRoute = $this->getTemplateType();

        return in_array(
            $currentRoute,
            [
                RouteConfig::CANCELLATION_RIGHTS,
                RouteConfig::CANCELLATION_FORM,
                RouteConfig::LEGAL_DISCLOSURE,
                RouteConfig::TERMS_CONDITIONS,
                RouteConfig::PRIVACY_POLICY
            ]
        );
    }

    private function getUrl($routeKey)
    {
        switch ($routeKey) {
            case RouteConfig::BASKET:               return $this->basket;
            case RouteConfig::CANCELLATION_RIGHTS:  return $this->cancellationRights;
            case RouteConfig::CANCELLATION_FORM:    return $this->cancellationForm;
            case RouteConfig::CHANGE_MAIL:          return $this->changeMail;
            case RouteConfig::CHECKOUT:             return $this->checkout;
            case RouteConfig::CONFIRMATION:         return $this->confirmation;
            case RouteConfig::CONTACT:              return $this->contact;
            case RouteConfig::HOME:                 return $this->home;
            case RouteConfig::LEGAL_DISCLOSURE:     return $this->legalDisclosure;
            case RouteConfig::LOGIN:                return $this->login;
            case RouteConfig::MY_ACCOUNT:           return $this->myAccount;
            case RouteConfig::NEWSLETTER_OPT_OUT:   return $this->newsletterOptOut;
            case RouteConfig::ORDER_DOCUMENT:       return $this->orderDocument;
            case RouteConfig::ORDER_RETURN:         return $this->returns;
            case RouteConfig::ORDER_RETURN_CONFIRMATION: return $this->returnConfirmation;
            case RouteConfig::PASSWORD_RESET:       return $this->passwordReset;
            case RouteConfig::PRIVACY_POLICY:       return $this->privacyPolicy;
            case RouteConfig::REGISTER:             return $this->registration;
            case RouteConfig::SEARCH:               return $this->search;
            case RouteConfig::TERMS_CONDITIONS:     return $this->gtc;
            case RouteConfig::WISH_LIST:            return $this->wishList;
            default:                                return null;
        }

    }
}
