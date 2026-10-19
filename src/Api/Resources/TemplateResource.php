<?php //strict

namespace IO\Api\Resources;

use IO\Api\ApiResource;
use IO\Api\ApiResponse;
use IO\Api\ResponseCode;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;


/**
 * Class TemplateResource
 *
 * Resource class for the route `io/template`.
 * @package IO\Api\Resources
 */
class TemplateResource extends ApiResource
{
    use Loggable;

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
            // (never false), so a "!== false" check would turn every non-JSON string into null.
            // Decode with JSON_THROW_ON_ERROR so invalid JSON throws instead of needing
            // json_last_error() (not an allowed plugin-interface function).
            if (!is_string($value))
            {
                continue;
            }

            try
            {
                $params[$key] = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
            }
            catch (\JsonException $e)
            {
                // leave the original string value untouched
            }
        }

        if (strlen($template) && $this->isRenderableTemplate($template))
        {
            /**
             * @var Twig $twig
             */
            $twig = pluginApp(Twig::class);

            try
            {
                $renderedTemplate = $twig->render($template, $params);
            }
            catch (\Throwable $e)
            {
                // Don't leak the raw exception (app paths, class names) to this anonymous route;
                // log it server-side and return a generic error.
                $this->getLogger(self::class)->error('IO::Debug.TemplateResource_renderFailed', [
                    'template' => $template,
                    'message'  => $e->getMessage(),
                ]);

                return $this->response->create('', ResponseCode::INTERNAL_SERVER_ERROR);
            }
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
