<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemLoader\Services\LoadResultFields;
use IO\Services\ItemSearch\Extensions\ItemSearchExtension;
use IO\Services\SessionStorageService;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\BaseCollapse;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\CollapseInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Index\Settings\Analysis\Filter\FilterInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\MultipleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SortingInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\MutatorInterface;
use Plenty\Modules\Item\Search\Aggregations\ItemCardinalityAggregation;
use Plenty\Modules\Item\Search\Aggregations\ItemCardinalityAggregationProcessor;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Modules\Item\Search\Sort\NameSorting;

class BaseSearchFactory
{
    use LoadResultFields;

    const SORTING_ORDER_ASC     = ElasticSearch::SORTING_ORDER_ASC;
    const SORTING_ORDER_DESC    = ElasticSearch::SORTING_ORDER_DESC;

    const INHERIT_MUTATORS      = 'mutators';
    const INHERIT_FILTERS       = 'filters';
    const INHERIT_RESULT_FIELDS = 'resultFields';

    /** @var AggregationInterface[] */
    private $aggregations = [];

    /** @var MutatorInterface[] */
    private $mutators = [];

    /** @var TypeInterface[] */
    private $filters = [];

    /** @var array  */
    private $resultFields = [];

    /** @var array */
    private $filterInstances = [];

    /** @var ItemSearchExtension[] */
    private $extensions = [];

    /** @var CollapseInterface */
    private $collapse = null;

    /** @var MultipleSorting */
    private $sorting = null;

    private $page = 1;

    private $itemsPerPage = -1;

    /**
     * @param BaseSearchFactory $searchBuilder
     * @param null|string[]     $inheritedProperties
     *
     * @return BaseSearchFactory
     * @throws \ErrorException
     */
    public static function inherit( $searchBuilder, $inheritedProperties = null )
    {
        /** @var BaseSearchFactory $newBuilder */
        $newBuilder = pluginApp( self::class );

        if ( $searchBuilder !== null )
        {
            if ( $inheritedProperties === null || in_array(self::INHERIT_MUTATORS, $inheritedProperties ) )
            {
                foreach( $searchBuilder->mutators as $mutator )
                {
                    $newBuilder->withMutator( $mutator );
                }
            }

            if ( $inheritedProperties === null || in_array( self::INHERIT_FILTERS, $inheritedProperties ) )
            {
                foreach( $searchBuilder->filters as $filter )
                {
                    $newBuilder->withFilter( $filter );
                }
            }

            if ( $inheritedProperties === null || in_array( self::INHERIT_RESULT_FIELDS, $inheritedProperties ) )
            {
                $newBuilder->withResultFields(
                    $searchBuilder->resultFields
                );
            }
        }

        return $newBuilder;
    }

    /**
     * @param MutatorInterface $mutator
     * @return BaseSearchFactory
     */
    public function withMutator( $mutator )
    {
        $this->mutators[] = $mutator;
        return $this;
    }

    /**
     * @param string    $filterClass
     *
     * @return TypeInterface
     */
    public function createFilter( $filterClass )
    {
        if ( !array_key_exists( $filterClass, $this->filterInstances ) )
        {
            $this->filterInstances[$filterClass] = pluginApp( $filterClass );
            $this->filters[] = $this->filterInstances[$filterClass];
        }

        return $this->filterInstances[$filterClass];
    }

    public function withFilter( $filter )
    {
        $filterClass = get_class( $filter );
        $this->filters[] = $filter;
        $this->filterInstances[$filterClass] = $filter;
        return $this;
    }

    /**
     * @param string|string[]   $fields
     *
     * @return BaseSearchFactory
     */
    public function withResultFields( $fields )
    {
        if ( is_array( $fields ) )
        {
            // set given result fields
            $this->resultFields = $fields;
        }
        else
        {
            // load result fields from given resource
            $this->resultFields = $this->loadResultFields( $fields );
        }
        return $this;
    }

    public function withExtension( $extensionClass, $extensionParams = [] )
    {
        $this->extensions[] = pluginApp( $extensionClass );
        return $this;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function withAggregation( $aggregation )
    {
        $this->aggregations[] = $aggregation;
        return $this;
    }

    public function setPage( $page, $itemsPerPage )
    {
        $this->page = $page;
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }

    public function sortBy( $field, $order = self::SORTING_ORDER_DESC )
    {
        if ( $this->sorting === null )
        {
            $this->sorting = pluginApp( MultipleSorting::class );
        }

        if ( $order !== self::SORTING_ORDER_ASC && $order !== self::SORTING_ORDER_DESC )
        {
            $order = self::SORTING_ORDER_DESC;
        }

        if ( strpos( $field, 'texts.name' ) !== false )
        {
            $sortingInterface = pluginApp(
                NameSorting::class,
                [
                    str_replace('texts.', '', $field ),
                    pluginApp(SessionStorageService::class)->getLang(),
                    $order
                ]
            );
        }
        else
        {
            $sortingInterface = pluginApp( SingleSorting::class, [$field, $order] );
        }

        $this->sorting->addSorting( $sortingInterface );

        return $this;
    }

    public function sortByMultiple( $sortingList )
    {
        foreach( $sortingList as $sorting )
        {
            $this->sortBy( $sorting['field'], $sorting['order'] );
        }

        return $this;
    }

    public function groupBy( $field )
    {
        $collapse = pluginApp( BaseCollapse::class, [$field] );
        $this->collapse = $collapse;

        $counterAggregationProcessor = pluginApp( ItemCardinalityAggregationProcessor::class );
        $counterAggregation = pluginApp( ItemCardinalityAggregation::class, [$counterAggregationProcessor] );
        $this->withAggregation( $counterAggregation );

        return $this;
    }

    /**
     * @return DocumentSearch
     */
    public function build()
    {
        $search = $this->prepareSearch();

        // ADD FILTERS
        foreach( $this->filters as $filter )
        {
            if ( $filter instanceof SearchFilter )
            {
                $search->addQuery( $filter );
            }
            else
            {
                $search->addFilter( $filter );
            }
        }

        // ADD COLLAPSE
        if ( $this->collapse instanceof CollapseInterface )
        {
            $search->setCollapse( $this->collapse );
        }

        // ADD AGGREGATIONS
        foreach( $this->aggregations as $aggregation )
        {
            $search->addAggregation( $aggregation );
        }

        // ADD RESULT FIELDS
        /** @var IncludeSource $source */
        $source = pluginApp( IncludeSource::class );
        $resultFields = $this->resultFields;
        if ( count( $resultFields ) )
        {
            $source->activateList( $resultFields );
        }
        else
        {
            $source->activateAll();
        }

        if ( $this->sorting !== null )
        {
            $search->setSorting( $this->sorting );
        }

        $search->setPage( $this->page, $this->itemsPerPage );

        $search->addSource( $source );

        return $search;
    }

    protected function prepareSearch()
    {
        /** @var DocumentProcessor $processor */
        $processor = pluginApp( DocumentProcessor::class );

        // ADD MUTATORS
        foreach( $this->mutators as $mutator )
        {
            $processor->addMutator( $mutator );
        }

        /** @var DocumentSearch $search */
        $search = pluginApp( DocumentSearch::class, [$processor] );

        return $search;
    }
}