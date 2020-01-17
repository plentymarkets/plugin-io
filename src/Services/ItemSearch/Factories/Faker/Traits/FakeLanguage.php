<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

use IO\Services\SessionStorageService;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;

trait FakeLanguage
{
    protected function shopLanguage($skipActiveLang = false)
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);

        //TODO VDI MEYER
        /** @var SessionStorageService $sessionStorageService */
        $sessionStorageService = pluginApp(SessionStorageService::class);
        $lang = $sessionStorageService->getLang();

        $languages = [];
        foreach ($webstoreConfigurationRepository->getActiveLanguageList() as $language) {
            if ($language !== $lang || !$skipActiveLang) {
                $languages[] = $language;
            }
        }

        if (!count($languages)) {
            return $lang !== 'en' ? 'en' : 'de';
        }

        $index = rand(0, count($languages));

        return $languages[$index];
    }
}
