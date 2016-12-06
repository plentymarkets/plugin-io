<?php //strict

namespace IO\Builder\Item;

use IO\Builder\Item\Params\ItemColumnsParams;

/**
 * Build an array of ItemDataLayer parameters to pass to ItemDataLayerRepository::search
 * Class ItemParamsBuilder
 * @package IO\Builder\Item
 */
class ItemParamsBuilder
{

	/**
	 * @var array
	 */
	private $params = [];

	/**
	 * Set a parameter value
	 * @param ItemColumnsParams $paramName The name of the param to set.
	 * @param mixed $paramValue The value of the param to set.
	 * @return ItemParamsBuilder The instance of the current builder.
	 */
	public function withParam(string $paramName, $paramValue):ItemParamsBuilder
	{
		$this->params[(string)$paramName] = $paramValue;
		return $this;
	}

	/**
	 * Return the generated parameters to pass to ItemDataLayerRepository
	 * @return array
	 */
	public function build():array
	{
		return $this->params;
	}

}
