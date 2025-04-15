<?php //strict

namespace IO\Services;
use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Manufacturer\Contracts\ManufacturerRepositoryContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Service Class BrandSearchService
 *
 * This service class contains functions related to manufacturers / brands.
 *
 * @package IO\Services
 */
class BrandSearchService
{

    use Loggable;
               

    /**
     * Check if facet data exists
     * @param string $searchQuery the search query
     * @param array $fields = ['externalName'] the fields of the Manufacturer we should search
     * @return array
     */
    public function getResults($searchQuery, $fields = ['externalName', 'name'])
    {
        if(!is_string($searchQuery) || strlen($searchQuery) < 3 || count($fields) == 0)
        {
            return [];
        }

        $authHelper = pluginApp(AuthHelper::class);
        $manufacturerRepository = pluginApp(ManufacturerRepositoryContract::class);

        // Setup params for search
        $params = [];
        foreach($fields as $field)
        {
            $params[$field] = "%" . $searchQuery . "%";
        }
        
        // Define return columns
        $columns = ['id', 'name', 'externalName', 'url', 'logo'];
        $searchResult = $authHelper->processUnguarded(
            function () use ($manufacturerRepository, $params, $columns) {
                return $manufacturerRepository->search($params, $columns);
            }
        );

        // No Results found
        if($searchResult->getTotalCount() == 0)
        {
            return [];
        }
        // Paginated Result of Manufatcturers -> getResult() is a Collection
        return $searchResult->getResult()->toArray();
    }
}
