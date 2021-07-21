<?php

namespace CodingSaxony\ArrayToDataTransferObject\Converters;

/**
 * Class IntegerToBool
 *
 * @package CodingSaxony\ArrayToDataTransferObject\Converters
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