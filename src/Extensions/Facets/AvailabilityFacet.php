<?php

namespace IO\Extensions\Facets;

use IO\Services\ItemLoader\Contracts\FacetExtension;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Item\Search\Aggregations\AvailabilityAggregation;
use Plenty\Modules\Item\Search\Aggregations\AvailabilityAggregationProcessor;
use Plenty\Modules\Item\Search\Filter\VariationBaseFilter;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Search\Aggregation\AggregationInterface;

/**
 * Class AvailabilityFacet
 * @package IO\Extensions\Facets
 */
class AvailabilityFacet implements FacetExtension
{
    private $templateConfigService = null;
    private $availableRange = [];
    private $notAvailableRange = [];
    private $currentAvailabilityFilterValue = null;
    
    /**
     * AvailabilityFacet constructor.
     * @param TemplateConfigService $templateConfigService
     */
    public function __construct(TemplateConfigService $templateConfigService)
    {
        $this->templateConfigService = $templateConfigService;
    }
    
    /**
     * @return AggregationInterface|AvailabilityAggregation
     * @throws \ErrorException
     */
    public function getAggregation()
    {
        /** @var AvailabilityAggregationProcessor $availabilityProcessor */
        $availabilityProcessor = pluginApp(AvailabilityAggregationProcessor::class);
        /** @var AvailabilityAggregation $availabilityAggregation */
        $availabilityAggregation = pluginApp(AvailabilityAggregation::class, [$availabilityProcessor]);
        
        return $availabilityAggregation;
    }
    
    /**
     * @param array $result
     * @return array
     */
    public function mergeIntoFacetsList($result): array
    {
        $availableCount = 0;
        $notAvailableCount = 0;
        
        foreach($result['availabilities'] as $availabilityId => $availabilityCount)
        {
           if(in_array($availabilityId, $this->getAvailableRange()))
           {
               $availableCount += $availabilityCount;
           }
           elseif(in_array($availabilityId, $this->getNotAvailableRange()))
           {
               $notAvailableCount += $availabilityCount;
           }
        }
        
        $availabilityFacet = [
            'id' => 'avalability',
            'name' => $this->templateConfigService->getTranslation('Template.itemAvailability'),
            'position' => $this->templateConfigService->get('filter.availabilityFilterPosition', 0),
            'values' => []
        ];
        
        if(is_null($this->currentAvailabilityFilterValue) || (int)$this->currentAvailabilityFilterValue == 1)
        {
            $availabilityFacet['values'][] = [
                'id' => 'availability-1',
                'name' => $this->templateConfigService->getTranslation('Template.itemAvailable'),
                'position' => 0,
                'count' => $availableCount,
            ];
        }
    
        if(is_null($this->currentAvailabilityFilterValue) || (int)$this->currentAvailabilityFilterValue == 2)
        {
            $availabilityFacet['values'][] = [
                'id' => 'availability-2',
                'name' => $this->templateConfigService->getTranslation('Template.itemNotAvailable'),
                'position' => 1,
                'count' => $notAvailableCount,
            ];
        }
    
        $result['facets'][] = $availabilityFacet;
        
        return $result;
    }
    
    /**
     * @param $filtersList
     * @return mixed|null|VariationBaseFilter
     * @throws \ErrorException
     */
    public function extractFilterParams($filtersList)
    {
        foreach ($filtersList as $filter)
        {
            if (strpos($filter, 'availability-') === 0)
            {
            
                $this->currentAvailabilityFilterValue = (INT)substr($filter, 13);
                
                /** @var VariationBaseFilter $variationFilter */
                $variationFilter = pluginApp(VariationBaseFilter::class);
                
                if($this->currentAvailabilityFilterValue == 1)
                {
                    $variationFilter->hasAtLeastOneAvailability($this->getAvailableRange());
                }
                elseif($this->currentAvailabilityFilterValue == 2)
                {
                    $variationFilter->hasAtLeastOneAvailability($this->getNotAvailableRange());
                }
                
                return $variationFilter;
            }
        }
        
        return null;
    }
    
    private function getAvailableRange()
    {
        if(count($this->availableRange) <= 0)
        {
            $availableRange = $this->templateConfigService->get('filter.availableRange', [1,2,3,4,5]);
    
            if(!is_array($availableRange) && strlen($availableRange))
            {
                $this->availableRange = explode(', ', $availableRange);
            }
        }
        
        return $this->availableRange;
    }
    
    private function getNotAvailableRange()
    {
        if(count($this->notAvailableRange) <= 0)
        {
            $availableRange = $this->templateConfigService->get('filter.availableRange', [1,2,3,4,5]);
            $availableRangePossible = [1,2,3,4,5,6,7,8,9,10];
    
            if(!is_array($availableRange) && strlen($availableRange))
            {
                $availableRange = explode(', ', $availableRange);
            }
    
            foreach($availableRangePossible as $availabilityId)
            {
                if(in_array((int)$availabilityId, $availableRange))
                {
                    unset($availableRangePossible[(int)$availabilityId-1]);
                }
            }
            
            $this->notAvailableRange = $availableRangePossible;
        }
        
        return $this->notAvailableRange;
    }
}