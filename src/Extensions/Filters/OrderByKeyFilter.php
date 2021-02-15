<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class OrderByKeyFilter
 *
 * Contains twig filter that sorts the array by a given key.
 *
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
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            'orderByKey' => 'getItemsOrderedByKey'
        ];
    }

    /**
     * Sorts the array based on the sort key.
     *
     * @param array $array Array that gets sorted.
     * @param string $sortKey Key based on what the array gets sorted.
     * @return array
     */
    public function getItemsOrderedByKey($array, $sortKey): array
    {
        usort($array, $this->orderArrayByKey($sortKey));

        return $array;
    }

    private function orderArrayByKey($key)
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
}
