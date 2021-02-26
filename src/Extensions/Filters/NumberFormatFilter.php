<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Helper\MemoryCache;
use Plenty\Modules\Webshop\Helpers\NumberFormatter;
use Plenty\Plugin\ConfigRepository;

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

	/** @var NumberFormatter $numberFormatter */
	private $numberFormatter;

    /**
     * NumberFormatFilter constructor.
     * @param ConfigRepository $config
     */
	public function __construct(ConfigRepository $config, NumberFormatter $numberFormatter)
	{
		parent::__construct();
		$this->config = $config;
		$this->numberFormatter = $numberFormatter;
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
     * @param string $value
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
	    return $this->numberFormatter->formatDecimal($value, $decimal_places);
	}

    /**
     * Format the given value to currency
     * @param float $value
     * @param string $currencyISO
     * @return string
     */
    public function formatMonetary($value, $currencyISO):string
    {
        return $this->numberFormatter->formatMonetary($value, $currencyISO);
    }
}
