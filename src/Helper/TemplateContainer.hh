<?hh //strict

namespace LayoutCore\Helper;

/**
 * Container to pass current template between separate layout plugins and this plugin.
 */
class TemplateContainer {

    private ?string $template = null;
    private array<string, mixed> $templateData = array();

    /**
     * Set the layout to use for current request.
     * Should be set in layout plugin when receiving triggered event.
     * @param string $template The template to use for current request.
     */
    public function setTemplate( string $template ):TemplateContainer
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
        if( $this->template === null ) {
            return "";
        }
        return $this->template;
    }

    /**
     * Checks if separate layout plugin has set a template to use for current request.
     */
    public function hasTemplate():bool
    {
        return $this->template !== null;
    }

    /**
     * Get the template data to pass to current template.
     */
    public function getTemplateData():array<string, mixed>
    {
        return $this->templateData;
    }

    /**
     * Override template data used by LayoutController when rendering a template for current request.
     */
    public function setTemplateData( ?array<string, mixed> $customData = null ):TemplateContainer
    {
        if( $customData !== null ) {
            $this->templateData = $customData;
        }
        return $this;
    }

    /**
     * Add additional template data to existing values.
     * @param mixed $data The data to add to map.
     * @param string $identifier An identifying string to access the given data
     */
    public function withData( mixed $data, string $identifier ):TemplateContainer
    {
        $this->templateData[$identifier] = $data;
        return $this;
    }
}
