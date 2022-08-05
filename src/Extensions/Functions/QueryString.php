<?php

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use IO\Helper\Utils;
use Plenty\Plugin\Http\Request;

/**
 * Class QueryString
 *
 * Registers a global twig function to get the query string of the current request and update or add multiple parameters.
 *
 * @package IO\Extensions\Functions
 */
class QueryString extends AbstractFunction
{
    public function construct()
    {
    }

    /**
     * Get the twig function to internal method name mapping. (twig function => internal method)
     *
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "queryString" => "getQueryString"
        ];
    }

    /**
     * Get the current query string from the URL.
     * Passed parameters will be replaced or added to the returned query string.
     *
     * @param array $params An associative array of parameter keys and values to replace or add to the query string.
     *
     * @return string A query string containing all query parameters of the current request and all defined parameters. Already includes a leading '?'.
     */
    public function getQueryString($params = []): string
    {
        /** @var Request $request */
        $request = pluginApp(Request::class);

        // FIX use $request->query() instead of $request->all() to avoid appending params from request body while rendering twig via POST calls, e.g. via the shop builder.
        $queryParameters = $request->query();
        $queryParameters = Utils::cleanUpExcludesContentCacheParams($queryParameters);
        $queryParameters = array_replace($queryParameters, $params);

        $queryParameters = http_build_query($queryParameters, null, '&', PHP_QUERY_RFC3986);
        return strlen($queryParameters) > 0 ? '?' . $queryParameters : '';
    }
}
