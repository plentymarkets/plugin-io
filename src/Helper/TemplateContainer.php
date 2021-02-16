<?php
namespace IO\Helper;

use Plenty\Plugin\Log\Loggable;

/**
 * Class TemplateContainer
 *
 * Container to pass current template between separate layout plugins and this plugin.
 *
 * @package IO\Helper
 */
class TemplateContainer
{
    use Loggable;

    /**
     * Get an instance of TemplateContainer based on the templateKey and additional data.
     * @param string $templateKey Key for the template.
     * @param array $data Additional data for the template.
     * @return mixed
     */
    public static function get($templateKey, $data = [])
    {
        $container = pluginApp(self::class);
        $container->setTemplateKey($templateKey);

        EventDispatcher::fire($templateKey, [
            $container,
            $data
        ]);

        return $container;
    }

	/**
	 * @var string
	 */
	private $template = null;
	
	/**
	 * @var array|\Closure
	 */
	private $templateData = null;

	/**
	 * @var string
	 */
	private $templateKey = '';

	/** @var string */
	private $contextClass = null;

	/**
	 * Set the layout to use for current request.
	 * Should be set in layout plugin when receiving triggered event.
	 * @param string $template The template to use for current request.
	 * @return TemplateContainer
	 */
	public function setTemplate(string $template):TemplateContainer
	{
	    $this->getLogger(__CLASS__)->debug(
	        "IO::Debug.TemplateContainer_setTemplate",
            [
                "templateKey" => $this->templateKey,
                "template" => $template
            ]
        );
		$this->template = $template;
		return $this;
	}

	/**
	 * Get the provided layout to use for current request.
	 * Will be rendered by LayoutController.
	 */
	public function getTemplate():string
	{
		if($this->template === null)
		{
			return "";
		}
		return $this->template;
	}

	/**
	 * Check whether a separate layout plugin has set a template to use for the current request.
	 */
	public function hasTemplate():bool
	{
		return $this->template !== null;
	}

	/**
	 * Get the template data to pass to current template.
	 */
	public function getTemplateData()
	{
		return $this->templateData;
	}
	
	/**
	 * Override template data used by LayoutController when rendering a template for current request.
	 * @param mixed $customData
	 * @return TemplateContainer
	 */
	public function setTemplateData( $customData )
	{
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.TemplateContainer_setTemplateData",
            [
                "templateKey" => $this->templateKey,
                "templateData" => $customData
            ]
        );
		if($customData !== null)
		{
			$this->templateData = $customData;
		}
		return $this;
	}

	/**
     * Getter for the templateKey property.
	 * @return string
	 */
	public function getTemplateKey()
	{
		return $this->templateKey;
	}

	/**
     * Setter for the templateKey property.
	 * @param string $templateKey
	 * @return $this
	 */
	public function setTemplateKey($templateKey)
	{
		$this->templateKey = $templateKey;
		return $this;
	}

    /**
     * Setter for the contextClass property.
     * @param $contextClass
     */
	public function setContext($contextClass)
    {
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.TemplateContainer_setContext",
            [
                "templateKey"  => $this->templateKey,
                "contextClass" => $contextClass
            ]
        );
        $this->contextClass = $contextClass;
    }

    /**
     * Getter for the contextClass property.
     * @return string|null
     */
    public function getContext()
    {
        return $this->contextClass;
    }

	/**
	 * Add additional template data to the existing values.
	 * @param mixed $data The data to add to map.
	 * @param string $identifier An identifying string to access the given data.
	 * @return TemplateContainer
	 */
	public function withData($data, string $identifier):TemplateContainer
	{
        $this->getLogger(__CLASS__)->debug(
            "IO::Debug.TemplateContainer_mergeTemplateData",
            [
                "templateKey" => $this->templateKey,
                "templateData" => [
                    "key" => $identifier,
                    "value" => $data
                ]
            ]
        );
		$this->templateData[$identifier] = $data;
		return $this;
	}
}
