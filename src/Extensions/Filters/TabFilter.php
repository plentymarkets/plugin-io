<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class TabFilter
 *
 * Contains twig filter that filters tab symbols out of a string.
 *
 * @package IO\Extensions\Filters
 */
class TabFilter extends AbstractFilter
{
    /**
     * ShuffleFilter constructor.
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
            'filterTabs' => 'filterTabs'
        ];
    }

    /**
     * Gets string that contains no tab symbols.
     *
     * @param string $string String that gets filtered.
     * @return string|string[]|null
     */
    public function filterTabs(string $string)
    {
        return preg_replace('/\s+/', ' ', $string);
    }
}
