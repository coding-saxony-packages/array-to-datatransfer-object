<?php

namespace CodingSaxonyPackages\ArrayToDataTransferObject\Converters;

/**
 * Class StringToInt
 *
 * @package CodingSaxonyPackages\ArrayToDataTransferObject\Converters
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