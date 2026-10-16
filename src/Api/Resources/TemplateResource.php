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
 *
 * Resource class for the route `io/template`.
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
     * Return a rendered template via TWIG with given parameters.
     * @return Response
     */
    public function index():Response
    {
        $template = $this->request->get('template', '');
        $params = $this->request->get('params', []);
        $renderedTemplate = '';

        if (!is_array($params))
        {
            $params = [];
        }

        foreach($params as $key => $value)
        {
            // Only replace the value when it is valid JSON. json_decode() returns null on failure
            // (never false), so the previous "!== false" check was always true and turned every
            // non-JSON string into null. Guard on json_last_error() instead.
            if (!is_string($value))
            {
                continue;
            }

            $decoded = json_decode($value);
            if(json_last_error() === JSON_ERROR_NONE)
            {
                $params[$key] = $decoded;
            }
        }

        if (strlen($template) && $this->isRenderableTemplate($template))
        {
            /**
             * @var Twig $twig
             */
            $twig             = pluginApp(Twig::class);
            $renderedTemplate = $twig->render($template, $params);
        }

        return $this->response->create($renderedTemplate, ResponseCode::OK);
    }

    /**
     * Only render namespaced plugin template references (e.g. "Ceres::Checkout.OrderConfirmation").
     * This public route must never be able to resolve a filesystem path, traversal sequence or
     * stream wrapper as a template name (local file inclusion).
     *
     * @param string $template
     * @return bool
     */
    private function isRenderableTemplate($template): bool
    {
        if (!is_string($template) || strpos($template, '::') === false)
        {
            return false;
        }

        return strpos($template, '/') === false
            && strpos($template, '..') === false
            && strpos($template, "\0") === false
            && strpos($template, '://') === false;
    }

}
