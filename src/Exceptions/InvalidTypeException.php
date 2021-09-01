<?php


namespace IsakzhanovR\ValueObject\Exceptions;

use RuntimeException;

final class InvalidTypeException extends RuntimeException
{
    public function __construct(string $model, string $key, string $type)
    {
        parent::__construct('The Eloquent Model "' . $model . '" must use the type "' . $type . '" specified in casts -> ' . $key);
    }
}
