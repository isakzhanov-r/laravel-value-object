<?php


namespace Tests\ValueObjects;


use IsakzhanovR\ValueObject\ValueObject;

class Email extends ValueObject
{
    public function domain()
    {
        return substr($this->value(), strrpos($this->value(), '@') + 1);
    }

    protected function transformInput($value)
    {
        return trim($value);
    }

    protected function rules(): array
    {
        return [
            $this->key => ['required', 'string', 'email'],
        ];
    }
}
