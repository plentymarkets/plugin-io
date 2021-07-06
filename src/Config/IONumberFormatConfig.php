<?php

namespace IO\Config;

use Plenty\Modules\Webshop\Helpers\PluginConfig;

/**
 * Class IONumberFormatConfig
 *
 * IO number format configuration data.
 *
 * @package IO\Config
 */
class IONumberFormatConfig extends PluginConfig
{
    /**
     * @var string Defines number of decimals, default is "2".
     */
    public $numberDecimals;

    /**
     * @var string Defines seperator of decimals, default is ",".
     */
    public $separatorDecimal;

    /**
     * @var string Defines seperator of thousand, default is ".".
     */
    public $separatorThousands;

    /**
     * @var string Defines to use the locale currency format or the configured, default is "1".
     */
    public $useLocaleCurrencyFormat;

    /**
     * Fill configuration data from plugin configuration.
     */
    protected function load()
    {
        $this->numberDecimals = $this->getTextValue('format.number_decimals', '2');
        $this->separatorDecimal = $this->getTextValue('format.separator_decimal', ',');
        $this->separatorThousands = $this->getTextValue('format.separator_thousands', '');
        $this->useLocaleCurrencyFormat = $this->getTextValue('format.use_locale_currency_format', '1');
    }

    /**
     * Return the current plugin name.
     *
     * @return string
     */
    protected function getPluginName()
    {
        return 'IO';
    }
}
