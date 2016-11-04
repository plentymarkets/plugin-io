<?php //strict

namespace LayoutCore\Extensions\Filters;

use Plenty\Plugin\ConfigRepository;
use LayoutCore\Extensions\AbstractFilter;
use Plenty\Modules\Item\DataLayer\Models\ItemDescription;

/**
 * Class ItemNameFilter
 * @package LayoutCore\Extensions\Filters
 */
class ItemNameFilter extends AbstractFilter
{

    /**
    * @var ConfigRepository
    */
    private $config;

    /**
     * ItemNameFilter constructor.
     * @param ConfigRepository $config
     */
    public function __construct( ConfigRepository $config )
    {
      parent::__construct();
      $this->config = $config;
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

    if ($configName == '0' && $itemDescription->name1 != '')
    {
      $showName = $itemDescription->name1;
    }
    elseif ($configName == '1' && $itemDescription->name2 != '')
    {
      $showName = $itemDescription->name2;
    }
    elseif ($configName == '2' && $itemDescription->name3 != '')
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
