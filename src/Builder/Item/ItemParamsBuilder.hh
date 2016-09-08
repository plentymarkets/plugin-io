<?hh //strict

namespace LayoutCore\Builder\Item;

use LayoutCore\Builder\Item\Params\ItemColumnsParams;

/**
 * Builds array of ItemDataLayer params to pass to ItemDataLayerRepository::search
 */
class ItemParamsBuilder {

    private array<string, mixed> $params = array();

    /**
     * Set a param value.
     * @param ItemColumnsParams $paramName The name of the param to set.
     * @param mixed $paramValue The value of the param to set.
     * @return ItemParamsBuilder The instance of the current builder.
     */
    public function withParam( ItemColumnsParams $paramName, mixed $paramValue ):ItemParamsBuilder {
        $this->params[(string) $paramName] = $paramValue;
        return $this;
    }

    /**
     * Returns generated params to pass to ItemDataLayerRepository
     * @return array<string, mixed>
     */
    public function build():array<string, mixed> {
        return $this->params;
    }

}
