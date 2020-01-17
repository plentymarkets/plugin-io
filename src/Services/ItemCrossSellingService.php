<?php

namespace IO\Services;

use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Webshop\ItemSearch\Helper\SortingHelper;
use Plenty\Modules\Cloud\ElasticSearch\Lib\ElasticSearch;
use Plenty\Modules\Webshop\Template\Contracts\TemplateConfigRepositoryContract;

/**
 * Class ItemCrossSellingService
 * @package IO\Services
 */
class ItemCrossSellingService
{
    /** @var SessionStorageRepositoryContract $sessionStorageRepository */
    private $sessionStorageRepository;

    /** @var TemplateConfigRepositoryContract */
    private $templateConfigRepository;

    /** @var SortingHelper */
    private $sortingHelper;

    /**
     * ItemLastSeenService constructor.
     * @param SessionStorageRepositoryContract $sessionStorage
     * @param TemplateConfigRepositoryContract $templateConfigRepository
     * @param SortingHelper $sortingHelper
     */
    public function __construct(
        SessionStorageRepositoryContract $sessionStorageRepository,
        TemplateConfigRepositoryContract $templateConfigRepository,
        SortingHelper $sortingHelper
    ) {
        $this->sessionStorageRepository = $sessionStorageRepository;
        $this->templateConfigRepository = $templateConfigRepository;
        $this->sortingHelper = $sortingHelper;
    }

    /**
     * @param string $type
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

    public function getType()
    {
        return $this->templateConfigRepository->get('item.lists.cross_selling_type', 'Similar');
    }

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

    public function getSorting()
    {
        $sorting = $this->templateConfigRepository->get('item.lists.cross_selling_sorting');

        if (is_null($sorting) || !strlen($sorting)) {
            $sorting = 'texts.' . $this->sortingHelper->getUsedItemName() . '_' . ElasticSearch::SORTING_ORDER_ASC;
        }

        return $sorting;
    }
}
