<?php //strict

namespace IO\Api\Resources;

use IO\Constants\CategoryType;
use IO\Services\CategoryService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use Plenty\Plugin\Templates\Twig;

/**
 * Class CategoryTreeResource
 * @package IO\Api\Resources
 */
class CategoryTreeResource extends ApiResource
{
    private $categoryService;

    private $customerService;

    private $sessionStorageService;

    /**
     * CategoryTreeResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CategoryService $categoryService
     * @param CustomerService $customerService
     * @param SessionStorageService $sessionStorageService
     */
    public function __construct(Request $request, ApiResponse $response, CategoryService $categoryService, CustomerService $customerService, SessionStorageService $sessionStorageService)
    {
        parent::__construct($request, $response);
        $this->categoryService       = $categoryService;
        $this->customerService       = $customerService;
        $this->sessionStorageService = $sessionStorageService;
    }

    /**
     * Get Category Items
     * @return Response
     */
    public function index():Response
    {
        $type = $this->request->get('type', CategoryType::ALL);
        $categoryId = $this->request->get('categoryId', null);
        $response = $this->categoryService->getPartialTree($categoryId, $type);

        return $this->response->create($response, ResponseCode::OK);
    }

    public function getTemplateForChildren():Response
    {
        /** @var Twig $twig */
        $twig = pluginApp(Twig::class);

        $categoryId = $this->request->get('categoryId', null);
        $currentUrl = $this->request->get('currentUrl', null);

        $partialTree = $this->categoryService->getPartialTree($categoryId);
        $children = $this->findInTree($partialTree, $categoryId);

        $template = "{% import \"Ceres::Category.Macros.CategoryTree\" as Tree %}";
        $template .= "{{ Tree.get_sidemenu(categoryBreadcrumbs, categories, currentUrl, spacingPadding, inlinePadding, openableChildren) }}";

        $renderedTemplate = $twig->renderString($template, [
            "categories" => $children["children"],
            "currentUrl" => $currentUrl,
            "openableChildren" => true
        ]);

        return $this->response->create($renderedTemplate, ResponseCode::OK);
    }

    private function findInTree($tree, $categoryId)
    {
        $result = null;

        foreach ($tree as $category)
        {
            if ($category["id"] == $categoryId)
            {
                $result = $category;
                break;
            }

            if (is_null($result) && count($category["children"]))
            {
                $result = $this->findInTree($category["children"], $categoryId);
            }
        }

        return $result;
    }
}
