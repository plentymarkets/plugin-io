<?php //strict

namespace IO\Controllers;

use IO\Services\TemplateService;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Category\Models\Category;

use IO\Helper\TemplateContainer;
use IO\Helper\CategoryMap;
use IO\Helper\CategoryKey;
use IO\Services\NavigationService;

use IO\Constants\CategoryType;
use IO\Constants\Language;
use IO\Services\CategoryService;

/**
 * Supercall for specific controllers
 * Provide global methods for rendering templates received from separate layout plugin
 * Class LayoutController
 * @package IO\Controllers
 */
class LayoutController extends Controller
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
	private $event;

	/**
	 * @var CategoryRepositoryContract
	 */
	protected $categoryRepo;
	/**
	 * @var TemplateContainer
	 */
	private $templateContainer;

	protected $categoryMap;

	// Used by concrete controllers to set the current category
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
     * @param TemplateContainer $templateContainer
     * @param CategoryRepositoryContract $categoryRepo
     * @param CategoryMap $categoryMap
     * @param CategoryService $categoryService
     */
	public function __construct(Application $app, Twig $twig, Dispatcher $event, TemplateContainer $templateContainer, CategoryRepositoryContract $categoryRepo, CategoryMap $categoryMap, CategoryService $categoryService)
	{
		$this->app               = $app;
		$this->twig              = $twig;
		$this->event             = $event;
		$this->categoryRepo      = $categoryRepo;
		$this->templateContainer = $templateContainer;
		$this->categoryMap       = $categoryMap;
		$this->categoryService   = $categoryService;
	}

	/**
	 * Prepare global template data which should be available in all templates
	 * @param array $customData Data to pass to template from concrete Controller.
	 * @return TemplateContainer
	 */
	private function prepareTemplateData( $customData = null ):TemplateContainer
	{
		$this->templateContainer
			->setTemplateData($customData);

		return $this->templateContainer;
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
			return $this->abort(404, "Category not found.");
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
	 * Emit an event to layout plugin to receive twig-template to use for current request.
	 * Add global template data to custom data from specific controller.
	 * Will pass request to the plentymarkets system if no template is provided by the layout plugin.
	 * @param string $templateEvent     The event to emit to separate layout plugin
	 * @param array $templateData       Additional template data from concrete controller
	 * @return string
	 */
	protected function renderTemplate(string $templateEvent, array $templateData = array() ):string
	{
		// Emit event to receive layout to use.
		// Add TemplateContainer and template data from specific controller to event's payload
		$this->event->fire($templateEvent, [
			$this->templateContainer,
			$templateData
		]);

		if($this->templateContainer->hasTemplate())
		{
            TemplateService::$currentTemplate = $templateEvent;

			// Prepare the global data only if the template is available
			$this->prepareTemplateData($templateData);

			// Render the received plugin
			return $this->twig->render(
				$this->templateContainer->getTemplate(),
				$this->templateContainer->getTemplateData()
			);
		}
		else
		{
			return $this->abort(404, "Template not found.");
		}
	}

}
