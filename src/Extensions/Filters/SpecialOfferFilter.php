<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class SpecialOfferFilter
 * @package IO\Extensions\Filters
 */
class SpecialOfferFilter extends AbstractFilter
{
    /**
     * ItemNameFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFilters():array
    {
        return [
            "specialOffer" => "specialOffer"
        ];
    }

    /**
     * Build the item name from the configuration
     * @param string $defaultPrice
     * @param object $prices
     * @param string $priceType
	 * @param string $exact
     * @return string
     */
    public function specialOffer( $defaultPrice, $prices, $priceType, $exact = null )
    {
		$price = "";

		if ($prices["specialOffer"])
		{
			if ($exact)
			{
				$price = $prices["specialOffer"][$priceType][$exact] ? $prices["specialOffer"][$priceType][$exact] : $defaultPrice;
			}
			else
			{
				$price = $prices["specialOffer"][$priceType] ? $prices["specialOffer"][$priceType] : $defaultPrice;
			}
		}
		else
		{
			$price = $defaultPrice;
		}

		return $price;
    }
}
