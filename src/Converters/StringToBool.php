<?php

namespace CodingSaxony\Converters;

/**
 * Class StringToBool
 *
 * @package CodingSaxony\Converters
 */
class StringToBool
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public static function convert(string $value): bool
    {
        return (bool) $value;
    }
}