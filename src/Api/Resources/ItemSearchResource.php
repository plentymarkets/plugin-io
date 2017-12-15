<?php //strict

namespace IO\Api\Resources;

use IO\Services\ItemLoader\Extensions\TwigLoaderPresets;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\SearchItems;
use IO\Services\ItemLoader\Loaders\Facets;

/**
 * Class ItemSearchResource
 * @package IO\Api\Resources
 */
class ItemSearchResource extends ApiResource
{
    /**
     * ItemSearchResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }
    
    /**
     * Search items
     * @return Response
     */
    public function index():Response
    {
        $searchString = $this->request->get('query', '');
        $template = $this->request->get('template', '');
        
        if(strlen($searchString))
        {
            /** @var TwigLoaderPresets $twigLoaderPresets */
            $twigLoaderPresets = pluginApp(TwigLoaderPresets::class);
            $presets = $twigLoaderPresets->getGlobals();
            
            $response = pluginApp(ItemLoaderService::class)
                ->loadForTemplate($template, $presets['itemLoaderPresets']['search'], [
                    'query'             => $searchString,
                    'page'              => $this->request->get('page', 1),
                    'items'             => $this->request->get('items', 20),
                    'sorting'           => $this->request->get('sorting', 'itemName'),
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