<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;

/**
 * Class ExternalContent
 * @package IO\Extensions\Functions
 */
class ExternalContent extends AbstractFunction
{
    /**
     * Return the available filter methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "getExternalContent" => "getExternalContent"
        ];
    }
    
    /**
     * Return the content retrieved from external url
     * @param string $url
     * @return string
     */
    public function getExternalContent(string $url):string
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4
        );
    
        $ch = curl_init();
        
        foreach($options as $option => $value)
        {
            curl_setopt($ch, $option, $value);
        }
    
        $content = curl_exec($ch);
        curl_close($ch);
        
        return $content;
    }
}