<?php

namespace IO\Controllers;

use Plenty\Modules\Authorization\Services\AuthHelper;
use Plenty\Modules\Item\Manufacturer\Contracts\ManufacturerRepositoryContract;
use IO\Helper\StringUtils;
use Plenty\Plugin\Log\Loggable;
use IO\Helper\RouteConfig;
use Plenty\Plugin\Http\Request;
use IO\Services\CategoryService;
use IO\Controllers\CategoryController;
use Plenty\Modules\ShopBuilder\Helper\ShopBuilderRequest;

/**
 * Class BrandsListController
 * @package IO\Controllers
 */
class BrandsListController extends LayoutController
{
    use Loggable;
    /**
     * @param string $tagName
     */

    public function showBrandsList($category = null)
    {

        // Check for ?marke request in url.. 
        $request = pluginApp(Request::class);
        $marke = $request->get('marke', null);
        if($marke && intval($marke) > 0) {
            $url = $this->getManufacturerUrl($marke);
            if($url) {
                // Redirect to the manufacturer URL
                $this->getLogger(__CLASS__)->error("Redirect Brand",
                [
                    "url" => $url,
                    "manufacturerId" => $marke
                ]);
                return $this->urlService->redirectTo($url);
            }
        }


        $authHelper = pluginApp(AuthHelper::class);
        $manufacturerRepo = pluginApp(ManufacturerRepositoryContract::class);

        $outputList = [];
        $page = 1;
        $lastPage = 2;
        while($page <= $lastPage)
        {
            $manufacturerDataRaw = $paginatedResult = $authHelper->processUnguarded(
                function () use ($manufacturerRepo, $page) {
                    return $manufacturerRepo->all(['id', 'name', 'externalName', 'url', 'logo', 'position'], 100, $page); // limit = 500
                }
            );
            $lastPage = $paginatedResult->getLastPage();

            // $manufacturerData = $manufacturerDataRaw->getResult()->sortBy('name', SORT_NATURAL)->toArray();
            $manufacturerData = $manufacturerDataRaw->getResult()->toArray();
            foreach ($manufacturerData as $manufacturer) {
                // Ignore inactive manufacturers
                if (substr($manufacturer['name'], 0, 1) == "_")
                    continue;
                
                // Ognore manufacturers for naturwohnen
                if (strpos($manufacturer['name'], "Naturwohnen") !== false)
                    continue;
                
                // Check & optionally regenerate URL
                // If the URL is empty or contains "http", we need to regenerate it.
                $url = $this->checkManufacturerUrl($manufacturer);
               
                $newManufacturerDataset = [
                    'id' => $manufacturer['id'],
                    'name' => StringUtils::removeSpecialChars($manufacturer['externalName']),
                    'logo' => $manufacturer['logo'],
                    'url' => $url,
                    'position' => $manufacturer['position']
                ];
            
                $outputList[] = $newManufacturerDataset;
            }
            $page++;
        }

        // Use url to resort brands,
        // as the url is already clean and without umlauts 
        // or similar problematic things for sorting
        usort($outputList, function ($a, $b) {
            return strcmp($a['url'], $b['url']);
        });

        $grouped = [];
        foreach($outputList as $brand)
        {
            if(!strlen($brand['name']))
                continue;
            
            $firstLetter = strtoupper(substr($brand['url'], 0, 1));
            
            if(is_numeric($firstLetter))
                $firstLetter = "#";
            
            if(!isset($grouped[$firstLetter]))
                $grouped[$firstLetter] = [];
            
            $grouped[$firstLetter][] = $brand;
        }     
    

        if(!is_null($category)) {
            $categoryService = pluginApp(CategoryService::class);
            $categoryService->setCurrentCategory($category);

            /** @var ShopBuilderRequest $shopBuilderRequest */
            $shopBuilderRequest = pluginApp(ShopBuilderRequest::class);
            $shopBuilderRequest->setMainContentType('content');
            $shopBuilderRequest->setMainCategory($category->id);     
        }

        return $this->renderTemplate(
            'tpl.brands_list',
            [
                'category' => $category,
                'sorting' => $request->get('sorting', null),
                'itemsPerPage' => $request->get('items', null),
                'page' => $request->get('page', null),
                'facets' => $request->get('facets', ''),
                'brandsList' => $outputList,
                'groupedBrandsList' => $grouped,
            ],
            false
        );
    }

    public function checkManufacturerUrl($manufacturer)
    {
        $url = $manufacturer['url'];
        if (empty($url) || strpos($url, "http") !== false) {
            return $this->regenerateBrandUrl($manufacturer);
        }
        return $url;
    }

    public function regenerateBrandUrl($brand)
    {
        $brandId = $brand['id'];
        $brandName = $brand['externalName'];
        $urlName = StringUtils::string4URL($brandName);

        $manufacturerRepo = pluginApp(ManufacturerRepositoryContract::class);
        $authHelper = pluginApp(AuthHelper::class);
        $updatedManufacturer = $authHelper->processUnguarded(
            function () use ($manufacturerRepo, $urlName, $brandId) {
                return $manufacturerRepo->update(['url' => $urlName], $brandId);   
            }
        );
      
        $this->getLogger(__CLASS__)
            ->addReference("manufacturerId", $brandId)
            ->addReference("manufacturerName", $brandName)
            ->info(
            "IO::Debug.BrandListController_UpdatedUrl",
            [
                "brandName" => $brandName,
                "brandId" => $brandId,
                "url" => $urlName,
                "updatedManufacturer" => $updatedManufacturer
            ]
        );
        return $urlName;
    }

    public function getManufacturerUrl($manufacturerId)
    {
        $manufacturerRepo = pluginApp(ManufacturerRepositoryContract::class);
        $authHelper = pluginApp(AuthHelper::class);
        $manufacturer = $authHelper->processUnguarded(
            function () use ($manufacturerRepo, $manufacturerId) {
                return $manufacturerRepo->findById($manufacturerId);
            }
        );

        if (!$manufacturer) {
            return null;
        }
        // Check if the URL is already set
        $url = $this->checkManufacturerUrl($manufacturer);

        if ($url) {
            // If the URL is set, return the URL
            return RouteConfig::BRANDS_LIST . "/" . $url . "-" . $manufacturerId . "/";
        }
        return null;
    }


    public function redirect()
    {

        if (!is_null($categoryByUrl = $this->checkForExistingCategory())) {
            return $categoryByUrl;
        }

        /** @var CategoryController $categoryController */
        $categoryController = pluginApp(CategoryController::class);
        return $categoryController->redirectRoute(RouteConfig::BRANDS_LIST);
    }

    public function showBrand(string $brandSlug = "")
    {
        preg_match('/^(.*)-(\d+)$/', $brandSlug, $matches);

        $urlPart = $matches[1]; // "that-s-mine"
        $manufacturerId = (int) $matches[2]; // 4

        $authHelper = pluginApp(AuthHelper::class);
        $manufacturerRepo = pluginApp(ManufacturerRepositoryContract::class);
        $manufacturer = $authHelper->processUnguarded(
            function () use ($manufacturerRepo, $manufacturerId) {
                return $manufacturerRepo->findById($manufacturerId);
            }
        );

        # Set parent category to brandsList /for breadcrumb)
        $brandsListCategoryId = RouteConfig::getCategoryId(RouteConfig::BRANDS_LIST);
        if ($brandsListCategoryId > 0) {
            $categoryService = pluginApp(CategoryService::class);
            $categoryService->setCurrentCategoryID($brandsListCategoryId);
        }

        /** @var Request $request */
        $request = pluginApp(Request::class);

        return $this->renderTemplate(
            "tpl.search",
            [
                'category' => null,
                'isManufacturer' => true,
                'manufacturer' => $manufacturer,
                'manufacturerId' => $manufacturerId,
                'page' => $request->get('page', null),
                'itemsPerPage' => $request->get('items', null),
                'query' => $request->get('query', null),
                'sorting' => $request->get('sorting', null),
                'facets' => $request->get('facets', '')
            ],
            true
        );
    }

}
