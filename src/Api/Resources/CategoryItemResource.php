<?php //strict

namespace IO\Api\Resources;

use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\CategoryItems;
use IO\Services\ItemLoader\Loaders\Facets;

/**
 * Class CategoryItemResource
 * @package IO\Api\Resources
 */
class CategoryItemResource extends ApiResource
{
    /**
     * CategoryItemResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Get Category Items
     * @return Response
     */
    public function index():Response
    {
        $categoryId = $this->request->get('categoryId', 0);
        $template = $this->request->get('template', '');
        
        if((int)$categoryId > 0)
        {
            /** @var TwigLoaderPresets $twigLoaderPresets */
            $twigLoaderPresets = pluginApp(TwigLoaderPresets::class);
            $presets = $twigLoaderPresets->getGlobals();
            
            $response = pluginApp(ItemLoaderService::class)
                ->loadForTemplate($template, $presets['itemLoaderPresets']['categoryList'], [
                    'categoryId'        => $this->request->get('categoryId', 1),
                    'page'              => $this->request->get('page', 1),
                    'items'             => $this->request->get('items', 20),
                    'sorting'           => $this->request->get('sorting', ''),
                    'facets'            => $this->request->get('facets', ''),
                    'variationShowType' => $this->request->get('variationShowType', ''),
                ]);
            
            return $this->response->create($response, ResponseCode::OK);
        }
        else
        {
            return $this->response->error(1, '');
        }
        
    }
}