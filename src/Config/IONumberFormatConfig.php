<?php

namespace IO\Config;

use Plenty\Modules\Webshop\Helpers\PluginConfig;

class IONumberFormatConfig extends PluginConfig
{
    public $numberDecimals;
    public $separatorDecimal;
    public $separatorThousands;
    public $useLocaleCurrencyFormat;

    protected function load()
    {
        $this->numberDecimals = $this->getTextValue('format.number_decimals', '2');
        $this->separatorDecimal = $this->getTextValue('format.separator_decimal', ',');
        $this->separatorThousands = $this->getTextValue('format.separator_thousands', '.');
        $this->useLocaleCurrencyFormat = $this->getTextValue('format.use_locale_currency_format', '1');
    }

    protected function getPluginName()
    {
        return 'IO';
    }
}
