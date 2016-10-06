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

class GetBasePrice extends AbstractFunction
{
    private Application $app;
    private ItemDataLayerRepositoryContract $itemRepository;
    private ItemColumnBuilder $columnBuilder;
    private ItemFilterBuilder $filterBuilder;
    private ItemParamsBuilder $paramsBuilder;

    public function __construct(
        Application $app,
        ItemDataLayerRepositoryContract $itemRepository,
        ItemColumnBuilder $columnBuilder,
        ItemFilterBuilder $filterBuilder,
        ItemParamsBuilder $paramsBuilder
    )
    {
        parent::__construct();
        $this->app = $app;
        $this->itemRepository = $itemRepository;
        $this->columnBuilder = $columnBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->paramsBuilder = $paramsBuilder;
    }

    public function getFunctions():array<string, string>
    {
        return [
            "getBasePrice" => "getBasePrice",
            "getBasePriceList" => "getBasePriceList"
        ];
    }

    public function getBasePrice( int $variationId ):array<string, mixed>
    {
        return $this->getBasePriceList( [$variationId] )[$variationId];
    }

    public function getBasePriceList( array<int> $variationIds ):array<int, array<string, mixed>>
    {
        $columns = $this->columnBuilder
            ->withVariationBase([
                VariationBaseFields::ID,
                VariationBaseFields::CONTENT,
                VariationBaseFields::UNIT_ID
            ])
            ->withVariationRetailPrice([
                VariationRetailPriceFields::PRICE
            ])
            ->build();

        $filter = $this->filterBuilder
            ->variationHasId( $variationIds )
            ->build();

        // set params
        // TODO: make current language global
        $params = $this->paramsBuilder
            ->withParam( ItemColumnsParams::LANGUAGE, Language::DE )
            ->withParam( ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId() )
            ->build();

        $variations = $this->itemRepository->search(
            $columns,
            $filter,
            $params
        );

        $basePriceList = array();
        foreach( $variations as $variation )
        {
            $price = $variation->variationRetailPrice->price;
            $lot = (int) $variation->variationBase->content;
            $unit = $variation->variationBase->unitId;

            $bp_lot = 1;
            $bp_unit = $unit;
            $factor = 1.0;

            if( (float) $lot <= 0 )
            {
                $lot = 1;
            }

            if( $unit == 'LTR' || $unit == 'KGM' )
        		{
        			$bp_lot = 1;
        		}
        		elseif( $unit == 'GRM' || $unit == 'MLT' )
        		{
        			if( $lot <= 250 )
        			{
        				$bp_lot = 100;
        			}
        			else
        			{
        				$factor = 1000.0;
        				$bp_lot = 1;
        				$bp_unit = $unit == 'GRM' ? 'KGM' : 'LTR';
        			}
          		}
          		elseif( $unit == 'CMK' )
          		{
          			if( $lot <= 2500 )
          			{
          				$bp_lot = 10000;
          			}
          			else
          			{
          				$factor = 10000.0;
          				$bp_lot = 1;
          				$bp_unit = 'MTK';
          			}
          		}
          		else
                  {
          			$bp_lot = 1;
          		}

            $basePriceList[$variation->variationBase->id] = [
                "lot" => $bp_lot,
                "price" => $price * $factor * ($bp_lot/$lot),
                "unit" => $bp_unit
            ];
        }

        return $basePriceList;
    }
}
