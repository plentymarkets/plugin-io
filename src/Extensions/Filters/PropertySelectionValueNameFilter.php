<?php //strict

namespace IO\Extensions\Filters;

use Illuminate\Database\Eloquent\Collection;
use IO\Extensions\AbstractFilter;
use IO\Helper\MemoryCache;
use IO\Services\SessionStorageService;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Property\Contracts\PropertySelectionRepositoryContract;

/**
 * Class PropertySelectionValueNameFilter
 * @package IO\Extensions\Filters
 */
class PropertySelectionValueNameFilter extends AbstractFilter
{
    use MemoryCache;

    /** @var AuthHelper $authHelper */
    private $authHelper;

    /** @var PropertySelectionRepositoryContract */
    private $propertySelectionRepository;

    /** @var SessionStorageService $sessionStorageService */
    private $sessionStorageService;

    /**
     * PropertySelectionValueNameFilter constructor.
     * @param AuthHelper $authHelper
     * @param PropertySelectionRepositoryContract $propertyRepository
     * @param SessionStorageService $sessionStorageService
     */
    public function __construct(
        AuthHelper $authHelper,
        PropertySelectionRepositoryContract $propertyRepository,
        SessionStorageService $sessionStorageService
    )
    {
        parent::__construct();

        $this->authHelper                  = $authHelper;
        $this->propertySelectionRepository = $propertyRepository;
        $this->sessionStorageService       = $sessionStorageService;
    }

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFilters():array
    {
        return [
            "propertySelectionValueName" => "getPropertySelectionValueName"
        ];
    }

    public function getPropertySelectionValueName($property, $lang = null)
    {
        $propertyId       = $property['propertyId'];
        $selectionValueId = $property['value'];

        if ($lang === null)
        {
            $lang = $this->sessionStorageService->getLang();
        }

        $selectionValueName = $this->fromMemoryCache("selectionValueName.$propertyId.$selectionValueId.$lang", function() use ($propertyId, $selectionValueId, $lang)
        {
            $selectionValues = $this->authHelper->processUnguarded(function() use ($propertyId, $lang)
            {
                return $this->propertySelectionRepository->findByProperty($propertyId, $lang);
            });

            return $selectionValues->firstWhere('id', $selectionValueId)->name;
        });

        return $selectionValueName;
    }
}
