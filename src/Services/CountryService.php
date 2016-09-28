<?php //strict

namespace LayoutCore\Services;

use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Frontend\Contracts\Checkout;

class CountryService
{
	/**
	 * @var CountryRepositoryContract
	 */
	private $countryRepository;
	/**
	 * @var Checkout
	 */
	private $checkout;

	public function __construct(
		CountryRepositoryContract $countryRepository,
		Checkout $checkout)
	{
		$this->countryRepository = $countryRepository;
		$this->checkout          = $checkout;
	}

	public function getActiveCountriesList():array
	{
/*        $list = $this->countryRepository->getCountriesList(1, array());

        $countriesList = array();
        foreach($list as $country)
        {
            $countriesList[] = $country;
        }

		return $countriesList;
*/
		return array();
	}

	public function getActiveCountryNameMap(string $language):array
	{
		return $this->countryRepository->getActiveCountryNameMap($language);
	}

	public function setShippingCountryId(int $shippingCountryId)
	{
		$this->checkout->setShippingCountryId($shippingCountryId);
	}

	public function getCountryById(int $countryId):Country
	{
		return $this->countryRepository->getCountryById($countryId);
	}

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
