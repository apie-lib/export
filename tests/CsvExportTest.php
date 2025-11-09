<?php
namespace Apie\Tests\Export;

use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\NonEmptyString;
use Apie\Export\CsvExport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CsvExportTest extends TestCase
{
    #[Test]
    public function it_can_create_a_csv_file(): void
    {
        $testItem = new CsvExport();
        $stream = $testItem->streamFromSheets([
            'First Sheet' => (function () {
                yield ['Alice', 30, null, true, false];
                yield ['Bob', 25, [1,2,3], new ItemList([3,2,1]), NonEmptyString::fromNative('pi')];
            })(),
        ], 'test_export.csv');
        $this->assertTrue($stream->isReadable());
        // $this->assertFalse($stream->isWritable());
        $contents = $stream->getContents();
        $fixtureFile = __DIR__ . '/../fixtures/CsvExportTest.csv';
        //file_put_contents($fixtureFile, $contents);
        $this->assertEquals(file_get_contents($fixtureFile), $contents);
    }

    #[Test]
    public function it_can_not_create_a_csv_file_from_multiple_sheets(): void
    {
        $testItem = new CsvExport();
        $this->expectExceptionMessage('I can only export one sheet with CSV!');
        $testItem->streamFromSheets(['a' => [], 'b' => []]);
    }
}
