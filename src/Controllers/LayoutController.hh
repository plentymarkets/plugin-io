<?hh //strict

namespace LayoutCore\Controllers;

use Plenty\Plugin\Application;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Templates\Twig;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;

use LayoutCore\Helper\TemplateContainer;
use LayoutCore\Helper\CategoryMap;
use LayoutCore\Helper\CategoryKey;
use LayoutCore\Services\NavigationService;

use LayoutCore\Constants\CategoryType;
use LayoutCore\Constants\Language;
use LayoutCore\Services\CategoryService;

/**
 * Supercall for concrete controllers:
 * Provides global methods for rendering templates received from separate layout plugin.
 */
class LayoutController extends Controller {

    protected Application $app;
    private Twig $twig;
    private Dispatcher $event;
    protected CategoryRepositoryContract $categoryRepo;
    private TemplateContainer $templateContainer;
    protected CategoryMap $categoryMap;

    // used by concrete controllers to set current category
    protected CategoryService $categoryService;

    private bool $debug = true;

    public function __construct( Application $app, Twig $twig, Dispatcher $event, TemplateContainer $templateContainer, CategoryRepositoryContract $categoryRepo, CategoryMap $categoryMap, CategoryService $categoryService )
    {
        $this->app = $app;
        $this->twig = $twig;
        $this->event = $event;
        $this->categoryRepo = $categoryRepo;
        $this->templateContainer = $templateContainer;
        $this->categoryMap = $categoryMap;
        $this->categoryService = $categoryService;
    }

    /**
     * Prepare global template data which should be available in all templates.
     * @param array<string, mixed> $customData Data to pass to template from concrete Controller.
     * @return TemplateContainer
     */
    private function prepareTemplateData( ?array<string, mixed> $customData = null ):TemplateContainer
    {
        $this->templateContainer
            ->setTemplateData( $customData );

        return $this->templateContainer;
    }

    protected function renderCategory( ?Category $category ):string
    {
        if( $category === null ) {
            $category = $this->categoryRepo->get(
                $this->categoryMap->getID( CategoryKey::PAGE_NOT_FOUND )
            );
        }

        if( $category === null ) {
            return $this->abort(404, "Category not found.");
        }

        $this->categoryService->setCurrentCategory( $category );

        return $this->renderTemplate(
            "tpl.category.".$category->type,
            array(
                "category" => $category
            )
        );
    }

    /**
     * Aborts handling current route and passes request to plentymarkets system.
     */
    protected function abort( int $code, string $message ):string {
        if( $this->debug === false ) {
            $this->app->abort($code, $message);
        }
        return $message;
    }

    /**
     * Emits event to layout plugin to receive twig-template to use for current request.
     * Adds global template data to custom data from concrete controller.
     * Will pass request to plentymarkets system if no template is provided by layout plugin.
     * @param string $templateEvent The event to emit to separate layout plugin
     * @param array<string, mixed> Additional template data from concrete controller
     */
    protected function renderTemplate( string $templateEvent, array<string, mixed> $templateData ):string
    {
        // emit event to receive layout to use.
        // Add TemplateContainer and template data from concrete controller to event's payload
        $this->event->fire( $templateEvent, array(
            $this->templateContainer,
            $templateData
        ));

        if( $this->templateContainer->hasTemplate() ) {
            // prepare global data only if template is available
            $this->prepareTemplateData( $templateData );

            // render received plugin
            return $this->twig->render(
                $this->templateContainer->getTemplate(),
                $this->templateContainer->getTemplateData()
            );
        } else {
            return $this->abort(404, "Template not found.");
        }
    }

}
