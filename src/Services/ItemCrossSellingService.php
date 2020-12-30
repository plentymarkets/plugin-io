<?php

namespace IO\Services;

use IO\Helper\Utils;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helpers\SortingHelper;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;

/**
 * Service Class ItemCrossSellingService
 *
 * This service class contains functions that are related to cross selling of items.
 * All public functions are available in the Twig template renderer.
 *
 * @package IO\Services
 */
class ItemCrossSellingService
{
    /** @var SessionStorageRepositoryContract $sessionStorageRepository */
    private $sessionStorageRepository;

    /** @var SortingHelper */
    private $sortingHelper;

    /**
     * ItemLastSeenService constructor.
     * @param SessionStorageRepositoryContract $sessionStorageRepository
     * @param SortingHelper $sortingHelper
     */
    public function __construct(
        SessionStorageRepositoryContract $sessionStorageRepository,
        SortingHelper $sortingHelper
    )
    {
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->sortingHelper = $sortingHelper;
    }

    /**
     * Set the type of cross selling
     * @param string $type Type of cross selling
     */
    public function setType($type)
    {
        if (strlen($type)) {
            $this->sessionStorageRepository->setSessionValue(
                SessionStorageRepositoryContract::CROSS_SELLING_TYPE,
                $type
            );
        }
    }

    /**
     * Get the type of cross selling
     * @return mixed|null
     */
    public function getType()
    {
        return Utils::getTemplateConfig('item.lists.cross_selling_type', 'Similar');
    }

    /**
     * Set the sorting for cross selling
     * @param string $sorting Sorting type for cross selling
     */
    public function setSorting($sorting)
    {
        if (!strlen($sorting)) {
            $sorting = 'texts.' . $this->sortingHelper->getUsedItemName() . '_' . ElasticSearch::SORTING_ORDER_ASC;
        }

        $this->sessionStorageRepository->setSessionValue(
            SessionStorageRepositoryContract::CROSS_SELLING_SORTING,
            $sorting
        );
    }

    /**
     * Get the sorting for cross selling
     * @return string
     */
    public function getSorting()
    {
        $sorting = Utils::getTemplateConfig('item.lists.cross_selling_sorting');

        if (is_null($sorting) || !strlen($sorting)) {
            $sorting = 'texts.' . $this->sortingHelper->getUsedItemName() . '_' . ElasticSearch::SORTING_ORDER_ASC;
        }

        return $sorting;
    }
}
