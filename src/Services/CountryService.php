<?php //strict

namespace IO\Services;

use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Frontend\Contracts\Checkout;

/**
 * Class CountryService
 * @package IO\Services
 */
class CountryService
{
	/**
	 * @var CountryRepositoryContract
	 */
	private $countryRepository;

    /**
     * @var Country[][]
     */
	private static $activeCountries = [];

    /**
     * CountryService constructor.
     * @param CountryRepositoryContract $countryRepository
     */
	public function __construct(CountryRepositoryContract $countryRepository)
	{
		$this->countryRepository = $countryRepository;
	}

    /**
     * List all active countries
     * @param string $lang
     * @return Country[]
     */
    public function getActiveCountriesList($lang = 'de'):array
    {
        if (!isset(self::$activeCountries[$lang])) {
            $list = $this->countryRepository->getActiveCountriesList();

            foreach ($list as $country) {
                $country->currLangName   = $country->names->contains('language', $lang) ?
                    $country->names->where('language', $lang)->first()->name :
                    $country->names->first()->name;
                self::$activeCountries[$lang][] = $country;
            }
        }

        return self::$activeCountries[$lang];
    }

    /**
     * Get a list of names for the active countries
     * @param string $language
     * @return array
     */
	public function getActiveCountryNameMap(string $language):array
	{
        $nameMap = [];
        foreach ($this->getActiveCountriesList($language) as $country) {
            $nameMap[$country->id] = $country->currLangName;
        }

        return $nameMap;
	}

    /**
     * Set the ID of the current shipping country
     * @param int $shippingCountryId
     */
	public function setShippingCountryId(int $shippingCountryId)
	{
		pluginApp(Checkout::class)->setShippingCountryId($shippingCountryId);
	}

    /**
     * Get a specific country by ID
     * @param int $countryId
     * @return Country
     */
	public function getCountryById(int $countryId):Country
	{
		return $this->countryRepository->getCountryById($countryId);
	}

    /**
     * Get the name of specific country
     * @param int $countryId
     * @param string $lang
     * @return string
     */
	public function getCountryName(int $countryId, string $lang = "de"):string
	{
		$country = $this->countryRepository->getCountryById($countryId);
		if($country instanceof Country && count($country->names) != 0)
		{
			foreach($country->names as $countryName)
			{
				if($countryName->language == $lang)
				{
					return $countryName->name;
				}
			}
			return $country->names[0]->name;
		}
		return "";
	}
}
