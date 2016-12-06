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
            "itemName" => "itemName"
        ];
    }

    /**
     * Build the item name from the configuration
     * @param ItemDescription $itemDescription
     * @param string $configName
     * @return string
     */
    public function itemName( ItemDescription $itemDescription, string $configName )
    {
        $showName = '';
    
        if($configName == '0' && $itemDescription->name1 != '')
        {
            $showName = $itemDescription->name1;
        }
        elseif($configName == '1' && $itemDescription->name2 != '')
        {
            $showName = $itemDescription->name2;
        }
        elseif($configName == '2' && $itemDescription->name3 != '')
        {
            $showName = $itemDescription->name3;
        }
        else
        {
            $showName = $itemDescription->name1;
        }
        
        return $showName;
    }

}
