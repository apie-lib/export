<?php
namespace Apie\Tests\Export;

use Apie\Export\OdsExport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OdsExportTest extends TestCase
{
    #[Test]
    public function it_can_create_an_ods_file(): void
    {
        $testItem = new OdsExport();
        $stream = $testItem->streamFromSheets([
            'First Sheet' => (function () {
                yield ['Alice', 30, null, true, false];
                yield ['Bob', 25, [1, 2, 3], 'pi'];
            })(),
            'Second Sheet' => (function () {
                yield ['Product', 'Price'];
                yield ['Book', 12.99];
                yield ['Pen', 1.99];
            })(),
        ], 'test_export.ods');

        $this->assertTrue($stream->isReadable());
        $this->assertNotSame('', $stream->getContents());
    }
}
