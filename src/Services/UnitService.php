<?php //strict

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
use Plenty\Modules\Item\Unit\Models\UnitName;
use Plenty\Modules\Webshop\Helpers\UnitUtils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Unit\Contracts\UnitRepositoryContract;
use IO\Helper\MemoryCache;

/**
 * Class UnitService
 * @package IO\Services
 * @deprecated since 5.0.0 will be removed in 6.0.0
 */
class UnitService
{
    use MemoryCache;

    const METER = 1;
    const DECIMETER = 2;
    const CENTIMETER = 3;
    const MILLIMETER = 4;

    /**
     * @var UnitNameRepositoryContract $unitNameRepository
     */
    private $unitNameRepository;

    /** @var UnitRepositoryContract $unitRepository */
    private $unitRepository;

    /**
     * @var array
     */
    public static $aMeasureUnits = array(
        'MTR' => array('value' => self::METER, 'code' => 'm'),    //	Meter
        'MTK' => array('value' => self::METER, 'code' => 'm'),    //	Quadratmeter
        'SDM' => array('value' => self::DECIMETER, 'code' => 'dm'),    //	(?) Dezimeter
        'CMT' => array('value' => self::CENTIMETER, 'code' => 'cm'),    //	Zentimeter
        'SCM' => array('value' => self::CENTIMETER, 'code' => 'cm'),    //	Quadratzentimeter (kein Standard)
        'CMK' => array('value' => self::CENTIMETER, 'code' => 'cm'),    //	Quadratzentimeter
        'MMT' => array('value' => self::MILLIMETER, 'code' => 'mm'),    //	Millimeter
        'MMK' => array('value' => self::MILLIMETER, 'code' => 'mm'),    //	Quadratmillimeter
        'SMM' => array('value' => self::MILLIMETER, 'code' => 'mm'),    //	Quadratmillimeter (kein Standard)
    );

    /**
     * UnitService constructor.
     * @param UnitNameRepositoryContract $unitNameRepository
     * @param UnitRepositoryContract $unitRepository
     */
    public function __construct(UnitNameRepositoryContract $unitNameRepository, UnitRepositoryContract $unitRepository)
    {
        $this->unitNameRepository = $unitNameRepository;
        $this->unitRepository = $unitRepository;
    }

    /**
     * Get the unit by ID
     * @param int $unitId
     * @param string $lang
     * @return UnitName
     * @deprecated since 5.0.0 will be removed in 6.0.0
     */
    public function getUnitById(int $unitId, string $lang = "de"): UnitName
    {
        return $this->unitNameRepository->findOne($unitId, $lang);
    }

    /**
     * @param $unitKey
     * @param null $lang
     * @return mixed
     * @deprecated since 5.0.0 will be removed in 6.0.0
     */
    public function getUnitNameByKey($unitKey, $lang = null)
    {
        if ($lang === null) {
            $lang = Utils::getLang();
        }

        return $this->fromMemoryCache(
            "unitName.$unitKey.$lang",
            function () use ($unitKey, $lang) {
                /**
                 * @var UnitRepositoryContract $unitRepository
                 */
                $unitRepository = pluginApp(UnitRepositoryContract::class);

                /** @var AuthHelper $authHelper */
                $authHelper = pluginApp(AuthHelper::class);

                $unitData = $authHelper->processUnguarded(
                    function () use ($unitRepository, $unitKey) {
                        $unitRepository->setFilters(['unitOfMeasurement' => $unitKey]);
                        return $unitRepository->all(['*'], 1, 1);
                    }
                );

                $unitId = $unitData->getResult()->first()->id;

                return $this->unitNameRepository->findOne($unitId, $lang)->name;
            }
        );
    }

    // copy from PlentyDimension.class.php

    /**
     * Checks if the given string unit is a valid one for the PlentyDimension.
     * @param string $sUnit The unit to be checked
     * @return boolean
     * @deprecated since 5.0.0 will be removed in 6.0.0
     */
    public static function isValidUnit($sUnit)
    {
        return UnitUtils::isValidUnit($sUnit);
    }

    /**
     * Returns HTML code for the unit ('m','cm' o'MM')
     * @param string $sUnit One of 'MTK', 'SCM', 'SMM'
     * @return string
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\UnitUtils::getHTML4Unit()
     */
    public static function getHTML4Unit($sUnit = 'SMM')
    {
        return UnitUtils::getHTML4Unit($sUnit);
    }
}
