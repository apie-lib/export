<?php
namespace Apie\Export\Lists;

use Apie\Core\Lists\ItemList;
use Apie\Export\ValueObjects\FileExtension;

class FileExtensionList extends ItemList
{
    public function offsetGet(mixed $offset): FileExtension
    {
        return parent::offsetGet($offset);
    }
}