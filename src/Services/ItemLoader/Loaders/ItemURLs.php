<?php

namespace IO\Services\ItemLoader\Loaders;

use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderPaginationContract;
use IO\Services\ItemLoader\Contracts\ItemLoaderSortingContract;
use IO\Services\WebstoreConfigurationService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;

class ItemURLs implements ItemLoaderContract, ItemLoaderPaginationContract, ItemLoaderSortingContract
{
    private $options;

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
        return $this->getPrimaryLoader()->getFilterStack($this->options);
    }

    /**
     * @param array $options
     */
    public function setOptions($options = [])
    {
        $this->options = $options;
    }

    /**
     * @param array $defaultResultFields
     * @return array
     */
    public function getResultFields($defaultResultFields)
    {
        return [
            'item.id',
            'variation.id',
            'texts.*',
            'defaultCategories'];
    }

    /**
     * @param array $options
     * @return int
     */
    public function getCurrentPage($options = [])
    {
        $primaryLoader = $this->getPrimaryLoader();
        if ( $primaryLoader instanceof ItemLoaderPaginationContract )
        {
            return $primaryLoader->getCurrentPage( $this->options );
        }

        return 1;
    }

    /**
     * @param array $options
     * @return int
     */
    public function getItemsPerPage($options = [])
    {
        $primaryLoader = $this->getPrimaryLoader();
        if ( $primaryLoader instanceof ItemLoaderPaginationContract )
        {
            return $primaryLoader->getItemsPerPage( $this->options );
        }

        return 1;
    }

    /**
     * @param array $options
     * @return SortingInterface
     */
    public function getSorting($options = [])
    {
        $primaryLoader = $this->getPrimaryLoader();
        if ($primaryLoader instanceof ItemLoaderSortingContract)
        {
            return $primaryLoader->getSorting($this->options);
        }

        return null;
    }


    private function getPrimaryLoader()
    {
        $loaderClassList = $this->options['loaderClassList'];
        if ( $loaderClassList !== null && count($loaderClassList['single'] ) )
        {
            return pluginApp( $loaderClassList['single'][0] );
        }

        return pluginApp( SingleItem::class );
    }
}