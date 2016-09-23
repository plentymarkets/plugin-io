<?hh //strict

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

class Component extends AbstractFunction
{
    private array<string> $components = [];

    public function getFunctions():array<string, string>
    {
        return [
            "component" => "component",
            "get_components" => "getComponents"
        ];
    }

    public function component( string $path ):void
    {
        if( !in_array( $path, $this->components ) )
        {
            array_push( $this->components, $path );
        }
    }

    public function getComponents():array<string>
    {
        return $this->components;
    }
}
