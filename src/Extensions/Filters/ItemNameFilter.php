<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use Plenty\Modules\Item\DataLayer\Models\ItemDescription;

/**
 * Class ItemNameFilter
 * @package IO\Extensions\Filters
 */
class ItemNameFilter extends AbstractFilter
{
    /**
     * ItemNameFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFilters():array
    {
        return [
            "itemName" => "itemName",
            "newItemName" => "newItemName"
        ];
    }

    /**
     * Build the item name from the configuration
     * @param array $itemTexts
     * @param string $configName
     * @return string
     */
    public function itemName( $itemTexts, string $configName )
    {
        $showName = '';

        if($configName == '0' && $itemTexts['name1'] != '')
        {
            $showName = $itemTexts['name1'];
        }
        elseif($configName == '1' && $itemTexts['name2'] != '')
        {
            $showName = $itemTexts['name2'];
        }
        elseif($configName == '2' && $itemTexts['name3'] != '')
        {
            $showName = $itemTexts['name3'];
        }
        else
        {
            $showName = $itemTexts['name1'];
        }

        return $showName;
    }

    public function newItemName( $itemData, string $configName, string $considerVariationName )
    {
        $itemTexts = $itemData['texts'];
        $variationName = $itemData['variation']['name'];

        if ($considerVariationName == 'variationName' && $variationName)
        {
            return $variationName;
        }

        $showName = '';

        if ($configName == '1' && $itemTexts['name2'] != '')
        {
            $showName = $itemTexts['name2'];
        }
        elseif ($configName == '2' && $itemTexts['name3'] != '')
        {
            $showName = $itemTexts['name3'];
        }
        else
        {
            $showName = $itemTexts['name1'];
        }

        if ($considerVariationName == 'variationName' && $variationName)
        {
            $showName .= ' ' . $variationName;
        }

        return $showName;
    }
}
