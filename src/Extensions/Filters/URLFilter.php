<?php //strict

namespace LayoutCore\Extensions\Filters;

use Plenty\Plugin\ConfigRepository;
use LayoutCore\Extensions\AbstractFilter;
use LayoutCore\Services\ItemService;

/**
 * Class URLFilter
 * @package LayoutCore\Extensions\Filters
 */
class URLFilter extends AbstractFilter
{
	/**
	 * @var ConfigRepository
	 */
	private $config;
	/**
	 * @var ItemService
	 */
	private $itemService;

    /**
     * URLFilter constructor.
     * @param ConfigRepository $config
     * @param ItemService $itemService
     */
	public function __construct(ConfigRepository $config, ItemService $itemService)
	{
		parent::__construct();
		$this->config      = $config;
		$this->itemService = $itemService;
	}

    /**
     * Return the available filter methods
     * @return array
     */
	public function getFilters():array
	{
		return [
			"buildItemURL" => "buildItemURL",
            "buildVariationURL" => "buildVariationURL"
		];
	}

    /**
     * Build the URL for the item by item ID or variation ID
     * @param int $itemId
     * @param int $variationId
     * @param bool $withItemName
     * @return string
     */
	public function buildItemURL(int $itemId = 0, int $variationId = 0, bool $withItemName = false):string
	{
		$itemURL = '/' . $itemId;

		if($variationId > 0)
		{
			$itemURL .= '/' . $variationId;
		}

		if($withItemName)
		{
			$item           = $this->itemService->getItemURL($itemId);
			$itemURLContent = $item->itemDescription->urlContent;

            if( $itemURLContent != "" )
            {
                $e        = explode('/', $itemURLContent);
                $itemName = $e[count($e) - 1];

                $itemURL = '/' . $itemName . $itemURL;
            }
		}

		return $itemURL;
	}

	public function buildVariationURL(int $variationId = 0, bool $withItemName = false):string
    {
        $itemId = $this->itemService->getVariation( $variationId )->itemBase->id;
        return $this->buildItemURL( $itemId, $variationId, $withItemName );
    }
}
