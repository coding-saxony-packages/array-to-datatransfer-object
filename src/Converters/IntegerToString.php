<?php


namespace CodingSaxony\Converters;


class IntegerToString
{
    public static function convert(int $value): string
    {
        return (string) $value;
    }
}