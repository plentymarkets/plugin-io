<?php

namespace IO\Services\ItemSearch\Factories;

use IO\Services\ItemSearch\Helper\LoadResultFields;
use IO\Services\ItemSearch\Helper\VariationSearchResultAbstractFaker;
use IO\Services\ItemSearch\Helper\VariationSearchResultMap;

class VariationSearchResultFactory
{
    use LoadResultFields;

    private $resultFields = [];
    private $defaultResult = [];

    public function fillSearchResults( $result, $resultFieldsTemplate, $numberOfEntries = 1 )
    {
        $this->defaultResult = [];
        $this->resultFields = $this->loadResultFields($resultFieldsTemplate);

        foreach(VariationSearchResultMap::RESULT_FIELDS as $field => $value)
        {
            try
            {
                $faker = pluginApp($value);
                if ( $faker instanceof VariationSearchResultAbstractFaker )
                {
                    if ( $faker->isList )
                    {
                        $count = rand($faker->listRange[0] ?? 1, $faker->listRange[1] ?? 3);
                        $defaultValue = [];
                        for($i = 0; $i < $count; $i++)
                        {
                            $defaultValue[] = $faker->generate();
                        }
                    }
                    else
                    {
                        $defaultValue = $faker->generate();
                    }
                }
                else
                {
                    $defaultValue = $value;
                }
            }
            catch(\Exception $e)
            {
                $defaultValue = $value;
            }

            $this->setValue($this->defaultResult, $field, $defaultValue);
        }

        if(is_null($result))
        {
            $result = [
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
            if(empty($result['documents'][$i]))
            {
                $result['documents'][$i] = [
                    'score' => 0,
                    'id'    => rand(100, 100000),
                    'data'  => []
                ];
            }
        }

        foreach($result['documents'] as $i => $document)
        {
            $this->injectValues($result['documents'][$i], $this->defaultResult );
        }

        return $result;
    }

    private function setValue( &$object, $field, $value )
    {
        $path = explode(".", $field);
        $key = array_shift($path);

        if(count($path))
        {
            $this->setValue($object[$key], implode(".", $path), $value);
        }
        else
        {
            $object[$key] = $value;
        }
    }

    private function injectValues( &$object, $defaults )
    {
        foreach($defaults as $key => $defaultValue)
        {
            if ( is_array($defaultValue) && is_array($object[$key]) )
            {
                $this->injectValues($object[$key], $defaultValue);
            }
            else
            {
                $object[$key] = $defaultValue;
            }
        }
    }
}