<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;
use Plenty\Modules\Tag\Models\Tag;

class TagsFMD extends FieldMapDefinition
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
            VariationBaseAttribute::TAG
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'tags';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {

        $tagRelationships = $decoratedVariation->base->with()->tags;

        $content['tags'] = [];

        return $content;
        // TODO!;

        $data = [];

        foreach ($tagRelationships AS $relationship) {

            $entry['id'] = $relationship->tagId; // maybe a simple to array is enough and there is no need to loop.

            /** @var Tag $tag */
            $tag = $relationship->tag();
            foreach ($tag->names AS $name) {
                $entry['names'][] = $name->toArray();
            }

            $data[] = $entry;
        }

        $content['tags'] = $data;

        return $content;
    }
}
