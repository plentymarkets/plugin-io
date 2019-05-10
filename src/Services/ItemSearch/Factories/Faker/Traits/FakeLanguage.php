<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

use IO\Services\SessionStorageService;
use IO\Services\WebstoreConfigurationService;

trait FakeLanguage
{
    protected function shopLanguage($skipActiveLang = false)
    {
        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp(WebstoreConfigurationService::class);

        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);
        $lang = $sessionStorageService->getLang();

        $languages = $webstoreConfigService->getActiveLanguageList();
        if(($removeIndex = array_search($lang, $languages)) !== false && $skipActiveLang)
        {
            array_splice($languages, $removeIndex, 1);
        }

        $index = rand(0, count($languages));

        return $languages[$index];
    }
}