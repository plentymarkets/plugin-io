<?php
namespace IO\Helper;
use IO\Helper\ContextInterface;

/**
 * Container to pass current template between separate layout plugins and this plugin.
 * Class TemplateContainer
 * @package IO\Helper
 */
class TemplateContainer
{

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

	/** @var ContextInterface */
	private $contextClass = null;

	/**
	 * Set the layout to use for current request.
	 * Should be set in layout plugin when receiving triggered event.
	 * @param string $template The template to use for current request.
	 * @return TemplateContainer
	 */
	public function setTemplate(string $template):TemplateContainer
	{
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
		if($customData !== null)
		{
			$this->templateData = $customData;
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTemplateKey()
	{
		return $this->templateKey;
	}

	/**
	 * @param string $templateKey
	 * @return $this
	 */
	public function setTemplateKey($templateKey)
	{
		$this->templateKey = $templateKey;
		return $this;
	}

	public function setContext($contextClass)
    {
        $this->contextClass = $contextClass;
    }

    public function getContext()
    {
        return $this->contextClass;
    }

	/**
	 * Add additional template data to the existing values.
	 * @param mixed $data The data to add to map.
	 * @param string $identifier An identifying string to access the given data
	 * @return TemplateContainer
	 */
	public function withData($data, string $identifier):TemplateContainer
	{
		$this->templateData[$identifier] = $data;
		return $this;
	}
}
