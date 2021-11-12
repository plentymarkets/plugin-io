<?php //strict

namespace IO\Extensions;

use IO\Helper\ArrayHelper;
use IO\Helper\ContextInterface;
use IO\Helper\EventDispatcher;
use IO\Helper\TemplateContainer;
use IO\Services\TemplateService;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\Templates\Extensions\Twig_Extension;

class TwigTemplateContextExtension extends Twig_Extension
{
    use Loggable;

    /**
     * Return the name of the extension. The name must be unique.
     *
     * @return string The name of the extension
     */
    public function getName(): string
    {
        return "IO_Extension_TwigTemplateContextExtensions";
    }

    /**
     * Return a list of filters to add.
     *
     * @return array The list of filters to add.
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * Return a list of functions to add.
     *
     * @return array the list of functions to add.
     */
    public function getFunctions(): array
    {
        return [];
    }

    /**
     * Return a list of global variables
     * @return array
     */
    public function getGlobals(): array
    {
        $contextEvent = 'ctx.default';
        if (strlen(TemplateService::$currentTemplate)) {
            $contextEvent = 'ctx' . substr(TemplateService::$currentTemplate, 3);
        }

        /** @var TemplateContainer $templateContainer */
        $templateContainer = pluginApp(TemplateContainer::class);
        EventDispatcher::fire($contextEvent, [$templateContainer]);

        $contextClass = $templateContainer->getContext();
        if (strlen($contextClass)) {
            $context = pluginApp($contextClass);
            if ($context instanceof ContextInterface) {
                try {
                    $context->init(TemplateService::$currentTemplateData);
                } catch (\Exception $exception) {
                    $this->getLogger(__CLASS__)->logException($exception);
                    return [];
                }
            }

            return ArrayHelper::toArray($context) ?? [];
        }

        return [];
    }
}
