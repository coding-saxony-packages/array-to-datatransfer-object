<?php

namespace CodingSaxony\ArrayToDataTransferObject\Converters;

/**
 * Class IntegerToString
 *
 * @package CodingSaxony\ArrayToDataTransferObject\Converters
 */
class IntegerToString
{
    /**
     * @param int $value
     *
     * @return string
     */
    public static function convert(int $value): string
    {
        return (string) $value;
    }
}