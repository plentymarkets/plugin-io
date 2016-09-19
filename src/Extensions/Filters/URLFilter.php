<?php //strict

namespace LayoutCore\Extensions\Filters;

use Plenty\Plugin\ConfigRepository;
use LayoutCore\Extensions\AbstractFilter;
use LayoutCore\Services\ItemService;

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
	
	public function __construct(ConfigRepository $config, ItemService $itemService)
	{
		parent::__construct();
		$this->config      = $config;
		$this->itemService = $itemService;
	}
	
	public function getFilters():array
	{
		return [
			"buildItemURL" => "buildItemURL"
		];
	}
	
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
			
			$e        = explode('/', $itemURLContent);
			$itemName = $e[count($e) - 1];
			
			$itemURL = '/' . $itemName . $itemURL;
		}
		
		return $itemURL;
	}
}
