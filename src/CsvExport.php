<?php
namespace Apie\Export;

use Apie\Export\Concerns\FlattensValues;
use Apie\Export\Lists\FileExtensionList;
use Apie\Export\ValueObjects\FileExtension;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use UnitEnum;

class CsvExport implements ExportInterface
{
    use FlattensValues;

    public function streamFromSheets(array $sheets, string $outputFilename = 'export.csv'): StreamInterface
    {
        if (count($sheets) !== 1) {
            throw new \LogicException("I can only export one sheet with CSV!");
        }
        $data = reset($sheets);
        $stream = fopen('php://temp', 'r+');
        // Add UTF-8 BOM to help Excel detect UTF-8
        fwrite($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
        $outputStream = new Stream($stream);
        foreach ($data as $row) {
            // If row is Traversable or object, try to convert to array
            if (is_object($row) && !$row instanceof \Stringable && !$row instanceof UnitEnum) {
                if ($row instanceof \Traversable) {
                    $row = iterator_to_array($row);
                } else {
                    // cast to array will extract properties; prefer that to avoid errors
                    $row = (array)$row;
                }
            }
            if (!is_array($row)) {
                // single value row -> wrap
                $row = [$row];
            }

            $converted = [];
            foreach ($row as $cell) {
                $cell = $this->toSingleValue($cell);
                // fputcsv expects strings/numbers/null
                if ($cell === null) {
                    $converted[] = '';
                } else {
                    $converted[] = (string)$cell;
                }
            }
            // Use fputcsv for proper escaping
            fputcsv($stream, $converted, ',', '"', '\\');
        }

        rewind($stream);
        return $outputStream;
    }

    public function getSupportedExtensions(): FileExtensionList
    {
        return new FileExtensionList([
            new FileExtension('csv'),
        ]);
    }
}
