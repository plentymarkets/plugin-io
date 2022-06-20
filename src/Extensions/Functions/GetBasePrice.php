<?php //strict

namespace IO\Extensions\Functions;

use IO\Helper\Utils;
use Plenty\Plugin\Application;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use IO\Builder\Item\ItemColumnBuilder;
use IO\Builder\Item\ItemFilterBuilder;
use IO\Builder\Item\ItemParamsBuilder;
use IO\Builder\Item\Fields\VariationBaseFields;
use IO\Builder\Item\Fields\VariationRetailPriceFields;
use IO\Builder\Item\Params\ItemColumnsParams;
use IO\Extensions\AbstractFunction;

/**
 * Class GetBasePrice
 *
 * Contains twig functions that get the base price based on an variation ID.
 *
 * @package IO\Extensions\Functions
 */
class GetBasePrice extends AbstractFunction
{
    /**
     * @var Application
     */
    private $app;
    /**
     * @var ItemDataLayerRepositoryContract
     */
    private $itemRepository;
    /**
     * @var ItemColumnBuilder
     */
    private $columnBuilder;
    /**
     * @var ItemFilterBuilder
     */
    private $filterBuilder;
    /**
     * @var ItemParamsBuilder
     */
    private $paramsBuilder;

    /**
     * GetBasePrice constructor.
     * @param Application $app
     * @param ItemDataLayerRepositoryContract $itemRepository
     * @param ItemColumnBuilder $columnBuilder
     * @param ItemFilterBuilder $filterBuilder
     * @param ItemParamsBuilder $paramsBuilder
     */
    public function __construct(
        Application $app,
        ItemDataLayerRepositoryContract $itemRepository,
        ItemColumnBuilder $columnBuilder,
        ItemFilterBuilder $filterBuilder,
        ItemParamsBuilder $paramsBuilder
    ) {
        parent::__construct();
        $this->app = $app;
        $this->itemRepository = $itemRepository;
        $this->columnBuilder = $columnBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->paramsBuilder = $paramsBuilder;
    }

    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            "getBasePrice" => "getBasePrice",
            "getBasePriceList" => "getBasePriceList"
        ];
    }

    /**
     * Get the base price for the specified variation.
     *
     * @param int $variationId Variation ID to get the base price from.
     * @return array
     */
    public function getBasePrice(int $variationId): array
    {
        return $this->getBasePriceList([$variationId])[$variationId];
    }

    /**
     * Get base prices for a list of variations.
     *
     * @param array $variationIds Variation IDs to get the base prices from.
     * @return array
     */
    public function getBasePriceList($variationIds): array
    {
        $variations = array();
        if (is_array($variationIds) && count($variationIds)) {
            $columns = $this->columnBuilder
                ->withVariationBase(
                    [
                        VariationBaseFields::ID,
                        VariationBaseFields::CONTENT,
                        VariationBaseFields::UNIT_ID
                    ]
                )
                ->withVariationRetailPrice(
                    [
                        VariationRetailPriceFields::PRICE
                    ]
                )
                ->build();

            $filter = $this->filterBuilder
                ->variationHasId($variationIds)
                ->build();

            // set params
            $params = $this->paramsBuilder
                ->withParam(ItemColumnsParams::LANGUAGE, Utils::getLang())
                ->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
                ->build();

            $variations = $this->itemRepository->search(
                $columns,
                $filter,
                $params
            );
        }

        $basePriceList = array();

        if (count($variations)) {
            foreach ($variations as $variation) {
                $price = $variation->variationRetailPrice->price;
                $lot = (int)$variation->variationBase->content;
                $unit = $variation->variationBase->unitId;

                $bp_lot = 1;
                $bp_unit = $unit;
                $factor = 1.0;

                if ((float)$lot <= 0) {
                    $lot = 1;
                }

                if ($unit == 'LTR' || $unit == 'KGM') {
                    $bp_lot = 1;
                } elseif ($unit == 'GRM' || $unit == 'MLT') {
                    if ($lot <= 250) {
                        $bp_lot = 100;
                    } else {
                        $factor = 1000.0;
                        $bp_lot = 1;
                        $bp_unit = $unit == 'GRM' ? 'KGM' : 'LTR';
                    }
                } elseif ($unit == 'CMK') {
                    if ($lot <= 2500) {
                        $bp_lot = 10000;
                    } else {
                        $factor = 10000.0;
                        $bp_lot = 1;
                        $bp_unit = 'MTK';
                    }
                } else {
                    $bp_lot = 1;
                }

                $basePriceList[$variation->variationBase->id] = [
                    "lot" => $bp_lot,
                    "price" => $price * $factor * ($bp_lot / $lot),
                    "unit" => $bp_unit
                ];
            }
        }

        return $basePriceList;
    }
}
