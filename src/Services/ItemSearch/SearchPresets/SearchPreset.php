<?php

namespace IO\Services\ItemSearch\SearchPresets;

/**
 * Interface SearchPreset
 *
 * Define a preset of a search factory.
 *
 * @package IO\Services\ItemSearch\SearchPresets
 */
interface SearchPreset
{
    /**
     * Get the search factory from the preset.
     *
     * @param   array     $options
     *
     * @return mixed
     */
    public static function getSearchFactory( $options );
}