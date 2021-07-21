<?php

namespace CodingSaxony\Converters;

/**
 * Class StringToInt
 *
 * @package CodingSaxony\Converters
 */
class StringToInt
{
    /**
     * @param string $value
     *
     * @return int
     */
    public static function convert(string $value): int
    {
        return (int) $value;
    }
}