<?php

namespace CodingSaxonyPackages\ArrayToDataTransferObject\Converters;

/**
 * Class StringToBool
 *
 * @package CodingSaxonyPackages\ArrayToDataTransferObject\Converters
 */
class StringToBool
{
    /**
     * @param string $value
     *
     * @return bool
     */
    public static function convert(string $value): bool
    {
        return boolval($value);
    }
}