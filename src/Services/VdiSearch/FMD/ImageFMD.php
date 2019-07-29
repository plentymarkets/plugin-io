<?php

namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

class ImageFMD extends FieldMapDefinition
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
            VariationBaseAttribute::IMAGE
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'images';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $itemImages = $decoratedVariation->base->with()->images;

        $variationImages = $decoratedVariation->images;

        $allImages = $itemImageList = $variationImageList = [];
        foreach ($itemImages AS $itemImage) {

            $availabilities = [];

            foreach ($itemImage['availabilities'] as $availability) {

                $availabilities[$availability['type']][] = $availability['value'];
            }

            $itemImage['availabilities'] = $availabilities;

            $allImages[] = $itemImage;

            // maybe the variationImages has to be converted to a proper array.
            if (in_array($itemImage['id'], $variationImages)) {
                $variationImageList[] = $itemImage;
                continue;
            }

            $itemImageList[] = $itemImage;
        }

        $content['images']['all'] = $allImages;
        $content['images']['item'] = $itemImageList;
        $content['images']['variation'] = $variationImageList;

        return $content;
    }
}
