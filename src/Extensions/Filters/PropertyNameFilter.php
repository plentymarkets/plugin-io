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
 *
 * Contains twig filter to get order property names.
 *
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
    ) {
        parent::__construct();

        $this->authHelper = $authHelper;
    }

    /**
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            "propertyName" => "getPropertyName",
            "propertySelectionValueName" => "getPropertySelectionValueName"
        ];
    }

    /**
     * Gets the name of the given order property.
     *
     * @param array $property Order property to get the name for.
     * @param string $lang Language to get the name in. Defaults to current webshop language.
     * @return string
     */
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

        if (isset($property['name'])) {
            return $property['name'];
        } else {
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
        }
        return '';
    }

    /**
     * Gets the name of the given order property from type selection.
     *
     * @param array $property Order property to get the name for.
     * @param string $lang Language to get the name in. Defaults to current webshop language.
     * @return mixed
     */
    public function getPropertySelectionValueName($property, $lang = null)
    {
        $propertyValueDecoded = json_decode($property['value'], true);
        if (is_array($propertyValueDecoded) && count($propertyValueDecoded)) {
            $property['value'] = array_shift($propertyValueDecoded);
        }
        
        if(is_numeric($property['value'])) {
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
        } elseif (strlen($property['value'])) {
            return $property['value'];
        }

        return '';
    }
}
