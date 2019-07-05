<?php //strict

namespace IO\Api\Resources;

use IO\Services\CategoryService;
use IO\Services\CustomerService;
use IO\Services\SessionStorageService;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;

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
     */
    public function __construct(Request $request, ApiResponse $response, CategoryService $categoryService, CustomerService $customerService, SessionStorageService $sessionStorageService)
    {
        parent::__construct($request, $response);
        $this->categoryService = $categoryService;
        $this->customerService = $customerService;
        $this->sessionStorageService = $sessionStorageService;
    }

    /**
     * Get Category Items
     * @return Response
     */
    public function index():Response
    {
        $type = $this->request->get('type', 'all');
        $categoryId = $this->request->get('categoryId', null);

        $response = $this->categoryService->getNavigationTree($type, $this->sessionStorageService->getLang(), 6, $this->customerService->getContactClassId());

        if($categoryId > 0)
        {
            $response = $this->categoryService->filterPartialCategoryTree($response, $categoryId);
        }
        return $this->response->create($response, ResponseCode::OK);
    }
}
