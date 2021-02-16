<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Helper\MemoryCache;
use Plenty\Modules\Webshop\Helpers\NumberFormatter;
use Plenty\Plugin\ConfigRepository;

/**
 * Class NumberFormatFilter
 *
 * Contains twig filters that help working with numbers and formatting them.
 *
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
     * Get the twig filter to method name mapping. (twig filter => method name)
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [
            "formatDecimal" => "formatDecimal",
            "formatMonetary" => "formatMonetary",
            "trimNewlines" => "trimNewlines",
            "formatDateTime" => "formatDateTime"
        ];
    }

    /**
     * Format incorrect JSON ENCODED dateTimeFormat.
     *
     * @param string $value Incorrectly formatted date.
     * @return string
     */
    public function formatDateTime($value): string
    {
        if (strpos($value, '+') === false && !is_object($value)) {
            $value = str_replace(' ', '+', $value);
        }

        return $value;
    }

    /**
     * Trims all newlines from a string.
     *
     * @param string $value String to trim.
     * @return string
     */
    public function trimNewlines($value): string
    {
        return preg_replace('/\s+/', '', $value);
    }

    /**
     * Format the given value to decimal.
     *
     * @param float $value Value that should be formatted.
     * @param int $decimal_places Sets how many decimal places there should be.
     * @return string
     */
    public function formatDecimal($value, int $decimal_places = -1): string
    {
        return $this->numberFormatter->formatDecimal($value, $decimal_places);
    }

    /**
     * Format the given value to currency.
     *
     * @param float $value Value that should be formatted.
     * @param string $currencyISO ISO to which the value should be formatted.
     * @return string
     */
    public function formatMonetary($value, $currencyISO): string
    {
        return $this->numberFormatter->formatMonetary($value, $currencyISO);
    }
}
