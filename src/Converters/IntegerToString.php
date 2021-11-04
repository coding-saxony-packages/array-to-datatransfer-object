<?php

namespace CodingSaxonyPackages\ArrayToDataTransferObject\Converters;

/**
 * Class IntegerToString
 *
 * @package CodingSaxonyPackages\ArrayToDataTransferObject\Converters
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