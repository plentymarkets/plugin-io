<?php //strict

namespace IO\Services;

use IO\Helper\MemoryCache;
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

	/**
	 * @var UnitNameRepositoryContract
	 */
	private $unitNameRepository;

	private $defaultLang;

    /**
     * UnitService constructor.
     * @param UnitNameRepositoryContract $unitRepository
     */
	public function __construct(UnitNameRepositoryContract $unitRepository)
	{
		$this->unitNameRepository = $unitRepository;
		$this->defaultLang = pluginApp(SessionStorageService::class)->getLang();
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
}
