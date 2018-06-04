<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\ItemSearch\SearchPresets\BasketItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\VariationBundle\Contracts\VariationBundleRepositoryContract;

class BundleComponentExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch($parentSearchBuilder)
    {
        return VariationSearchFactory::inherit(
            $parentSearchBuilder,
            [
                VariationSearchFactory::INHERIT_FILTERS,
                VariationSearchFactory::INHERIT_PAGINATION,
                VariationSearchFactory::INHERIT_COLLAPSE,
                VariationSearchFactory::INHERIT_AGGREGATIONS,
                VariationSearchFactory::INHERIT_SORTING
            ])
            ->withResultFields([
                'variation.bundleType'
            ])
            ->build();
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
            $document = $baseResult['documents'][$key];
            if ( count( $extensionDocument )
                && count( $extensionDocument['data']['variation'] )
                && $extensionDocument['data']['variation']['bundleType'] = 'bundle' )
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
                $bundleVariations  = $baseResult['documents'][$key]['data']['bundleComponents'] = $itemSearchService->getResult(
                    BasketItems::getSearchFactory([
                        'variationIds' => $bundleVariationIds
                    ])
                );

                $bundleComponents = [];
                foreach( $bundleVariations[0]['documents'] as $bundleVariation )
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