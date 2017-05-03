<?php
namespace IO\Services\ItemLoader\Extensions;
use IO\Services\ItemLoader\Loaders\CategoryItems;
use IO\Services\ItemLoader\Loaders\LastSeenItemList;
use IO\Services\ItemLoader\Loaders\SearchItems;
use IO\Services\ItemLoader\Loaders\Facets;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\ItemLoader\Loaders\SingleItemAttributes;
use IO\Services\ItemLoader\Loaders\Items;
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
					SingleItem::class,
				    SingleItemAttributes::class
				],
			    
			    "categoryList" => [
				    CategoryItems::class,
                    Facets::class
			    ],
                "search" => [
                    SearchItems::class,
                    Facets::class
                ],
                "lastSeenItemsList" => [
                    LastSeenItemList::class
                ],
                "items" => [
                    Items::class
                ]
			]
		];
	}
}