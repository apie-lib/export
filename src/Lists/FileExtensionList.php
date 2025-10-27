<?php
namespace Apie\Export\Lists;

use Apie\Core\Lists\ItemList;
use Apie\Export\ValueObjects\FileExtension;

class FileExtensionList extends ItemList
{
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof FileExtension) {
            $value = FileExtension::fromNative($value);
        }
        parent::offsetSet($offset, $value);
    }

    public function offsetGet(mixed $offset): FileExtension
    {
        return parent::offsetGet($offset);
    }
}
