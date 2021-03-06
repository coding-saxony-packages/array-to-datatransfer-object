<?php

namespace CodingSaxonyPackages\ArrayToDataTransferObject\Converters;

/**
 * Class ArrayToString
 *
 * @package CodingSaxonyPackages\ArrayToDataTransferObject\Converters
 */
class ArrayToString
{
    /**
     * @param array $value
     *
     * @return string
     */
    public static function convert(array $value): string
    {
        $convertedValue = '';

        if (empty($value) === false) {
            $convertedValue = implode(',', $value);
        }

        return $convertedValue;
    }
}