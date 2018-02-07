<?php //strict

namespace IO\Extensions\Filters;

use IO\Helper\LanguageMap;
use IO\Helper\MemoryCache;
use IO\Services\TemplateConfigService;
use Plenty\Plugin\ConfigRepository;
use IO\Extensions\AbstractFilter;

/**
 * Class NumberFormatFilter
 * @package IO\Extensions\Filters
 */
class NumberFormatFilter extends AbstractFilter
{
    use MemoryCache;

	/**
	 * @var ConfigRepository
	 */
	private $config;

    /**
     * NumberFormatFilter constructor.
     * @param ConfigRepository $config
     */
	public function __construct(ConfigRepository $config)
	{
		parent::__construct();
		$this->config = $config;
	}

    /**
     * Return the available filter methods
     * @return array
     */
	public function getFilters():array
	{
		return [
			"formatDecimal"  => "formatDecimal",
			"formatMonetary" => "formatMonetary",
            "trimNewlines"   => "trimNewlines",
            "formatDateTime" => "formatDateTime"
		];
	}

    /**
     * Format incorrect JSON ENCODED dateTimeFormat
     * @param $value
     * @return string
     */
    public function formatDateTime($value):string
    {
        if(strpos($value, '+') === false && !is_object($value))
        {
            $value = str_replace(' ', '+', $value);
        }

        return $value;
    }

    /**
     * Trim newlines from string
     * @param string $value
     * @return string
     */
    public function trimNewlines($value):string
    {
        return preg_replace('/\s+/', '', $value);
    }

    /**
     * Format the given value to decimal
     * @param float $value
     * @param int $decimal_places
     * @return string
     */
	public function formatDecimal($value, int $decimal_places = -1):string
	{
		if($decimal_places < 0)
		{
			$decimal_places = $this->config->get('IO.format.number_decimals');
		}

		if($decimal_places === "")
		{
			$decimal_places = 0;
		}
		$decimal_separator   = $this->config->get('IO.format.separator_decimal');
		$thousands_separator = $this->config->get('IO.format.separator_thousands');
		return number_format($value, $decimal_places, $decimal_separator, $thousands_separator);
	}

    /**
     * Format the given value to currency
     * @param $value
     * @param $currencyISO
     * @return string
     */
    public function formatMonetary($value, $currencyISO):string
    {
        if(!is_null($value) && !is_null($currencyISO) && strlen($currencyISO))
        {
            $value = $this->trimNewlines($value);
            $currencyISO = $this->trimNewlines($currencyISO);

            $formatter = $this->fromMemoryCache(
                "formatter.$currencyISO",
                function() use ($currencyISO) {
                    $locale            = LanguageMap::getLocale();

                    $formatter = numfmt_create($locale, \NumberFormatter::CURRENCY);

                    /** @var TemplateConfigService $templateConfigService */
                    $templateConfigService = pluginApp( TemplateConfigService::class );

                    if( $templateConfigService->get('currency.format') === 'symbol' )
                    {
                        $formatter->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $currencyISO);
                    }
                    else
                    {
                        $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $currencyISO);
                    }

                    if($this->config->get('IO.format.use_locale_currency_format') === "0")
                    {
                        $decimal_separator   = $this->config->get('IO.format.separator_decimal');
                        $thousands_separator = $this->config->get('IO.format.separator_thousands');
                        $formatter->setSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $decimal_separator);
                        $formatter->setSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $thousands_separator);
                    }

                    return $formatter;
                }
            );

            return $formatter->format($value);

        }

        return '';
    }
}
