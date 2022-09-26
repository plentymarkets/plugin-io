<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Helper\Utils;
use IO\Services\ItemSearch\Extensions\ItemSearchExtension;
use IO\Services\ItemSearch\Extensions\SortExtension;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\BaseCollapse;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Collapse\InnerHit\BaseInnerHit;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentInnerHitsToRootProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\ScoreModifier\RandomScore;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\MultipleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Sorting\SingleSorting;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\IncludeSource;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\MutatorInterface;
use Plenty\Modules\Item\Search\Aggregations\ItemAttributeValueCardinalityAggregation;
use Plenty\Modules\Item\Search\Aggregations\ItemAttributeValueCardinalityAggregationProcessor;
use Plenty\Modules\Item\Search\Filter\SearchFilter;
use Plenty\Modules\Item\Search\Sort\NameSorting;
use Plenty\Modules\Webshop\ItemSearch\Helpers\LoadResultFields;
use Plenty\Plugin\Log\Loggable;
use Plenty\Modules\Webshop\ItemSearch\Factories\VariationSearchFactory AS VariationSearchFactoryContract;
/**
 * Class BaseSearchFactory
 *
 * Base factory to build elastic search requests.
 *
 * @package IO\Services\ItemSearch\Factories
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory
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

    /** @var array */
    private $filterInstances = [];

    /** @var ItemSearchExtension[] */
    private $extensions = [];

    /** @var string */
    private $collapseField = null;

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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::inherit()
     */
    public function inherit( $inheritedProperties = null )
    {
        /** @var BaseSearchFactory $newBuilder */
        $newBuilder = pluginApp( self::class );

        /*if ( $inheritedProperties === null || in_array(VariationSearchFactoryContract::INHERIT_COLLAPSE, $inheritedProperties ) )
        {
            $newBuilder->collapse = $this->collapse;
        }

        if ( $inheritedProperties === null || in_array(VariationSearchFactoryContract::INHERIT_COLLAPSE, $inheritedProperties ) && !is_null($newBuilder->collapseField))
        {
            $newBuilder->groupBy($newBuilder->collapseField);
        }*/

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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::withMutator()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::createFilter()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::withFilter()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::withResultFields()
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
            $this->resultFields = $this->loadResultFields( (string)$fields );
        }
        return $this;
    }

    /**
     * @return array
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::getResultFields()
     */
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::withExtension()
     */
    public function withExtension( $extensionClass, $extensionParams = [] )
    {
        $this->extensions[] = ['class' => $extensionClass, 'params' => $extensionParams];
        return $this;
    }

    /**
     * Get all registered extensions
     *
     * @return ItemSearchExtension[]
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::getExtensions()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::withAggregation()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::setPage()
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::sortBy()
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
        if ( strpos( $field, 'texts.name' ) !== false )
        {
            $sortingInterface = pluginApp(
                NameSorting::class,
                [
                    str_replace('texts.', '', $field ),
                    Utils::getLang(),
                    $order
                ]
            );
        }
        else if ( strlen($field) )
        {
            if ( strpos( $field, 'sorting.price.') !== false )
            {
                $field = sprintf(
                    'sorting.priceByClientDynamic.%d.%s',
                    Utils::getPlentyId(),
                    substr($field, strlen('sorting.price.'))
                );
            }

            $sortingInterface = pluginApp( SingleSorting::class, [$field, $order] );
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::sortByMultiple()
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
     * @param array $idList
     * @return $this
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::setOrder()
     */
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::groupBy()
     */
    public function groupBy( $field )
    {
        $this->collapseField = $field;
        return $this;
    }

    /**
     * Build the elastic search request.
     *
     * @return DocumentSearch
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::build()
     */
    public function build()
    {
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

        $search = $this->prepareSearch($source);

        // ADD FILTERS
        $filterClasses = [];
        $queryClasses = [];
        foreach( $this->filters as $filter )
        {
            if ( $filter instanceof SearchFilter )
            {
                $queryClasses[] = get_class($filter);
                $search->addQuery( $filter );
            }
            else
            {
                $filterClasses[] = get_class($filter);
                $search->addFilter( $filter );
            }
        }

        // ADD RANDOM MODIFIER
        if($this->randomScoreModifier instanceof RandomScore)
        {
            $search->setScoreModifier($this->randomScoreModifier);
        }

        // ADD AGGREGATIONS
        $aggregationClasses = [];
        foreach( $this->aggregations as $aggregation )
        {
            $aggregationClasses[] = get_class($aggregation);
            $search->addAggregation( $aggregation );
        }

        if ( $this->sorting !== null )
        {
            $search->setSorting( $this->sorting );
        }

        if ( $this->itemsPerPage < 0 )
        {
            $this->itemsPerPage = 1000;
        }

        $search->setPage( $this->page, $this->itemsPerPage );

        $search->addSource( $source );

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

        return $search;
    }

    /**
     * Build the search instance itself. May be overridden by concrete factories.
     *
     * @param IncludeSource $source
     * @return DocumentSearch
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::prepareSearch()
     */
    protected function prepareSearch($source)
    {
        $collapse = null;
        if(!is_null($this->collapseField))
        {
            /** @var BaseCollapse $collapse */
            $collapse = pluginApp( BaseCollapse::class, [$this->collapseField] );

            $counterAggregationProcessor = pluginApp( ItemAttributeValueCardinalityAggregationProcessor::class );
            $counterAggregation = pluginApp( ItemAttributeValueCardinalityAggregation::class, [$counterAggregationProcessor, $this->collapseField] );
            $this->withAggregation( $counterAggregation );

            /** @var BaseInnerHit $innerHit */
            $innerHit = pluginApp(BaseInnerHit::class, ['cheapest']);
            $innerHit->setSorting(pluginApp(SingleSorting::class, ['sorting.price.avg', 'asc']));
            $innerHit->setSource($source);
            $collapse->addInnerHit($innerHit);

            /** @var DocumentInnerHitsToRootProcessor $docProcessor */
            $processor = pluginApp(DocumentInnerHitsToRootProcessor::class, [$innerHit->getName()]);
            $search = pluginApp(DocumentSearch::class, [$processor]);

            // Group By Item Id
            $search->setCollapse($collapse);
        }
        else
        {
            /** @var DocumentProcessor $processor */
            $processor = pluginApp( DocumentProcessor::class );
            /** @var DocumentSearch $search */
            $search = pluginApp( DocumentSearch::class, [$processor] );
        }

        // ADD MUTATORS
        $mutatorClasses = [];
        foreach( $this->mutators as $mutator )
        {
            $processor->addMutator( $mutator );
            $mutatorClasses[] = get_class($mutator);
        }

        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.BaseSearchFactory_prepareSearch",
            [
                "hasCollapse"   => $collapse instanceof BaseCollapse,
                "mutators"      => $mutatorClasses
            ]
        );

        return $search;
    }

    /**
     * @param string $sortingField
     * @return string
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Factories\BaseSearchFactory::checkRandomSorting()
     */
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
