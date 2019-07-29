<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class CrossSellingFMD extends FieldMapDefinition
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
            VariationBaseAttribute::CROSS_SELLING
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'crossSelling';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $crossSellings = $decoratedVariation->base->with()->crossSelling;

        if(!is_array($crossSellings))
        {

            $content['crossSelling'] = [];

            return $content;
        }

        $data = [];
        foreach ($crossSellings AS $crossSelling) {

            $entry['itemId'] = $crossSelling['itemId'];
            $entry['isDynamic'] = (bool)$crossSelling['isDynamic'];
            $entry['relationship'] = $crossSelling['relationship'];

            $data[] = $entry;
        }

        $content['crossSelling'] = $data;

        return $content;
    }
}
