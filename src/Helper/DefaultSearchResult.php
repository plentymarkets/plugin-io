<?php

namespace IO\Helper;

use Plenty\Plugin\Application;

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
    
    const ADMIN_PREVIEW_DEFAULT_RESULT = [
        'texts'                     => [
            'name1'                 => 'N / A',
            'name2'                 => 'N / A',
            'name3'                 => 'N / A'
        ],
        'prices'                    => [
            'default' => [
                'price' => [
                    'value' => null,
                    'formatted' => 'N / A'
                ],
                'unitPrice' => [
                    'value' => null,
                    'formatted' => 'N / A'
                ],
                'basePrice' => 'N / A',
            ],
        ]
    ];

    public static function merge( $data )
    {
        $defaults = self::DEFAULT_RESULT;
        if ( pluginApp(Application::class)->isAdminPreview() )
        {
            $defaults = self::mergeValues( $defaults, self::ADMIN_PREVIEW_DEFAULT_RESULT );
        }
        return self::mergeValues( $data, $defaults );
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

            else if ( !array_key_exists( $key, $data ) || $data[$key] === null || !strlen($data[$key]) )
            {
                $data[$key] = $defaults[$key];
            }
        }

        return $data;
    }
}