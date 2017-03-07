<?php
namespace IO\Helper;

/**
 * Container to pass current component between separate theme plugins and this plugin.
 * Class ComponentContainer
 * @package IO\Helper
 */
class ComponentContainer
{
    /**
     * @var string
     */
    private $originComponentTemplate = '';
    
    /**
     * @var string
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
     * @return string
     */
    public function getOriginComponentTemplate(): string
    {
        return $this->originComponentTemplate;
    }
    
    /**
     * @return string
     */
    public function getNewComponentTemplate(): string
    {
        return $this->newComponentTemplate;
    }
    
    /**
     * @param string $newComponentTemplate
     * @return $this
     */
    public function setNewComponentTemplate($newComponentTemplate)
    {
        $this->newComponentTemplate = $newComponentTemplate;
        return $this;
    }
    
    
}