<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;

/**
 * Class Component
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
     * Find item variation by id
     * @param int $variationId Id to find
     * @param array $variations variations to filter
     *
     * @return mixed variation
     */
    public function findItemVariationById( $variationId, $variations )
    {
        return array_shift(array_filter($variations, function ($variation) use ($variationId) {
            return $variation['data']['variation']['id'] == $variationId;
        }));
    }
}
