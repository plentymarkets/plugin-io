<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

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
     * @return array
     */
    public function getFilters():array
    {
        return [
            'filterTabs' => 'filterTabs'
        ];
    }
    
    public function filterTabs( $string )
    {
        $newString = preg_replace('/\s+/', ' ', $string);
        return $newString;
    }
}
