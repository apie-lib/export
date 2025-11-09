<?php
namespace Apie\Export;

use Apie\Export\Concerns\FlattensValues;
use Apie\Export\Lists\FileExtensionList;
use Apie\Export\ValueObjects\FileExtension;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use UnitEnum;
use ZipStream\ZipStream;

class ZippedCsvExport implements ExportInterface
{
    use FlattensValues;
    
    public function streamFromSheets(array $sheets, string $outputFilename = 'export.zip'): StreamInterface
    {
        // Sanitize and reindex sheet names
        $sheetNames = [];
        $i = 1;
        foreach ($sheets as $name => $gen) {
            // remove path separators and other chars that are problematic in filenames
            $clean = preg_replace('/[\\\\\/\?\*\[\]:]/', '', (string)$name);
            $clean = $clean === '' ? "Sheet{$i}" : $clean;
            // limit filename length to 200 chars to be safe
            $sheetNames[] = substr($clean, 0, 200);
            $i++;
        }

        // Initialize ZIP stream
        $stream = fopen('php://temp', 'r+');
        $outputStream = new Stream($stream);

        $zip = new ZipStream(
            outputName: $outputFilename,
            outputStream: $outputStream,
            sendHttpHeaders: false,
        );

        // Add each sheet as an individual CSV file
        $index = 1;
        $usedFilenames = [];
        foreach ($sheets as $name => $rowsGenerator) {
            $filenameBase = $sheetNames[$index - 1] . '.csv';
            $filename = $filenameBase;
            $occ = 1;
            while (in_array($filename, $usedFilenames, true)) {
                $filename = pathinfo($filenameBase, PATHINFO_FILENAME) . "({$occ}).csv";
                $occ++;
            }
            $usedFilenames[] = $filename;

            $zip->addFileFromCallback($filename, function () use ($rowsGenerator) {
                $stream = fopen('php://temp', 'r+');
                // Add UTF-8 BOM to help Excel detect UTF-8
                fwrite($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));

                foreach ($rowsGenerator as $row) {
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
                    fputcsv($stream, $converted, ',', '"');
                }

                rewind($stream);
                return $stream;
            });

            $index++;
        }

        $zip->finish();
        rewind($stream);
        return $outputStream;
    }

   

    public function getSupportedExtensions(): FileExtensionList
    {
        return new FileExtensionList([
            new FileExtension('zip'),
        ]);
    }
}
