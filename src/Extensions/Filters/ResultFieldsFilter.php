<?php //strict

namespace IO\Extensions\Filters;

use Exception;
use IO\Extensions\AbstractFilter;
use IO\Helper\DataFilter;
use Plenty\Modules\Webshop\ItemSearch\Helpers\LoadResultFields;

/**
 * Class ResultFieldsFilter
 *
 * Contains twig filter that allows to filter object fields.
 *
 * @package IO\Extensions\Filters
 */
class ResultFieldsFilter extends AbstractFilter
{
    use LoadResultFields;

    /**
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            "filterFields" => "filterFields"
        ];
    }

    /**
     * Gets filtered object based on the given result fields.
     *
     * @param array $data Object to filter.
     * @param null $resultFields Fields that the final object should contain.
     * @return array
     * @throws Exception
     */
    public function filterFields($data, $resultFields = null)
    {
        if (is_null($resultFields)) {
            return $data;
        }

        /** @var DataFilter $dataFilter */
        $dataFilter = pluginApp(DataFilter::class);
        if (is_string($resultFields)) {
            return $dataFilter->getFilteredData($data, $this->loadResultFields($resultFields));
        } else {
            return $dataFilter->getFilteredData($data, $resultFields);
        }
    }
}
