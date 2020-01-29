<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Helper\MemoryCache;
use IO\Services\TemplateConfigService;
use Plenty\Modules\Webshop\Contracts\LocalizationRepositoryContract;
use Plenty\Plugin\ConfigRepository;

/**
 * Class NumberFormatFilter
 * @package IO\Extensions\Filters
 *
 * @deprecated since 5.0.0 will be deleted in 6.0.0
 * @see \Plenty\Modules\Webshop\Filters\NumberFormatFilter
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
     *
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Filters\NumberFormatFilter::getFilters()
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
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Filters\NumberFormatFilter::formatDateTime()
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
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Filters\NumberFormatFilter::trimNewlines()
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
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Filters\NumberFormatFilter::formatDecimal()
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
     * @deprecated since 5.0.0 will be deleted in 6.0.0
     * @see \Plenty\Modules\Webshop\Filters\NumberFormatFilter::formatMonetary()
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
                    /** @var LocalizationRepositoryContract $localizationRepository */
                    $localizationRepository = pluginApp(LocalizationRepositoryContract::class);
                    $locale = $localizationRepository->getLocale();
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
                        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->config->get('IO.format.number_decimals', 2));
                    }

                    return $formatter;
                }
            );

            return $formatter->format($value);
        }

        return '';
    }
}
