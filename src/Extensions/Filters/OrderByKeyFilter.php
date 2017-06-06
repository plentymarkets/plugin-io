<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class ItemImagesFilter
 * @package IO\Extensions\Filters
 */
class OrderByKeyFilter extends AbstractFilter
{
    /**
     * ItemImagesFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getFilters():array
    {
        return [
            'orderByKey' => 'getItemsOrderedByKey'
        ];
    }

    /**
     * @param $array
     * @param $sortKey
     * @return array
     */
    public function getItemsOrderedByKey ($array, $sortKey):array
    {
        usort($array, $this->orderArrayByKey($sortKey));

        return $array;
    }

    private function orderArrayByKey($key)
    {
        return function ($a, $b) use ($key)
        {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
}
