<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;


class FilterFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationBaseAttribute::class;
    }

    public function getSourceFields()
    {
        return [
            'filter.*'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'filter';
    }

    /**
     * @param Variation $decoratedVariation
     * @param array $content
     * @param array $sourceFields
     * @return array
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $esSource = $decoratedVariation->getElasticSearchSource();

        $esSourceFilter = isset($esSource['data']['filter']) ? $esSource['data']['filter'] : [];

        $filter = [
            'hasActiveChildren' => false,
            'hasAnyName' => false,
            'hasAnyNameInLanguage' => false,
            'hasAttribute' => false,
            'hasBarcode' => false,
            'hasCategory' => false,
            'hasChildren' => false,
            'hasClient' => false,
            'hasDescriptionInLanguage' => false,
            'hasFacet' => false,
            'hasImage' => false,
            'hasItemImage' => false,
            'hasManufacturer' => false,
            'hasMarket' => false,
            'hasName1InLanguage' => false,
            'hasName2InLanguage' => false,
            'hasName3InLanguage' => false,
            'hasProperty' => false,
            'hasSKU' => false,
            'hasSalesPrice' => false,
            'hasSupplier' => false,
            'hasVariationImage' => false,
            'hasVariationProperties' => false,
            'isSalable' => isset($esSourceFilter['isSalable']) && $esSourceFilter['isSalable'],
            'isSalableAndActive' => isset($esSourceFilter['isSalableAndActive']) && $esSourceFilter['isSalableAndActive'],
        ];

        foreach ($filter as $key => $value) {
            if (strpos($key, 'has') === false) {
                continue;
            }

            $hasKey = strtolower(str_replace('has', '', $key));

            $filter[$key] = isset($esSourceFilter['has']) && in_array($hasKey, $esSourceFilter['has']);
        }


        $filter['barcodes'] = isset($esSourceFilter['barcodes']) ? $esSourceFilter['barcodes'] : [];
        $filter['hasName1InLanguage'] = isset($esSourceFilter['hasName1InLanguage']) ? $esSourceFilter['hasName1InLanguage'] : [];
        $filter['hasName2InLanguage'] = isset($esSourceFilter['hasName2InLanguage']) ? $esSourceFilter['hasName2InLanguage'] : [];
        $filter['hasName3InLanguage'] = isset($esSourceFilter['hasName3InLanguage']) ? $esSourceFilter['hasName3InLanguage'] : [];
        $filter['hasAnyNameInLanguage'] = isset($esSourceFilter['hasAnyNameInLanguage']) ? $esSourceFilter['hasAnyNameInLanguage'] : [];

        $content['filter'] = $filter;

        return $content;
    }
}
