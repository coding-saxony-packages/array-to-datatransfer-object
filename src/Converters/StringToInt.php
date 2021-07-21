<?php

namespace CodingSaxony\ArrayToDataTransferObject\Converters;

/**
 * Class StringToInt
 *
 * @package CodingSaxony\ArrayToDataTransferObject\Converters
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