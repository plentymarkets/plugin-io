<?php

namespace IO\Helper;

use Plenty\Modules\Pim\VariationDataInterface\Contracts\VariationDataInterfaceResultInterface;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class VDIToElasticSearchMapper
{

    public function __construct(){}

    public function map(VariationDataInterfaceResultInterface $vdiResult, $resultFields = ['*'])
    {
        $data = [];
        /**
         * @var Variation $vdiVariation
         */
        foreach($vdiResult->get() as $vdiVariation)
        {
            $item = [];
            $item['texts'] = $vdiVariation->base->with()->texts;

            foreach($vdiVariation->salesPrices as $salesPrice)
            {
                $item['prices'][] = $salesPrice->with()->salesPrice;
            }

            $data['documents'][] = $item;
        }

        return $data;
    }
}
