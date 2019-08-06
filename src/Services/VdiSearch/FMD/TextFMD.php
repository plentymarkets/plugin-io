<?php


namespace IO\Services\VdiSearch\FMD;

use IO\Services\SessionStorageService;
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
       /**
         * @var SessionStorageService $sessionStorageService
         */
        $sessionStorageService = pluginApp(SessionStorageService::class);
        $lang = $sessionStorageService->getLang();
        $text = $decoratedVariation->base->with()->texts[$lang];
        if($text !== null)
        {
            $text = self::map($text, 'name', 'name1');
            $text = self::map($text, 'previewDescription', 'shortDescription');
            $text = self::map($text, 'metaKeywords', 'keywords');
        }
        else
        {
            $text = [];
        }

        $content['texts'] = $text;

        return $content;
    }
}
