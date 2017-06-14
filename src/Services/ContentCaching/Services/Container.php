<?php
namespace IO\Services\ContentCaching\Services;

use IO\Services\ContentCaching\Contracts\CachingSettings;

/**
 * Created by ptopczewski, 14.06.17 08:57
 * Class Container
 * @package IO\Services\ContentCaching
 */
class Container
{
    /**
     * @var array
     */
    private $templateCacheMap = [];

    /**
     * @var CachingSettings[]
     */
    private $settingsContainer = [];

    /**
     * @param string $templateName
     * @param string $settingsClassName
     */
    public function register($templateName, $settingsClassName)
    {
        $this->templateCacheMap[$templateName] = $settingsClassName;
    }

    /**
     * @param string $templateName
     * @return CachingSettings
     * @throws \Exception
     */
    public function get($templateName):CachingSettings
    {
        if(!array_key_exists($templateName, $this->templateCacheMap)){
            throw new \Exception('no caching settings for '.$templateName.' not found');
        }

        if(!array_key_exists($templateName, $this->settingsContainer)){
            $settings = pluginApp($this->templateCacheMap[$templateName]);

            if(!$settings instanceof CachingSettings){
                throw new \Exception('caching settings class has to implement the CachingSettings interface');
            }

            $this->settingsContainer[$templateName] = $settings;
        }

        return $this->settingsContainer[$templateName];
    }
}