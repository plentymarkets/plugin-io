<?php

namespace IO\Helper;

class DataFilter
{
    public function getFilteredData( $data, $resultFields, $removeEmpty = false )
    {
        $data = ArrayHelper::toArray( $data );
        $result = $data;

        if ( count($resultFields) && !in_array("*", $resultFields) )
        {
            if ( !ArrayHelper::isAssoc($data) )
            {
                $result = [];
                foreach( $data as $dataEntry )
                {
                    $result[] = $this->getFilteredData( $dataEntry, $resultFields );
                }
            }
            else
            {
                $result = $this->filterData(
                    $data,
                    $this->getPrefixedResultFields( $resultFields )
                );

                $prefixes = $this->getPrefixes( $resultFields );
                foreach( $prefixes as $prefix )
                {
                    $prefixedFields = $this->getPrefixedResultFields( $resultFields, $prefix );
                    if ( count( $prefixedFields ) )
                    {
                        if ( ArrayHelper::isAssoc($data[$prefix] ) )
                        {
                            $result[$prefix] = $this->filterData(
                                $data[$prefix],
                                $prefixedFields
                            );
                        }
                        else
                        {
                            $result[$prefix] = $this->filterDataList(
                                $data[$prefix],
                                $prefixedFields
                            );
                        }
                    }
                }
            }
        }

        if ( $removeEmpty )
        {
            return $this->removeEmpty( $result );
        }

        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    private function removeEmpty( $data )
    {
        if ( !is_object( $data ) && !is_array( $data ) )
        {
            return $data;
        }

        if ( is_object( $data ) )
        {
            $data = ArrayHelper::toArray($data);
        }

        $result = [];
        foreach( $data as $key => $value )
        {
            if ( !empty($value) )
            {
                if ( is_array($value) )
                {
                    $result[$key] = $this->removeEmpty($value);
                }
                else
                {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    private function filterData( $data, $resultFields )
    {
        if ( in_array( "*", $resultFields ) )
        {
            return $data;
        }

        $result = [];
        foreach( $data as $key => $value )
        {
            if ( in_array( $key, $resultFields ) )
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function filterDataList( $dataList, $resultFields )
    {
        if ( in_array( "*", $resultFields ) )
        {
            return $dataList;
        }

        $result = [];
        foreach( $dataList as $data )
        {
            $result[] = $this->filterData( $data, $resultFields );
        }
        return $result;
    }

    private function getPrefixes( $resultFields )
    {
        return array_reduce(
            $resultFields,
            function( $prefixes, $resultField )
            {
                if ( strpos($resultField, ".") !== false )
                {
                    $prefix = substr( $resultField, 0, strpos($resultField, ".") );
                    if ( !in_array( $prefix, $prefixes ) )
                    {
                        $prefixes[] = $prefix;
                    }
                }

                return $prefixes;
            },
            []
        );
    }

    private function getPrefixedResultFields( $resultFields, $prefix = null )
    {
        return array_reduce(
            $resultFields,
            function( $result, $resultField ) use ( $prefix )
            {
                if ( is_null( $prefix ) && !strpos( $resultField, "." ) )
                {
                    $result[] = $resultField;
                }
                else if ( !is_null( $prefix ) && strpos( $resultField, $prefix . "." ) === 0 )
                {
                    $result[] = substr( $resultField, strlen($prefix) + 1 );
                }

                return $result;
            },
            []
        );
    }
}