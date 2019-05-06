<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use IO\Helper\ComponentContainer;
use Plenty\Plugin\Events\Dispatcher;

/**
 * Class Component
 * @package IO\Extensions\Functions
 */
class GetCdnMetadata extends AbstractFunction
{

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "cdn_metadata" => "getCdnMetadata"
        ];
    }

    /**
     * Get the metadata for a file stored on plentymarkets cdn
     * @param string    $imageUrl   Resource url to get metadata for
     * @param string    $key        Metadata key to get value for
     * @param mixed     $default    Default value to return if no value is stored in metadata
     *
     * @return mixed
     */
    public function getCdnMetadata( $imageUrl, $key = null, $default = null )
    {
        if( !preg_match('!^https?://cdn(\d+|dev)\.plentymarkets\.com/!', $imageUrl) )
        {
            return [];
        }

        $metadata = [];
        $options = array(
            CURLOPT_URL             => $imageUrl,
            CURLOPT_HEADER          => true,
            CURLOPT_CUSTOMREQUEST   => 'HEAD',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_NOBODY          => true,
            CURLOPT_HEADERFUNCTION  => function($request, $header) use (&$metadata)
            {
                if (preg_match('/^x-amz-meta-(\w+):(.*)$/m', $header, $match))
                {
                    $key = trim($match[1]);
                    $value = trim($match[2]);

                    $metadata[$key] = $value;
                }
                return strlen($header);
            }
        );

        $ch = curl_init();

        foreach($options as $option => $value)
        {
            curl_setopt($ch, $option, $value);
        }

        curl_exec($ch);
        curl_close($ch);

        if ( strlen($key) )
        {
            return $metadata[$key] ?? $default;
        }

        return $metadata;
    }

}
