<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class SpecialOfferFilter
 *
 *
 *
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
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            "specialOffer" => "specialOffer"
        ];
    }

    /**
     * Gets the special offer price.
     *
     * @param string $defaultPrice Default price if no special offer price is set.
     * @param array $prices Price object based on which the special offer price is returned.
     * @param string $priceType Price type accessor.
     * @param string $exact Accessor to return from the object, if passed.
     * @return string
     */
    public function specialOffer($defaultPrice, $prices, $priceType, $exact = null): string
    {
        $price = "";

        if ($prices["specialOffer"]) {
            if ($exact) {
                $price = $prices["specialOffer"][$priceType][$exact] ? $prices["specialOffer"][$priceType][$exact] : $defaultPrice;
            } else {
                $price = $prices["specialOffer"][$priceType] ? $prices["specialOffer"][$priceType] : $defaultPrice;
            }
        } else {
            $price = $defaultPrice;
        }

        return $price;
    }
}
