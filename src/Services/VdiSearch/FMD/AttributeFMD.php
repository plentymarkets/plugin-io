<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationAttributeValueAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;


class AttributeFMD extends FieldMapDefinition
{
    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationAttributeValueAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationAttributeValueAttribute::VALUE,
            VariationAttributeValueAttribute::ATTRIBUTE
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'attributes';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $attributeValues = $decoratedVariation->attributeValues;

        $data = [];
        foreach ($attributeValues as $attributeValue) {
            $attribute = $attributeValue->with()->attribute;
            $attribute = $this::map($attribute, 'attributeNames', 'names');
            $attribute['names'] = $attribute['names']->first()->toArray();
            $attribute['names'] = $this::map($attribute['names'], 'attribute_id', 'attributeId');


            $value = $attributeValue->with()->attributeValue;
            $value = $this::map($value, 'valueNames', 'names');
            $value['names'] = $value['names']->first()->toArray();
            $value['names'] = $this::map($value['names'], 'value_id', 'valueId');

            $entry = [
                'attributeId' => $attribute->id,
                'valueId' => $value->id,
                'isLinkableToImage' => $attribute->isLinkableToImage,
                'attribute' => $attribute->toArray(),
                'value' => $value->toArray()
                'attributeValueSetId' => $attributeValue->attributeValueSetId
            ];

            $data[] = $entry;
        }

        $content['attributes'] = $data;


        return $content;
    }
}
