<?php

namespace IO\Services\ItemSearch\Mutators;

use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BaseMutator;
use Plenty\Modules\Item\Property\Contracts\PropertySelectionRepositoryContract;

/**
 * Class OrderPropertySelectionValueMutator
 * @package IO\Services\ItemSearch\Mutators
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\ItemSearch\Mutators\OrderPropertySelectionValueMutator
 */
class OrderPropertySelectionValueMutator extends BaseMutator
{
    /**
     * @param array $data
     * @return array
     * @throws \Throwable
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\ItemSearch\Mutators\OrderPropertySelectionValueMutator::mutate()
     */
    public function mutate(array $data)
    {
        $lang = Utils::getLang();
        
        /** @var PropertySelectionRepositoryContract $propertySelectionRepo */
        $propertySelectionRepo = pluginApp(PropertySelectionRepositoryContract::class);
        
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        
        foreach($data['properties'] as $key => $property)
        {
            if($property['property']['valueType'] == 'selection' && $property['property']['isOderProperty'] && !isset($property['property']['selectionValues']))
            {
                $selectionValues = $authHelper->processUnguarded(function() use ($propertySelectionRepo, $property, $lang) {
                    return $propertySelectionRepo->findByProperty($property['property']['id'], $lang);
                });
    
                $newSelectionValues = [];
                if(count($selectionValues))
                {
                    foreach($selectionValues as $value)
                    {
                        $newSelectionValues[$value->id] = [
                            'name'        => $value->name,
                            'description' => $value->description
                        ];
                    }
                }
                $data['properties'][$key]['property']['selectionValues'] = $newSelectionValues;
            }
        }
        
        return $data;
    }
}
