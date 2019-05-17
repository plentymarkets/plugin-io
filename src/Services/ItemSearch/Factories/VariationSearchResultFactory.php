<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\ItemSearch\Factories\Faker\AttributeFaker;
use IO\Services\ItemSearch\Factories\Faker\BarcodeFaker;
use IO\Services\ItemSearch\Factories\Faker\CategoryFaker;
use IO\Services\ItemSearch\Factories\Faker\CrossSellingFaker;
use IO\Services\ItemSearch\Factories\Faker\DefaultCategoryFaker;
use IO\Services\ItemSearch\Factories\Faker\FacetFaker;
use IO\Services\ItemSearch\Factories\Faker\FilterFaker;
use IO\Services\ItemSearch\Factories\Faker\IdsFaker;
use IO\Services\ItemSearch\Factories\Faker\ImageFaker;
use IO\Services\ItemSearch\Factories\Faker\ItemFaker;
use IO\Services\ItemSearch\Factories\Faker\PriceFaker;
use IO\Services\ItemSearch\Factories\Faker\PropertyFaker;
use IO\Services\ItemSearch\Factories\Faker\SalesPriceFaker;
use IO\Services\ItemSearch\Factories\Faker\SkuFaker;
use IO\Services\ItemSearch\Factories\Faker\SortingFaker;
use IO\Services\ItemSearch\Factories\Faker\StockFaker;
use IO\Services\ItemSearch\Factories\Faker\TagFaker;
use IO\Services\ItemSearch\Factories\Faker\TextFaker;
use IO\Services\ItemSearch\Factories\Faker\UnitFaker;
use IO\Services\ItemSearch\Factories\Faker\VariationFaker;
use IO\Services\ItemSearch\Factories\Faker\VariationPropertyFaker;
use IO\Services\ItemSearch\Helper\LoadResultFields;

class VariationSearchResultFactory
{
    const FAKER_MAP = [
        "attributes"            => AttributeFaker::class,
        "barcodes"              => BarcodeFaker::class,
        "categories"            => CategoryFaker::class,
        "crossSelling"          => CrossSellingFaker::class,
        "defaultCategories"     => DefaultCategoryFaker::class,
        "facets"                => FacetFaker::class,
        "filter"                => FilterFaker::class,
        "ids"                   => IdsFaker::class,
        "images"                => ImageFaker::class,
        "item"                  => ItemFaker::class,
        "properties"            => PropertyFaker::class,
        "salesPrices"           => SalesPriceFaker::class,
        "skus"                  => SkuFaker::class,
        "sorting"               => SortingFaker::class,
        "stock"                 => StockFaker::class,
        "tags"                  => TagFaker::class,
        "texts"                 => TextFaker::class,
        "unit"                  => UnitFaker::class,
        "variation"             => VariationFaker::class,
        "variationProperties"   => VariationPropertyFaker::class
    ];

    const MANDATORY_FAKER_MAP = [
        "prices"                => PriceFaker::class
    ];

    use LoadResultFields;

    public function fillSearchResults( $searchResult, $resultFieldsTemplate, $numberOfEntries = 1 )
    {
        $resultFields   = $this->loadResultFields($resultFieldsTemplate);
        $entries        = [];
        foreach($resultFields as $resultField)
        {
            if (strpos($resultField,"."))
            {
                $entry = substr($resultField, 0, strpos($resultField,"."));
            }
            else
            {
                $entry = $resultField;
            }

            if (!in_array($entry, $entries))
            {
                $entries[] = $entry;
            }
        }

        if(is_null($searchResult))
        {
            $searchResult = [
                'took'      => rand(1, 100),
                'total'     => $numberOfEntries,
                'maxScore'  => 0,
                'documents' => [],
                'success'   => true,
                'error'     => null
            ];
        }

        for($i = 0; $i < $numberOfEntries; $i++)
        {
            if(empty($searchResult['documents'][$i]))
            {
                $searchResult['documents'][$i] = [
                    'score' => 0,
                    'id'    => rand(100, 100000),
                    'data'  => []
                ];
            }
        }

        foreach($searchResult['documents'] as $i => $document)
        {
            $searchResult['documents'][$i] = $this->fillDocument($document, $entries);
        }

        return $searchResult;
    }

    private function fillDocument($document, $entries)
    {
        AbstractFaker::resetGlobals();
        foreach($entries as $entry)
        {
            $fakerClass = self::FAKER_MAP[$entry];
            $document['data'][$entry] = $this->runFaker($fakerClass, $document['data'][$entry] ?? []);
        }

        foreach(self::MANDATORY_FAKER_MAP as $entry => $fakerClass)
        {
            $document['data'][$entry] = $this->runFaker($fakerClass, $document['data'][$entry] ?? []);
        }

        return $document;
    }

    private function runFaker($fakerClass, $value)
    {
        $result = [];
        if(strlen($fakerClass))
        {
            try
            {
                $faker = pluginApp($fakerClass);
                if($faker instanceof AbstractFaker)
                {
                    if ($faker->isList)
                    {
                        $count = max(rand(...$faker->range), count($value));
                        for($i = 0; $i < $count; $i++)
                        {
                            $listValue = $value[$i];
                            $result[$i] = $faker->fill($listValue);
                        }
                    }
                    else
                    {
                        $result = $faker->fill($value);
                    }
                }
            }
            catch(\Exception $e)
            {

            }
        }

        return $result;
    }
}