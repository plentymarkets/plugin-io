<?php
namespace IO\Services;
use IO\Services\ItemSearch\SearchPresets\ManufacturerItems;
use IO\Services\ItemSearch\Services\ItemSearchService;

class ItemManufacturerService
{
    public function getItemsByManufacturerID($manufacturerId)
    {
        $manufacturerSearchOptions = [
            'manufacturerId' => $manufacturerId,
            'page' => 1,
            'itemsPerPage' => 20
        ];

        /** @var ItemSearchService $itemSearchService */
        $itemSearchService = pluginApp( ItemSearchService::class );
        $result = $itemSearchService->getResults(ManufacturerItems::getSearchFactory( $manufacturerSearchOptions ));
        return $result;
    }
}