<?php


namespace IsakzhanovR\ValueObject\Exceptions;

use IsakzhanovR\ValueObject\ValueObject;
use RuntimeException;

final class ValueObjectException extends RuntimeException
{
    public function __construct(string $class)
    {
        parent::__construct('The class "' . $class . '" is not inherited from the "' . ValueObject::class .
            '" and does not implement the necessary functionality ¯\_(ツ)_/¯');
    }
}
