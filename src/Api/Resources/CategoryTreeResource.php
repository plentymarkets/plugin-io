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
 *
 * Resource class for the route `io/categorytree`.
 * @package IO\Api\Resources
 */
class CategoryTreeResource extends ApiResource
{
    /**
     * @var CategoryService $categoryService Instance of the CategoryService.
     */
    private $categoryService;

    /**
     * @var CustomerService $customerService Instance of the CustomerService.
     */
    private $customerService;

    /**
     * @var SessionStorageService $sessionStorageService Instance of the SessionStorageService.
     */
    private $sessionStorageService;

    /**
     * CategoryTreeResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param CategoryService $categoryService
     * @param CustomerService $customerService
     * @param SessionStorageService $sessionStorageService
     */
    public function __construct(
        Request $request,
        ApiResponse $response,
        CategoryService $categoryService,
        CustomerService $customerService,
        SessionStorageService $sessionStorageService
    ) {
        parent::__construct($request, $response);
        $this->categoryService = $categoryService;
        $this->customerService = $customerService;
        $this->sessionStorageService = $sessionStorageService;
    }

    /**
     * Get the category tree, beginning with a given categoryId.
     * @return Response
     */
    public function index(): Response
    {
        $type = $this->request->get('type', CategoryType::ALL);
        $categoryId = $this->request->get('categoryId', null);
        $response = $this->categoryService->getPartialTree($categoryId, $type);

        return $this->response->create($response, ResponseCode::OK);
    }

    /**
     * Get the children of a category with a given categoryId.
     * @return Response
     */
    public function getChildren(): Response
    {
        $categoryId = $this->request->get('categoryId', null);
        $indexStart = (int)$this->request->get('indexStart', 0);
        $maxCount = $this->request->get('maxCount', null);

        $partialTree = $this->categoryService->getPartialTree($categoryId);
        $tree = $this->findInTree($partialTree, $categoryId);
        $children = $tree['children'] ?? [];

        if (!is_null($maxCount)) {
            $maxCount = (int)$maxCount;
            $filteredChildren = [];

            for ($i = 0; $i < $maxCount; $i++) {
                $index = $i + $indexStart;

                if (array_key_exists($index, $children)) {
                    $filteredChildren[] = $children[$index];
                }
            }

            return $this->response->create($filteredChildren, ResponseCode::OK);
        }

        return $this->response->create($children, ResponseCode::OK);
    }

    /**
     * Get rendered markup via TWIG for the side navigation.
     * @return Response
     */
    public function getTemplateForChildren(): Response
    {
        /** @var Twig $twig */
        $twig = pluginApp(Twig::class);

        $categoryId = $this->request->get('categoryId', null);
        $currentUrl = $this->request->get('currentUrl', null);
        $showItemCount = $this->request->get('showItemCount', false);
        $showItemCount = (boolean)$showItemCount;
        $spacingPadding = $this->request->get('spacingPadding', '');
        $inlinePadding = $this->request->get('inlinePadding', '');

        $partialTree = $this->categoryService->getPartialTree($categoryId);
        $children = $this->findInTree($partialTree, $categoryId);

        $template = "{% import \"Ceres::Category.Macros.CategoryTree\" as Tree %}";
        $template .= "{{ Tree.get_sidemenu(categoryBreadcrumbs, categories, currentUrl, spacingPadding, inlinePadding, showItemCount, expandableChildren) }}";

        $renderedTemplate = $twig->renderString(
            $template,
            [
                'categories' => $children['children'],
                'currentUrl' => $currentUrl,
                'showItemCount' => $showItemCount,
                'expandableChildren' => true,
                'spacingPadding' => $spacingPadding,
                'inlinePadding' => $inlinePadding
            ]
        );

        return $this->response->create($renderedTemplate, ResponseCode::OK);
    }

    /**
     * Find a category in a tree. Recursive method.
     * @param array $tree Category tree to search.
     * @param int $categoryId ID of the category to find.
     * @return array Found category.
     */
    private function findInTree($tree, $categoryId)
    {
        $result = null;

        foreach ($tree as $category) {
            if ($category['id'] == $categoryId) {
                $result = $category;
                break;
            }

            if (is_null($result) && is_array($category['children']) && count($category['children'])) {
                $result = $this->findInTree($category['children'], $categoryId);
            }
        }

        return $result;
    }
}
