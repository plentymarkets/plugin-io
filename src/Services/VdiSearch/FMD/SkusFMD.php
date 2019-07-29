<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationSkuAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class SkusFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationSkuAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'skus';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        /** @var VariationSku[] $skus */
        $skus = $decoratedVariation->skus;

        $data = [];
        foreach ($skus AS $sku) {
            $data[] = $sku->toArray();
        }

        $content['skus'] = $data;

        return $content;
    }
}
