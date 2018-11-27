<?php //strict
namespace IO\Api\Resources;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Category\Models\Category;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
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
        $response = [];

        $description1 = (int)$this->request->get('description1', 0);
        $description2 = (int)$this->request->get('description2', 0);

        $categoryService = pluginApp(CategoryService::class);
        $sessionStorageService = pluginApp(SessionStorageService::class);

        $category = $categoryService->get($categoryId, $sessionStorageService->getLang());

        if($category instanceof Category)
        {
            if($description1)
            {
                $response['description1'] = $category->details[0]->description;
            }

            if($description2)
            {
                $response['description2'] = $category->details[0]->description2;
            }
        }

        return $this->response->create($response, ResponseCode::OK);
    }
}
