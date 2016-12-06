<?php

namespace IO\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Plenty\Repositories\Models\PaginatedResult;


abstract class ModelWrapper extends BaseModel
{
    /**
     * @param mixed     $original
     * @param array     ...$args
     * @return mixed
     */
    public abstract static function wrap( $original, ...$args );

    /**
     * @param array|Collection  $elements
     * @param array             ...$args
     * @return array
     */
    public static function wrapList( $elements, ...$args ):array
    {
        $result = [];
        foreach( $elements as $element )
        {
            $newElement = static::wrap( $element, ...$args );
            array_push( $result, $newElement );
        }

        return $result;
    }

    /**
     * @param PaginatedResult   $paginated
     * @param array             ...$args
     * @return PaginatedResult
     */
    public static function wrapPaginated( PaginatedResult $paginated, ...$args ):PaginatedResult
    {
        $result = self::wrapList( $paginated->getResult(), ...$args );
        $paginated->setResult( $result );
        return $paginated;
    }
}