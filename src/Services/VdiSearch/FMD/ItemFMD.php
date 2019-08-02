<?php


namespace IO\Services\VdiSearch\FMD;

use Illuminate\Support\Collection;
use Plenty\Modules\Core\Data\Exceptions\LazyLoaderException;
use Plenty\Modules\Core\Data\Exceptions\ModelFlattenerException;
use Plenty\Modules\Core\Data\Factories\LazyLoaderFactory;
use Plenty\Modules\Core\Data\Services\LazyLoader;
use Plenty\Modules\Item\Item\Models\Item;
use Plenty\Modules\Item\Manufacturer\Models\Manufacturer;
use Plenty\Modules\Marking\Models\Marking;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Pim\VariationDataInterface\Model\Attributes\VariationBaseAttribute;
use Plenty\Modules\Pim\VariationDataInterface\Model\Variation;


class ItemFMD extends FieldMapDefinition
{
    /**
     * @var array
     */
    protected static $conditions = [
        0 => [
            'id' => 0,
            'names' => [
                ['lang' => 'de', 'name' => 'Neu'],
                ['lang' => 'en', 'name' => 'New']
            ]
        ],
        1 => [
            'id' => 1,
            'names' => [
                ['lang' => 'de', 'name' => 'Gebraucht'],
                ['lang' => 'en', 'name' => 'Used']
            ]
        ],
        2 => [
            'id' => 2,
            'names' => [
                ['lang' => 'de', 'name' => 'Neu & OVP'],
                ['lang' => 'en', 'name' => 'New & with original packaging']
            ]
        ],
        3 => [
            'id' => 3,
            'names' => [
                ['lang' => 'de', 'name' => 'neu mit Etikett'],
                ['lang' => 'en', 'name' => 'New with label']
            ]
        ],
        4 => [
            'id' => 4,
            'names' => [
                ['lang' => 'de', 'name' => 'B-Ware'],
                ['lang' => 'en', 'name' => 'Factory seconds']
            ]
        ]
    ];

    /**
     * @var array
     */
    protected static $conditionsApi = [
        0 => [
            'id' => 0,
            'names' => [
                ['lang' => 'de', 'name' => 'Neu'],
                ['lang' => 'en', 'name' => 'New']
            ]
        ],
        1 => [
            'id' => 1,
            'names' => [
                ['lang' => 'de', 'name' => 'Gebraucht wie neu'],
                ['lang' => 'en', 'name' => 'Used but as new']
            ]
        ],
        2 => [
            'id' => 2,
            'names' => [
                ['lang' => 'de', 'name' => 'Gebraucht sehr gut'],
                ['lang' => 'en', 'name' => 'Used but very good']
            ]
        ],
        3 => [
            'id' => 3,
            'names' => [
                ['lang' => 'de', 'name' => 'Gebraucht gut'],
                ['lang' => 'en', 'name' => 'Used but good']
            ]
        ],
        4 => [
            'id' => 4,
            'names' => [
                ['lang' => 'de', 'name' => 'Gebraucht annehmbar'],
                ['lang' => 'en', 'name' => 'Used but acceptable']
            ]
        ],
        5 => [
            'id' => 5,
            'names' => [
                ['lang' => 'de', 'name' => 'B-Ware'],
                ['lang' => 'en', 'name' => 'Factory seconds']
            ]
        ]
    ];

    /**
     * @var array
     */
    protected static $storeSpecial = [
        1 => [
            'id' => 1,
            'names' => [
                ['lang' => 'de', 'name' => 'Sonderangebot'],
                ['lang' => 'en', 'name' => 'Special offer']
            ]
        ],
        2 => [
            'id' => 2,
            'names' => [
                ['lang' => 'de', 'name' => 'Neuheit'],
                ['lang' => 'en', 'name' => 'New item']
            ]
        ],
        3 => [
            'id' => 3,
            'names' => [
                ['lang' => 'de', 'name' => 'Top-Artikel'],
                ['lang' => 'en', 'name' => 'Top item']
            ]
        ]
    ];

    /**
     * @var array
     */
    protected static $marks1;

    /**
     * @var array
     */
    protected static $marks2;

    /**
     * @var LazyLoader
     */
    protected $manufacturerLazyLoader;

    /**
     * @var LazyLoader
     */
    protected $producingCountryLazyLoader;

    /**
     * ItemFMD constructor.
     */
    public function __construct()
    {
        $this->manufacturerLazyLoader = LazyLoaderFactory::getLazyLoaderFor(Manufacturer::class);
        $this->producingCountryLazyLoader = LazyLoaderFactory::getLazyLoaderFor(Country::class);

        if (!count(self::$marks1) || !count(self::$marks2)) {
            /** @var Collection $markings */
            $markings = Marking::query()
                ->whereIn('type', [Marking::MARKING_TYPE_ITEM_ONE, Marking::MARKING_TYPE_ITEM_TWO])
                ->get();

            /** @var Marking $marking */
            foreach ($markings->all() AS $marking) {
                if ($marking->type == Marking::MARKING_TYPE_ITEM_ONE) {
                    self::$marks1[$marking->markId] = $marking->toArray();
                } else {
                    self::$marks2[$marking->markId] = $marking->toArray();
                }
            }
        }

    }

    /**
     * @inheritDoc
     */
    public function getAttribute(): string
    {
        return VariationBaseAttribute::class;
    }

    /**
     * @inheritDoc
     */
    public function getLazyLoadable()
    {
        return [
            VariationBaseAttribute::ITEM,
            //VariationBaseAttribute::FEEDBACK
        ];
    }

    /**
     * @inheritDoc
     */
    public function getOldField(): string
    {
        return 'item';
    }

    /**
     * @inheritDoc
     * @throws LazyLoaderException
     * @throws ModelFlattenerException
     */
    public function fill(Variation $decoratedVariation, array $content, array $sourceFields)
    {
        $x = Item::find($decoratedVariation->base->itemId);
        $y = $x->toArray();
        $item = $decoratedVariation->base->with()->item->toArray();
        $f = $decoratedVariation->base->with()->feedback;

        $item['condition'] = isset(self::$conditions[$item['condition']]) ? self::$conditions[$item['condition']] : null;
        $item['conditionApi'] = isset(self::$conditionsApi[$item['conditionApi']]) ? self::$conditionsApi[$item['conditionApi']] : null;
        $item['storeSpecial'] = isset(self::$storeSpecial[$item['storeSpecial']]) ? self::$storeSpecial[$item['storeSpecial']] : null;

        if (!is_null($item['producingCountryId'])) {
            /** @var Country $producingCountry */
            $producingCountry = $this->producingCountryLazyLoader->getById($item['producingCountryId']);

            if (!is_null($producingCountry)) {

                $producingCountry['names'] = array_map(function ($name) {
                    return [
                        'name' => $name['name'],
                        'lang' => $name['language']
                    ];
                }, $producingCountry['names']);

                $item['producingCountry'] = $producingCountry;
            }
        }

        $item['flags'] = [
            'flag1' => static::map(self::$marks1[$item['flagOne']], 'markId', 'id'),
            'flag2' => static::map(self::$marks2[$item['flagTwo']], 'markId', 'id')
        ];

        $item['manufacturer'] = null;
        if ($item['manufacturerId'] > 0) {
            /** @var Manufacturer $manufacturer */
            $manufacturer = $this->manufacturerLazyLoader->getById($item['manufacturerId']);
            //$manufacturer = static::map($manufacturer, 'externalName', 'nameExternal');
            $item['manufacturer'] = $manufacturer;
        }
        $content['item'] = $item;

        return $content;
    }
}
