<?php //strict
namespace IO\Api\Resources;
use IO\Helper\Utils;
use Plenty\Modules\Category\Models\CategoryDetails;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Category\Models\Category;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\CategoryService;

/**
 * Class CategoryDescriptionResource
 *
 * Resource class for the route `io/category/description`.
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
     * Get the description1 and description2 of a category by categoryId.
     * @param string $categoryId
     * @return Response
     */
    public function show(string $categoryId):Response
    {
        $response = [];

        $description1 = (int)$this->request->get('description1', 0);
        $description2 = (int)$this->request->get('description2', 0);

        $categoryService = pluginApp(CategoryService::class);

        $category = $categoryService->get($categoryId, Utils::getLang());

        if($category instanceof Category)
        {
            $categoryDetails = $categoryService->getDetails($category, Utils::getLang());

            if($categoryDetails instanceof CategoryDetails)
            {
                if($description1)
                {
                    $response['description1'] = $categoryDetails->description;
                }

                if($description2)
                {
                    $response['description2'] = $categoryDetails->description2;
                }
            }
        }

        return $this->response->create($response, ResponseCode::OK);
    }
}
