<?php

namespace IO\Config;

/**
 * Class IOConfig
 *
 * IO Configuriaton data
 *
 * @package IO\Config
 */
class IOConfig
{
    /**
     * @var IONumberFormatConfig IO configuration data for number format.
     */
    public $format;

    /**
     * IOConfig constructor, load sub configuration data.
     */
    public function __construct()
    {
        $this->format = pluginApp(IONumberFormatConfig::class);
    }
}
