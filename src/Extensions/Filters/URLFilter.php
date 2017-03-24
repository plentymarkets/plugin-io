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
			"itemURL" => "buildItemURL",
		];
	}

    /**
     * Build the URL for the item by item ID or variation ID
     * @param int $itemId
     * @param int $variationId
     * @param bool $withItemName
     * @return string
     */
	public function buildItemURL(array $itemData):string
	{
        $itemURL = '';
        
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];
        $urlContent = $itemData['texts'][0]['urlPath'];
        
        if((int)$itemId > 0)
        {
            $itemURL .= '/';
            if(strlen($urlContent))
            {
                $itemURL .= $urlContent.'_'.$itemId;
            }
            else
            {
                $itemURL .= $itemId;
            }
            
            if($variationId > 0)
            {
                $itemURL .= '_' . $variationId;
            }
        }
        
        return $itemURL;
	}
}
