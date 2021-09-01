<?php

namespace Tests\Unit;

use IsakzhanovR\ValueObject\ValueObject;
use Exception;
use Illuminate\Support\Arr;
use Tests\TestCase;

class ValueObjectTest extends TestCase
{
    public function testCreateValueObject()
    {
        $class = $this->getValueObject('string', 'key');

        $this->assertEquals('string', $class->value());
    }

    public function testValidatorValueObject()
    {
        try {
            $this->getValueObject('str', 'key');
        } catch (Exception $exception) {
            /**
             * @var $exception \Illuminate\Validation\ValidationException
             */
            $messages = Arr::get($exception->errors(), 'key');
            self::assertIsArray($messages);
            self::assertEquals('The key must be at least 5 characters.', Arr::first($messages));
        }
    }

    public function testErrorTransform()
    {
        try {
            new class('string', 'key') extends ValueObject {
                protected function rules(): array
                {
                    return [
                        $this->key => ['string', 'min:5'],
                    ];
                }

                protected function transformInput($value)
                {
                    return [$value];
                }
            };
        } catch (Exception $exception) {
            /**
             * @var $exception \Illuminate\Validation\ValidationException
             */
            $messages = Arr::get($exception->errors(), 'key');
            self::assertIsArray($messages);
            self::assertEquals(["The key must be a string.", "The key must be at least 5 characters."], $messages);
        }
    }

    private function getValueObject($value, $key)
    {
        return new class($value, $key) extends ValueObject {
            protected function rules(): array
            {
                return [
                    $this->key => ['string', 'min:5'],
                ];
            }

            protected function transformInput($value)
            {
                return $value;
            }
        };
    }
}
