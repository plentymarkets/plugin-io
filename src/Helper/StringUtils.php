<?php

namespace IO\Helper;

use Plenty\Modules\Webshop\Helpers\StringUtils as NewStringUtils;

/**
 * Class StringUtils
 * @package IO\Helper
 * @deprecated since 5.0.0 will be removed in 6.0.0
 * @see \Plenty\Modules\Webshop\Helpers\StringUtils
 */
class StringUtils
{
    /**
     * @param $n
     * @return false|string|string[]|null
     * @deprecated since 5.0.0 will be removed in 6.0.0
     * @see \Plenty\Modules\Webshop\Helpers\StringUtils::string4URL()
     */
    public static function string4URL( $n )
    {
        return NewStringUtils::string4URL($n);
    }
}
