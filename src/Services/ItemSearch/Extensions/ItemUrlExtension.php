<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Contracts\VariationSearchFactoryContract as VariationSearchFactory;
use IO\Services\UrlBuilder\VariationUrlBuilder;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Webshop\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Webshop\Contracts\WebstoreConfigurationRepositoryContract;

/**
 * Class ItemUrlExtension
 *
 * Check if item data already contains a calculated item url.
 * Otherwise generate item url and store url for later usage.
 *
 * @package IO\Services\ItemSearch\Extensions
 */
class ItemUrlExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch($parentSearchBuilder)
    {
        /** @var WebstoreConfigurationRepositoryContract $webstoreConfigurationRepository */
        $webstoreConfigurationRepository = pluginApp(WebstoreConfigurationRepositoryContract::class);

        $languageMutator = pluginApp(
            LanguageMutator::class,
            ["languages" => $webstoreConfigurationRepository->getActiveLanguageList()]
        );

        return $parentSearchBuilder->inherit(
            [
                VariationSearchFactory::INHERIT_FILTERS,
                VariationSearchFactory::INHERIT_PAGINATION,
                VariationSearchFactory::INHERIT_COLLAPSE,
                VariationSearchFactory::INHERIT_AGGREGATIONS,
                VariationSearchFactory::INHERIT_SORTING
            ]
        )
            ->withResultFields(
                [
                    'item.id',
                    'variation.id',
                    'texts.*',
                    'defaultCategories'
                ]
            )
            ->withMutator($languageMutator);
    }

    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {
        /** @var UrlBuilderRepositoryContract $urlBuilderRepository */
        $urlBuilderRepository = pluginApp(UrlBuilderRepositoryContract::class);

        foreach ($extensionResult['documents'] as $key => $urlDocument) {
            VariationUrlBuilder::fillItemUrl($urlDocument['data']);
            $document = $baseResult['documents'][$key];
            if (count($document)
                && count($document['data']['texts'])
                && strlen($document['data']['texts']['urlPath']) <= 0) {
                // attach generated item url if not defined
                $itemUrl = $urlBuilderRepository->buildVariationUrl(
                    $urlDocument['data']['item']['id'],
                    $urlDocument['data']['variation']['id']
                )->getPath();

                $baseResult['documents'][$key]['data']['texts']['urlPath'] = $itemUrl;
            }
        }

        return $baseResult;
    }
}
