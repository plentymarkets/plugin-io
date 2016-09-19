<?php //strict

namespace LayoutCore\Extensions\Filters;

use LayoutCore\Extensions\AbstractFilter;

class PatternFilter extends AbstractFilter
{
	public function getFilters():array
	{
		return [
			"find" => "findPattern"
		];
	}
	
	public function findPattern(string $input, string $regex):array
	{
		$matches = [];
		preg_match_all("/(" . $regex . ")/", $input, $matches);
		return $matches[0];
	}
}
