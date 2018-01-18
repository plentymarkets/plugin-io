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

    public function addStyleTemplate( string $path, array $params = [] )
    {
        $this->styleTemplates[] = [
            'path' => $path,
            'params' => $params
        ];
    }

    public function getScriptTemplates():array
    {
        return $this->scriptTemplates;
    }

    public function addScriptTemplate( string $path, array $params = [] )
    {
        $this->scriptTemplates[] = [
            'path' => $path, 
            'params' => $params
        ];
    }
}