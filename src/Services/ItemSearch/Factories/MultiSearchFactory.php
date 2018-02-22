<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemSearch\Extensions\ItemSearchExtension;
use IO\Services\ItemSearch\SearchPresets\SearchPreset;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Item\Search\Contracts\VariationElasticSearchMultiSearchRepositoryContract;

class MultiSearchFactory
{
    private $searches = [];

    private $extensions = [];

    /**
     * @return array
     */
    public function getSearches()
    {
        return $this->searches;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param string            $resultName
     * @param BaseSearchFactory $searchBuilder
     *
     * @return MultiSearchFactory
     */
    public function addSearch( $resultName, $searchBuilder )
    {
        if ( !array_key_exists( $resultName, $this->searches ) )
        {
            /** @var DocumentSearch $search */
            $search = $searchBuilder->build();
            $search->setName( $resultName );

            $secondarySearches = [];
            foreach( $searchBuilder->getExtensions() as $i => $extension )
            {
                $secondarySearch = $extension->getSearch( $searchBuilder );
                if ( $secondarySearch !== null )
                {
                    $secondarySearch->setName( $resultName . "__" . $i );
                    $secondarySearches[] = $secondarySearch;
                }
            }

            $this->searches[$resultName] = [
                'primary'   => $search,
                'secondary' => $secondarySearches
            ];

            $this->extensions[$resultName] = $searchBuilder->getExtensions();
        }
        return $this;
    }

    public function getResults()
    {
        /** @var VariationElasticSearchMultiSearchRepositoryContract $searchRepository */
        $searchRepository = pluginApp( VariationElasticSearchMultiSearchRepositoryContract::class );

        $primarySearchNames = [];
        foreach( $this->searches as $resultName => $searches )
        {
            $searchRepository->addSearch( $searches['primary'] );
            $primarySearchNames[] = $resultName;
            foreach( $searches['secondary'] as $secondarySearch )
            {
                $searchRepository->addSearch( $secondarySearch );
            }
        }

        $rawResults = $searchRepository->execute();
        $results = [];

        foreach( $primarySearchNames as $searchName )
        {
            $result = $rawResults[$searchName];
            foreach( $this->extensions[$searchName] as $i => $extension )
            {
                if ( $extension instanceof ItemSearchExtension )
                {
                    $result = $extension->transformResult( $result, $rawResults[$searchName."__".$i] );
                }
            }

            $results[$searchName] = $result;
        }

        return $results;
    }
}