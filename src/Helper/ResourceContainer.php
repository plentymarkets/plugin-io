<?php //strict

namespace IO\Helper;

/**
 * Class ResourceContainer
 *
 * Helper class for JS and CSS resources.
 * Plugins can register their script and style templates in the resource container for easy deployment on the page.
 *
 * @package IO\Helper
 */
class ResourceContainer
{
    private $styleTemplates = [];

    private $scriptTemplates = [];

    /**
     * Getter for the list of registered style templates.
     * @return array
     */
    public function getStyleTemplates():array
    {
        return $this->styleTemplates;
    }

    /**
     * Add a style template to be rendered.
     * @param string $path Path to the template (for example: 'Plugin::Template.Style').
     * @param array $params Additional data for rendering the template.
     */
    public function addStyleTemplate( string $path, array $params = [] )
    {
        $this->styleTemplates[] = [
            'path' => $path,
            'params' => $params
        ];
    }

    /**
     * Getter for the list of registered script templates.
     * @return array
     */
    public function getScriptTemplates():array
    {
        return $this->scriptTemplates;
    }

    /**
     * Add a script template to be rendered.
     * @param string $path Path to the template (for example: 'Plugin::Template.Scripts').
     * @param array $params Additional data for rendering the template.
     */
    public function addScriptTemplate( string $path, array $params = [] )
    {
        $this->scriptTemplates[] = [
            'path' => $path, 
            'params' => $params
        ];
    }
}
