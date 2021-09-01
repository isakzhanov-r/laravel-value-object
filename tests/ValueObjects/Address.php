<?php


namespace Tests\ValueObjects;


use IsakzhanovR\ValueObject\ValueObject;

class Address extends ValueObject
{
    public static function unserialize($value)
    {
        return json_decode($value, true);
    }

    public static function serialize($value)
    {
        return json_encode($value);
    }

    protected function transformInput($value)
    {
        return $value;
    }

    protected function rules(): array
    {
        return [
            $this->key              => ['required', 'array'],
            $this->key . '.country' => ['required', 'string'],
            $this->key . '.city'    => ['required', 'string'],
            $this->key . '.street'  => ['required', 'string'],
            $this->key . '.number'  => ['required', 'numeric'],
        ];
    }
}
