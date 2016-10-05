<?php //strict

namespace LayoutCore\Extensions\Functions;

use Plenty\Plugin\Application;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use LayoutCore\Builder\Item\ItemColumnBuilder;
use LayoutCore\Builder\Item\ItemFilterBuilder;
use LayoutCore\Builder\Item\ItemParamsBuilder;
use LayoutCore\Builder\Item\Fields\VariationBaseFields;
use LayoutCore\Builder\Item\Fields\VariationRetailPriceFields;
use LayoutCore\Builder\Item\Params\ItemColumnsParams;
use LayoutCore\Constants\Language;
use LayoutCore\Extensions\AbstractFunction;

/**
 * Class Component
 * @package LayoutCore\Extensions\Functions
 */
class Component extends AbstractFunction
{
    /**
     * @var int
     */
    private $currentComponent = 0;

    /**
     * @var array
     */
    private $components = array();

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "component" => "component",
            "has_component_template" => "hasComponentTemplate",
            "get_component_template" => "getComponentTemplate"
        ];
    }

    /**
     * Push the component to the component stack
     * @param string $path
     */
    public function component( string $path )
    {
        if( !in_array( $path, $this->components ) )
        {
            array_push( $this->components, $path );
        }
    }

    /**
     * Check whether a component template exists
     * @return bool
     */
    public function hasComponentTemplate():bool
    {
        return $this->currentComponent < count( $this->components );
    }

    /**
     * Get the component from the component stack
     * @return string
     */
    public function getComponentTemplate():string
    {
        $template = $this->components[$this->currentComponent];
        $this->currentComponent++;
        return $template;
    }

}
