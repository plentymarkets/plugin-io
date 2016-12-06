<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Services\ItemService;

/**
 * Class URLFilter
 * @package IO\Extensions\Filters
 */
class URLFilter extends AbstractFilter
{
	/**
	 * @var ItemService
	 */
	private $itemService;

    /**
     * URLFilter constructor.
     * @param ItemService $itemService
     */
	public function __construct(ItemService $itemService)
	{
		parent::__construct();
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
