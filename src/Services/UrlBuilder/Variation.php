<?php

namespace IO\Services\UrlBuilder;

use IO\Helper\StringUtils;
use IO\Services\ItemLoader\Loaders\ItemURLs;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\VariationDescription\Contracts\VariationDescriptionRepositoryContract;

class Variation
{
    public static $urlPathMap;

    public static function fillItemUrl( $itemData )
    {
        $itemId = $itemData['item']['id'];
        $variationId = $itemData['variation']['id'];
        $defaultCategory = 0;
        if ( count($itemData['defaultCategories']) )
        {
            $defaultCategory = $itemData['defaultCategories'][0]['id'];
        }

        $templateConfigService = pluginApp( TemplateConfigService::class );
        $usedItemName = $templateConfigService->get('item.name');
        if ( strlen( $usedItemName ) <= 0 )
        {
            $usedItemName = '0';
        }

        $itemNameFields = ['name1', 'name2', 'name3'];

        $usedName = $itemNameFields[$usedItemName];

        foreach( $itemData['texts'] as $lang => $texts )
        {
            self::$urlPathMap[$itemId][$variationId][$lang] = [
                'urlPath'           => $texts['urlPath'],
                'name'              => $texts[$usedName],
                'defaultCategory'   => $defaultCategory
            ];
        }
    }

    /**
     * @param int $itemId
     * @param int $variationId
     * @param string|null $lang
     * @return UrlQuery
     */
    public function buildUrl(int $itemId, int $variationId, string $lang = null ): UrlQuery
    {
        if ( $lang === null )
        {
            $lang = pluginApp( SessionStorageService::class )->getLang();
        }

        $itemData = self::$urlPathMap[$itemId][$variationId][$lang];

        if ( $itemData !== null && $itemData['urlPath'] !== null && strlen( $itemData['urlPath'] ) )
        {
            return $this->buildUrlQuery( $itemData['urlPath'], $lang );
        }
        else
        {
            if ( $itemData === null )
            {
                $itemData = $this->searchItem( $itemId, $variationId, $lang );
                if ( strlen($itemData['urlPath']) )
                {
                    return $this->buildUrlQuery( $itemData['urlPath'], $lang );
                }
            }

            $itemName4Url = StringUtils::string4URL( $itemData['name'] );
            $itemUrl = null;
            if ( $itemData['defaultCategory'] > 0 )
            {
                /** @var Category $categoryUrlBuilder */
                $categoryUrlBuilder = pluginApp( Category::class );
                $itemUrl = $categoryUrlBuilder
                    ->buildUrl( $itemData['defaultCategory'], $lang )
                    ->join( $itemName4Url );
            }
            else
            {
                $itemUrl = $this->buildUrlQuery( $itemName4Url, $lang );
            }

            /** @var AuthHelper $authHelper */
            $authHelper = pluginApp( AuthHelper::class );

            $authHelper->processUnguarded(
                function() use ( $variationId, $lang, $itemUrl  ) {
                    /** @var VariationDescriptionRepositoryContract $variationDescriptionRepository */
                    $variationDescriptionRepository = pluginApp( VariationDescriptionRepositoryContract::class );

                    $variationDescriptionRepository->update(
                        [
                            'urlPath' => $itemUrl->getPath()
                        ],
                        $variationId,
                        $lang
                    );
                }
            );
            self::$urlPathMap[$itemId][$variationId][$lang] = [
                'urlPath' => $itemUrl->getPath(),
            ];

            self::$urlPathMap[$itemId][$variationId][$lang] = [
                'urlPath'           => $itemUrl->getPath(),
                'name'              => $itemData['name'],
                'defaultCategory'   => $itemData['defaultCategory']
            ];

            return $itemUrl;
        }

    }

    public function getSuffix( $itemId, $variationId )
    {
        $templateConfigService = pluginApp( TemplateConfigService::class );
        $enableOldUrlPattern = $templateConfigService->get('global.enableOldUrlPattern') === "true";
        return $enableOldUrlPattern ? "/a-" . $itemId : "_" . $itemId . "_" . $variationId;
    }

    private function searchItem( $itemId, $variationId, $lang )
    {
        /** @var ItemLoaderService $itemLoader */
        $itemLoader = pluginApp( ItemLoaderService::class );
        $result = $itemLoader
            ->setLoaderClassList([ItemURLs::class])
            ->setOptions([
                'itemId' => $itemId,
                'variationId' => $variationId,
                'lang' => $lang
            ])
            ->load();

        if ( count($result['documents']) )
        {
            self::fillItemUrl( $result['documents'][0]['data'] );
            return self::$urlPathMap[$itemId][$variationId][$lang];
        }

        return [];
    }


    private function buildUrlQuery( $path, $lang ): UrlQuery
    {
        return pluginApp(
            UrlQuery::class,
            array('path' => $path, 'lang' => $lang)
        );
    }
}