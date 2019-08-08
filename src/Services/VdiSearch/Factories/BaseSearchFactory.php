<?php

namespace IO\Services\VdiSearch\Factories;

use IO\Contracts\VariationSearchFactoryContract;
use IO\Helper\VDIPart;
use IO\Services\ItemSearch\Extensions\ItemSearchExtension;
use IO\Services\ItemSearch\Extensions\SortExtension;
use IO\Services\ItemSearch\Helper\LoadResultFields;
use IO\Services\PriceDetectService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\BaseCollapse;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\CollapseInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\ScoreModifier\RandomScore;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\MultipleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleNestedSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\MutatorInterface;
use Plenty\Modules\Item\Search\Aggregations\ItemAttributeValueCardinalityAggregation;
use Plenty\Modules\Item\Search\Aggregations\ItemAttributeValueCardinalityAggregationProcessor;
 use Plenty\Modules\Pim\SearchService\Query\ManagedSearchQuery;
use Plenty\Modules\Pim\SearchService\Query\NameAutoCompleteQuery;
use Plenty\Modules\Pim\VariationDataInterface\Model\Context\GroupBy;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;
use Plenty\Plugin\Log\Loggable;

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
    use Loggable;

    /** @var AggregationInterface[] */
    private $aggregations = [];

    /** @var MutatorInterface[] */
    private $mutators = [];

    /** @var TypeInterface[] */
    private $filters = [];

    /** @var array  */
    private $resultFields = [];

    /** @var array  */
    private $parts = [];

    /** @var array */
    private $filterInstances = [];

    /** @var ItemSearchExtension[] */
    private $extensions = [];

    /** @var CollapseInterface */
    private $collapse = null;
    
    private $groupBy = null;

    /** @var MultipleSorting */
    private $sorting = null;

    /** @var RandomScore */
    private $randomScoreModifier = null;

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
    public function inherit( $inheritedProperties = null )
    {
        /** @var BaseSearchFactory $newBuilder */
        $newBuilder = pluginApp( self::class );

        if ( $inheritedProperties === null || in_array(VariationSearchFactoryContract::INHERIT_COLLAPSE, $inheritedProperties ) )
        {
            $newBuilder->collapse = $this->collapse;
        }

        if ( $inheritedProperties === null || in_array(VariationSearchFactoryContract::INHERIT_EXTENSIONS, $inheritedProperties ) )
        {
            $newBuilder->extensions = $this->extensions;
        }

        if ( $inheritedProperties === null || in_array( VariationSearchFactoryContract::INHERIT_FILTERS, $inheritedProperties ) )
        {
            foreach( $this->filters as $filter )
            {
                $newBuilder->withFilter( $filter );
            }
        }

        if ( $inheritedProperties === null || in_array(VariationSearchFactoryContract::INHERIT_MUTATORS, $inheritedProperties ) )
        {
            foreach( $this->mutators as $mutator )
            {
                $newBuilder->withMutator( $mutator );
            }
        }

        if ( $inheritedProperties === null || in_array( VariationSearchFactoryContract::INHERIT_PAGINATION, $inheritedProperties ) )
        {
            $newBuilder->setPage(
                $this->page,
                $this->itemsPerPage
            );
        }

        if ( $inheritedProperties === null || in_array( VariationSearchFactoryContract::INHERIT_RESULT_FIELDS, $inheritedProperties ) )
        {
            $newBuilder->withResultFields(
                $this->resultFields
            );
        }

        if ( $inheritedProperties === null || in_array( VariationSearchFactoryContract::INHERIT_SORTING, $inheritedProperties ) )
        {
            $newBuilder->sorting = $this->sorting;
            $newBuilder->randomScoreModifier = $this->randomScoreModifier;
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
    public function createFilter( $filterClass, $params = [] )
    {
        if ( !array_key_exists( $filterClass, $this->filterInstances ) )
        {
            $this->filterInstances[$filterClass] = app( $filterClass, $params ); //TODO pluginApp()
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

        $this->addParts();

        return $this;

    }
    
    public function getResultFields()
    {
        return $this->resultFields;
    }

    private function addParts()
    {
        /**
         * @var VDIPart $vdiPartHelper
         */
        $vdiPartHelper = pluginApp(VDIPart::class);
        $this->parts = $vdiPartHelper->getPartsByResultFields($this->resultFields);
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
        $this->extensions[] = ['class' => $extensionClass, 'params' => $extensionParams] ;
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
    public function sortBy( $field, $order = VariationSearchFactoryContract::SORTING_ORDER_DESC )
    {
        $field = $this->checkRandomSorting($field);
        if ( $this->sorting === null )
        {
            $this->sorting = pluginApp( MultipleSorting::class );
        }

        if ( $order !== VariationSearchFactoryContract::SORTING_ORDER_ASC && $order !== VariationSearchFactoryContract::SORTING_ORDER_DESC )
        {
            $order = VariationSearchFactoryContract::SORTING_ORDER_DESC;
        }

        $sortingInterface = null;
        if ( strlen($field) )
        {
            if ( strpos( $field, 'filter.prices.price') !== false )
            {
                /** @var PriceDetectService  $priceDetectService */
                $priceDetectService = pluginApp(PriceDetectService::class);
                $sortingInterface = new SingleNestedSorting($field, $order, 'filter.prices', [
                    'terms' => [
                        'filter.prices.priceId' => $priceDetectService->getPriceIdsForCustomer()
                    ]
                ]);
            }
            else
            {
                $sortingInterface = pluginApp( SingleSorting::class, [$field, $order] );
            }
            
        }

        if ( !is_null($sortingInterface) )
        {
            $this->sorting->addSorting( $sortingInterface );
        }
        
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

    public function setOrder( $idList )
    {
        return $this->withExtension(SortExtension::class, [
            'idList' => $idList
        ]);
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
        $this->groupBy = new GroupBy($field);
        return $this;
    }

    /**
     * Build the vdi request.
     *
     * @return VariationDataInterfaceContext
     */
    public function build()
    {
        $vdiContext = $this->prepareSearch();

        // ADD FILTERS
        $filterClasses = [];
        $queryClasses = [];
        foreach( $this->filters as $filter )
        {
            if ( $filter instanceof NameAutoCompleteQuery || $filter instanceof ManagedSearchQuery)
            {
                $queryClasses[] = get_class($filter);
                $vdiContext->addQuery( $filter );
            }
            else
            {
                $filterClasses[] = get_class($filter);
                $vdiContext->addFilter( $filter );
            }
        }

        // ADD RANDOM MODIFIER
        //TODO
        /*if($this->randomScoreModifier instanceof RandomScore)
        {
            $search->setScoreModifier($this->randomScoreModifier);
        }*/

        // ADD AGGREGATIONS
        $aggregationClasses = [];
        //TODO
        /*foreach( $this->aggregations as $aggregation )
        {
            $aggregationClasses[] = get_class($aggregation);
            $vdiContext->addAggregation( $aggregation );
        }*/
        

        if ( $this->sorting !== null )
        {
            $vdiContext->setSorting( $this->sorting );
        }

        if ( $this->itemsPerPage < 0 )
        {
            $this->itemsPerPage = 1000;
        }

        $vdiContext->setPage( $this->page, $this->itemsPerPage );

        //TODO
        //$search->addSource( $source );

        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.BaseSearchFactory_buildSearch",
            [
                "queries"       => $queryClasses,
                "filter"        => $filterClasses,
                "aggregations"  => $aggregationClasses,
                "sorting"       => is_null($this->sorting) ? null : $this->sorting->toArray(),
                "page"          => $this->page,
                "itemsPerPage"  => $this->itemsPerPage,
                "resultFields"  => $this->resultFields
            ]
        );

        return $vdiContext;
    }

    /**
     * Build the search instance itself. May be overridden by concrete factories.
     *
     * @return VariationDataInterfaceContext
     */
    protected function prepareSearch()
    {
        /** @var VariationDataInterfaceContext $vdiContext */
        $vdiContext = app(VariationDataInterfaceContext::class);
        $vdiContextParts = $this->parts;
        if(count($vdiContextParts))
        {
            $vdiContext->setParts($vdiContextParts);
        }

        if($this->groupBy instanceof GroupBy)
        {
            $vdiContext->setGroupBy($this->groupBy);
        }
        
        return $vdiContext;
    }

    private function checkRandomSorting($sortingField)
    {
        if($sortingField == 'item.random')
        {
            if(!$this->randomScoreModifier instanceof RandomScore)
            {
                $this->randomScoreModifier = pluginApp(RandomScore::class);
                $this->randomScoreModifier->setSeed(time());
            }

            $sortingField = '_score';
        }

        return $sortingField;
    }
}
