<?php

namespace IO\Services\ItemSearch\SearchPresets;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;

/**
 * Class TagItems
 *
 * Search preset for tagged variations
 * Available options (see VariationList for inherited options)
 * - tagIds: List of tag ids to get assigned items for
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
class TagItems extends VariationList
{
    public static function getSearchFactory($options)
    {
        $tagIds = [];
        if ( array_key_exists('tagIds', $options ) )
        {
            $tagIds = $options['tagIds'];
        }
        /** @var VariationSearchFactory $factory */
        $factory = parent::getSearchFactory($options);
        $factory->hasAnyTag($tagIds);

        return $factory;
    }
}