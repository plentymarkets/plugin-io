<?php

namespace IO\Helper;

/**
 * Class DefaultSearchResult
 *
 * This class contains a structural blueprint for a search result.
 *
 * @package IO\Helper
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Helpers\DefaultSearchResult
 */
class DefaultSearchResult
{
    /** @var array The blueprint for a search result. */
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

    /** @var array Additional blueprint for an admin preview search result. */
    const ADMIN_PREVIEW_DEFAULT_RESULT = [
        'texts'                     => [
            'name1'                 => 'N / A',
            'name2'                 => 'N / A',
            'name3'                 => 'N / A'
        ],
        'prices'                    => [
            'default' => [
                'price' => [
                    'value' => 'N / A',
                    'formatted' => 'N / A'
                ],
                'unitPrice' => [
                    'value' => 'N / A',
                    'formatted' => 'N / A'
                ],
                'basePrice' => 'N / A',
            ],
        ]
    ];

    /**
     * Merge the blueprint in the search result.
     * @param array $data A search result.
     * @return array|mixed
     */
    public static function merge( $data )
    {
        $defaults = self::DEFAULT_RESULT;
        if ( Utils::isAdminPreview() )
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
