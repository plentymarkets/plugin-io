<?php

namespace IO\Services\ItemSearch\SearchPresets;

/**
 * Interface SearchPreset
 *
 * Define a preset of a search factory.
 *
 * @package IO\Services\ItemSearch\SearchPresets
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchPreset
 */
interface SearchPreset
{
    /**
     * Get the search factory from the preset.
     *
     * @param   array     $options
     *
     * @return mixed
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\SearchPresets\SearchPreset::getSearchFactory()
     */
    public static function getSearchFactory( $options );
}
