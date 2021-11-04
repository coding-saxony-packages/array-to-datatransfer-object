<?php

namespace CodingSaxonyPackages\ArrayToDataTransferObject\Converters;

/**
 * Class IntegerToBool
 *
 * @package CodingSaxonyPackages\ArrayToDataTransferObject\Converters
 */
class IntegerToBool
{
    /**
     * @param int $value
     *
     * @return string
     */
    public static function convert(int $value): string
    {
        return (bool) $value;
    }
}