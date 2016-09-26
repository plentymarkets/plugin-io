<?php //strict

namespace LayoutCore\Extensions\Filters;

use Plenty\Plugin\ConfigRepository;
use LayoutCore\Extensions\AbstractFilter;
use Plenty\Modules\Item\DataLayer\Models\ItemDescription;

class ItemNameFilter extends AbstractFilter
{

  /**
   * @var ConfigRepository
   */
  private $config;

  public function __construct( ConfigRepository $config )
  {
      parent::__construct();
      $this->config = $config;
  }

  public function getFilters():array<string, string>
  {
      return [
          "itemName" => "itemName"
      ];
  }

  public function itemName( ItemDescription $itemDescription, string $configName ):string
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
