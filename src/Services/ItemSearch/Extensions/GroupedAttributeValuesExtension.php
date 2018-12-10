<?php

namespace IO\Services\ItemSearch\Extensions;

use IO\Services\ItemSearch\Factories\VariationSearchFactory;
use IO\Services\SessionStorageService;

class GroupedAttributeValuesExtension implements ItemSearchExtension
{
    /**
     * @inheritdoc
     */
    public function getSearch($parentSearchBuilder)
    {
        return VariationSearchFactory::inherit(
            $parentSearchBuilder,
            [
                VariationSearchFactory::INHERIT_FILTERS,
                VariationSearchFactory::INHERIT_PAGINATION,
                VariationSearchFactory::INHERIT_COLLAPSE,
                VariationSearchFactory::INHERIT_AGGREGATIONS,
                VariationSearchFactory::INHERIT_SORTING
            ])
            ->withResultFields([
                'attributes.*'
            ])
            ->build();
    }

    /**
     * @inheritdoc
     */
    public function transformResult($baseResult, $extensionResult)
    {
        $lang = pluginApp(SessionStorageService::class)->getLang();
        foreach( $baseResult["documents"] as $i => $document )
        {
            $attributes = $extensionResult["documents"][$i]["data"]["attributes"];
            $groupedAttributes = [];
            if ( !is_null($attributes) )
            {
                foreach( $attributes as $attribute )
                {
                    if ( $attribute["attribute"]["isGroupable"] )
                    {
                        $name = "";
                        foreach( $attribute["attribute"]["names"] as $attrName )
                        {
                            if ( $attrName["lang"] === $lang )
                            {
                                $name = $attrName["name"];
                                break;
                            }
                        }

                        $value = "";
                        foreach( $attribute["value"]["names"] as $attrValue )
                        {
                            if ( $attrValue["lang"] === $lang )
                            {
                                $value = $attrValue["name"];
                                break;
                            }
                        }

                        $groupedAttributes[] = [
                            "name" => $name,
                            "value" => $value
                        ];
                    }
                }
            }
            $baseResult["documents"][$i]["data"]["groupedAttributes"] = $groupedAttributes;
        }
        return $baseResult;
    }
}