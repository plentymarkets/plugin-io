<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\TemplateConfigService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\VariationBundle\Contracts\VariationBundleRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory;
use Plenty\Modules\Webshop\ItemSearch\SearchPresets\BasketItems;
use Plenty\Modules\Webshop\ItemSearch\Services\ItemSearchService;

/**
 * Class BundleComponentExtension
 * @package IO\Services\ItemSearch\Extensions
 * @deprecated since 5.0.0 will be removed in 6.0.0
 */
class BundleComponentExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch($parentSearchBuilder)
    {
        return $parentSearchBuilder->inherit(
            [
                VariationSearchFactory::INHERIT_FILTERS,
                VariationSearchFactory::INHERIT_PAGINATION,
                VariationSearchFactory::INHERIT_COLLAPSE,
                VariationSearchFactory::INHERIT_AGGREGATIONS,
                VariationSearchFactory::INHERIT_SORTING
            ])
            ->withResultFields([
                'variation.bundleType',
                'variation.id'
            ]);
    }

    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {
        /** @var TemplateConfigService $config */
        $config = pluginApp( TemplateConfigService::class );
        if ( $config->get('basket.split_bundles') === 'onlyBundleItem' )
        {
            return $baseResult;
        }

        foreach( $extensionResult['documents'] as $key => $extensionDocument )
        {
            $document = $extensionResult['documents'][$key];
            if ( count( $extensionDocument )
                && is_array($extensionDocument['data']['variation'])
                && count( $extensionDocument['data']['variation'] )
                && $extensionDocument['data']['variation']['bundleType'] === 'bundle' )
            {

                /** @var AuthHelper $authHelper */
                $authHelper  = pluginApp( AuthHelper::class );
                $variationId = $document['data']['variation']['id'];
                $bundle      = $authHelper->processUnguarded(
                    function() use ($variationId) {
                        /** @var VariationBundleRepositoryContract $bundleRepository */
                        $bundleRepository = pluginApp( VariationBundleRepositoryContract::class );
                        return $bundleRepository->findByVariationId( $variationId );
                    }
                );

                $bundleVariationIds = [];
                $bundleQuantities   = [];
                foreach( $bundle as $bundleComponent )
                {
                    $bundleVariationIds[] = $bundleComponent->componentVariationId;
                    $bundleQuantities[$bundleComponent->componentVariationId] = $bundleComponent->componentQuantity;
                }

                /** @var ItemSearchService $itemSearchService */
                $itemSearchService = pluginApp( ItemSearchService::class );
                $bundleVariations  = $itemSearchService->getResults([
                    BasketItems::getSearchFactory([
                        'variationIds' => $bundleVariationIds
                    ])
                ])[0];

                $bundleComponents = [];
                foreach( $bundleVariations['documents'] as $bundleVariation )
                {
                    $bundleComponents[] = [
                        'quantity'  => $bundleQuantities[$bundleVariation['id']],
                        'data'      => $bundleVariation['data']
                    ];
                }

                $baseResult['documents'][$key]['data']['bundleComponents'] = $bundleComponents;
            }

        }

        return $baseResult;
    }
}
