<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class ShuffleFilter
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
     * @return array
     */
    public function getFilters():array
    {
        return [
            'shuffle' => 'shuffle'
        ];
    }

    public function shuffle( array $arrayToShuffle ):array
    {
        shuffle($arrayToShuffle);
        return $arrayToShuffle;
    }
}
