<?php
namespace Apie\Export\ValueObjects;

use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\IsStringWithRegexValueObject;

class FileExtension implements StringValueObjectInterface
{
    use IsStringWithRegexValueObject;

    public static function getRegularExpression(): string
    {
        return '/^[a-zA-Z0-9]{1,10}(\.[a-zA-Z0-9]{1,10})*$/';
    }
}