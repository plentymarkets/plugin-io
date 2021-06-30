<?php
namespace IO\Helper;

/**
 * Class ComponentContainer
 *
 * Container to pass current component between separate theme plugins and this plugin.
 *
 * @package IO\Helper
 */
class ComponentContainer
{
    /**
     * @var string The original component template.
     */
    private $originComponentTemplate = '';
    
    /**
     * @var string The new component template.
     */
    private $newComponentTemplate = '';
    
    /**
     * ComponentContainer constructor.
     * @param string $originComponentTemplate
     */
    public function __construct($originComponentTemplate)
    {
        $this->originComponentTemplate = $originComponentTemplate;
    }
    
    /**
     * Getter for the originComponentTemplate property.
     * @return string
     */
    public function getOriginComponentTemplate(): string
    {
        return $this->originComponentTemplate;
    }
    
    /**
     * Getter for the newComponentTemplate property.
     * @return string
     */
    public function getNewComponentTemplate(): string
    {
        return $this->newComponentTemplate;
    }
    
    /**
     * Setter for the newComponentTemplate property.
     * @param string $newComponentTemplate
     * @return $this
     */
    public function setNewComponentTemplate($newComponentTemplate)
    {
        $this->newComponentTemplate = $newComponentTemplate;
        return $this;
    }
    
    
}
