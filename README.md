# Laravel Value Object

> Allows you to create value objects in eloquent models, in the form of casts that are then stored in the database, or to represent the data as an object.

<p align="center">
    <a href="https://packagist.org/packages/isakzhanov-r/laravel-value-object"><img src="https://img.shields.io/packagist/dt/isakzhanov-r/laravel-value-object.svg?style=flat-square" alt="Total Downloads" /></a>
    <a href="https://packagist.org/packages/isakzhanov-r/laravel-value-object"><img src="https://poser.pugx.org/isakzhanov-r/laravel-value-object/v/stable?format=flat-square" alt="Latest Stable Version" /></a>
    <a href="https://packagist.org/isakzhanov-r/laravel-value-object"><img src="https://poser.pugx.org/isakzhanov-r/laravel-value-object/v/unstable?format=flat-square" alt="Latest Unstable Version" /></a>
    <a href="LICENSE"><img src="https://poser.pugx.org/isakzhanov-r/laravel-value-object/license?format=flat-square" alt="License" /></a>
</p>

## Contents

* [Installation](#installation)
* [Usage](#usage)
    * [Creating](#creating)
    * [Validation](#validation)
    * [Use in Model](#use-in-model)
    * [Transform data](#transform)
    * [Serialize and unserialize](#serialize-and-unserialize)
* [License](#license)

## Installation

To get the latest version of Laravel Value Object package, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require isakzhanov-r/laravel-value-object
```

Instead, you can, of course, manually update the dependency block `require` in `composer.json` and run `composer update` if you want to:

```json
{
    "require-dev": {
        "isakzhanov-r/laravel-value-object": "^1.0"
    }
}
```

## Usage

### Creating

To use `Value Object`, you need to create a class that will inherit from the abstract ValueObject class

```php
use IsakzhanovR\ValueObject\ValueObject;

class Temperature extends ValueObject 
{
    ....
}
```

The ValueObject class has mandatory methods for implementation, `transform` and `rules`

```php
use IsakzhanovR\ValueObject\ValueObject;

class Temperature extends ValueObject 
{
    protected function transformInput($value)
    {
        return $value;
    }

    protected function rules(): array
    {
        return [];
    }
}
```

The ValueObject inheritor class has two methods for creating an object, via `new FooValueObject($value, $key)` and via a static call to the `create` function

```php

  $temperature = new Temperature(25);

  $temperature = Temperature::create(25);
```

if the key is not passed to the function argument, it is generated automatically from the class name

```php
 {
  #key: "temperature"
  -value: 25
 }

 echo $temperature;  // 25  
```

### Validation

The data in ValueObject must be valid for this, the `Illuminate\Validation\ValidatesWhenResolvedTrait` validation trait is used. The same trait is used
in `FormRequest`. To define rules for the validator, use the `rules` method

```php
use IsakzhanovR\ValueObject\ValueObject;

class Temperature extends ValueObject 
{
    ....

    protected function rules(): array
    {
        return [
            $this->key => ['required','numeric','between:-100,100']
        ];
    }
}
```

If ValueObject is array, then its value is validated in the same way:

```php
use IsakzhanovR\ValueObject\ValueObject;

class Address extends ValueObject 
{
    ....

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
```

For custom error messages, use the `messages` method:

```php
use IsakzhanovR\ValueObject\ValueObject;

class Temperature extends ValueObject 
{
    ....

    protected function messages(): array
    {
        return [
            $this->key.'.between' => 'The range of tmp temperatures should be from -100 to +100',
        ];
    }
}
```

You can also declare an `authorize` method that returns `true` or `false`.

### Use in Model

To use `ValueObject` in Eloquent models, you do not need to add anything, just specify it in `$casts`.

Let's say you have an Eloquent `Whether` model . You may want to apply transformations to this field or get a value in degrees Celsius, Fahrenheit or Kelvin. If
you use this type of field in multiple models, copying and pasting getAttribute functions can be difficult.

Such valuable items become very useful. Let's see how to do this. First, we created a weather model that will have a temperature field brought to the
temperature value object.

```php
use Illuminate\Database\Eloquent\Model;

class Weather extends Model
{
    ....
    
    protected $casts = [
        ...
        'temperature' => Temperature::class, 
        ...
      ];
    
    ....
}    
```

We will add the following methods to the temperature object

```php
use IsakzhanovR\ValueObject\ValueObject;

class Temperature extends ValueObject 
{
    ....

    public function inCelsius()
    {
        return (float) $this->value();
    }

    public function inKelvin()
    {
        return (float) $this->value() + 273.15;
    }

    public function inFahrenheit()
    {
        return (float) $this->value() * 1.8 + 32;
    }
}
```

Objects with a value are stored in the database as a prime number and can be used as follows:

```php
    $weather = new Weather;
    $weather->temperature = new Temperature(9);
  
    echo $weather->temperature;                 // Prints '9'
    echo $weather->temperature->value();        // Prints '9'
    echo $weather->temperature->inKelvin();     // Prints 282.15
    echo $weather->temperature->inFahrenheit(); // Prints 48.2
```

To write to the model, you must use an instance of ValueObject:

```php
    $weather = new Weather;
    $weather->temperature = new Temperature(9);
    $weather->save()
    
//Or

    $temperature = new Temperature(9);
    $weather = Weather::create(compact('temperature'));
    
//Or
    $weather = Weather::create(Temperature::create(9)->toDTO());
```

You may also use Accessors and Mutators, just like in Eloquent Models.

```php
class Weather extends Model
{
    ....
    
    protected $casts = [
        ...
        'temperature' => Temperature::class, 
        ...
      ];
    
    protected $appends = ['celsius','kelvin','fahrenheit'] 
    
    public function getCelsiusAttribute()
    {
        return $this->temperature->inCelsius();
    }
    
    public function getKelvinAttribute()
    {
        return $this->temperature->inKelvin();
    }
    
    public function getFahrenheitAttribute()
    {
        return $this->temperature->inFahrenheit();
    }
}  
```

### Transform data

Data transformation is intended for minor manipulations with data, for example, clearing unnecessary characters. This method is performed before validation. The
`transformInput` function is also executed in the Eloquent model with both `get` and `set` methods

```php
use IsakzhanovR\ValueObject\ValueObject;

class Title extends ValueObject 
{
    protected function transformInput($value)
    {
        $value = trim(e($value));

        return mb_ucfirst(Str::lower($value));
    }
    
    ...
}
```

### Serialize and unserialize

Sometimes objects with a value may not be so simple and may require several fields instead of one. Let's say we have a `User` model with an address field that
contains the country, city, street and house number, this field in the database is of the `json` type. Then we could define the `User` model as we did before:

```php
class User extends Model
{
    protected $casts = [
        'address' => Address::class
    ];
}
```

Then we will be able to define the address value object as follows:

```php
use IsakzhanovR\ValueObject\ValueObject;

class Address extends ValueObject 
{
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
```

In order to write data, you need to convert an array to a json string and, accordingly, when reading this string from the database, you need to convert it back
to an array, for this we will need the `serialize` and `unserialize` static methods. These methods are executed only if they are declared.

```php
class Address Extends ValueObject
{
    ....
    
    public static function unserialize($value)
    {
        return json_decode($value, true);
    }

    public static function serialize($value)
    {
        return json_encode($value);
    }
    
    ....
} 

```

## License

This package is released under the[MIT License](LICENSE.md).

