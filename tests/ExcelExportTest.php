<?php
namespace Apie\Tests\Export;

use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\NonEmptyString;
use Apie\Export\ExcelExport;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class ExcelExportTest extends TestCase
{
    #[Test]
    public function it_can_create_an_excel_file(): void
    {
        $testItem = new ExcelExport();
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
        $this->assertValidExcel($contents);
    }

    private function assertValidExcel(string $contents): void
    {
        $this->assertStringStartsWith('PK', $contents); // ZIP files start with
        
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
            file_put_contents($tempFile, $contents);
            //            file_put_contents(__DIR__ . '/' . md5($contents) . '.xlsx', $contents);
            try {
                $res = $zip->open($tempFile);
                $this->assertTrue($res === true, 'Failed to open generated XLSX as ZIP archive');
                $this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'), 'Sheet1.xml not found in XLSX');
                $this->assertNotFalse($zip->locateName('xl/worksheets/sheet2.xml'), 'Sheet2.xml not found in XLSX');
                $zip->close();
            } finally {
                @unlink($tempFile);
            }
        }
    }

    #[Test]
    public function it_can_create_an_excel_file_from_intermediate_data(): void
    {
        $testItem = new ExcelExport();
        $stream = $testItem->streamFromSheets([
            'First Sheet' => (function () {
                yield ['Alice', 30, null, true, false];
                yield ['Bob', 25, [1,2,3], new ItemList([3,2,1]), NonEmptyString::fromNative('pi')];
            })(),
            'Second sheet' => [],
        ], 'test_export.xlsx');
        $this->assertTrue($stream->isReadable());
        // $this->assertFalse($stream->isWritable());
        $contents = $stream->getContents();
        $this->assertValidExcel($contents);
    }
}
