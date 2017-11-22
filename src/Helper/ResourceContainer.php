<?php //strict

namespace IO\Helper;

class ResourceContainer
{
    private $styleTemplates = [];

    private $scriptTemplates = [];

    public function getStyleTemplates():array
    {
        return $this->styleTemplates;
    }

    public function addStyleTemplate( string $path )
    {
        $this->styleTemplates[] = $path;
    }

    public function getScriptTemplates():array
    {
        return $this->scriptTemplates;
    }

    public function addScriptTemplate( string $path )
    {
        $this->scriptTemplates[] = $path;
    }
}