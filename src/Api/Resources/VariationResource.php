<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\ItemLoader\Services\ItemLoaderService;
use IO\Services\ItemLoader\Loaders\SingleItem;
use IO\Services\ItemLoader\Loaders\SingleItemAttributes;

/**
 * Class VariationResource
 * @package IO\Api\Resources
 */
class VariationResource extends ApiResource
{
    /**
     * VariationResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param ItemService $itemService
     */
    public function __construct(
        Request $request,
        ApiResponse $response )
    {
        parent::__construct( $request, $response );
    }

    /**
     * Get variation by id
     * @param string $variationId
     * @return Response
     */
    public function show( string $variationId ):Response
    {
        $variation = [];
        
        $template = $this->request->get('template', '');
        if(strlen($template))
        {
            $variation = pluginApp(ItemLoaderService::class)->loadForTemplate($template, [SingleItem::class, SingleItemAttributes::class], ['variationId' => (int)$variationId]);
        }
        
        return $this->response->create($variation, ResponseCode::OK);
    }
}
