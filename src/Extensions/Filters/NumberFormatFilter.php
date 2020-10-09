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
     * @param $value
     * @return string
     */
    public function formatDateTime($value):string
    {
        return $this->numberFormatter->formatDateTime($value);
    }

    /**
     * Trim newlines from string
     * @param string $value
     * @return string
     */
    public function trimNewlines($value):string
    {
        return $this->numberFormatter->trimNewlines($value);
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
     * @param $value
     * @param $currencyISO
     * @return string
     */
    public function formatMonetary($value, $currencyISO):string
    {
        return $this->numberFormatter->formatMonetary($value, $currencyISO);
    }
}
