<?php //strict

namespace IO\Api\Resources;

use IO\Models\LocalizedOrder;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use IO\Services\OrderTotalsService;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use IO\Services\TemplateService;

/**
 * Class OrderTemplateResource
 * @package IO\Api\Resources
 */
class OrderTemplateResource extends ApiResource
{
    /**
     * @var OrderRepositoryContract
     */
    private $orderRepository;
    
    /**
     * @var TemplateService
     */
    private $templateService;
    
    /**
     * OrderTemplateResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     * @param OrderRepositoryContract $orderRepository
     * @param TemplateService $templateService
     */
    public function __construct(Request $request, ApiResponse $response, OrderRepositoryContract $orderRepository, TemplateService $templateService)
    {
        parent::__construct($request, $response);
        
        $this->orderRepository = $orderRepository;
        $this->templateService = $templateService;
    }
    
    /**
     * Return the given rendered order template
     * @return Response
     */
    public function index():Response
    {
        $renderedTemplate = '';
        
        $template = $this->request->get('template', '');
        $orderId = $this->request->get('orderId', 0);
        
        if((int)$orderId > 0)
        {
            $order = $this->orderRepository->findOrderById($orderId);
            if($order instanceof Order)
            {
                $renderedTemplate = $this->templateService->renderTemplate($template, [
                    'orderData' => LocalizedOrder::wrap($order, 'de'),
                    'totals' => pluginApp(OrderTotalsService::class)->getAllTotals($order)
                ]);
            }
        }
        
        return $this->response->create($renderedTemplate, ResponseCode::OK);
    }
    
}