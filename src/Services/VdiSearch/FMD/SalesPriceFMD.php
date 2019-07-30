<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSalesPriceAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class SalesPriceFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationSalesPriceAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationSalesPriceAttribute::SALES_PRICE
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'salesPrices';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $variationSalesPrices = $decoratedVariation->salesPrices;

        $data = [];
        foreach ($variationSalesPrices AS $variationSalesPrice){
            $entry = $variationSalesPrice->with()->salesPrice;

            $entry = $entry->toArray();
            if(isset($entry['names']))
            {
                $entry['names'] = array_map(function($name) use ($variationSalesPrice) {

                    $name['priceId'] = (string)$variationSalesPrice->salesPriceId;

                    return $name;
                }, $entry['names']);
            }

            $entry['price'] =  sprintf('%.2F',  $variationSalesPrice->price);
            $entry['settings'] = [];
            foreach ([
                         'countries' => 'countryId',
                         'customerClasses' => 'customerClassId',
                         'referrers' => 'referrerId', 'clients' => 'plentyId', 'currencies' => 'currency'
                     ] as $key => $valueKey) {

                $setting = isset($entry[$key]) ? $entry[$key] : [];

                $setting = array_map(function ($settingValue) use ($valueKey, $key) {
                    $value = $settingValue[$valueKey];
                    if (in_array($key, ['countries', 'clients', 'currencies', 'customerClasses'])) {
                        $value = (string)$value;
                    }

                    if (in_array($key, ['referrers'])) {
                        $value = (double)$value;
                    }

                    return $value;
                }, $setting);

                sort($setting);

                $entry['settings'][$key] = $setting;

                unset($entry[$key]);
            }

            $data[] = $entry;
        }

        $content['salesPrices'] = $data;

        return $content;
    }
}
