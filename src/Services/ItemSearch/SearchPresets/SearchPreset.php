<?php

namespace IO\Services\ItemSearch\SearchPresets;

interface SearchPreset
{
    public static function getSearchFactory( $options );
}