<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\DocumentService\Models\Variation\Barcode as DocumentBarcode;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBarcodeAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;


class BarcodeFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationBarcodeAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationBarcodeAttribute::BARCODE
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'barcodes';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        /** @var DocumentBarcode[] $variationBarcodes */
        $variationBarcodes = $decoratedVariation->barcodes;

        $data = [];
        foreach ($variationBarcodes AS $variationBarcode) {
            $barcode = $variationBarcode->with()->barcode->toArray();
            $entry = array_merge($variationBarcode->toArray(), $barcode);
            $data[] = $entry;
        }

        $content['barcodes'] = $data;

        return $content;
    }
}
