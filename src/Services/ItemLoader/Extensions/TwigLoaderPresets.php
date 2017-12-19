<?php
namespace IO\Services\ItemLoader\Extensions;

use IO\Services\ItemLoader\Loaders\BasketItems;
use IO\Services\ItemLoader\Loaders\CategoryItems;
use IO\Services\ItemLoader\Loaders\CrossSellingItems;
use IO\Services\ItemLoader\Loaders\ItemURLs;
use IO\Services\ItemLoader\Loaders\LastSeenItemList;
use IO\Services\ItemLoader\Loaders\SearchItems;
use IO\Services\ItemLoader\Loaders\Facets;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\ItemLoader\Loaders\SingleItemAttributes;
use IO\Services\ItemLoader\Loaders\Items;
use IO\Services\ItemLoader\Loaders\TagItems;
use Plenty\Plugin\Templates\Extensions\Twig_Extension;

/**
 * Created by ptopczewski, 09.01.17 11:12
 * Class TwigLoaderPresets
 * @package IO\Services\ItemLoader\Extensions
 */
class TwigLoaderPresets extends Twig_Extension
{
	/**
	 * @return string
	 */
	public function getName():string
	{
		return 'IO_Extension_TwigLoaderPresets';
	}
	
	
	/**
	 * Return a list of global variables
	 * @return array
	 */
	public function getGlobals():array
	{
		return [
			"itemLoaderPresets" => [
				
				"singleItem" => [
				    "single" => [
                        SingleItem::class
                    ],
                    "multi" => [
                        'crossSellingItemsList' => CrossSellingItems::class,
                        'singleItemAttributes' => SingleItemAttributes::class,
                        'itemURLs' => ItemURLs::class
                    ]
				],

			    "categoryList" => [
			        "single" => [
                        CategoryItems::class
                    ],
                    "multi" => [
                        'facets' => Facets::class,
                        'itemURLs' => ItemURLs::class
                    ]
			    ],
                "search" => [
                    "single" => [
                        SearchItems::class
                    ],
                    "multi" => [
                        'facets' => Facets::class,
                        'itemURLs' => ItemURLs::class
                    ]
                ],
                "lastSeenItemsList" => [
                    "single" => [
                        LastSeenItemList::class
                    ],
                    "multi" => [
                        'itemURLs' => ItemURLs::class
                    ]
                ],
                "items" => [
                    "single" => [
                        Items::class
                    ]
                ],
                "crossSellingItemsList" => [
                    "single" => [
                        CrossSellingItems::class
                    ],
                    "multi" => [
                        'itemURLs' => ItemURLs::class
                    ]
                ],
                "tagList" => [
                    "single" => [
                        TagItems::class
                    ],
                    "multi" => [
                        'itemURLs' => ItemURLs::class
                    ]
                ],
                "basketItems" => [
                    "single" => [
                        BasketItems::class
                    ],
                    "multi" => [
                        'itemURLs' => ItemURLs::class
                    ]
                ]
			]
		];
	}
}