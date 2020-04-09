<?php

namespace IO\Middlewares;

use IO\Controllers\CategoryController;
use IO\Helper\Utils;
use IO\Services\CheckoutService;
use IO\Services\LocalizationService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

class DetectLanguage extends Middleware
{
    const WEB_AJAX_BASE = '/WebAjaxBase.php';

    public static $DETECTED_LANGUAGE = null;

    /**
     * @param Request $request
     */
    public function before(Request $request)
    {
        if (substr($request->getRequestUri(), 0, strlen(self::WEB_AJAX_BASE)) !== self::WEB_AJAX_BASE) {
            /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
            $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
            $webstoreConfig = $webstoreConfigurationRepository->getWebstoreConfiguration();
            $splittedURL = explode('/', $request->get('plentyMarkets'));

            // request uri is not "/webAjaxBase.php"
            if (!is_null(self::$DETECTED_LANGUAGE)) {
                // language has been detected by plentymarkets core
                $this->setLanguage(self::$DETECTED_LANGUAGE, $webstoreConfig);

                if ($splittedURL[0] !== self::$DETECTED_LANGUAGE) {
                    $isValidLang = array_key_exists($splittedURL[0], Utils::getLanguageList());
                    if ($isValidLang) {
                        CategoryController::$LANGUAGE_FROM_URL = $splittedURL[0];
                    } else {
                        CategoryController::$LANGUAGE_FROM_URL = Utils::getDefaultLang();
                    }
                    // Do not cache content if detected language does not match the language of the url
                    TemplateService::$shouldBeCached = false;
                }
            } elseif (strpos(end($splittedURL), '.') === false) {
                // language has not been detected. check if url points to default language
                $this->setLanguage($splittedURL[0], $webstoreConfig);
            }
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        return $response;
    }

    /**
     * @param string $language
     * @param WebstoreConfiguration $webstoreConfig
     */
    private function setLanguage($language, $webstoreConfig)
    {
        if (is_null($language) || strlen($language) !== 2 || !in_array($language, $webstoreConfig->languageList)) {
            // language is not valid. set default language
            $language = $webstoreConfig->defaultLanguage;
        }

        if ($language === Utils::getLang()) {
            // language has not changed
            return;
        }

        $service = pluginApp(LocalizationService::class);
        $service->setLanguage($language);

        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $enabledCurrencies = explode(', ', $templateConfigService->get('currency.available_currencies'));
        $currency = $webstoreConfig->defaultCurrencyList[$language];
        if (!is_null($currency) && (in_array(
                    $currency,
                    $enabledCurrencies
                ) || array_pop($enabledCurrencies) == 'all')) {
            /** @var CheckoutService $checkoutService */
            $checkoutService = pluginApp(CheckoutService::class);
            $checkoutService->setCurrency($currency);
        }
    }
}
