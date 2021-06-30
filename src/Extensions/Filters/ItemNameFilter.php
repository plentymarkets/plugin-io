<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Services\TemplateConfigService;

/**
 * Class ItemNameFilter
 *
 * Contains twig filter to get the item name.
 *
 * @package IO\Extensions\Filters
 */
class ItemNameFilter extends AbstractFilter
{
    private $defaultConfigItemName;
    private $defaultConfigItemDisplayName;

    /**
     * ItemNameFilter constructor.
     */
    public function __construct()
    {
        /** @var TemplateConfigService $configService */
        $configService = pluginApp(TemplateConfigService::class);

        $this->defaultConfigItemName = $configService->get('item.name');
        $this->defaultConfigItemDisplayName = $configService->get('item.displayName');

        parent::__construct();
    }

    /**
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            "itemName" => "itemName"
        ];
    }

    /**
     * Gets the item name which is configured to be shown in the shop.
     *
     * @param array $itemData Item data from which the name is returned.
     * @param string $configName What item name to get.
     *                               Use name that is set in the plugin config if nothing or null is given.
     * @param string $displayName Decides if the variation name is attached, only shown or not shown at all.
     *                                Defaults to the value set in the plugin config.
     * @return string
     */
    public function itemName($itemData, $configName = null, $displayName = null)
    {
        if ($configName === null) {
            $configName = $this->defaultConfigItemName;
        }

        if ($displayName === null) {
            $displayName = $this->defaultConfigItemDisplayName;
        }

        $itemTexts = $itemData['texts'];
        $variationName = $itemData['variation']['name'];

        $configName = intval($configName);
        if ($configName === 1 && strlen($itemTexts['name2'])) {
            $showName = $itemTexts['name2'];
        } elseif ($configName === 2 && strlen($itemTexts['name3'])) {
            $showName = $itemTexts['name3'];
        } else {
            $showName = $itemTexts['name1'];
        }

        if ($displayName === 'itemNameVariationName' && strlen($variationName)) {
            $showName .= ' ' . $variationName;
        }

        if ($displayName === 'variationName' && strlen($variationName)) {
            $showName = $variationName;
        }

        return $showName;
    }
}
