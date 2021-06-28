<?php

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;

/**
 * Class FindItemVariation
 *
 * @package IO\Extensions\Functions
 */
class FindItemVariation extends AbstractFunction
{

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "find_item_variation" => "findItemVariationById"
        ];
    }

    /**
     * Find item variation by id in given variation array
     *
     * @param int $variationId Variation ID to find in $variations
     * @param array $variations An array of variation documents
     *
     * @return mixed variation
     */
    public function findItemVariationById(int $variationId, array $variations = [])
    {
        $array = array_filter(
            $variations,
            function ($variation) use ($variationId) {
                return $variation['data']['variation']['id'] == $variationId;
            }
        );
        return array_shift($array);
    }
}
