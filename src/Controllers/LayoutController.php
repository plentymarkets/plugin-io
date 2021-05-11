<?php

namespace IO\Controllers;

use IO\Api\ResponseCode;
use IO\Helper\ArrayHelper;
use IO\Helper\CategoryMap;
use IO\Helper\TemplateContainer;
use IO\Helper\Utils;
use IO\Middlewares\CheckNotFound;
use IO\Services\CategoryService;
use IO\Services\NotificationService;
use IO\Services\TemplateService;
use IO\Services\UrlService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\ContentCache\Contracts\ContentCacheRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Twig;

/**
 * Supercall for specific controllers
 * Provide global methods for rendering templates received from separate layout plugin
 * Class LayoutController
 * @package IO\Controllers
 */
abstract class LayoutController extends Controller
{
    use Loggable;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var Dispatcher
     */
    protected $event;

    /**
     * @var CategoryRepositoryContract
     */
    protected $categoryRepo;

    /**
     * @var CategoryMap
     */
    protected $categoryMap;

    /**
     * Used by concrete controllers to set the current category
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * @var UrlService $urlService
     */
    protected $urlService;

    /**
     * @var bool
     */
    private $debug = true;

    /**
     * LayoutController constructor.
     * @param Application $app
     * @param Twig $twig
     * @param Dispatcher $event
     * @param CategoryRepositoryContract $categoryRepo
     * @param CategoryMap $categoryMap
     * @param CategoryService $categoryService
     */
    public function __construct(
        Application $app,
        Twig $twig,
        Dispatcher $event,
        CategoryRepositoryContract $categoryRepo,
        CategoryMap $categoryMap,
        CategoryService $categoryService,
        UrlService $urlService
    ) {
        parent::__construct();
        $this->app = $app;
        $this->twig = $twig;
        $this->event = $event;
        $this->categoryRepo = $categoryRepo;
        $this->categoryMap = $categoryMap;
        $this->categoryService = $categoryService;
        $this->urlService = $urlService;
    }

    /**
     * Abort handling current route and pass request to the plentymarkets system
     * @param int $code
     * @param string $message
     * @return string
     */
    protected function abort(int $code, string $message): string
    {
        $this->getLogger(__CLASS__)->error(
            "IO::Debug.LayoutController_requestAborted.",
            [
                "code" => $code,
                "message" => $message
            ]
        );

        if ($this->debug === false) {
            $this->app->abort($code, $message);
        }
        return $message;
    }

    /**
     * @param string $templateEvent
     * @param mixed $controllerData
     * @return TemplateContainer
     * @throws \ErrorException
     */
    protected function buildTemplateContainer(string $templateEvent, $controllerData = []): TemplateContainer
    {
        return TemplateContainer::get($templateEvent, $controllerData);
    }

    /**
     * Emit an event to layout plugin to receive twig-template to use for current request.
     * Add global template data to custom data from specific controller.
     * Will pass request to the plentymarkets system if no template is provided by the layout plugin.
     *
     * @param string $templateEvent
     * @param mixed $controllerData
     * @param bool $cacheContent
     *
     * @return string
     * @throws \ErrorException
     */
    protected function renderTemplate(string $templateEvent, $controllerData = [], $cacheContent = true): string
    {
        TemplateService::$currentTemplate = $templateEvent;
        $templateContainer = $this->buildTemplateContainer($templateEvent, $controllerData);
        if ($templateContainer->hasTemplate()) {
            TemplateService::$currentTemplate = $templateEvent;
            TemplateService::$currentTemplateData = $controllerData;
            TemplateService::$shouldBeCached = $cacheContent;

            $renderedTemplate = $this->renderTemplateContainer($templateContainer, $controllerData);

            // activate content cache
            $notificationService = pluginApp(NotificationService::class);
            if (TemplateService::$shouldBeCached && !$notificationService->hasNotifications()) {
                $this->getLogger(__CLASS__)->info(
                    "IO::Debug.LayoutController_enableContentCache",
                    [
                        "template" => $templateEvent,
                        "controllerData" => $controllerData,
                    ]
                );
                /** @var ContentCacheRepositoryContract $cacheRepository */
                $cacheRepository = pluginApp(ContentCacheRepositoryContract::class);
                $cacheRepository->enableCacheForResponse(
                    [
                        'enableQueryParams' => true
                    ]
                );
            }

            // Render the received plugin
            return $renderedTemplate;
        } else {
            return $this->abort(404, "Template not found.");
        }
    }

    /**
     * @param TemplateContainer $templateContainer
     * @param mixed $controllerData
     *
     * @return string
     */
    protected function renderTemplateContainer(TemplateContainer $templateContainer, $controllerData)
    {
        if ($templateContainer->getTemplateData() instanceof \Closure) {
            $callback = $templateContainer->getTemplateData();
            $templateData = $callback->call($this);
        } else {
            $templateData = $templateContainer->getTemplateData();
        }

        $templateData = ArrayHelper::toArray($templateData);

        // Render the received plugin
        try {
            return $this->twig->render(
                $templateContainer->getTemplate(),
                $templateData
            );
        } catch (\Exception $e) {
            $this->getLogger(__CLASS__)->error(
                "IO::Debug.LayoutController_cannotRenderTwigTemplate",
                [
                    "templateKey" => $templateContainer->getTemplateKey(),
                    "template" => $templateContainer->getTemplate(),
                    "data" => $templateData
                ]
            );
        }

        return '';
    }

    /**
     * Return a NOT_FOUND response
     *
     * @return Response
     */
    protected function notFound()
    {
        /** @var Response $response */
        $response = pluginApp(Response::class);
        $response->forceStatus(ResponseCode::NOT_FOUND);
        CheckNotFound::$FORCE_404 = true;

        return $response;
    }

    /**
     * @param string $url
     * @return Response|string|null
     */
    protected function checkForExistingCategory($url = null)
    {
        if (is_null($url)) {
            /** @var Request $request */
            $request = pluginApp(Request::class);
            list($url, $queryString) = explode("?", $request->getRequestUri());
        }

        $branch = explode("/", trim($url, "/"));
        $lang = Utils::getLang();
        $webstoreId = Utils::getWebstoreId();
        $category = $this->categoryRepo->findCategoryByUrl(
            $branch[0],
            $branch[1],
            $branch[2],
            $branch[3],
            $branch[4],
            $branch[5],
            $webstoreId,
            $lang
        );

        if ($category instanceof Category) {
            /** @var CategoryController $categoryController */
            $categoryController = pluginApp(CategoryController::class);
            return $categoryController->showCategoryById($category->id);
        }

        return null;
    }
}
