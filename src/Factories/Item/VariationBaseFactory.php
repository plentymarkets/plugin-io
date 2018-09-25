<?php
namespace IO\Factories\Item;

use Faker\Generator;
use Plenty\Data\Factory;
use Plenty\Modules\Item\Variation\Models\Variation;

/**
 * Class VariationBaseFactory
 * @package IO\Factories\Item
 */
class VariationBaseFactory extends Factory
{
    /**
     * @var string
     */
    protected $modelName = Variation::class;


    /**
     * @param Generator $faker
     * @return array
     */
    public function make(Generator $faker)
    {
      return [
            'primary_variation' => 'Y',
            'active' => 'Y',
            'position' => 1,
            'availability' => 1,
            'limit_order_by_stock_select' => 0,
            'auto_stock_visible' => 'Y',
            'auto_stock_invisible' => 'Y',
            'main_warehouse' => 1,
            'custom_number' => chr(rand(65,90))."-".rand(100000,999999)
        ];
    }


}