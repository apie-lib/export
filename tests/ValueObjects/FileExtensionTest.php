<?php
namespace Apie\Tests\Export\ValueObjects;

use Apie\Export\ValueObjects\FileExtension;
use Apie\Fixtures\TestHelpers\ValueObjectTestCase;

class FileExtensionTest extends ValueObjectTestCase
{
    public static function className(): string
    {
        return FileExtension::class;
    }

    public static function provideFromNative(): array
    {
        return [
            'regular extension' => ['csv', 'csv'],
            'double extension' => ['tar.gz', 'tar.gz'],
        ];
    }

    public static function getOpenApiSchemaForCreation(): array
    {
        return [
            'type' => 'string',
            'format' => 'fileextension',
            'pattern' => true,
        ];
    }

}
