<?php
namespace Apie\Export\Concerns;

use Apie\Core\ValueObjects\Utils;
use UnitEnum;

trait FlattensValues
{
     private function toSingleValue(mixed $input): string|int|float|bool|null
    {
        $input = Utils::toNative($input);
        if ($input instanceof UnitEnum) {
            return $input->name;
        }
        if (is_array($input)) {
            return implode(', ', array_map([$this, 'toSingleValue'], $input));
        }

        return $input;
    }
}