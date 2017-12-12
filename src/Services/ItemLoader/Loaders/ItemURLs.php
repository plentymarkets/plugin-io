<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;

class ItemURLs implements ItemLoaderContract
{
    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        /** @var WebstoreConfigurationService $webstoreConfigService */
        $webstoreConfigService = pluginApp( WebstoreConfigurationService::class );

        $languageMutator = pluginApp(
            LanguageMutator::class,
            [ "languages" => $webstoreConfigService->getActiveLanguageList() ]
        );

        $documentProcessor = pluginApp( DocumentProcessor::class );
        $documentProcessor->addMutator( $languageMutator );

        return pluginApp( DocumentSearch::class, [$documentProcessor] );
    }

    /**
     * @return array
     */
    public function getAggregations()
    {
        return [];
    }

    /**
     * @param array $options
     * @return TypeInterface[]
     */
    public function getFilterStack($options = [])
    {
        return pluginApp(SingleItem::class)->getFilterStack($options);
    }
}