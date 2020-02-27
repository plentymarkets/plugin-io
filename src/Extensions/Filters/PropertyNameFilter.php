<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Helper\MemoryCache;
use IO\Helper\Utils;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Property\Contracts\PropertyRepositoryContract;
use Plenty\Modules\Item\Property\Contracts\PropertySelectionRepositoryContract;

/**
 * Class PropertySelectionValueNameFilter
 * @package IO\Extensions\Filters
 */
class PropertyNameFilter extends AbstractFilter
{
    use MemoryCache;

    /** @var AuthHelper $authHelper */
    private $authHelper;

    /** @var PropertyRepositoryContract */
    private $propertyRepository;

    /** @var PropertySelectionRepositoryContract */
    private $propertySelectionRepository;

    /**
     * PropertySelectionValueNameFilter constructor.
     * @param AuthHelper $authHelper
     */
    public function __construct(
        AuthHelper $authHelper
    )
    {
        parent::__construct();

        $this->authHelper                   = $authHelper;
    }

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFilters():array
    {
        return [
            "propertyName"                  => "getPropertyName",
            "propertySelectionValueName"    => "getPropertySelectionValueName"
        ];
    }

    public function getPropertyName($property, $lang = null)
    {
        if ($lang === null)
        {
            $lang = Utils::getLang();
        }

        if (is_null($this->propertyRepository))
        {
            $this->propertyRepository = pluginApp(PropertyRepositoryContract::class);
        }

        $propertyId = $property['propertyId'];
        $propertyNames = $this->fromMemoryCache("propertyName.$propertyId", function() use ($propertyId, $lang)
        {
            return $this->authHelper->processUnguarded(function() use ($propertyId)
            {
                return $this->propertyRepository->findById($propertyId)->names;
            });
        });

        $propertyName = $propertyNames->firstWhere('lang', $lang);
        if(!is_null($propertyName))
        {
            return $propertyName->name;
        }

        return "";
    }

    public function getPropertySelectionValueName($property, $lang = null)
    {
        $propertyId       = $property['propertyId'];
        $selectionValueId = $property['value'];

        if ($lang === null)
        {
            $lang = Utils::getLang();
        }

        if (is_null($this->propertySelectionRepository))
        {
            $this->propertySelectionRepository = pluginApp(PropertySelectionRepositoryContract::class);
        }

        $selectionValues = $this->fromMemoryCache("selectionValues.$propertyId.$lang", function() use ($propertyId, $lang)
        {
            return $this->authHelper->processUnguarded(function() use ($propertyId, $lang)
            {
                return $this->propertySelectionRepository->findByProperty($propertyId, $lang);
            });
        });
        return $selectionValues->firstWhere('id', $selectionValueId)->name;
    }
}
