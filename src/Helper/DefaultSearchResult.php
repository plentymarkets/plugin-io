<?php

namespace IO\Helper;

class DefaultSearchResult
{
    const DEFAULT_RESULT = [
        'images' => [
            'all'       => [],
            'variation' => []
        ],
        'item' => [
            'customsTariffNumber'   => '',
            'producingCountry'      => [
                'names' => []
            ],
            'manufacturer'          => [],
            'condition'             => [
                'names' => []
            ]
        ],
        'variation' => [
            'model'                 => '',
            'name'                  => '',
        ],
        'facets'                    => [],
        'filter'                    => [],
        'unit'                      => [],
        'texts'                     => [],
        'attributes'                => [],
        'properties'                => []
    ];

    public static function merge( $data )
    {
        return self::mergeValues( $data, self::DEFAULT_RESULT );
    }

    private static function mergeValues( $data, $defaults )
    {
        if ( $data === null )
        {
            $data = [];
        }

        foreach( $defaults as $key => $value )
        {
            if ( is_array( $defaults[$key] ) )
            {
                $data[$key] = self::mergeValues( $data[$key], $defaults[$key] );
            }

            else if ( !array_key_exists( $key, $data ) || $data[$key] === null )
            {
                $data[$key] = $defaults[$key];
            }
        }

        return $data;
    }
}