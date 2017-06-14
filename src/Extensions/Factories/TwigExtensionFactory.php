<?php

namespace IO\Extensions\Factories;

/**
 * Created by ptopczewski, 07.06.17 15:54
 * Class TwigExtensionFactory
 * @package IO\Extensions\Factories
 */
class TwigExtensionFactory
{
    /**
     * @var array
     */
    private $functionsMap = [];

    /**
     * @var array
     */
    private $filtersMap = [];

    /**
     * @param string $functionClass
     * @param array $functionsList
     */
    public function registerFunction($functionClass, $functionsList)
    {
        $this->functionsMap[$functionClass] = $functionsList;
    }

    /**
     * @param string $filterClass
     * @param array $filtersList
     */
    public function registerFilter($filterClass, $filtersList)
    {
        $this->filtersMap[$filterClass] = $filtersList;
    }

    /**
     * @return array
     */
    public function getFunctionsMap(): array
    {
        return $this->functionsMap;
    }

    /**
     * @return array
     */
    public function getFiltersMap(): array
    {
        return $this->filtersMap;
    }
}