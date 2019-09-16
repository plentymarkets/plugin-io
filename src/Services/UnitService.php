<?php //strict

namespace IO\Services;

use IO\Helper\MemoryCache;
use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
use Plenty\Modules\Item\Unit\Contracts\UnitRepositoryContract;
use Plenty\Modules\Item\Unit\Models\UnitName;

/**
 * Class UnitService
 * @package IO\Services
 */
class UnitService
{
    use MemoryCache;

    const METER				= 1;
	const DECIMETER			= 2;
	const CENTIMETER		= 3;
	const MILLIMETER		= 4;

	/**
	 * @var UnitNameRepositoryContract
	 */
	private $unitNameRepository;

	private $defaultLang;

	/**
	 *
	 * @var array
	 */
	public static $aMeasureUnits = array(
		'MTR'=>array('value'=>self::METER,		'code'=>'m'),	//	Meter
		'MTK'=>array('value'=>self::METER,		'code'=>'m'),	//	Quadratmeter
		'SDM'=>array('value'=>self::DECIMETER,	'code'=>'dm'),	//	(?) Dezimeter
		'CMT'=>array('value'=>self::CENTIMETER,	'code'=>'cm'),	//	Zentimeter
		'SCM'=>array('value'=>self::CENTIMETER,	'code'=>'cm'),	//	Quadratzentimeter (kein Standard)
		'CMK'=>array('value'=>self::CENTIMETER,	'code'=>'cm'),	//	Quadratzentimeter
		'MMT'=>array('value'=>self::MILLIMETER,	'code'=>'mm'),	//	Millimeter
		'MMK'=>array('value'=>self::MILLIMETER,	'code'=>'mm'),	//	Quadratmillimeter
		'SMM'=>array('value'=>self::MILLIMETER,	'code'=>'mm'),	//	Quadratmillimeter (kein Standard)
	);

    /**
     * UnitService constructor.
     * @param UnitNameRepositoryContract $unitRepository
     */
	public function __construct(UnitNameRepositoryContract $unitRepository)
	{
		$this->unitNameRepository = $unitRepository;
		$this->defaultLang = Utils::getLang();
	}

    /**
     * Get the unit by ID
     * @param int $unitId
     * @param string $lang
     * @return UnitName
     */
	public function getUnitById(int $unitId, string $lang = "de"):UnitName
	{
		return $this->unitNameRepository->findOne($unitId, $lang);
	}

    public function getUnitNameByKey( $unitKey, $lang = null )
    {
        if ( $lang === null )
        {
            $lang = $this->defaultLang;
        }

        return $this->fromMemoryCache(
            "unitName.$unitKey.$lang",
            function() use ($unitKey, $lang)
            {
                /**
                 * @var UnitRepositoryContract $unitRepository
                 */
                $unitRepository = pluginApp(UnitRepositoryContract::class);

                /** @var AuthHelper $authHelper */
                $authHelper = pluginApp(AuthHelper::class);

                $unitData = $authHelper->processUnguarded( function() use ($unitRepository, $unitKey)
                {
                    $unitRepository->setFilters(['unitOfMeasurement' => $unitKey]);
                    return $unitRepository->all(['*'], 1, 1);
                });


                $unitId = $unitData->getResult()->first()->id;

                return $this->unitNameRepository->findOne($unitId, $lang)->name;
            }
        );
    }

    // copy from PlentyDimension.class.php
    /**
	 * Checks if the given string unit is a valid one for the PlentyDimension.
	 * @param string $sUnit	The unit to be checked
	 * @return boolean
	 */
	public static function isValidUnit($sUnit)
	{
		return in_array($sUnit,array_keys(self::$aMeasureUnits));
	}

	/**
	 * Returns HTML code for the unit ('m','cm' o'MM')
	 * @param string $sUnit	One of 'MTK', 'SCM', 'SMM'
	 * @return string
	 */
	public static function getHTML4Unit($sUnit='SMM')
	{
		if(!self::isValidUnit($sUnit))
		{
			return 'mm';
		}
		return self::$aMeasureUnits[$sUnit]['code'];
	}
}
