<?php

namespace IO\Services\ItemSearch\Extensions;


use IO\Extensions\Filters\ItemImagesFilter;
use IO\Extensions\Filters\NumberFormatFilter;

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

        if(is_array($baseResult['documents']) && count($baseResult['documents']))
        {
            /** @var NumberFormatFilter $numberFormatFilter */
            $numberFormatFilter = pluginApp(NumberFormatFilter::class);

            foreach( $baseResult['documents'] as $key => $extensionDocument )
            {
                $numberFormatDecimals = (floor($extensionDocument['data']['unit']['content']) !== $extensionDocument['data']['unit']['content'] ? -1 : 0);

                $newResult['variations'][$extensionDocument['id']] = [
                    'variationId'       => $extensionDocument['id'],
                    'unitId'            => $extensionDocument['data']['unit']['id'],
                    'unitName'          => $numberFormatFilter->formatDecimal($extensionDocument['data']['unit']['content'], $numberFormatDecimals).' '.$extensionDocument['data']['unit']['names']['name'],
                    'unitCombinationId' => $extensionDocument['data']['variation']['unitCombinationId'],
                    'url'               => $extensionDocument['data']['texts']['urlPath'].'_'.$extensionDocument['data']['item']['id'].'_'.$extensionDocument['data']['variation']['id'],
                    'isSalable'         => $extensionDocument['data']['filter']['isSalable'],
                    'attributes'        => [],
                ];
                if(is_array($extensionDocument['data']['attributes']) && count($extensionDocument['data']['attributes']))
                {
                    foreach($extensionDocument['data']['attributes'] as $attribute)
                    {
                        $newResult['variations'][$extensionDocument['id']]['attributes'][$attribute['attributeId']] = [
                            'attributeId'      => $attribute['attributeId'],
                            'attributeValueId' => $attribute['value']['id']
                        ];

                        if(!array_key_exists($attribute['attributeId'], $newResult['attributes']))
                        {
                            $newResult['attributes'][$attribute['attributeId']] = [
                                'attributeId' => $attribute['attributeId'],
                                'type'        => $attribute['attribute']['typeOfSelectionInOnlineStore'],
                                'name'        => $attribute['attribute']['names']['name'],
                                'position'    => $attribute['attribute']['position'],
                                'values'      => []
                            ];
                        }



                        $attributeImageUrl = '';
                        if(strlen($attribute['value']['image']))
                        {
                            $attributeImageUrl = '/images/produkte/grp/'.$attribute['value']['image'];
                        }
                        elseif(is_array($extensionDocument['data']['images']['variation']) && count($extensionDocument['data']['images']['variation']))
                        {
                            /** @var ItemImagesFilter $itemImageFilter */
                            $itemImageFilter = pluginApp(ItemImagesFilter::class);
                            $attributeImageUrl = $itemImageFilter->getFirstItemImageUrl($extensionDocument['data']['images'], 'urlPreview');
                        }

                        $newResult['attributes'][$attribute['attributeId']]['values'][$attribute['value']['id']] = [
                            'attributeValueId' => $attribute['value']['id'],
                            'position'         => $attribute['value']['position'],
                            'imageUrl'         => $attributeImageUrl,
                            'name'             => $attribute['value']['names']['name']
                        ];

                    }
                    $newResult['variations'][$extensionDocument['id']]['attributes'] = array_values($newResult['variations'][$extensionDocument['id']]['attributes']);

                }
            }

            if(count($newResult['attributes']))
            {
                usort($newResult['attributes'], function($attribute1, $attribute2) {
                    return $attribute1['position'] <=> $attribute2['position'];
                });

                foreach($newResult['attributes'] as $attributeKey => $attribute)
                {
                    usort($newResult['attributes'][$attributeKey]['values'], function($attributeValue1, $attributeValue2) {
                        return $attributeValue1['position'] <=> $attributeValue2['position'];
                    });
                }
            }
        }

        $newResult['variations'] = array_values($newResult['variations']);

        return $newResult;
    }
}
