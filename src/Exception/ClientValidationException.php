<?php
/*
 * This file is part of the fw4/sweepbright-api library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SweepBright\Exception;

class ClientValidationException extends \Exception
{
    public static function forExpectedType(string $classname, string $property, string $expected_type, $value): self
    {
        $value_type = (is_object($value) ? get_class($value) : gettype($value));
        $message = sprintf('%s::%s expects %s, got %s instead', $classname, $property, $expected_type, $value_type);
        throw new ClientValidationException($message);
    }
}
