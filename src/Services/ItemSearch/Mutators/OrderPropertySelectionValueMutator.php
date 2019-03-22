<?php

namespace IO\Services\ItemSearch\Mutators;

use IO\Services\SessionStorageService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Cloud\ElasticSearch\Lib\Source\Mutator\BaseMutator;
use Plenty\Modules\Item\Property\Contracts\PropertySelectionRepositoryContract;

class OrderPropertySelectionValueMutator extends BaseMutator
{
    public function mutate(array $data)
    {
        $lang = pluginApp(SessionStorageService::class)->getLang();
        
        /** @var PropertySelectionRepositoryContract $propertySelectionRepo */
        $propertySelectionRepo = pluginApp(PropertySelectionRepositoryContract::class);
        
        /** @var AuthHelper $authHelper */
        $authHelper = pluginApp(AuthHelper::class);
        
        foreach($data['properties'] as $key => $property)
        {
            if($property['property']['valueType'] == 'selection' && $property['property']['isOderProperty'])
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