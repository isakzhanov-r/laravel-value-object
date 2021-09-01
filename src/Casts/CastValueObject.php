<?php


namespace IsakzhanovR\ValueObject\Casts;


use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use IsakzhanovR\ValueObject\Exceptions\InvalidTypeException;
use IsakzhanovR\ValueObject\Exceptions\ValueObjectException;
use IsakzhanovR\ValueObject\ValueObject;

final class CastValueObject implements CastsAttributes
{
    /**
     * @var \IsakzhanovR\ValueObject\ValueObject|string|null
     */
    protected $valueObject;

    public function __construct($class)
    {
        if (!class_exists($class) && !$class instanceof ValueObject) {
            throw new ValueObjectException($class);
        }

        $this->valueObject = $class;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (method_exists($this->valueObject, 'unserialize')) {
            $value = $this->valueObject::unserialize($value);
        }

        return new $this->valueObject($value, $key);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return mixed
     * @throws \Exception
     */
    public function set($model, string $key, $value, array $attributes)
    {
        $this->checkType($model, $key, $value);

        return $this->setValue($value);
    }

    /**
     * @param \IsakzhanovR\ValueObject\ValueObject $object
     *
     * @return mixed
     * @throws \Exception
     */
    protected function setValue(ValueObject $object)
    {
        if (method_exists($object, 'serialize')) {
            return $object::serialize($object->value());
        }

        return $object->value();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    protected function checkType(Model $model, $key, $value)
    {
        $type = Arr::get($model->getCasts(), $key);

        if (!$value instanceof $type) {
            throw new InvalidTypeException(get_class($model), $key, $type);
        }
    }
}
