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
                $variationResult = [
                    'variationId'       => $baseResult['id'],
                    'unitId'            => $extensionDocument['data']['unit']['id'],
                    'unitName'          => $extensionDocument['data']['unit']['names']['name'],
                    'unitCombinationId' => $extensionDocument['data']['variation']['unitCombinationId'],
                    'url'               => $extensionDocument['data']['texts']['urlPath'].'_'.$extensionDocument['data']['item']['id'].'_'.$extensionDocument['data']['variation']['id'],
                    'isSalable'         => $extensionDocument['data']['filter']['isSalable'],
                    'attributes'        => [],
                ];
    
                if(count($extensionDocument['data']['attributes']))
                {
                    foreach($extensionDocument['data']['attributes'] as $attribute)
                    {
                        $variationResult['attributes'][] = [
                            'attributeId'      => $attribute['attributeId'],
                            'attributeValueId' => $attribute['value']['id']
                        ];
            
                        if(!$this->in_array_r($attribute['attributeId'], $newResult['attributes'], 'attributeId'))
                        {
                            $newResult['attributes'][] = [
                                'attributeId' => $attribute['attributeId'],
                                'type'     => $attribute['attribute']['typeOfSelectionInOnlineStore'],
                                'name'     => $attribute['attribute']['names']['name'],
                                'position' => $attribute['attribute']['position'],
                                'values'   => [[
                                    'attributeValueId' => $attribute['value']['id'],
                                    'position' => $attribute['value']['position'],
                                    'imageUrl' => $attribute['value']['image'],
                                    'name'     => $attribute['value']['names']['name']
                                ]]
                            ];
                        }
                        else
                        {
                            foreach($newResult['attributes'] as $newKey => $newAttribute)
                            {
                                if($newAttribute['attributeId'] == $attribute['attributeId'] && !$this->in_array_r($attribute['value']['id'], $newAttribute['values'], 'attributeValueId'))
                                {
                                    $newResult['attributes'][$newKey]['values'][] = [
                                        'attributeValueId' => $attribute['value']['id'],
                                        'position' => $attribute['value']['position'],
                                        'imageUrl' => $attribute['value']['image'],
                                        'name'     => $attribute['value']['names']['name']
                                    ];
                                }
                            }
                        }
    
                    }
                    
                }
    
                $newResult['variations'][] = $variationResult;
            }
            
            if(count($newResult['attributes']))
            {
                uasort($newResult['attributes'], function($attribute1, $attribute2) {
                    return $attribute1['position'] <=> $attribute2['position'];
                });
                
                foreach($newResult['attributes'] as $attributeKey => $attribute)
                {
                    uasort($newResult['attributes'][$attributeKey]['values'], function($attributeValue1, $attributeValue2) {
                        return $attributeValue1['position'] <=> $attributeValue2['position'];
                    });
                }
            }
        }
        
        return $newResult;
    }
    
    private function in_array_r($needle, $haystack, $key, $strict = false)
    {
        foreach ($haystack as $item)
        {
            if (($strict ? $item[$key] === $needle : $item[$key] == $needle) || (is_array($item[$key]) && $this->in_array_r($needle, $item, $key, $strict)))
            {
                return true;
            }
        }
        
        return false;
    }
}