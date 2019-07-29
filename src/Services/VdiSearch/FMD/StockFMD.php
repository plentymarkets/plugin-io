<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class StockFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationBaseAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationBaseAttribute::STOCK
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'stock';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {

        $stocks = $decoratedVariation->base->with()->stock;

        if(!is_array($stocks))
        {
            $content['stock']['net'] = 0;
            return $content;
        }

        $netSum = 0;
        foreach ($stocks AS $stock) {
            if ($stock->storehouse_type != 2) { // ?!
                continue;
            }

            $netSum += $stock->stockNet;
        }

        $content['stock']['net'] = $netSum;

        return $content;
    }
}
