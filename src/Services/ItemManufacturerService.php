<?php
namespace IO\Services;
use IO\Services\ItemSearch\SearchPresets\SearchItemManufacturer;

class ItemManufacturerService
{
    public function getItemsByManufacturerID($manufacturerId)
    {
        $manufacturerSearchOptions = [
            'manufacturerId' => $manufacturerId,
            'page' => 1,
            'itemsPerPage' => 20
        ];
        /** @var SearchItemManufacturer $searchItemManufacturer */
        $searchItemManufacturer = pluginApp(SearchItemManufacturer::class);
        $searchItemManufacturer->getSearchFactory($manufacturerSearchOptions);
    }
}
