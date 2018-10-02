<?php

namespace IO\Helper;


class DataFilter
{
    protected $fields = [];
    protected $prefixes = [];
    protected $listPrefixes = [];

    protected function getFilteredData( $data, $resultFields )
    {
        if ( !count( $resultFields ) || in_array( "*", $resultFields ) )
        {
            return $data;
        }

        $result = $this->filterData(
            $data,
            $this->getPrefixedResultFields( $resultFields )
        );

        foreach( $this->prefixes as $prefix )
        {
            $prefixedFields = $this->getPrefixedResultFields( $resultFields, $prefix );
            if ( count( $prefixedFields ) )
            {
                $result[$prefix] = $this->filterData(
                    $data[$prefix],
                    $prefixedFields,
                    $prefix
                );
            }
        }

        foreach( $this->listPrefixes as $listPrefix )
        {
            $prefixedFields = $this->getPrefixedResultFields( $resultFields, $listPrefix );
            if ( count( $prefixedFields ) )
            {
                $result[$listPrefix] = $this->filterDataList(
                    $data[$listPrefix],
                    $prefixedFields,
                    $listPrefix
                );
            }
        }

        return $result;
    }

    private function filterData( $data, $resultFields, $prefix = null )
    {
        if ( in_array( "*", $resultFields ) )
        {
            return $data;
        }

        $result = [];
        $availableFields = $this->getPrefixedResultFields( $this->fields, $prefix );
        foreach( $availableFields as $field )
        {
            if ( in_array( $field, $resultFields ) )
            {
                $result[$field] = $data[$field];
            }
        }

        return $result;
    }

    private function filterDataList( $dataList, $resultFields, $prefix = null )
    {
        if ( in_array( "*", $resultFields ) )
        {
            return $dataList;
        }

        $result = [];
        foreach( $dataList as $data )
        {
            $result[] = $this->filterData( $data, $resultFields, $prefix );
        }
        return $result;
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