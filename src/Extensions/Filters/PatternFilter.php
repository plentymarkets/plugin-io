<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;

/**
 * Class PatternFilter
 *
 * Contains twig filters.
 *
 * @package IO\Extensions\Filters
 */
class PatternFilter extends AbstractFilter
{
    /**
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            "find" => "findPattern",
            "getObjectValue" => "getObjectValue"
        ];
    }

    /**
     * Object that gets json_encoded and decoded and then the value based on the key accessor is returned.
     *
     * @param mixed $object Object to return value from.
     * @param string $key Object key to return value from.
     * @return string
     */
    public function getObjectValue($object, $key): string
    {
        $jsonObject = json_encode($object);

        $jsonDecodedObject = json_decode($jsonObject, true);

        return (string)$jsonDecodedObject[$key];
    }

    /**
     * Find matches in input with given regex.
     *
     * @param string $input Input to get matches from.
     * @param string $regex Regex to match the input with.
     * @return array
     */
    public function findPattern(string $input, string $regex): array
    {
        $matches = [];
        preg_match_all("/(" . $regex . ")/", $input, $matches);
        return $matches[0];
    }
}
