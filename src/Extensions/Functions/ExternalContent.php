<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;

/**
 * Class ExternalContent
 *
 * Contains a function to load external content from.
 *
 * @package IO\Extensions\Functions
 */
class ExternalContent extends AbstractFunction
{
    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            "getExternalContent" => "getExternalContent"
        ];
    }

    /**
     * Gets external JSON content.
     *
     * @param string $url Url to load the content from.
     * @return array
     */
    public function getExternalContent(string $url): array
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 4
        );

        $ch = curl_init();

        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $content = curl_exec($ch);
        curl_close($ch);

        return json_decode($content, true);
    }
}
