<?php

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
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
     * Return all available twig functions to be registered globally.
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
    public function getQueryString($params = [])
    {
        $request = pluginApp(Request::class);

        // FIX use $request->query() instead of $request->all() to avoid appending params from request body while rendering twig via POST calls, e.g. via the shop builder
        $queryParameters = $request->query();
        unset($queryParameters['plentyMarkets']);
        $queryParameters = array_replace($queryParameters, $params);

        if (!is_array($queryParameters)) {
            return '';
        }

        $queryParameters = http_build_query($queryParameters);
        return strlen($queryParameters) > 0 ? '?' . $queryParameters : '';
    }
}
