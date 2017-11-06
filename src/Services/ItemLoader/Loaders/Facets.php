<?php
namespace IO\Services\ItemLoader\Loaders;

use IO\Builder\Facet\FacetBuilder;
use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\ItemLoader\Contracts\ItemLoaderContract;
use IO\Services\ItemLoader\Services\FacetExtensionContainer;
use IO\Services\SessionStorageService;
use IO\Services\PriceDetectService;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Processor\DocumentProcessor;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Query\Type\TypeInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Document\DocumentSearch;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\SearchInterface;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BuiltIn\LanguageMutator;
use Plenty\Modules\Item\Search\Aggregations\FacetAggregation;
use Plenty\Modules\Item\Search\Aggregations\FacetAggregationProcessor;
use Plenty\Modules\Item\Search\Filter\FacetFilter;
use Plenty\Modules\Item\Search\Mutators\ImageMutator;
use Plenty\Plugin\Application;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Item\Search\Filter\SalesPriceFilter;

/**
 * Created by ptopczewski, 06.01.17 14:44
 * Class SingleItemAttributes
 * @package IO\Services\ItemLoader\Loaders
 */
class Facets implements ItemLoaderContract
{
    /**
     * @var FacetExtensionContainer
     */
    private $facetExtensionContainer;

    /**
     * Facets constructor.
     * @param FacetExtensionContainer $facetExtensionContainer
     */
    public function __construct(FacetExtensionContainer $facetExtensionContainer)
    {
        $this->facetExtensionContainer = $facetExtensionContainer;
    }

    /**
     * @return SearchInterface
     */
    public function getSearch()
    {
        $languageMutator = pluginApp(LanguageMutator::class, ["languages" => [pluginApp(SessionStorageService::class)->getLang()]]);
        $imageMutator    = pluginApp(ImageMutator::class);
        $imageMutator->addClient(pluginApp(Application::class)->getPlentyId());

        $documentProcessor = pluginApp(DocumentProcessor::class);
        $documentProcessor->addMutator($languageMutator);
        $documentProcessor->addMutator($imageMutator);

        return pluginApp(DocumentSearch::class, [$documentProcessor]);
    }

    /**
     * @return array
     */
    public function getAggregations()
    {
        $facetProcessor = pluginApp(FacetAggregationProcessor::class);
        $facetSearch    = pluginApp(FacetAggregation::class, [$facetProcessor]);

        $aggregations = [$facetSearch];

        foreach ($this->facetExtensionContainer->getFacetExtensions() as $facetExtension) {
            if ($facetExtension instanceof FacetExtension) {
                $aggregations[] = $facetExtension->getAggregation();
            }
        }

        return $aggregations;
    }

    /**
     * @param array $options
     * @return TypeInterface[]
     */
    public function getFilterStack($options = [])
    {
        if (array_key_exists('facets', $options) && count($options['facets'])) {
            $facetValues   = FacetBuilder::buildFacetValues($options['facets']);
            $activeFilters = explode(',', $options['facets']);
        } else {
            /**
             * @var Request $request
             */
            $request       = pluginApp(Request::class);
            $facetValues   = FacetBuilder::buildFacetValues($request->get('facets', ''));
            $activeFilters = explode(',', $request->get('facets', ''));
        }

        $additionalFilters = [];
        if(!empty($activeFilters)){
            foreach ($this->facetExtensionContainer->getFacetExtensions() as $facetExtension) {
                if ($facetExtension instanceof FacetExtension) {
                    $filter = $facetExtension->extractFilterParams($activeFilters);
                    if(!is_null($filter)){
                        $additionalFilters[] = $filter;
                    }
                }
            }
        }


        $filters = [];
        if (count($facetValues)) {

            /**
             * @var FacetFilter $facetFilter
             */
            $facetFilter = pluginApp(FacetFilter::class);
            $facetFilter->hasEachFacet($facetValues);

            $filters[] = $facetFilter;
        }

        $filters = array_merge($filters, $additionalFilters);
    
        /**
         * @var PriceDetectService $priceDetectService
         */
        $priceDetectService = pluginApp(PriceDetectService::class);
        $priceIds = $priceDetectService->getPriceIdsForCustomer();
    
        /**
         * @var SalesPriceFilter $priceFilter
         */
        $priceFilter = pluginApp(SalesPriceFilter::class);
        $priceFilter->hasAtLeastOnePrice($priceIds);
        
        $filters[] = $priceFilter;

        return $filters;
    }
}