<?php

namespace IO\Helper;

use IO\Services\TemplateConfigService;
use IO\Services\UrlBuilder\UrlQuery;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Translation\Translator;

class Utils
{
    public static function getPlentyId()
    {
        /** @var Application $app */
        $app = pluginApp(Application::class);
        return (int)$app->getPlentyId();
    }

    public static function getWebstoreId()
    {
        /** @var Application $app */
        $app = pluginApp(Application::class);
        return (int)$app->getWebstoreId();
    }

    public static function getLang()
    {
        /** @var LocalizationRepositoryContract $localizationRepository */
        $localizationRepository = pluginApp(LocalizationRepositoryContract::class);
        return $localizationRepository->getLanguage();
    }

    public static function getDefaultLang()
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        return $webstoreConfigurationRepository->getWebstoreConfiguration()->defaultLanguage;
    }

    public static function getLanguageList()
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
        return $webstoreConfigurationRepository->getActiveLanguageList();
    }

    public static function isAdminPreview()
    {
        /** @var Application $app */
        $app = pluginApp(Application::class);
        return $app->isAdminPreview();
    }

    public static function isShopBuilder()
    {
        /** @var ShopBuilderRequest $sbRequest */
        $sbRequest = pluginApp(ShopBuilderRequest::class);
        return $sbRequest->isShopBuilder();
    }

    public static function isContactLoggedIn()
    {
        /** @var AccountService $accountService */
        $accountService = pluginApp(AccountService::class);
        return $accountService->getIsAccountLoggedIn();
    }

    public static function getTemplateConfig($key, $default = null)
    {
        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        return $templateConfigService->get($key, $default);
    }

    public static function translate($key, $params = [], $locale = null)
    {
        /** @var Translator $translator */
        $translator = pluginApp(Translator::class);
        return $translator->trans($key, $params, $locale);
    }

    public static function makeRelativeUrl($path = null, $includeLanguage = false)
    {
        /** @var UrlQuery $query */
        $query = pluginApp(UrlQuery::class, ['path' => $path]);
        return $query->toRelativeUrl($includeLanguage);
    }
}
