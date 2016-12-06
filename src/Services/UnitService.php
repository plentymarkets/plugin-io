<?php //strict

namespace IO\Services;

use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
use Plenty\Modules\Item\Unit\Models\UnitName;

/**
 * Class UnitService
 * @package IO\Services
 */
class UnitService
{
	/**
	 * @var UnitNameRepositoryContract
	 */
	private $unitRepository;

    /**
     * UnitService constructor.
     * @param UnitNameRepositoryContract $unitRepository
     */
	public function __construct(UnitNameRepositoryContract $unitRepository)
	{
		$this->unitRepository = $unitRepository;
	}

    /**
     * Get the unit by ID
     * @param int $unitId
     * @param string $lang
     * @return UnitName
     */
	public function getUnitById(int $unitId, string $lang = "de"):UnitName
	{
		return $this->unitRepository->findOne($unitId, $lang);
	}
}
