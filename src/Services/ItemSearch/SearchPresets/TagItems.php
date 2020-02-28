<?php

namespace IO\Services\ItemSearch\SearchPresets;

use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;

/**
 * Class TagItems
 *
 * Search preset for tagged variations
 * Available options (see VariationList for inherited options)
 * - tagIds: List of tag ids to get assigned items for
 *
 * @package IO\Services\ItemSearch\SearchPresets
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\TagItems
 */
class TagItems extends VariationList
{
    /**
     * @inheritDoc
     */
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
