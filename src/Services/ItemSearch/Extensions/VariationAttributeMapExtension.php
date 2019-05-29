<?php

namespace IO\Services\ItemSearch\Extensions;

class VariationAttributeMapExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch($parentSearchBuilder)
    {
        return null;
    }
    
    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {
        $newResult = [
            'attributes' => [],
            'variations' => []
        ];
        
        if(count($baseResult['documents']))
        {
            foreach( $baseResult['documents'] as $key => $extensionDocument )
            {
                $newResult['variations'][$extensionDocument['id']] = [
                    'attributes' => [],
                    'unitId'     => $extensionDocument['data']['unit']['id'],
                    'unitName'   => $extensionDocument['data']['unit']['names']['name'],
                    'unitCombinationId' => $extensionDocument['data']['variation']['unitCombinationId'],
                    'url'        => $extensionDocument['data']['texts']['urlPath'].'_'.$extensionDocument['data']['item']['id'].'_'.$extensionDocument['data']['variation']['id']
                ];
        
                if(count($extensionDocument['data']['attributes']))
                {
                    foreach($extensionDocument['data']['attributes'] as $attribute)
                    {
                        $newResult['variations'][$extensionDocument['id']]['attributes'][$attribute['attributeId']] = $attribute['value']['id'];
        
                        if(!array_key_exists($attribute['attributeId'], $newResult['attributes']))
                        {
                            $newResult['attributes'][$attribute['attributeId']] = [
                                'type' => $attribute['attribute']['typeOfSelectionInOnlineStore'],
                                'name' => $attribute['attribute']['names']['name'],
                                'position' => $attribute['attribute']['position'],
                                'values' => []
                            ];
                        }
        
                        $newResult['attributes'][$attribute['attributeId']]['values'][$attribute['value']['id']] = [
                            'position' => $attribute['value']['position'],
                            'imageUrl' => $attribute['value']['image'],
                            'name'     => $attribute['value']['names']['name']
                        ];
                    }
                }
            }
        }
        
        return $newResult;
    }
}