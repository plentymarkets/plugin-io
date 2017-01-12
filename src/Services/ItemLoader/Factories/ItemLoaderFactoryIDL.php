<?php
namespace IO\Services\ItemLoader\Factories;

use IO\Builder\Category\CategoryParams;
use IO\Builder\Item\ItemColumnBuilder;
use IO\Builder\Item\ItemFilterBuilder;
use IO\Builder\Item\ItemParamsBuilder;
use IO\Builder\Item\Params\ItemColumnsParams;
use IO\Constants\ItemConditionTexts;
use IO\Services\ItemLoader\Contracts\ItemLoaderFactory;
use IO\Services\ItemLoader\Loaders\CategoryItems;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\SessionStorageService;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\ItemDescription;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\Item\DataLayer\Models\VariationImage;
use Plenty\Modules\Item\Unit\Contracts\UnitRepositoryContract;
use Plenty\Modules\Item\Unit\Models\Unit;
use Plenty\Modules\Item\Unit\Models\UnitName;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;
use Plenty\Modules\Order\Shipping\Countries\Models\CountryName;
use Plenty\Plugin\Application;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Created by ptopczewski, 11.01.17 16:44
 * Class ItemLoaderFactoryIDL
 * @package IO\Services\ItemLoader\Factories
 */
class ItemLoaderFactoryIDL implements ItemLoaderFactory
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
     * SessionStorageService
     */
    private $sessionStorage;

    /**
     * ItemService constructor.
     * @param Application $app
     * @param ItemDataLayerRepositoryContract $itemRepository
     * @param SessionStorageService $sessionStorage
     */
    public function __construct(
        Application $app,
        ItemDataLayerRepositoryContract $itemRepository,
        SessionStorageService $sessionStorage
    ) {
        $this->app            = $app;
        $this->itemRepository = $itemRepository;
        $this->sessionStorage = $sessionStorage;
    }

    /**
     * @param array $loaderClassList
     * @param array $resultFields
     * @param array $options
     * @return array
     */
    public function runSearch($loaderClassList, $resultFields, $options = [])
    {
        //Single Item by itemId || variationId
        if (in_array(SingleItem::class, $loaderClassList)) {
            if (array_key_exists('variationId', $options)) {
                return $this->recordToESArray($this->getVariation($options['variationId']));
            } else {
                return $this->recordToESArray($this->getItem($options['itemId']));
            }
        }

        //category items
        if (in_array(CategoryItems::class, $loaderClassList)) {
            /** @var CategoryParams $categoryParams */
            $categoryParams               = pluginApp(CategoryParams::class);
            $categoryParams->itemsPerPage = $options['itemsPerPage'];

            return $this->paginatedResultToESArray($this->getItemForCategory($options['categoryId'], $categoryParams,
                $options['page']), $options['itemsPerPage']);
        }

        //TODO search items

        return [];
    }

    /**
     * @param $data
     * @param $closure
     * @return array
     */
    private function buildMap($data, $closure)
    {
        try {
            if ($closure instanceof \Closure) {
                return $closure->call($this, $data);
            }
        } catch (\Exception $exc) {
            //TODO log error
            return [];
        }
    }

    /**
     * @param Record $record
     * @param bool $buildOuterScope
     * @return array
     */
    private function recordToESArray(Record $record, $buildOuterScope = true)
    {
        /** @var UnitRepositoryContract $unitRepo */
        $unitRepo = pluginApp(UnitRepositoryContract::class);
        try {
            $unit = $unitRepo->findById($record->variationBase->unitId);
        } catch (\Exception $exc) {
            $unit = pluginApp(Unit::class);
        }

        /** @var CountryRepositoryContract $countryRepo */
        $countryRepo = pluginApp(CountryRepositoryContract::class);

        try {
            $producingCountry = $countryRepo->getCountryById($record->itemBase->producingCountryId);
        } catch (\Exception $exc) {
            $producingCountry = pluginApp(Country::class);
        }

        $result = [
            'score' => 1,
            'id'    => $record->variationBase->id,
            'data'  => [
                'filter' => [],
                'unit'   => [
                    'id'                     => $unit->id,
                    'unitOfMeasurement'      => $unit->unitOfMeasurement,
                    'position'               => $unit->position,
                    'isDecimalPlacesAllowed' => $unit->isDecimalPlacesAllowed,
                    'names'                  => $this->buildMap($unit->langs, function ($data) {
                        $result = [];
                        foreach ($data as $unitLang) {
                            /** @var UnitName $unitLang */
                            $result[] = [
                                'name' => $unitLang->name,
                                'lang' => $unitLang->lang
                            ];
                        }
                        return $result;
                    })
                ],
                'item'   => [
                    'storeSpecial'        => [
                        'id' => $record->itemBase->storeSpecial
                    ],
                    'customsTariffNumber' => $record->itemBase->customsTariffNumber,
                    'couponRestriction'   => $record->itemBase->noCoupon,
                    'ebayCategory2'       => $record->itemBase->ebayCategory2,
                    'producingCountry'    => [
                        'id'       => $producingCountry->id,
                        'name'     => $producingCountry->name,
                        'isoCode2' => $producingCountry->isoCode2,
                        'isoCode3' => $producingCountry->isoCode3,
                        'names'    => $this->buildMap($producingCountry->names, function ($data) {
                            $result = [];
                            foreach ($data as $countryName) {
                                /** @var CountryName $countryName */
                                $result[] = [
                                    'name' => $countryName->name,
                                    'lang' => $countryName->language
                                ];
                            }
                            return $result;
                        }),
                    ],
                    'flags'               => [
                        'flag1' => [
                            'id' => $record->itemBase->markingOne
                        ],
                        'flag2' => [
                            'id' => $record->itemBase->markingTwo
                        ]
                    ],
                    'amazonFbaPlatform'   => $record->itemBase->shippingWithAmazonFba,
                    'isActive'            => !$record->itemBase->inactive,
                    'manufacturer'        => [
                        'name' => $record->itemBase->producer,
                        'id'   => $record->itemBase->producerId
                    ],
                    'id'                  => $record->itemBase->id,
                    'conditionApi'        => [
                        'id'    => $record->itemBase->apiCondition,
                        'names' => [
                            [
                                'name' => ItemConditionTexts::$itemConditionTexts[$record->itemBase->apiCondition],
                                'lang' => 'de'
                            ]
                        ]
                    ],
                    'condition'           => [
                        'id'    => $record->itemBase->condition,
                        'names' => [
                            [
                                'name' => ItemConditionTexts::$itemConditionTexts[$record->itemBase->condition],
                                'lang' => 'de'
                            ]
                        ]
                    ],
                    'ageRestriction'      => $record->itemBase->ageRestriction,
                    'position'            => $record->itemBase->position,
                ], //end item

                'images' => [
                    'all'       => $this->buildMap($record->variationImageList, function ($variationImageList) {
                        $result = [];
                        foreach ($variationImageList as $variationImage) {
                            /** @var VariationImage $variationImage */
                            $result[] = [
                                'type'                      => $variationImage->type,
                                'position'                  => $variationImage->position,
                                'urlMiddle'                 => $variationImage->path,
                                'url'                       => $variationImage->path,
                                'urlSecondPreview'          => $variationImage->path,
                                'urlPreview'                => $variationImage->path,
                                'documentUploadPathPreview' => $variationImage->path,
                                'documentUploadPath'        => $variationImage->path,
                                'path'                      => $variationImage->path,
                                'cleanImageName'            => $variationImage->cleanImageName,
                                'fileType'                  => $variationImage->fileType,
                            ];
                        }
                        return $result;
                    }),
                    'item'      => $this->buildMap($record->variationImageList, function ($variationImageList) {
                        $result = [];
                        foreach ($variationImageList as $variationImage) {
                            /** @var VariationImage $variationImage */
                            $result[] = [
                                'type'                      => $variationImage->type,
                                'position'                  => $variationImage->position,
                                'urlMiddle'                 => $variationImage->path,
                                'url'                       => $variationImage->path,
                                'urlSecondPreview'          => $variationImage->path,
                                'urlPreview'                => $variationImage->path,
                                'documentUploadPathPreview' => $variationImage->path,
                                'documentUploadPath'        => $variationImage->path,
                                'path'                      => $variationImage->path,
                                'cleanImageName'            => $variationImage->cleanImageName,
                                'fileType'                  => $variationImage->fileType,
                            ];
                        }
                        return $result;
                    }),
                    'variation' => $this->buildMap($record->variationImageList, function ($variationImageList) {
                        $result = [];
                        foreach ($variationImageList as $variationImage) {
                            /** @var VariationImage $variationImage */
                            $result[] = [
                                'type'                      => $variationImage->type,
                                'position'                  => $variationImage->position,
                                'urlMiddle'                 => $variationImage->path,
                                'url'                       => $variationImage->path,
                                'urlSecondPreview'          => $variationImage->path,
                                'urlPreview'                => $variationImage->path,
                                'documentUploadPathPreview' => $variationImage->path,
                                'documentUploadPath'        => $variationImage->path,
                                'path'                      => $variationImage->path,
                                'cleanImageName'            => $variationImage->cleanImageName,
                                'fileType'                  => $variationImage->fileType,
                            ];
                        }
                        return $result;
                    })

                ], //end images

                'texts' => $this->buildMap($record->itemDescription, function (ItemDescription $description) {
                    return [
                        [
                            'name1'            => $description->name1,
                            'name2'            => $description->name2,
                            'name3'            => $description->name3,
                            'lang'             => $description->lang,
                            'keywords'         => $description->keywords,
                            'technicalData'    => $description->technicalData,
                            'description'      => $description->description,
                            'shortDescription' => $description->shortDescription,
                            'metaDescription'  => $description->metaDescription,
                            'urlPath'          => $description->urlContent
                        ]
                    ];
                })
            ]
        ];


        /*{
            "data": {


                "clients": [
                        1000
                    ],
                "texts": [
                  {
                      "name3": "",
                    "keywords": "",
                    "technicalData": "",
                    "description": "Der Zweisitzer Paradise Now kommt in schlichter und doch edler Optik daher. Das klassische Design mit moderner Silhouette macht in jedem Wohnzimmer eine gute Figur. Dank abwaschbarem und Ã¤uÃŸerst strapazierfÃ¤higem Glattleder kÃ¶nnen auch zum Beispiel schmutzige KinderhÃ¤nde keinen dauerhaften Schaden anrichten. StandfÃ¼ÃŸe aus Echtholz setzen warme, interessante Akzente.\n",
                    "shortDescription": "",
                    "lang": "de",
                    "name2": "",
                    "name1": "Zweisitzer Paradise Now",
                    "metaDescription": "",
                    "urlPath": "wohnzimmer/sofas/zweisitzer-paradise-now"
                  }
                ],
                "defaultCategories": [
                  {
                      "linklist": true,
                    "manually": false,
                    "level": 2,
                    "plentyId": 1000,
                    "right": "all",
                    "type": "item",
                    "branch": {
                      "category2Id": 18,
                      "category3Id": 0,
                      "category1Id": 16,
                      "category4Id": 0,
                      "category5Id": 0,
                      "category6Id": 0,
                      "categoryId": 18
                    },
                    "parentCategoryId": 16,
                    "details": [
                      {
                          "nameUrl": "sofas",
                        "description": "",
                        "description2": "",
                        "plenty_category_details_image2": null,
                        "metaDescription": "",
                        "canonicalLink": "",
                        "image2Document": null,
                        "metaKeywords": "moebel, sofa, couch, wohnzimmer",
                        "webTemplateExists": "N",
                        "fulltext": "N",
                        "imageDocument": null,
                        "lang": "de",
                        "image": null,
                        "plentyId": 0,
                        "plenty_category_details_last_update_user": "",
                        "shortDescription": "",
                        "image2": null,
                        "pageView": "PageDesignContent",
                        "metaRobots": "ALL",
                        "plenty_category_details_image": null,
                        "plenty_category_details_fulltext": "N",
                        "metaTitle": "",
                        "name": "Sofas",
                        "position": 1,
                        "itemListView": "ItemViewCategoriesList",
                        "placeholderTranslation": "Y",
                        "categoryId": 18,
                        "singleItemView": "ItemViewSingleItem"
                      }
                    ],
                    "id": 18,
                    "position": 0,
                    "sitemap": true,
                    "isNeckermannPrimary": true
                  }
                ],
                "_meta": {
                        "updatedAt": "2017-01-12T09:20:11+01:00"
                },
                "attributes": [
                  {
                      "attributeId": 1,
                    "isLinkableToImage": false,
                    "valueId": 3,
                    "attribute": {
                      "isLinkableToImage": true,
                      "neckermannAtEpAttribute": 0,
                      "laRedouteAttribute": 0,
                      "isSurchargePercental": false,
                      "amazonAttribute": "",
                      "pixmaniaAttribute": 0,
                      "typeOfSelectionInOnlineStore": "dropdown",
                      "fruugoAttribute": "color",
                      "googleShoppingAttribute": "",
                      "isGroupable": false,
                      "names": [
                        {
                            "attributeId": 1,
                          "name": "Farbe",
                          "lang": "de"
                        }
                      ],
                      "backendName": "Couch color",
                      "id": 1,
                      "position": 1,
                      "ottoAttribute": "",
                      "updatedAt": "2015-04-30T09:56:34+02:00"
                    },
                    "attributeValueSetId": 2,
                    "value": {
                      "image": "",
                      "percentageDistribution": 0,
                      "ottoValue": "",
                      "laRedouteValue": "",
                      "attributeId": 1,
                      "tracdelightValue": "",
                      "amazonValue": "",
                      "names": [
                        {
                            "valueId": 3,
                          "name": "rot",
                          "lang": "de"
                        }
                      ],
                      "backendName": "red",
                      "comment": "",
                      "id": 3,
                      "position": 3,
                      "neckermannAtEpValue": "",
                      "updatedAt": "2014-01-15T00:32:44+01:00"
                    }
                  }
                ],
                "salesPrices": [
                  {
                      "isDisplayedByDefault": true,
                    "createdAt": "2016-09-05 13:24:53",
                    "settings": {
                      "clients": [
                          -1
                      ],
                      "referrers": [
                          "0.00",
                          "1.00",
                          "3.00",
                          "5.00",
                          "6.00",
                          "7.00",
                          "101.00",
                          "102.00",
                          "103.00",
                          "105.00",
                          "106.00",
                          "106.02",
                          "107.00",
                          "108.00",
                          "108.02",
                          "109.00",
                          "110.00",
                          "111.00",
                          "114.00",
                          "115.00",
                          "116.00",
                          "117.00",
                          "118.00",
                          "119.00",
                          "120.00",
                          "121.00",
                          "121.02",
                          "122.00",
                          "123.00",
                          "124.00",
                          "125.00",
                          "126.00",
                          "127.00",
                          "130.00",
                          "131.00",
                          "132.00",
                          "133.00",
                          "134.00",
                          "135.00",
                          "136.00",
                          "137.00",
                          "138.00",
                          "139.00",
                          "143.00",
                          "143.02",
                          "144.00",
                          "145.00",
                          "147.00",
                          "149.00",
                          "150.00"
                      ],
                      "customerClasses": [
                          -1
                      ],
                      "accounts": [],
                      "countries": [
                          -1
                      ],
                      "currencies": [
                          "-1"
                      ]
                    },
                    "names": [
                      {
                          "createdAt": "2016-09-05T13:24:53+02:00",
                        "nameInternal": "Preis",
                        "nameExternal": "Preis",
                        "priceId": 1,
                        "lang": "de",
                        "updatedAt": "2016-09-05T14:46:34+02:00"
                      },
                      {
                          "createdAt": "2016-09-05T13:24:53+02:00",
                        "nameInternal": "Price",
                        "nameExternal": "Price",
                        "priceId": 1,
                        "lang": "en",
                        "updatedAt": "2016-09-05T14:46:34+02:00"
                      }
                    ],
                    "isCustomerPrice": false,
                    "minimumOrderQuantity": 1,
                    "price": "1350.00",
                    "id": 1,
                    "position": 0,
                    "type": "default",
                    "isLiveConversion": false,
                    "updatedAt": "2016-09-06 11:02:02"
                  },
                  {
                      "isDisplayedByDefault": true,
                    "createdAt": "2016-09-05 13:24:53",
                    "settings": {
                      "clients": [
                          -1
                      ],
                      "referrers": [
                          "0.00",
                          "1.00",
                          "3.00",
                          "5.00",
                          "6.00",
                          "7.00",
                          "101.00",
                          "102.00",
                          "103.00",
                          "105.00",
                          "106.00",
                          "106.02",
                          "107.00",
                          "108.00",
                          "108.02",
                          "109.00",
                          "110.00",
                          "111.00",
                          "114.00",
                          "115.00",
                          "116.00",
                          "117.00",
                          "118.00",
                          "119.00",
                          "120.00",
                          "121.00",
                          "121.02",
                          "122.00",
                          "123.00",
                          "124.00",
                          "125.00",
                          "126.00",
                          "127.00",
                          "130.00",
                          "131.00",
                          "132.00",
                          "133.00",
                          "134.00",
                          "135.00",
                          "136.00",
                          "137.00",
                          "138.00",
                          "139.00",
                          "143.00",
                          "143.02",
                          "144.00",
                          "145.00",
                          "147.00",
                          "149.00",
                          "150.00"
                      ],
                      "customerClasses": [
                          -1
                      ],
                      "accounts": [],
                      "countries": [
                          -1
                      ],
                      "currencies": [
                          "-1"
                      ]
                    },
                    "names": [
                      {
                          "createdAt": "2016-09-05T13:24:54+02:00",
                        "nameInternal": "UVP",
                        "nameExternal": "UVP",
                        "priceId": 2,
                        "lang": "de",
                        "updatedAt": "2016-09-05T13:24:54+02:00"
                      },
                      {
                          "createdAt": "2016-09-05T13:24:54+02:00",
                        "nameInternal": "RRP",
                        "nameExternal": "RRP",
                        "priceId": 2,
                        "lang": "en",
                        "updatedAt": "2016-09-06T15:53:37+02:00"
                      }
                    ],
                    "isCustomerPrice": false,
                    "minimumOrderQuantity": 0,
                    "price": "1500.00",
                    "id": 2,
                    "position": 0,
                    "type": "rrp",
                    "isLiveConversion": false,
                    "updatedAt": "2016-09-06 11:02:46"
                  }
                ],
                "categories": {
                        "all": [
                            16,
                            18
                        ],
                  "details": [
                    {
                        "linklist": true,
                      "manually": false,
                      "level": 2,
                      "plentyId": 1000,
                      "right": "all",
                      "type": "item",
                      "branch": {
                        "category2Id": 18,
                        "category3Id": 0,
                        "category1Id": 16,
                        "category4Id": 0,
                        "category5Id": 0,
                        "category6Id": 0,
                        "categoryId": 18
                      },
                      "parentCategoryId": 16,
                      "details": [
                        {
                            "nameUrl": "sofas",
                          "description": "",
                          "description2": "",
                          "metaDescription": "",
                          "canonicalLink": "",
                          "image2Document": null,
                          "metaKeywords": "moebel, sofa, couch, wohnzimmer",
                          "webTemplateExists": false,
                          "fulltext": false,
                          "imageDocument": null,
                          "lang": "de",
                          "image": null,
                          "plentyId": 0,
                          "shortDescription": "",
                          "image2": null,
                          "pageView": "PageDesignContent",
                          "metaRobots": "ALL",
                          "plenty_category_details_fulltext": "N",
                          "metaTitle": "",
                          "name": "Sofas",
                          "position": 1,
                          "itemListView": "ItemViewCategoriesList",
                          "placeholderTranslation": true,
                          "categoryId": 18,
                          "singleItemView": "ItemViewSingleItem"
                        }
                      ],
                      "id": 18,
                      "position": 0,
                      "sitemap": true,
                      "isNeckermannPrimary": true
                    }
                  ],
                  "branches": [
                            18
                        ]
                },
                "variation": {
                        "intervalOrderQuantity": 0,
                  "automaticClientVisibility": 0,
                  "stockLimitation": 1,
                  "minimumOrderQuantity": 0,
                  "isUnavailableIfNetStockIsNotPositive": false,
                  "packingUnits": 0,
                  "vatId": 0,
                  "relatedUpdatedAt": "2016-09-19T12:32:50+02:00",
                  "purchasePrice": 0,
                  "isActive": true,
                  "widthMM": 0,
                  "number": "NEW-135",
                  "createdAt": "2016-09-19T12:32:50+02:00",
                  "availableUntil": null,
                  "isInvisibleIfNetStockIsNotPositive": true,
                  "weightG": 0,
                  "customs": 0,
                  "model": "",
                  "id": 1069,
                  "updatedAt": "2016-09-19T12:33:23+02:00",
                  "extraShippingCharge2": 0,
                  "isMain": false,
                  "categoryVariationId": 1030,
                  "picking": null,
                  "palletTypeId": null,
                  "isVisibleIfNetStockIsPositive": true,
                  "itemId": 132,
                  "operatingCosts": 0,
                  "mainWarehouseId": 1,
                  "name": "",
                  "mainVariationId": 1030,
                  "position": 0,
                  "salesPriceVariationId": 1030,
                  "releasedAt": null,
                  "weightNetG": 0,
                  "transportationCosts": 0,
                  "packingUnitTypeId": 0,
                  "isAvailableIfNetStockIsPositive": false,
                  "isHiddenInCategoryList": false,
                  "availability": {
                            "averageDays": 2,
                    "names": [
                      {
                          "availabilityId": 1,
                        "name": "Sofort versandfertig, Lieferzeit 48h",
                        "lang": "de"
                      },
                      {
                          "availabilityId": 1,
                        "name": "Ready for shipping, delivery in 48h",
                        "lang": "en"
                      }
                    ],
                    "icon": "av1.gif",
                    "id": 1
                  },
                  "bundleType": null,
                  "maximumOrderQuantity": 0,
                  "extraShippingCharge1": 0,
                  "unitsContained": 1,
                  "heightMM": 0,
                  "externalId": "",
                  "priceCalculationId": null,
                  "marketVariationId": 1030,
                  "warehouseVariationId": 1030,
                  "lengthMM": 0,
                  "clientVariationId": 1030,
                  "estimatedAvailableAt": null,
                  "supplierVariationId": 1030,
                  "storageCosts": 0
                }
              }
            }*/

        if ($buildOuterScope) {
            $result = [
                "took"      => 1,
                "total"     => 1,
                "documents" => [$result]
            ];
        }
        return $result;
    }

    /**
     * @param RecordList $recordList
     * @param bool $buildOuterScope
     * @return array
     */
    private function recordListToESArray(RecordList $recordList, $buildOuterScope = true)
    {
        $result = [];
        foreach ($recordList as $record) {
            $result[] = $this->recordToESArray($record, false);
        }

        if ($buildOuterScope) {
            $result = [
                "took"      => count($recordList),
                "total"     => count($recordList),
                "documents" => $result
            ];
        }
        return $result;
    }

    /**
     * @param PaginatedResult $paginatedResult
     * @param $itemsPerPage
     * @return array
     */
    private function paginatedResultToESArray(PaginatedResult $paginatedResult, $itemsPerPage)
    {
        $result     = [];
        $recordList = $paginatedResult->getResult();
        if ($recordList instanceof RecordList) {
            $result = [
                "took"      => $itemsPerPage,
                "total"     => count($paginatedResult->getResult()),
                "documents" => $this->recordListToESArray($recordList, false)
            ];
        }

        return $result;
    }

    /**
     * Get an item by ID
     * @param int $itemId
     * @return Record
     */
    private function getItem(int $itemId = 0) : Record
    {
        return $this->getItems([$itemId])->current();
    }

    /**
     * Get a list of items with the specified item IDs
     * @param array $itemIds
     * @return RecordList
     */
    private function getItems(array $itemIds):RecordList
    {
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp(ItemColumnBuilder::class);
        $columns       = $columnBuilder
            ->defaults()
            ->build();

        // Filter the current item by item ID
        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp(ItemFilterBuilder::class);
        $filter        = $filterBuilder
            ->hasId($itemIds)
            ->variationIsActive()
            ->build();

        // Set the parameters
        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp(ItemParamsBuilder::class);
        $params        = $paramsBuilder
            ->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
            ->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
            ->build();

        return $this->itemRepository->search(
            $columns,
            $filter,
            $params
        );
    }

    /**
     * Get an item variation by ID
     * @param int $variationId
     * @return Record
     */
    private function getVariation(int $variationId = 0):Record
    {
        return $this->getVariations([$variationId])->current();
    }

    /**
     * Get a list of item variations with the specified variation IDs
     * @param array $variationIds
     * @return RecordList
     */
    private function getVariations(array $variationIds):RecordList
    {
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp(ItemColumnBuilder::class);
        $columns       = $columnBuilder
            ->defaults()
            ->build();

        // Filter the current variation by variation ID
        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp(ItemFilterBuilder::class);
        $filter        = $filterBuilder
            ->variationHasId($variationIds)
            ->variationIsActive()
            ->build();

        // Set the parameters
        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp(ItemParamsBuilder::class);
        $params        = $paramsBuilder
            ->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
            ->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
            ->build();

        return $this->itemRepository->search(
            $columns,
            $filter,
            $params
        );
    }

    /**
     * Get all items for a specific category
     * @param int $catID
     * @param CategoryParams $params
     * @param int $page
     * @return PaginatedResult
     */
    private function getItemForCategory(int $catID, CategoryParams $params, int $page = 1)
    {
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp(ItemColumnBuilder::class);
        $columns       = $columnBuilder
            ->defaults()
            ->build();

        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp(ItemFilterBuilder::class);
        if ($params->variationShowType == 2) {
            $filterBuilder->variationIsPrimary();
        }

        if ($params->variationShowType == 3) {
            $filterBuilder->variationIsChild();
        }

        $filter = $filterBuilder
            ->variationHasCategory($catID)
            ->variationIsActive()
            ->build();

        /** @var ItemParamsBuilder $paramsBuilder */
        $paramsBuilder = pluginApp(ItemParamsBuilder::class);
        if ($params->orderBy != null && strlen($params->orderBy) > 0) {
            $paramsBuilder->withParam(ItemColumnsParams::ORDER_BY,
                ["orderBy." . $params->orderBy => $params->orderByKey]);
        }

        $offset = ($page - 1) * $params->itemsPerPage;
        $params = $paramsBuilder
            ->withParam(ItemColumnsParams::LIMIT, $params->itemsPerPage)
            ->withParam(ItemColumnsParams::OFFSET, $offset)
            ->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
            ->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
            ->build();

        return $this->itemRepository->searchWithPagination($columns, $filter, $params);
    }

    /**
     * @param string $searchString
     * @param CategoryParams $params
     * @param int $page
     * @return PaginatedResult
     */
    private function searchItems(string $searchString, CategoryParams $params, int $page = 1)
    {
        /** @var ItemColumnBuilder $columnBuilder */
        $columnBuilder = pluginApp(ItemColumnBuilder::class);

        /** @var ItemFilterBuilder $filterBuilder */
        $filterBuilder = pluginApp(ItemFilterBuilder::class);

        /** @var ItemParamsBuilder $paramBuilder */
        $paramsBuilder = pluginApp(ItemParamsBuilder::class);

        $columns = $columnBuilder
            ->defaults()
            ->build();

        $filter = $filterBuilder
            ->descriptionContains($searchString, true)
            ->build();

        $offset = ($page - 1) * $params->itemsPerPage;

        $params = $paramsBuilder
            ->withParam(ItemColumnsParams::LIMIT, $params->itemsPerPage)
            ->withParam(ItemColumnsParams::OFFSET, $offset)
            ->withParam(ItemColumnsParams::LANGUAGE, $this->sessionStorage->getLang())
            ->withParam(ItemColumnsParams::PLENTY_ID, $this->app->getPlentyId())
            ->build();

        return $this->itemRepository->searchWithPagination($columns, $filter, $params);
    }
}