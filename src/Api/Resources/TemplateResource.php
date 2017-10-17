<?php //strict

namespace IO\Api\Resources;

use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Http\Request;
use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;


/**
 * Class TemplateResource
 * @package IO\Api\Resources
 */
class TemplateResource extends ApiResource
{
    /**
     * TemplateResource constructor.
     * @param Request $request
     * @param ApiResponse $response
     */
    public function __construct(Request $request, ApiResponse $response)
    {
        parent::__construct($request, $response);
    }

    /**
     * Return the given rendered template
     * @return Response
     */
    public function index():Response
    {
        $template = $this->request->get('template', '');
        $params = $this->request->get('params', []);
        $renderedTemplate = '';

        foreach($params as $key => $value)
        {
            $decoded = json_decode($value);
            if($decoded !== false)
            {
                $params[$key] = $decoded;
            }
        }

        if (strlen($template))
        {
            /**
             * @var Twig $twig
             */
            $twig             = pluginApp(Twig::class);
            $renderedTemplate = $twig->render($template, $params);
        }

        return $this->response->create($renderedTemplate, ResponseCode::OK);
    }
    
}