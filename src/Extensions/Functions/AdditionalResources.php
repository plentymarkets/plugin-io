<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use IO\Helper\ResourceContainer;
use Plenty\Plugin\Events\Dispatcher;

class AdditionalResources extends AbstractFunction
{
    /**
     * @var Dispatcher
     */
    private $event;

    /**
     * @var ResourceContainer
     */
    private $resourceContainer = null;

    public function __construct(Dispatcher $event)
    {
        parent::__construct();
        $this->event = $event;
    }

    public function getFunctions(): array
    {
        return [
            "get_additional_scripts" => "getAdditionalScripts",
            "get_additional_styles"  => "getAdditionalStyles"
        ];
    }

    public function getAdditionalScripts()
    {
        /** @var ResourceContainer $resourceContainer */
        if ( $this->resourceContainer === null )
        {
            $this->resourceContainer = pluginApp(ResourceContainer::class);
            $this->event->fire('IO.Resources.Import', [
                $this->resourceContainer
            ]);
        }

        return $this->resourceContainer->getScriptTemplates();
    }

    public function getAdditionalStyles()
    {
        /** @var ResourceContainer $resourceContainer */
        if ( $this->resourceContainer === null )
        {
            $this->resourceContainer = pluginApp(ResourceContainer::class);
            $this->event->fire('IO.Resources.Import', [
                $this->resourceContainer
            ]);
        }

        return $this->resourceContainer->getStyleTemplates();
    }
}