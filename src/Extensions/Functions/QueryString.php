<?php //strict

namespace IO\Extensions\Functions;

use Plenty\Plugin\Events\Dispatcher;
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
        $queryParameters = $this->createUniqueMultidimensionalArray($queryParameters);

        $queryParameters = http_build_query($queryParameters);
        return strlen($queryParameters) > 0 ? '?' . http_build_query($queryParameters) : '';
    }

    /**
     * @param array $array
     * @return array
     */
    private function createUniqueMultidimensionalArray(array $array): array
	{
	    $array = array_unique($array, SORT_REGULAR);
	
	    foreach ($array as $key => $elem) {
	        if (is_array($elem)) {
	            $array[$key] = $this->createUniqueMultidimensionalArray($elem);
	        }
	    }
	
	    return $array;
	}
}