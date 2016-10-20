<?php //strict

namespace LayoutCore\Builder\Category;

use LayoutCore\Builder\Category\CategoryParams;
use LayoutCore\Helper\AbstractFactory;

class CategoryParamsBuilder
{
    /**
     * @var CategoryParams
     */
    private $params;

    public function __construct( AbstractFactory $factory )
    {
        $this->params = $factory->make( \LayoutCore\Builder\Category\CategoryParams::class );
    }

    public function build():CategoryParams
    {
        return $this->params;
    }

    public function fromArray( array $data ):CategoryParams
    {
        if( $data["variationShowType"] != null )
        {
            $this->params->variationShowType = (int) $data["variationShowType"];
        }

        if( $data["itemsPerPage"] != null )
        {
            $this->params->itemsPerPage = (int) $data["itemsPerPage"];
        }

        if( $data["orderBy"] != null )
        {
            $this->params->orderBy = (string) $data["orderBy"];
        }

        if( $data["orderByKey"] != null )
        {
            $this->params->orderByKey = strtoupper((string) $data["orderByKey"]);
        }

        return $this->params;
    }

    public function onlyPrimaryVariations():CategoryParamsBuilder
    {
        $this->params->variationShowType = 2;
        return $this;
    }

    public function onlyChildVariations():CategoryParamsBuilder
    {
        $this->params->variationShowType = 3;
        return $this;
    }

    public function orderBy( string $orderByValue, string $orderByKey = "ASC" ):CategoryParamsBuilder
    {
        $this->params->orderBy = $orderByValue;
        $this->params->orderByKey = strtoupper($orderByKey);
        return $this;
    }

    public function itemsPerPage( int $itemsPerPage ):CategoryParamsBuilder
    {
        $this->params->itemsPerPage = $itemsPerPage;
        return $this;
    }
}
