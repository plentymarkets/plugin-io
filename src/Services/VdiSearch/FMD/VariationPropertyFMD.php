<?php


namespace IO\Services\VdiSearch\FMD;

use Plenty\Modules\Core\Data\Factories\LazyLoaderFactory;
use Plenty\Modules\Pim\VariationDataInterface\Model\VariationDataInterfaceContext;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;
use Plenty\Modules\Property\Models\Property;
use Plenty\Modules\Property\Models\PropertyOption;
use Plenty\Modules\Property\Models\PropertyRelation;

class VariationPropertyFMD extends FieldMapDefinition
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
            VariationBaseAttribute::CHARACTERISTIC
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'variationProperties';
    }

    /**
     * @inheritDoc
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $propertyLazyLoader = LazyLoaderFactory::getLazyLoaderFor(Property::class);

        /** @var PropertyRelation[] $propertyRelations */
        $propertyRelations = $decoratedVariation->base->with()->properties;

        $data = [];
        foreach ($propertyRelations AS $propertyRelation) {
            /** @var Property $property */
            $property = $propertyLazyLoader->getById($propertyRelation['propertyId']);

            $clients = [];
            $referrer = [];
            $options = [];
            $display = [];

//            foreach ($property['options'] as $option)
//            {
//                /** @var PropertyOption $option */
//                $type = $option['typeOptionIdentifier'];
//
//                foreach ($option['propertyOptionValues'] as $optionValue) {
//
//                    switch ($type) {
//                        case 'clients':
//                            $clients[] = (int)$optionValue['value'];
//                            break;
//                        case 'referrers':
//                            $referrer[] = $optionValue['value'];
//                            break;
//                        case 'display':
//                            $display[] = $optionValue['value'];
//                            break;
//                        default:
//                            $options[$type][] = (string)$optionValue['value'];
//                            break;
//                    }
//                }
//            }

            $propertyEntry = [];
            $propertyEntry['cast'] = $property['cast'];
            $propertyEntry['referrer'] = $referrer;
            $propertyEntry['names'] = $property['names'];
            $propertyEntry['clients'] = $clients;
            $propertyEntry['display'] = $display;
            $propertyEntry['options'] = $options;
            $propertyEntry['groups'] = $property['groups']; // ToDo: maybe the names have to be loaded
            $propertyEntry['id'] = $property['id'];
            $propertyEntry['position'] = $property['position'];

            $entry['values'] = $propertyRelation['relationValues'];
            $entry['property'] = $propertyEntry;
            $entry['id'] = $propertyRelation['id'];

            $data[] = $entry;
        }

        $content['variationProperties'] = $data;

        return $content;
    }
}
