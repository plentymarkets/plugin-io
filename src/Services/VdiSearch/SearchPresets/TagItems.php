<?php

namespace IO\Services\VdiSearch\SearchPresets;

use IO\Services\VdiSearch\Factories\VariationSearchFactory;

/**
 * Class TagItems
 *
 * Search preset for tagged variations
 * Available options (see VariationList for inherited options)
 * - tagIds: List of tag ids to get assigned items for
 *
 * @package IO\Services\VdiSearch\SearchPresets
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
        $factory = parent::getSearchFactory($options)
            ->hasAnyTag($tagIds)
            ->groupByTemplateConfig()
            ->withGroupedAttributeValues()
            ->withReducedResults();

        return $factory;
    }
}
