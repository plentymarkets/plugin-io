<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Item\DataLayer\Models\ItemDescription;

/**
 * Class ItemNameFilter
 * @package IO\Extensions\Filters
 */
class ItemNameFilter extends AbstractFilter
{
    private $defaultConfigItemName;
    private $defaultConfigConsiderVariationName;
    /**
     * ItemNameFilter constructor.
     */
    public function __construct()
    {
        /** @var TemplateConfigService $configService */
        $configService = pluginApp( TemplateConfigService::class );
        $this->defaultConfigItemName = $configService->get('item.name');
        $this->defaultConfigConsiderVariationName = $configService->get('item.considerVariationName');

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
     * @param object $itemData
     * @param string $configName
     * @param string $considerVariationName
     * @return string
     */
    public function itemName( $itemData, $configName = null, $considerVariationName = null )
    {
        if ( $configName === null )
        {
            $configName = $this->defaultConfigItemName;
        }

        if ( $considerVariationName === null )
        {
            $considerVariationName = $this->defaultConfigConsiderVariationName;
        }

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

        if ($considerVariationName == 'itemNameVariationName' && $variationName)
        {
            $showName .= ' ' . $variationName;
        }

        return $showName;
    }
}
