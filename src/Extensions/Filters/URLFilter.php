<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Services\ItemService;
use IO\Services\TemplateConfigService;
use IO\Services\UrlService;

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

	/** @var TemplateConfigService $templateConfigService */
	private $templateConfigService;

    /**
     * URLFilter constructor.
     * @param ItemService $itemService
     */
	public function __construct(ItemService $itemService, TemplateConfigService $templateConfigService )
	{
		parent::__construct();
		$this->itemService = $itemService;
		$this->templateConfigService = $templateConfigService;
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
     * @param $itemData
     * @param bool $withVariationId
     * @return string
     */
	public function buildItemURL($itemData, $withVariationId = true):string
	{
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];

        UrlService::prepareItemUrlMap( $itemData );

	    /** @var UrlService $urlService */
	    $urlService = pluginApp(UrlService::class);
	    $url = $urlService->getVariationURL( $itemId, $variationId )->toRelativeUrl();

	    if( !$withVariationId && $this->templateConfigService->get('global.enableOldUrlPattern') === "false" )
        {
            $url = substr( $url, 0, strlen($url) - strlen("_" . $variationId ));
        }

        return $url;
	}

    /**
     * @param int $variationId
     * @return string
     *
     * @deprecated
     */
    public function buildVariationURL($variationId = 0):string
    {
        $variation = $this->itemService->getVariation( $variationId );
        return $this->buildItemURL( $variation['documents'][0]['data'], true );
    }
    
}
