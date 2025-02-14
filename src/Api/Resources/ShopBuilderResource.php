<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ShopBuilderService;
use IO\Helper\Utils;


/**
 * Class ShopBuilderResource
 * @package IO\Api\Resources
 */
class ShopBuilderResource extends ApiResource
{
    /**
     * @var ShopBuilderService $shopBuilderService The instance of the current ShopBuilderService.
     */
    private $shopBuilderService;
    private $cacheKey = 'category_sb_content_';
    private $cache_age_in_minutes = 360; // 6 hours

    /**
     * ShopBuilderResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ShippingService $shippingService
     */
    public function __construct(Request $request, ApiResponse $response, ShopBuilderService $shopBuilderService)
    {
        parent::__construct($request, $response);
        $this->shopBuilderService = $shopBuilderService;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {        
        $markup = null;
        $categoryId = $this->request->get('categoryId', null);
        if(is_null($categoryId))
        {
            // no categoryId given
            return $this->response->create(null, ResponseCode::BAD_REQUEST);
        }

        $lang = Utils::getLang();

        // cache key for the content
        // key: category_sb_content_1299_de
        $cacheKey = $this->cacheKey . $categoryId . '_' . $lang; 

        // cache key for updated-at
        // key: category_sb_content_1299_updated_at
        $cachedUpdateKey = $this->cacheKey . $categoryId . '_updated_at'; 
        
        $updatedAt = $this->shopBuilderService->getContentUpdatedAt($categoryId);
        $cachedUpdatedAt = Utils::getCacheKey($cachedUpdateKey, null);

        // if updated-at cache is older than actual updated at - update cache
        if($cachedUpdatedAt == null || $updatedAt != $cachedUpdatedAt)
        {
            Utils::putCacheKey($cachedUpdateKey, $updatedAt, $this->cache_age_in_minutes);

            $markup = $this->shopBuilderService->getContent($categoryId);
            Utils::putCacheKey($cacheKey, $markup, $this->cache_age_in_minutes);
        } else {
            $markup = Utils::getCacheKey($cacheKey, null);
        }
       
        return $this->response->create($markup, ResponseCode::OK);
    }

}