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

/**
 * Class BaseSearchFactory
 *
 * Base factory to build elastic search requests.
 *
 * @package IO\Services\ItemSearch\Factories
 */
class BaseSearchFactory
{
    use LoadResultFields;

    const SORTING_ORDER_ASC     = ElasticSearch::SORTING_ORDER_ASC;
    const SORTING_ORDER_DESC    = ElasticSearch::SORTING_ORDER_DESC;

    const INHERIT_AGGREGATIONS  = 'aggregations';
    const INHERIT_COLLAPSE      = 'collapse';
    const INHERIT_EXTENSIONS    = 'extensions';
    const INHERIT_FILTERS       = 'filters';
    const INHERIT_MUTATORS      = 'mutators';
    const INHERIT_PAGINATION    = 'pagination';
    const INHERIT_RESULT_FIELDS = 'resultFields';
    const INHERIT_SORTING       = 'sorting';

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

    /** @var int */
    private $page = 1;

    /** @var int */
    private $itemsPerPage = -1;

    /**
     * Create a new factory instance based on properties of an existing factory.
     *
     * @param BaseSearchFactory     $searchBuilder          The search factory to inherit properties from.
     * @param null|array            $inheritedProperties    List of properties to inherit or null to inherit all properties.
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
            if ( $inheritedProperties === null || in_array(self::INHERIT_COLLAPSE, $inheritedProperties ) )
            {
                $newBuilder->collapse = $searchBuilder->collapse;
            }

            if ( $inheritedProperties === null || in_array(self::INHERIT_EXTENSIONS, $inheritedProperties ) )
            {
                $newBuilder->extensions = $searchBuilder->extensions;
            }

            if ( $inheritedProperties === null || in_array( self::INHERIT_FILTERS, $inheritedProperties ) )
            {
                foreach( $searchBuilder->filters as $filter )
                {
                    $newBuilder->withFilter( $filter );
                }
            }

            if ( $inheritedProperties === null || in_array(self::INHERIT_MUTATORS, $inheritedProperties ) )
            {
                foreach( $searchBuilder->mutators as $mutator )
                {
                    $newBuilder->withMutator( $mutator );
                }
            }

            if ( $inheritedProperties === null || in_array( self::INHERIT_PAGINATION, $inheritedProperties ) )
            {
                $newBuilder->setPage(
                    $searchBuilder->page,
                    $searchBuilder->itemsPerPage
                );
            }

            if ( $inheritedProperties === null || in_array( self::INHERIT_RESULT_FIELDS, $inheritedProperties ) )
            {
                $newBuilder->withResultFields(
                    $searchBuilder->resultFields
                );
            }

            if ( $inheritedProperties === null || in_array( self::INHERIT_SORTING, $inheritedProperties ) )
            {
                $newBuilder->sorting = $searchBuilder->sorting;
            }
        }

        return $newBuilder;
    }

    /**
     * Add a mutator
     *
     * @param MutatorInterface $mutator
     *
     * @return $this
     */
    public function withMutator( $mutator )
    {
        $this->mutators[] = $mutator;
        return $this;
    }

    /**
     * Add a filter. Will create a new instance of the filter class if not already created.
     *
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

    /**
     * Add a filter. Will override existing filter instances.
     *
     * @param TypeInterface $filter
     *
     * @return $this
     */
    public function withFilter( $filter )
    {
        $filterClass = get_class( $filter );
        $this->filters[] = $filter;
        $this->filterInstances[$filterClass] = $filter;
        return $this;
    }

    /**
     * Set fields to be contained in search result.
     * Can be a string referencing a json file to load or a list of fields.
     *
     * @param string|string[]   $fields     Reference to a json file to load fields from or a list of field names.
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

    public function getResultFields()
    {
        return $this->resultFields;
    }

    /**
     * Add an extension.
     *
     * @param string    $extensionClass     Extension class to add.
     * @param array     $extensionParams    Additional parameters to pass to extensions constructor
     * @return $this
     */
    public function withExtension( $extensionClass, $extensionParams = [] )
    {
        $this->extensions[] = pluginApp( $extensionClass, $extensionParams );
        return $this;
    }

    /**
     * Get all registered extensions
     *
     * @return ItemSearchExtension[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Add an aggregation
     *
     * @param AggregationInterface $aggregation
     *
     * @return $this
     */
    public function withAggregation( AggregationInterface $aggregation )
    {
        $this->aggregations[] = $aggregation;
        return $this;
    }

    /**
     * Set pagination parameters.
     *
     * @param int   $page
     * @param int   $itemsPerPage
     *
     * @return $this
     */
    public function setPage( $page, $itemsPerPage )
    {
        $this->page = $page;
        $this->itemsPerPage = $itemsPerPage;
        return $this;
    }

    /**
     * Add sorting parameters
     *
     * @param string    $field      The field to order by
     * @param string    $order      Direction to order results. Possible values: 'asc' or 'desc'
     *
     * @return $this
     */
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

    /**
     * Add multiple sorting parameters
     *
     * @param array     $sortingList    List of sorting parameters. Each entry should have a 'field' and an 'order' property.
     *
     * @return $this
     */
    public function sortByMultiple( $sortingList )
    {
        foreach( $sortingList as $sorting )
        {
            $this->sortBy( $sorting['field'], $sorting['order'] );
        }

        return $this;
    }

    /**
     * Group results by field
     *
     * @param string    $field  The field to group properties by.
     *
     * @return $this
     */
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
     * Build the elastic search request.
     *
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

    /**
     * Build the search instance itself. May be overridden by concrete factories.
     *
     * @return DocumentSearch
     */
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