<?php

namespace IO\Config;

class IOConfig
{
    /** @var IONumberFormatConfig */
    public $format;

    public function __construct()
    {
        $this->format = pluginApp(IONumberFormatConfig::class);
    }
}
