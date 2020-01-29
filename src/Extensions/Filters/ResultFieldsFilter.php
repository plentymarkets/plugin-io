<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Helper\DataFilter;
use Plenty\Modules\Webshop\ItemSearch\Helper\LoadResultFields;

/**
 * Class ResultFieldsFilter
 * @package IO\Extensions\Filters
 */
class ResultFieldsFilter extends AbstractFilter
{
    use LoadResultFields;

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFilters(): array
    {
        return [
            "filterFields" => "filterFields"
        ];
    }

    public function filterFields($data, $resultFields = null)
    {
        if ( is_null( $resultFields ) )
        {
            return $data;
        }

        /** @var DataFilter $dataFilter */
        $dataFilter = pluginApp( DataFilter::class );
        if ( is_string( $resultFields ) )
        {
            return $dataFilter->getFilteredData( $data, $this->loadResultFields( $resultFields ) );
        }
        else
        {
            return $dataFilter->getFilteredData( $data, $resultFields );
        }
    }
}
