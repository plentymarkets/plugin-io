<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use Plenty\Modules\Account\Address\Models\Address;

/**
 * Class AddressOptionTypeFilter
 *
 * Contains twig filter that gets the value of an option type.
 *
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
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            'addressOptionType' => 'addressOptionType'
        ];
    }

    /**
     * Gets the option type value from the given $address and $typeId.
     *
     * @param array $address Address array to get the option type value from.
     * @param int $typeId Id to get the value from.
     * @return string
     */
    public function addressOptionType(array $address, int $typeId): string
    {
        if (isset($address) && isset($address['options'])) {
            foreach ($address['options'] as $optionType) {
                if ($optionType['typeId'] === $typeId) {
                    return $optionType['value'];
                }
            }
        }

        return '';
    }
}
