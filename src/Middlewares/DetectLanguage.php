<?php

namespace IO\Middlewares;

use IO\Controllers\CategoryController;
use IO\Helper\Utils;
use IO\Services\CheckoutService;
use IO\Services\LocalizationService;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateService;
use Plenty\Modules\System\Models\WebstoreConfiguration;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Middleware;

/**
 * Class DetectLanguage
 *
 * Set language, if necessary.
 *
 * @package IO\Middlewares
 */
class DetectLanguage extends Middleware
{
    const WEB_AJAX_BASE = '/WebAjaxBase.php';

    /**
     * Before the request is processed, the language is changed, if necessary.
     *
     * @param Request $request
     */
    public function before(Request $request)
    {
        $this->detectLanguage($request);
    }
    
    public function detectLanguage($request, $url = null)
    {
        if (substr($request->getRequestUri(), 0, strlen(self::WEB_AJAX_BASE)) !== self::WEB_AJAX_BASE) {
            $requestUri = $url ?? $request->get('plentyMarkets');
            
            /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
            $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);
            $webstoreConfig = $webstoreConfigurationRepository->getWebstoreConfiguration();
            $splittedURL = explode('/', $requestUri);
        
            $isValidLang = in_array($splittedURL[0], Utils::getLanguageList());
            if ($isValidLang) {
                CategoryController::$LANGUAGE_FROM_URL = $splittedURL[0];
            } else {
                CategoryController::$LANGUAGE_FROM_URL = Utils::getDefaultLang();
            }
            
            $langFromUrl = $request->get('Lang', CategoryController::$LANGUAGE_FROM_URL);
            if (strpos(end($splittedURL), '.') === false) {
                $this->setLanguage($langFromUrl, $webstoreConfig);
            }
        }
    }

    /**
     * After the request is processed, do nothing here.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function after(Request $request, Response $response)
    {
        return $response;
    }

    /**
     * Set language to locale service and change currency, if necessary.
     *
     * @param string $language Language to be set.
     * @param WebstoreConfiguration $webstoreConfiguration WebstoreConfiguration from the current request.
     */
    private function setLanguage($language, $webstoreConfiguration)
    {
        if (is_null($language) || strlen($language) !== 2 || !in_array($language, $webstoreConfiguration->languageList)) {
            // language is not valid. set default language
            $language = $webstoreConfiguration->defaultLanguage;
        }

        if ($language === Utils::getLang()) {
            // language has not changed.
            return;
        }

        $service = pluginApp(LocalizationService::class);
        $service->setLanguage($language);

        /** @var TemplateConfigService $templateConfigService */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $enabledCurrencies = explode(', ', $templateConfigService->get('currency.available_currencies'));
        $currency = $webstoreConfiguration->defaultCurrencyList[$language];
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
