<?php

namespace CodingSaxony\ArrayToDataTransferObject\Converters;

/**
 * Class ArrayToInt
 *
 * @package CodingSaxony\ArrayToDataTransferObject\Converters
 */
class ArrayToInt
{
    /**
     * @param array $value
     *
     * @return int
     */
    public static function convert(array $value): int
    {
        $convertedValue = 0;

        if (empty($value) === false) {
            $convertedValue = (int) $value[0];
        }

        return $convertedValue;
    }
}