<?php


namespace IO\Services\VdiSearch\FMD;


use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;

/**
 * Class FieldMapDefinition
 * @package Plenty\Modules\Pim\MappingLayer\Models
 */
abstract class FieldMapDefinition
{
    /**
     * @return string
     */
    abstract public function getAttribute(): string;

    /**
     * @return null|array
     */
    public function getLazyLoadable()
    {
        return null;
    }

    protected static function map($object, $old, $new)
    {
        if(!is_array($object))
        {

            $object->{$new} = isset($object->{$old}) ? $object->{$old} : null;
            unset($object->{$old});
        }else
        {
            $object[$new] = isset($object[$old]) ? $object[$old] : null;
            unset($object[$old]);
        }

        return $object;
    }

    /**
     * @return string
     */
    abstract public function getOldField(): string;

    /**
     * @param Variation $variation
     * @param array $content
     * @param array $sourceFields
     * @return mixed
     */
    abstract public function fill(Variation $variation, array $content, array $sourceFields);
}
