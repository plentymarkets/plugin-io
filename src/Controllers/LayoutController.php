<?php

namespace IO\Controllers;

use IO\Helper\ContextInterface;
use IO\Helper\ArrayHelper;
use IO\Helper\CategoryMap;
use IO\Helper\TemplateContainer;
use IO\Services\CategoryService;
use IO\Services\TemplateService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Plugin\Templates\Twig;

/**
 * Supercall for specific controllers
 * Provide global methods for rendering templates received from separate layout plugin
 * Class LayoutController
 * @package IO\Controllers
 */
abstract class LayoutController extends Controller
{

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
	public function __construct(Application $app, Twig $twig, Dispatcher $event, CategoryRepositoryContract $categoryRepo, CategoryMap $categoryMap, CategoryService $categoryService)
	{
	    parent::__construct();
		$this->app             = $app;
		$this->twig            = $twig;
		$this->event           = $event;
		$this->categoryRepo    = $categoryRepo;
		$this->categoryMap     = $categoryMap;
		$this->categoryService = $categoryService;
	}

	/**
	 * Abort handling current route and pass request to the plentymarkets system
	 * @param int $code
	 * @param string $message
	 * @return string
	 */
	protected function abort(int $code, string $message):string
	{
		if($this->debug === false)
		{
			$this->app->abort($code, $message);
		}
		return $message;
	}
	
    /**
     * @param string    $templateEvent
     * @param mixed     $controllerData
     * @return TemplateContainer
     * @throws \ErrorException
     */
	protected function buildTemplateContainer(string $templateEvent, $controllerData = []):TemplateContainer
	{
		/** @var TemplateContainer $templateContainer */
		$templateContainer = pluginApp(TemplateContainer::class);
		$templateContainer->setTemplateKey($templateEvent);
		
		// Emit event to receive layout to use.
		// Add TemplateContainer and template data from specific controller to event's payload
		$this->event->fire('IO.' . $templateEvent, [
			$templateContainer,
            $controllerData
		]);

		$contextEvent = 'ctx' . substr($templateEvent, 3);
		$this->event->fire( 'IO.' . $contextEvent, [$templateContainer]);
        
        return $templateContainer;
	}
    
    /**
     * Emit an event to layout plugin to receive twig-template to use for current request.
     * Add global template data to custom data from specific controller.
     * Will pass request to the plentymarkets system if no template is provided by the layout plugin.
     *
     * @param string    $templateEvent
     * @param mixed     $controllerData
     *
     * @return string
     */
	protected function renderTemplate(string $templateEvent, $controllerData = []):string
	{
        TemplateService::$currentTemplate = $templateEvent;
		$templateContainer = $this->buildTemplateContainer($templateEvent, $controllerData);
		
		if($templateContainer->hasTemplate())
		{
			TemplateService::$currentTemplate = $templateEvent;
            
            // Render the received plugin
			return $this->renderTemplateContainer($templateContainer, $controllerData);
		}
		else
		{
			return $this->abort(404, "Template not found.");
		}
	}

    /**
     * @param TemplateContainer $templateContainer
     * @param mixed             $controllerData
     *
     * @return string
     */
	protected function renderTemplateContainer(TemplateContainer $templateContainer, $controllerData )
	{
	    if ( $templateContainer->getTemplateData() instanceof \Closure )
        {
            $callback = $templateContainer->getTemplateData();
            $templateData = $callback->call($this);
        }
        else
        {
            $templateData = $templateContainer->getTemplateData();
        }

        $templateData = ArrayHelper::toArray( $templateData );

        $context = pluginApp( $templateContainer->getContext() );

	    if ( $context instanceof ContextInterface )
        {
            $context->init( $controllerData );
            $context = ArrayHelper::toArray( $context );
        }

        $twigData = array_merge( $context, $templateData );

		// Render the received plugin
		return $this->twig->render(
			$templateContainer->getTemplate(),
            $twigData
		);
	}

}
