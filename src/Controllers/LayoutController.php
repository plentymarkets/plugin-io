<?php
namespace IO\Controllers;

use IO\Helper\CategoryKey;
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
		$this->app             = $app;
		$this->twig            = $twig;
		$this->event           = $event;
		$this->categoryRepo    = $categoryRepo;
		$this->categoryMap     = $categoryMap;
		$this->categoryService = $categoryService;
	}

	/**
	 * Prepare global template data which should be available in all templates
	 * @param TemplateContainer $templateContainer
	 * @param array $customData Data to pass to template from concrete Controller.
	 * @return TemplateContainer
	 */
	protected function prepareTemplateData(TemplateContainer $templateContainer, $customData = null):TemplateContainer
	{
		$templateContainer->setTemplateData($customData);

		return $templateContainer;
	}

	/**
	 * Render the category data
	 * @param $category
	 * @return string
	 */
	protected function renderCategory($category):string
	{
		if($category === null)
		{
			$category = $this->categoryRepo->get(
				(int)$this->categoryMap->getID(CategoryKey::PAGE_NOT_FOUND)
			);
		}

		if($category === null)
		{
            return '';
		}

		$this->categoryService->setCurrentCategory($category);

		return $this->renderTemplate(
			"tpl.category." . $category->type,
			[
				"category" => $category
			]
		);
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
	 * @param string $templateEvent
	 * @param array $templateData
	 * @return TemplateContainer
	 */
	protected function buildTemplateContainer(string $templateEvent, array $templateData = []):TemplateContainer
	{
		/** @var TemplateContainer $templateContainer */
		$templateContainer = pluginApp(TemplateContainer::class);
		$templateContainer->setTemplateKey($templateEvent);
		
		// Emit event to receive layout to use.
		// Add TemplateContainer and template data from specific controller to event's payload
		$this->event->fire('IO.' . $templateEvent, [
			$templateContainer,
			$templateData
		]);
		
		if($templateContainer->hasTemplate())
		{
			TemplateService::$currentTemplate = $templateEvent;
			
			// Prepare the global data only if the template is available
			$this->prepareTemplateData($templateContainer, $templateData);
		}
		return $templateContainer;
	}

	/**
	 * Emit an event to layout plugin to receive twig-template to use for current request.
	 * Add global template data to custom data from specific controller.
	 * Will pass request to the plentymarkets system if no template is provided by the layout plugin.
	 * @param string $templateEvent The event to emit to separate layout plugin
	 * @param array $templateData Additional template data from concrete controller
	 * @return string
	 */
	protected function renderTemplate(string $templateEvent, array $templateData = []):string
	{
		$templateContainer = $this->buildTemplateContainer($templateEvent, $templateData);
		
		if($templateContainer->hasTemplate())
		{
			TemplateService::$currentTemplate = $templateEvent;

			// Prepare the global data only if the template is available
			$this->prepareTemplateData($templateContainer, $templateData);


			// Render the received plugin
			return $this->renderTemplateContainer($templateContainer);
		}
		else
		{
			return $this->abort(404, "Template not found.");
		}
	}

	/**
	 * @param TemplateContainer $templateContainer
	 * @return string
	 */
	protected function renderTemplateContainer(TemplateContainer $templateContainer)
	{
		// Render the received plugin
		return $this->twig->render(
			$templateContainer->getTemplate(),
			$templateContainer->getTemplateData()
		);
	}

}
