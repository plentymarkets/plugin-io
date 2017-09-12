<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\CategoryItems;
use IO\Services\ItemLoader\Loaders\Facets;
use IO\Services\CategoryService;
use IO\Services\SessionStorageService;

/**
 * Class CategoryDescriptionResource
 * @package IO\Api\Resources
 */
class CategoryDescriptionResource extends ApiResource
{
    /**
     * CategoryDescriptionResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Get Category Items
     * @param string $categoryId
     * @return Response
     */
    public function show(string $categoryId):Response
    {
        $categoryService = pluginApp(CategoryService::class);
        $sessionStorageService = pluginApp(SessionStorageService::class);

        $response = $categoryService->get($categoryId, $sessionStorageService->getLang())->details[0]->description;

        return $this->response->create($response, ResponseCode::OK);
    }
}