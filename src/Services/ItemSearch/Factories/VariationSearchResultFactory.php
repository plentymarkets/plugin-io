<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemSearch\Factories\Faker\AbstractFaker;
use IO\Services\ItemSearch\Factories\Faker\AttributeFaker;
use IO\Services\ItemSearch\Helper\LoadResultFields;

class VariationSearchResultFactory
{
    const FAKER_MAP = [
        "attributes" => AttributeFaker::class
    ];

    use LoadResultFields;

    public function fillSearchResults( $searchResult, $resultFieldsTemplate, $numberOfEntries = 1 )
    {
        $resultFields   = $this->loadResultFields($resultFieldsTemplate);
        $entries        = [];
        foreach($resultFields as $resultField)
        {
            $entry = substr($resultField, 0, strpos($resultField,"."));
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
        foreach($entries as $entry)
        {
            $fakerClass = self::FAKER_MAP[$entry];
            $value = $document['data'][$entry] ?? [];

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
                                $document['data'][$entry][$i] = $faker->fill($listValue);
                            }
                        }
                        else
                        {
                            $document['data'][$entry] = $faker->fill($value);
                        }
                    }
                }
                catch(\Exception $e)
                {

                }
            }
        }

        return $document;
    }
}