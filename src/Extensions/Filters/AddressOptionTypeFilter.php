<?php  //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use Plenty\Modules\Account\Address\Models\Address;

/**
 * Class AddressOptionTypeFilter
 * @package IO\Extensions\Filters
 */
class AddressOptionTypeFilter extends AbstractFilter
{
    /**
     * AddressOptionTypeFilter constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            'addressOptionType' => 'addressOptionType'
        ];
    }

    public function addressOptionType(array $address, int $typeId): string
    {
        if (isset($address) && isset($address['options']))
        {
            foreach ($address['options'] as $optionType)
            {
                if ($optionType['typeId'] === $typeId)
                {
                    return $optionType['value'];
                }
            }
        }

        return '';
    }
}

