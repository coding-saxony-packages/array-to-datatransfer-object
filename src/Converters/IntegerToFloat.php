<?php

namespace CodingSaxony\ArrayToDataTransferObject\Converters;

/**
 * Class IntegerToFloat
 *
 * @package CodingSaxony\ArrayToDataTransferObject\Converters
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