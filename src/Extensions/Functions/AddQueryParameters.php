<?php //strict

namespace IO\Extensions\Functions;

use Plenty\Plugin\Events\Dispatcher;
use IO\Extensions\AbstractFunction;
use Plenty\Plugin\Http\Request;

class AddQueryParameters extends AbstractFunction
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
            "addQueryParameters" => "addQueryParameters"
        ];
    }

    /**
     * @param array $params
     * @return string
     */
    public function addQueryParameters(array $params)
    {
        $request = pluginApp(Request::class);
        
        $queryParameters = $request->all();
        array_splice($queryParameters, 0, 1);
        $queryParameters = array_replace($queryParameters, $params);

        $queryParamString = '';
        $queryParamSeparator = '?';

        foreach ($queryParameters as $key => $value)
        {
            $queryParamString .= $queryParamSeparator . urlencode($key) . '=' . urlencode($value);
            $queryParamSeparator = '&';
        }

        return $queryParamString;
    }
}