<?php
namespace Apie\Tests\Export;

use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\NonEmptyString;
use Apie\Export\ZippedCsvExport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class ZippedCsvExportTest extends TestCase
{
    #[Test]
    public function it_can_create_an_excel_file(): void
    {
        $testItem = new ZippedCsvExport();
        $stream = $testItem->streamFromSheets([
            'First Sheet' => (function () {
                yield ['Name', 'Age'];
                yield ['Alice', 30];
                yield ['Bob', 25];
            })(),
            'Second Sheet' => (function () {
                yield ['Product', 'Price'];
                yield ['Book', 12.99];
                yield ['Pen', 1.99];
            })(),
        ], 'test_export.xlsx');
        $this->assertTrue($stream->isReadable());
        // $this->assertFalse($stream->isWritable());
        $contents = $stream->getContents();
        $this->assertValidCsv($contents);
    }

    private function assertValidCsv(string $contents): void
    {
        $this->assertStringStartsWith('PK', $contents); // ZIP files start with
        
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            $tempFile = tempnam(sys_get_temp_dir(), 'zip');
            file_put_contents($tempFile, $contents);
            //            file_put_contents(__DIR__ . '/' . md5($contents) . '.zip', $contents);
            try {
                $res = $zip->open($tempFile);
                $this->assertTrue($res === true, 'Failed to open generated ZIP as ZIP archive');
                $this->assertNotFalse($zip->locateName('First Sheet.csv'), 'First Sheet.csv not found in ZIP');
                $this->assertNotFalse($zip->locateName('Second Sheet.csv'), 'Second sheet.csv not found in ZIP');
                $zip->close();
            } finally {
                @unlink($tempFile);
            }
        }
    }

    #[Test]
    public function it_can_create_an_excel_file_from_intermediate_data(): void
    {
        $testItem = new ZippedCsvExport();
        $stream = $testItem->streamFromSheets([
            'First Sheet' => (function () {
                yield ['Alice', 30, null, true, false];
                yield ['Bob', 25, [1,2,3], new ItemList([3,2,1]), NonEmptyString::fromNative('pi')];
            })(),
            'Second Sheet' => [],
        ], 'test_export.xlsx');
        $this->assertTrue($stream->isReadable());
        // $this->assertFalse($stream->isWritable());
        $contents = $stream->getContents();
        $this->assertValidCsv($contents);
    }
}
