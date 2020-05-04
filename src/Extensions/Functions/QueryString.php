<?php //strict

namespace IO\Extensions\Functions;

use IO\Extensions\AbstractFunction;
use Plenty\Plugin\Http\Request;

class QueryString extends AbstractFunction
{
    public function construct()
    {
    }

    /**
     * Return the available methods
     * @return array
     */
    public function getFunctions():array
    {
        return [
            "queryString" => "getQueryString"
        ];
    }

    /**
     * @param array $params
     * @return string
     */
    public function getQueryString($params = [])
    {
        $request = pluginApp(Request::class);

        $queryParameters = $request->all();
        unset($queryParameters['plentyMarkets']);
        $queryParameters = array_replace($queryParameters, $params);

        if (!is_array($queryParameters)) {
            return '';
        }

        $queryParameters = http_build_query($queryParameters);
        return strlen($queryParameters) > 0 ? '?' . $queryParameters : '';
    }
}