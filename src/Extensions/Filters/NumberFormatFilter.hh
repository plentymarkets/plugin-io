<?hh //strict

namespace LayoutCore\Extensions\Filters;

use Plenty\Plugin\ConfigRepository;
use LayoutCore\Extensions\AbstractFilter;

class NumberFormatFilter extends AbstractFilter
{
    private ConfigRepository $config;

    public function __construct( ConfigRepository $config )
    {
        parent::__construct();
        $this->config = $config;
    }

    public function getFilters():array<string, string>
    {
        return [
            "formatDecimal" => "formatDecimal",
            "formatMonetary" => "formatMonetary"
        ];
    }

    public function formatDecimal( float $value, int $decimal_places = -1 ):string
    {
        if( $decimal_places < 0 )
        {
            $decimal_places = $this->config->get('PluginLayoutCore.format.number_decimals');
        }
        
        if( $decimal_places === "" )
        {
            $decimal_places = 0;
        }
        $decimal_separator = $this->config->get('PluginLayoutCore.format.separator_decimal');
        $thousands_separator = $this->config->get('PluginLayoutCore.format.separator_thousands');
        return number_format( $value, $decimal_places, $decimal_separator, $thousands_separator );
    }

    public function formatMonetary( float $value, string $currencyISO ):string
    {
        $locale = 'de_DE';
        $useCurrencySymbol = true;

        $formatter = numfmt_create( $locale, \NumberFormatter::CURRENCY );
        if( !$useCurrencySymbol )
        {
            $formatter->setTextAttribute( \NumberFormatter::CURRENCY_CODE, $currencyISO );
            $formatter->setSymbol( \NumberFormatter::CURRENCY_SYMBOL, $currencyISO );
        }

        if( $this->config->get('PluginLayoutCore.format.use_locale_currency_format') === "0" )
        {
            $decimal_separator = $this->config->get('PluginLayoutCore.format.separator_decimal');
            $thousands_separator = $this->config->get('PluginLayoutCore.format.separator_thousands');
            $formatter->setSymbol( \NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $decimal_separator );
            $formatter->setSymbol( \NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $thousands_separator );
        }
        return $formatter->format( $value );
    }
}
