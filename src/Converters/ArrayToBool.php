<?php

namespace CodingSaxony\Converters;

/**
 * Class ArrayToBool
 *
 * @package CodingSaxony\Converters
 */
class ArrayToBool
{
    /**
     * @param array $value
     *
     * @return bool
     */
    public static function convert(array $value): bool
    {
        $convertedValue = false;

        if (empty($value) === false) {
            $convertedValue = (bool) $value[0];
        }

        return $convertedValue;
    }
}