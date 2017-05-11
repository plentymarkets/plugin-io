<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\SearchItems;

/**
 * Class ItemSearchResource
 * @package IO\Api\Resources
 */
class ItemSearchAutocompleteResource extends ApiResource
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
        
        if(strlen($searchString))
        {
            $template = $this->request->get('template', '');
            
            $response = pluginApp(ItemLoaderService::class)
                ->loadForTemplate($template, [SearchItems::class], [
                    'query'             => $searchString,
                    'autocomplete'      => true,
                    'page'              => 1,
                    'items'             => 20,
                    'variationShowType' => $this->request->get('variationShowType', 'all'),
                ]);
            
            return $this->response->create($response, ResponseCode::OK);
        }
        else
        {
            return $this->response->error(1, '');
        }
        
    }
}