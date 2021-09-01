<?php


namespace Tests\ValueObjects;




use IsakzhanovR\ValueObject\ValueObject;

class RomanNumber extends ValueObject
{
    protected function transformInput($value)
    {
        if (is_numeric($value)) {
            return $this->numberToRoman($value);
        }

        return $this->romanToNumber($value);
    }

    protected function rules(): array
    {
        return [];
    }

    private function numberToRoman(int $value)
    {
        $result  = '';
        $array   = ['X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $integer = intval($value);


        foreach ($array as $roman => $number) {
            $matches = intval($integer / $number);
            $result  .= str_repeat($roman, $matches);
            $integer = $integer % $number;
        }

        return $result;
    }

    private function romanToNumber(string $value)
    {
        $conv   = [
            ["letter" => 'I', "number" => 1],
            ["letter" => 'V', "number" => 5],
            ["letter" => 'X', "number" => 10],
            ["letter" => 'L', "number" => 50],
            ["letter" => 'C', "number" => 100],
            ["letter" => 'D', "number" => 500],
            ["letter" => 'M', "number" => 1000],
            ["letter" => 0, "number" => 0],
        ];
        $arabic = 0;
        $state  = 0;
        $len    = strlen($value);

        while ($len >= 0) {
            $i    = 0;
            $sidx = $len;

            while ($conv[$i]['number'] > 0) {
                if (strtoupper(@$value[$sidx]) == $conv[$i]['letter']) {
                    if ($state > $conv[$i]['number']) {
                        $arabic -= $conv[$i]['number'];
                    } else {
                        $arabic += $conv[$i]['number'];
                        $state  = $conv[$i]['number'];
                    }
                }
                $i++;
            }

            $len--;
        }

        return ($arabic);
    }
}
