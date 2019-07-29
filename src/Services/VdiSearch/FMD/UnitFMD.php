<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationUnitAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class UnitFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationUnitAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationUnitAttribute::UNIT
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'unit';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $variationUnit = $decoratedVariation->unit;

        $data = $variationUnit->with()->unit;
        $data['content'] = $variationUnit->content;
        $content['unit'] = $data;

        return $content;
    }
}
