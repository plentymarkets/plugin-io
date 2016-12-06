<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class PatternFilter
 * @package IO\Extensions\Filters
 */
class PatternFilter extends AbstractFilter
{
    /**
     * Return the available filter methods
     * @return array
     */
	public function getFilters():array
	{
		return [
			"find" => "findPattern"
		];
	}

    /**
     * Find matches in input with given regex
     * @param string $input
     * @param string $regex
     * @return array
     */
	public function findPattern(string $input, string $regex):array
	{
		$matches = [];
		preg_match_all("/(" . $regex . ")/", $input, $matches);
		return $matches[0];
	}
}
