<?php

namespace IO\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Plenty\Repositories\Models\PaginatedResult;

/**
 * Class ModelWrapper
 *
 * Abstract class to wrap a model to another data representation.
 *
 * @package IO\Models
 */
abstract class ModelWrapper extends BaseModel
{
    /**
     * Abstract wrapping function must be implemented in an inherting class.
     *
     * @param mixed $original Original data model.
     * @param array ...$args Additional params.
     * @return mixed
     */
    public abstract static function wrap($original, ...$args);

    /**
     * Get an array with multiple wrapped instances.
     *
     * @param array|Collection $elements Array or Collection with not wrapped elements.
     * @param array ...$args
     * @return array
     */
    public static function wrapList($elements, ...$args): array
    {
        $result = [];
        foreach ($elements as $element) {
            $newElement = static::wrap($element, ...$args);
            array_push($result, $newElement);
        }

        return $result;
    }

    /**
     * Get an paginated result with wrapped instances.
     *
     * @param PaginatedResult $paginated Paginated result with not wrapped elements.
     * @param array ...$args Additional params.
     * @return PaginatedResult
     */
    public static function wrapPaginated(PaginatedResult $paginated, ...$args): PaginatedResult
    {
        $result = self::wrapList($paginated->getResult(), ...$args);
        $paginated->setResult($result);
        return $paginated;
    }
}
