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
     * CountryService constructor.
     * @param CountryRepositoryContract $countryRepository
     */
	public function __construct(CountryRepositoryContract $countryRepository)
	{
		$this->countryRepository = $countryRepository;
	}

    /**
     * List all active countries
     * @return array
     */
	public function getActiveCountriesList(string $lang = "de"):array
	{
        $list = $this->countryRepository->getCountriesList(1, array('states'));

        $countriesList = array();
        foreach($list as $country)
        {
			$country->currLangName = $this->getCountryName($country->id, $lang);
            $countriesList[] = $country;
        }

		return $countriesList;
	}

    /**
     * Get a list of names for the active countries
     * @param string $language
     * @return array
     */
	public function getActiveCountryNameMap(string $language):array
	{
		return $this->countryRepository->getActiveCountryNameMap($language);
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
			return array_shift($country->names)->name;
		}
		return "";
	}
}
