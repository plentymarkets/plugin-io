<?hh //strict

namespace LayoutCore\Services;

//use Plenty\Modules\Item\Unit\Contracts\UnitLangRepositoryContract;
use Plenty\Modules\Item\Unit\Contracts\UnitNameRepositoryContract;
//use Plenty\Modules\Item\Unit\Models\UnitLang;
use Plenty\Modules\Item\Unit\Models\UnitName;

class UnitService
{
    //private UnitLangRepositoryContract $unitRepository;
    private UnitNameRepositoryContract $unitRepository;

    //public function __construct( UnitLangRepositoryContract $unitRepository )
    public function __construct( UnitNameRepositoryContract $unitRepository )
    {
        $this->unitRepository = $unitRepository;
    }

    public function getUnitById(int $unitId, string $lang = "de"):?UnitName
    {
        //return $this->unitRepository->findUnit($unitId, $lang);
        return $this->unitRepository->findOne($unitId, $lang);
    }
}
