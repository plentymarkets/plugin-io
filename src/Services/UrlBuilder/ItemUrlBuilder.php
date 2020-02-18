<?php

namespace IO\Services\UrlBuilder;

use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use IO\Helper\Utils;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

/**
 * Class ItemUrlBuilder
 * @package IO\Services\UrlBuilder
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract
 */
class ItemUrlBuilder
{
    /**
     * @param int $itemId
     * @param string|null $lang
     * @return UrlQuery|mixed
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract::buildItemUrl()
     */
    public function buildUrl(int $itemId, string $lang = null )
    {
        if ( $lang === null )
        {
            $lang = Utils::getLang();
        }

        $variationId = 0;
        if ( count( VariationUrlBuilder::$urlPathMap[$itemId] ) )
        {
            $variationIds = array_keys( VariationUrlBuilder::$urlPathMap[$itemId] );
            $variationId = $variationIds[0];
        }
        else
        {
            $variationId = $this->searchItemVariationId( $itemId, $lang );
        }

        if ( $variationId > 0 )
        {
            /** @var VariationUrlBuilder $variationUrlBuilder */
            $variationUrlBuilder = pluginApp( VariationUrlBuilder::class );
            return $variationUrlBuilder->buildUrl( $itemId, $variationId, $lang );
        }

        return pluginApp( UrlQuery::class, ['path' => $itemId, 'lang' => $lang]);
    }

    private function searchItemVariationId( $itemId, $lang )
    {
        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );

        /** @var VariationSearchFactory $searchFactory */
        $searchFactory = pluginApp( VariationSearchFactory::class );
        $searchFactory
            ->withLanguage( $lang )
            ->withUrls()
            ->hasItemId( $itemId );

        $itemSearchService->getResults([$searchFactory])[0];

        return 0;
    }
}
