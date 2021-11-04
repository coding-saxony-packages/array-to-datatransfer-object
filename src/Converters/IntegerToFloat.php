<?php

namespace CodingSaxonyPackages\ArrayToDataTransferObject\Converters;

/**
 * Class IntegerToFloat
 *
 * @package CodingSaxonyPackages\ArrayToDataTransferObject\Converters
 */
class IntegerToFloat
{
    /**
     * @param int $value
     *
     * @return float
     */
    public static function convert(int $value): float
    {
        return floatval($value);
    }
}