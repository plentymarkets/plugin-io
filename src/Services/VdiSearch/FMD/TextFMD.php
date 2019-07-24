<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;


class TextFMD extends FieldMapDefinition
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
            VariationBaseAttribute::TEXTS
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'texts';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {

        $languages = $decoratedVariation->base->with()->texts;
        $data = [];

        foreach ($languages AS $text) {
            $text = self::map($text, 'name', 'name1');
            $text = self::map($text, 'previewDescription', 'shortDescription');
            $text = self::map($text, 'metaKeywords', 'keywords');
            $data[] = $text->toArray();
        }

        $content['texts'] = $data;

        return $content;
    }
}
