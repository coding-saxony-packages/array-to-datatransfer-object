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
     * @return bool
     */
    public static function convert(int $value): bool
    {
        return boolval($value);
    }
}