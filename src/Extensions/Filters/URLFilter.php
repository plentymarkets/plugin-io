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
            "variationURL" => "buildVariationURL"
		];
	}

    /**
     * Build the URL for the item by item ID or variation ID
     * @param int $itemId
     * @param int $variationId
     * @param bool $withItemName
     * @return string
     */
	public function buildItemURL($itemData, $withVariationId = true):string
	{
        $itemURL = '';
        
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];
        $urlContent = $itemData['texts']['urlPath'];
        
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
            
            if($withVariationId && $variationId > 0)
            {
                $itemURL .= '_' . $variationId;
            }
        }
        
        return $itemURL;
	}
    
    public function buildVariationURL($variationId = 0, bool $withItemName = false):string
    {
        $variation = $this->itemService->getVariation( $variationId );
        return $this->buildItemURL( $variation['documents'][0]['data'] );
    }
    
}
