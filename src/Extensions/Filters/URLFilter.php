<?php

namespace IO\Extensions\Filters;

use Exception;
use IO\Extensions\AbstractFilter;
use IO\Helper\Utils;
use IO\Services\ItemService;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;

/**
 * Class URLFilter
 *
 * Contains twig filter to get item URLs with.
 *
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
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            'itemURL' => 'buildItemURL',
            'variationURL' => 'buildVariationURL'
        ];
    }

    /**
     * Build the URL for the item by item ID or variation ID.
     *
     * @param array $itemData Item data to build the URL from.
     * @param bool $withVariationId Determines if the variation ID suffix should be added.
     * @return string
     */
    public function buildItemURL($itemData, $withVariationId = true): string
    {
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];

        if ($itemId === null || $itemId <= 0) {
            return "";
        }

        /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
        $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);

        $includeLanguage = Utils::getLang() !== Utils::getDefaultLang();
        if ($variationId === null || $variationId <= 0) {
            return $urlBuilderRepository->buildItemUrl($itemId)->toRelativeUrl($includeLanguage);
        } else {
            $url = $urlBuilderRepository->buildVariationUrl($itemId, $variationId);

            return $url->append(
                $urlBuilderRepository->getSuffix($itemId, $variationId, $withVariationId)
            )->toRelativeUrl($includeLanguage);
        }
    }

    /**
     * Gets the item URL based on the variation ID.
     *
     * @param int $variationId Variation ID to get the URL with.
     * @return string
     *
     * @throws Exception
     * @deprecated
     */
    public function buildVariationURL($variationId = 0): string
    {
        $variation = $this->itemService->getVariation($variationId);
        return $this->buildItemURL($variation['documents'][0]['data'], true);
    }

}
