<?php

namespace IO\Services\ItemSearch\Factories\Faker\Traits;

use IO\Helper\ArrayHelper;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;

trait FakeCountry
{
    private $countries = null;

    protected function country()
    {
        if (is_null($this->countries))
        {
            /** @var CountryRepositoryContract $countryRepository */
            $countryRepository = pluginApp(CountryRepositoryContract::class);
            $this->countries = ArrayHelper::toArray($countryRepository->getCountriesList(null, []));
        }

        $index = rand(0, count($this->countries));
        return $this->countries[$index];
    }
}