<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class ShuffleFilter
 *
 * Contains twig filter that shuffles an array.
 *
 * @package IO\Extensions\Filters
 */
class ShuffleFilter extends AbstractFilter
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
            'shuffle' => 'shuffle'
        ];
    }

    /**
     * Shuffles the given array.
     *
     * @param array $arrayToShuffle Array that gets shuffled.
     * @return array
     */
    public function shuffle($arrayToShuffle): array
    {
        if (is_array($arrayToShuffle) && count($arrayToShuffle)) {
            shuffle($arrayToShuffle);

            return $arrayToShuffle;
        }

        return [];
    }
}
